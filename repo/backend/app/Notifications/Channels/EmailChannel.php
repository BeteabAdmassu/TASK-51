<?php

namespace App\Notifications\Channels;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class EmailChannel implements NotificationChannelInterface
{
    public function deliver(Notification $notification): void
    {
        $user = $notification->user;

        Log::debug(sprintf('Would send email to %s: %s', (string) $user?->email, $notification->title));
    }
}
