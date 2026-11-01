<?php

namespace Khurrambaig\HRManagement\Tests;

use Khurrambaig\HRManagement\HRManagementServiceProvider;
use Khurrambaig\HRManagement\Tests\User;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--database' => 'testing']);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');

        if (class_exists(\Spatie\Permission\PermissionServiceProvider::class)) {
            $this->loadMigrationsFrom($this->spatieMigrationsPath());
        }
    }

    protected function spatieMigrationsPath(): string
    {
        $source = dirname(__DIR__).'/vendor/spatie/laravel-permission/database/migrations/create_permission_tables.php.stub';
        $directory = sys_get_temp_dir().'/hr-management-spatie-migrations';
        $target = $directory.'/2024_01_01_000000_create_permission_tables.php';

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        if (! is_file($target) || filemtime($target) < filemtime($source)) {
            copy($source, $target);
        }

        return $directory;
    }

    protected function getPackageProviders($app)
    {
        return array_values(array_filter([
            HRManagementServiceProvider::class,
            class_exists(\Spatie\Permission\PermissionServiceProvider::class)
                ? \Spatie\Permission\PermissionServiceProvider::class
                : null,
        ]));
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('auth.providers.users.model', User::class);
    }

    protected function makeEmployee(array $attrs = []): \Khurrambaig\HRManagement\Models\Employee
    {
        $user = User::forceCreate([
            'name' => 'Test User',
            'email' => 'user'.uniqid().'@example.com',
            'password' => 'password',
        ]);

        return \Khurrambaig\HRManagement\Models\Employee::forceCreate(array_merge([
            'user_id' => $user->id,
            'role' => 'employee',
            'hire_date' => '2025-07-01',
            'probation_end_date' => '2026-10-01',
            'is_permanent' => false,
        ], $attrs));
    }

    protected function makeActiveSettlement(int $total = 20, int $probationCap = 2, int $year = 2026): \Khurrambaig\HRManagement\Models\LeaveSettlement
    {
        return \Khurrambaig\HRManagement\Models\LeaveSettlement::forceCreate([
            'year' => $year,
            'total_days' => $total,
            'probation_cap' => $probationCap,
            'is_active' => true,
        ]);
    }
}