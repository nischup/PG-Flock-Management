<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services
        $this->app->singleton(\App\Services\NotificationService::class);
        $this->app->singleton(\App\Services\ApprovalMatrixService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inertia::share([
            'flash' => fn () => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }
}
