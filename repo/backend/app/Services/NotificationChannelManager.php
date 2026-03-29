<?php

namespace App\Services;

use App\Notifications\Channels\EmailChannel;
use App\Notifications\Channels\InAppChannel;
use App\Notifications\Channels\NotificationChannelInterface;

class NotificationChannelManager
{
    /**
     * @return array<int, NotificationChannelInterface>
     */
    public function activeChannels(): array
    {
        $names = config('roadlink.channels', ['in_app']);
        $channels = [];

        foreach ($names as $name) {
            $channel = match ($name) {
                'in_app' => app(InAppChannel::class),
                'email' => app(EmailChannel::class),
                default => null,
            };

            if ($channel instanceof NotificationChannelInterface) {
                $channels[] = $channel;
            }
        }

        return $channels;
    }
}
