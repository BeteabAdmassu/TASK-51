<?php

namespace App\Notifications\Channels;

use App\Models\Notification;

class InAppChannel implements NotificationChannelInterface
{
    public function deliver(Notification $notification): void
    {
    }
}
