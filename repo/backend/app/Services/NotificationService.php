<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationFrequencyLog;
use App\Models\NotificationSubscription;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotificationService
{
    private ?bool $supportsFrequencyTypeColumn = null;

    public function __construct(private readonly NotificationChannelManager $channelManager)
    {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function send(User $user, string $type, string $title, string $body, array $data = [], ?string $groupKey = null): ?Notification
    {
        $priority = $this->determinePriority($user, $type, $data);

        if ($this->isSuppressed($user, $priority, $type)) {
            return null;
        }

        /** @var Notification $notification */
        $notification = DB::transaction(function () use ($user, $type, $priority, $title, $body, $data, $groupKey): Notification {
            $notification = Notification::query()->create([
                'user_id' => $user->id,
                'type' => $type,
                'priority' => $priority,
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'group_key' => $groupKey,
                'is_read' => false,
                'read_at' => null,
                'created_at' => now(),
            ]);

            $this->recordFrequencyLog($user, $priority, $type);

            return $notification;
        });

        $notification->loadMissing('user');

        foreach ($this->channelManager->activeChannels() as $channel) {
            $channel->deliver($notification);
        }

        return $notification;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function determinePriority(User $user, string $type, array $data): string
    {
        if (in_array($type, ['system', 'moderation'], true)) {
            return 'high';
        }

        $entityType = $data['entity_type'] ?? null;
        $entityId = $data['entity_id'] ?? null;

        if (is_string($entityType) && (is_int($entityId) || ctype_digit((string) $entityId))) {
            $isSubscribed = NotificationSubscription::query()
                ->where('user_id', $user->id)
                ->where('entity_type', $entityType)
                ->where('entity_id', (int) $entityId)
                ->exists();

            if ($isSubscribed) {
                return 'high';
            }
        }

        return 'normal';
    }

    private function isSuppressed(User $user, string $priority, string $type): bool
    {
        if ($priority === 'high') {
            $count = NotificationFrequencyLog::query()
                ->where('user_id', $user->id)
                ->where('priority', 'high')
                ->where('created_at', '>=', now()->subHour())
                ->count();

            return $count >= 3;
        }

        $count = $this->countNormalNotifications($user, $type);

        return $count >= 20;
    }

    private function countNormalNotifications(User $user, string $type): int
    {
        if ($this->supportsTypeColumn()) {
            try {
                return NotificationFrequencyLog::query()
                    ->where('user_id', $user->id)
                    ->where('priority', 'normal')
                    ->where('type', $type)
                    ->whereDate('created_at', now()->toDateString())
                    ->count();
            } catch (QueryException $exception) {
                if (! $this->isMissingTypeColumnException($exception)) {
                    throw $exception;
                }

                $this->supportsFrequencyTypeColumn = false;
            }
        }

        return NotificationFrequencyLog::query()
            ->where('user_id', $user->id)
            ->where('priority', 'normal')
            ->whereDate('created_at', now()->toDateString())
            ->count();
    }

    private function recordFrequencyLog(User $user, string $priority, string $type): void
    {
        if ($this->supportsTypeColumn()) {
            try {
                NotificationFrequencyLog::query()->create([
                    'user_id' => $user->id,
                    'priority' => $priority,
                    'type' => $type,
                    'created_at' => now(),
                ]);

                return;
            } catch (QueryException $exception) {
                if (! $this->isMissingTypeColumnException($exception)) {
                    throw $exception;
                }

                $this->supportsFrequencyTypeColumn = false;
            }
        }

        NotificationFrequencyLog::query()->create([
            'user_id' => $user->id,
            'priority' => $priority,
            'created_at' => now(),
        ]);
    }

    private function isMissingTypeColumnException(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, "unknown column 'type'")
            || str_contains($message, 'no such column: type')
            || str_contains($message, 'column "type" does not exist');
    }

    private function supportsTypeColumn(): bool
    {
        if ($this->supportsFrequencyTypeColumn !== null) {
            return $this->supportsFrequencyTypeColumn;
        }

        try {
            $this->supportsFrequencyTypeColumn = Schema::hasColumn('notification_frequency_logs', 'type');
        } catch (\Throwable) {
            $this->supportsFrequencyTypeColumn = false;
        }

        return $this->supportsFrequencyTypeColumn;
    }
}
