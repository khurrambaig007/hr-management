<?php

namespace Khurrambaig\HRManagement\Tests\Feature;

use Khurrambaig\HRManagement\Models\Employee;
use Khurrambaig\HRManagement\Services\EmployeeService;
use Khurrambaig\HRManagement\Tests\TestCase;
use Spatie\Permission\Models\Role;

class SpatieRolesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('hr-management.roles_source', 'spatie');
    }

    public function test_spatie_role_grants_hr_authorization_and_filtering(): void
    {
        $employee = $this->makeEmployee(['role' => 'employee']);

        $employee->user->assignRole(Role::findOrCreate('admin'));

        $this->assertTrue($employee->fresh()->isAdmin());
        $this->assertFalse($employee->fresh()->isManager());
        $this->assertSame(1, Employee::whereHrRole('admin')->count());
        $this->assertSame(0, Employee::whereHrRole('manager')->count());
    }

    public function test_employee_save_syncs_spatie_roles_and_keeps_the_column(): void
    {
        $employee = $this->makeEmployee(['role' => 'employee']);

        $employee->user->assignRole(Role::findOrCreate('employee'));

        $this->app->make(EmployeeService::class)->update($employee, ['role' => 'manager']);

        $employee->refresh();
        $user = $employee->user->fresh();

        $this->assertSame('manager', $employee->role);
        $this->assertTrue($user->hasRole('manager'));
        $this->assertFalse($user->hasRole('employee'));
    }

    public function test_column_is_used_until_the_user_has_a_spatie_role(): void
    {
        $employee = $this->makeEmployee(['role' => 'admin']);

        $this->assertTrue($employee->isAdmin());
        $this->assertSame(1, Employee::whereHrRole('admin')->count());
    }

    public function test_roles_outside_the_map_do_not_grant_hr_access(): void
    {
        $employee = $this->makeEmployee(['role' => 'employee']);

        $employee->user->assignRole(Role::findOrCreate('accountant'));

        $this->assertTrue($employee->fresh()->isEmployee());
        $this->assertFalse($employee->fresh()->isAdmin());
    }

    public function test_custom_spatie_role_names_are_mapped(): void
    {
        config()->set('hr-management.spatie.role_map.admin', 'hr-admin');

        $employee = $this->makeEmployee(['role' => 'employee']);

        $employee->user->assignRole(Role::findOrCreate('hr-admin'));

        $this->assertTrue($employee->fresh()->isAdmin());
        $this->assertSame(1, Employee::whereHrRole('admin')->count());
    }

    public function test_sync_command_mirrors_existing_employees(): void
    {
        $employee = $this->makeEmployee(['role' => 'admin']);

        $this->artisan('hr:sync-roles')->assertSuccessful();

        $this->assertTrue($employee->user->fresh()->hasRole('admin'));
    }

    public function test_employee_column_stays_authoritative_when_source_is_employee(): void
    {
        config()->set('hr-management.roles_source', 'employee');

        $employee = $this->makeEmployee(['role' => 'employee']);

        $employee->user->assignRole(Role::findOrCreate('admin'));

        $this->assertFalse($employee->fresh()->isAdmin());
        $this->assertTrue($employee->fresh()->isEmployee());
    }
}
