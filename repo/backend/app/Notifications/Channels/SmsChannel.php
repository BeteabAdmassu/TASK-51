<?php

namespace App\Notifications\Channels;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class SmsChannel implements NotificationChannelInterface
{
    public function deliver(Notification $notification): void
    {
        $user = $notification->user;

        Log::debug(sprintf('Would send SMS to user #%d: %s', (int) $user?->id, $notification->title));
    }
}
