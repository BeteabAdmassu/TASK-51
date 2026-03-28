<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RideOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DriverRideController extends Controller
{
    public function availableRides(Request $request): JsonResponse
    {
        $windowHours = (int) config('rides.available_window_hours', 2);
        $perPage = max(1, min((int) $request->query('per_page', 15), 50));

        $rides = RideOrder::query()
            ->where('status', 'matching')
            ->whereBetween('time_window_start', [
                now()->subHours($windowHours),
                now()->addHours($windowHours),
            ])
            ->select([
                'id',
                'origin_address',
                'destination_address',
                'rider_count',
                'time_window_start',
                'time_window_end',
                'notes',
                'status',
                'created_at',
                'updated_at',
            ])
            ->orderBy('time_window_start')
            ->paginate($perPage);

        Log::channel('app')->info(
            sprintf('Driver #%d viewed available rides (found %d)', $request->user()->id, $rides->total()),
            ['driver_id' => $request->user()->id, 'count' => $rides->total()]
        );

        return response()->json($rides);
    }

    public function myRides(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->query('per_page', 15), 50));

        $query = RideOrder::query()
            ->where('driver_id', $request->user()->id);

        if ($request->filled('status')) {
            $statuses = collect(explode(',', (string) $request->query('status')))
                ->map(fn (string $status) => trim($status))
                ->filter()
                ->values();

            if ($statuses->isNotEmpty()) {
                $query->whereIn('status', $statuses->all());
            }
        }

        $rides = $query
            ->with(['auditLogs' => fn ($audit) => $audit->orderBy('created_at')])
            ->orderByRaw("CASE WHEN status IN ('accepted', 'in_progress') THEN 1 WHEN status = 'completed' THEN 2 ELSE 3 END")
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        return response()->json($rides);
    }

    public function showMyRide(Request $request, RideOrder $rideOrder): JsonResponse
    {
        $this->authorize('driverAction', $rideOrder);

        $rideOrder->load([
            'auditLogs' => fn ($query) => $query->orderBy('created_at'),
            'rider:id,username',
            'driver:id,username',
        ]);

        return response()->json([
            'order' => $rideOrder,
        ]);
    }
}
