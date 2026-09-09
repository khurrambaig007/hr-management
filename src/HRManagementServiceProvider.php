<?php

namespace Khurrambaig\HRManagement;

use Illuminate\Support\ServiceProvider;
use Khurrambaig\HRManagement\Console\Commands\CheckPermanency;
use Khurrambaig\HRManagement\Console\Commands\GrantAnniversaryQuota;
use Khurrambaig\HRManagement\Http\Middleware\EnsureHRRole;
use Khurrambaig\HRManagement\Services\AccrualService;
use Khurrambaig\HRManagement\Services\AttendanceCorrectionService;
use Khurrambaig\HRManagement\Services\AttendanceService;
use Khurrambaig\HRManagement\Services\EmployeeService;
use Khurrambaig\HRManagement\Services\LeaveService;

class HRManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/hr-management.php', 'hr-management');

        $this->app->singleton(HRManagement::class, fn () => new HRManagement);
        $this->app->singleton('hr-management', HRManagement::class);

        $this->app->singleton(AccrualService::class);
        $this->app->singleton(AttendanceService::class);
        $this->app->singleton(AttendanceCorrectionService::class);
        $this->app->singleton(LeaveService::class, fn ($app) => new LeaveService($app->make(AccrualService::class)));
        $this->app->singleton(EmployeeService::class, fn ($app) => new EmployeeService($app->make(AccrualService::class)));
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'hr');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/hr-management.php' => config_path('hr-management.php'),
        ], 'hr-management-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'hr-management-migrations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/hr'),
        ], 'hr-management-views');

        $this->publishes([
            __DIR__.'/../database/seeders/HRDepartmentSeeder.php' => database_path('seeders/HRDepartmentSeeder.php'),
        ], 'hr-management-seeds');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckPermanency::class,
                GrantAnniversaryQuota::class,
            ]);
        }

        $this->app['router']->aliasMiddleware('ensure-hr-role', EnsureHRRole::class);
    }
}