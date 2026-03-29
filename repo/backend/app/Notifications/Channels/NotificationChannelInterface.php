<?php

namespace App\Notifications\Channels;

use App\Models\Notification;

interface NotificationChannelInterface
{
    public function deliver(Notification $notification): void;
}
