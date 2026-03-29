<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class ReadinessController extends Controller
{
    public function __invoke(): JsonResponse
    {
        /** @var \Illuminate\Database\Migrations\Migrator $migrator */
        $migrator = app('migrator');
        $requiredMigration = '2026_03_30_000100_add_type_to_notification_frequency_logs_table';
        $hasFrequencyTypeColumn = Schema::hasColumn('notification_frequency_logs', 'type');
        $pendingMigrations = $this->pendingMigrations($migrator);

        $isReady = $hasFrequencyTypeColumn;
        $statusCode = $isReady ? 200 : 503;

        return response()->json([
            'status' => $isReady ? 'ready' : 'degraded',
            'checks' => [
                'notification_frequency_type_column' => $hasFrequencyTypeColumn,
            ],
            'required_migrations' => [$requiredMigration],
            'pending_required_migrations' => in_array($requiredMigration.'.php', $pendingMigrations, true)
                ? [$requiredMigration]
                : [],
            'message' => $isReady
                ? 'Readiness checks passed.'
                : 'Schema drift detected for notification frequency logs. Run migrations before handling ride completion traffic.',
        ], $statusCode);
    }

    /**
     * @return array<int, string>
     */
    private function pendingMigrations(\Illuminate\Database\Migrations\Migrator $migrator): array
    {
        $paths = [database_path('migrations')];
        $files = $migrator->getMigrationFiles($paths);
        $ran = $migrator->getRepository()->getRan();

        return array_values(array_diff(array_keys($files), $ran));
    }
}
