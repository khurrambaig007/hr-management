<?php

namespace Khurrambaig\HRManagement\Console\Commands;

use Illuminate\Console\Command;
use Khurrambaig\HRManagement\Models\Employee;

class SyncSpatieRoles extends Command
{
    protected $signature = 'hr:sync-roles
                            {--user= : Sync only the given user id}';

    protected $description = 'Mirror employees.role values to spatie/laravel-permission roles';

    public function handle(): int
    {
        if (! Employee::spatieEnabled()) {
            $this->error('Spatie roles are not enabled. Install spatie/laravel-permission and set hr-management.roles_source to "spatie".');

            return self::FAILURE;
        }

        $query = Employee::query()->with('user');

        if ($userId = $this->option('user')) {
            $query->where('user_id', (int) $userId);
        }

        $count = 0;

        $query->each(function (Employee $employee) use (&$count) {
            Employee::syncSpatieRole($employee, $employee->role);
            $count++;
        });

        $this->info("Synced {$count} employee role(s) to Spatie.");

        return self::SUCCESS;
    }
}
