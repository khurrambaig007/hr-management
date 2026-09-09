<?php

namespace Khurrambaig\HRManagement\Http\Traits;

use Khurrambaig\HRManagement\Models\Employee;

trait ProvidesEmployeeContext
{
    protected function hrEmployee(): ?Employee
    {
        $employee = request()->attributes->get('hr.employee');

        if ($employee instanceof Employee) {
            return $employee;
        }

        if (auth()->check()) {
            return Employee::where('user_id', auth()->id())->first();
        }

        return null;
    }

    protected function requiresEmployeeRecord(): Employee
    {
        abort_unless($this->hrEmployee() instanceof Employee, 403, 'No employee profile found for the current user.');

        return $this->hrEmployee();
    }

    protected function requireAdmin(): void
    {
        abort_unless($this->hrEmployee()?->isAdmin(), 403, 'You do not have permission to perform this action.');
    }

    protected function requireManager(): void
    {
        abort_unless($this->hrEmployee()?->canManageTeam(), 403, 'You do not have permission to perform this action.');
    }
}