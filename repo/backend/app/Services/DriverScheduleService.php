<?php

namespace App\Services;

use App\Models\RideOrder;
use App\Models\User;

class DriverScheduleService
{
    public function hasOverlap(User $driver, RideOrder $targetOrder): bool
    {
        return RideOrder::query()
            ->where('driver_id', $driver->id)
            ->whereIn('status', ['accepted', 'in_progress'])
            ->where('id', '!=', $targetOrder->id)
            ->where('time_window_start', '<', $targetOrder->time_window_end)
            ->where('time_window_end', '>', $targetOrder->time_window_start)
            ->exists();
    }
}
