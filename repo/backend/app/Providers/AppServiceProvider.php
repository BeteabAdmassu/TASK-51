<?php

namespace App\Providers;

use App\Models\RideOrder;
use App\Policies\RideOrderPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(RideOrder::class, RideOrderPolicy::class);

        if ($this->app->environment('testing')) {
            return;
        }

        try {
            if (Schema::hasTable('notification_frequency_logs') && ! Schema::hasColumn('notification_frequency_logs', 'type')) {
                Log::channel('app')->warning(
                    'Schema drift detected: notification_frequency_logs.type is missing. Run php artisan migrate to restore per-type notification suppression.'
                );
            }
        } catch (\Throwable $exception) {
            Log::channel('app')->warning('Skipping schema drift startup check due to database connectivity issue.', [
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
