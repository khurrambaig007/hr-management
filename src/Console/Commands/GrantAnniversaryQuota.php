<?php

namespace Khurrambaig\HRManagement\Console\Commands;

use Illuminate\Console\Command;
use Khurrambaig\HRManagement\Models\Employee;
use Khurrambaig\HRManagement\Services\AccrualService;

class GrantAnniversaryQuota extends Command
{
    protected $signature = 'hr:grant-anniversary-quota {--user= : Process only the given user id}';

    protected $description = 'Ensure current and next leave-year balance rows exist for employees.';

    public function handle(AccrualService $accrual): int
    {
        $query = Employee::query();

        if ($this->option('user')) {
            $query->where('user_id', (int) $this->option('user'));
        }

        $employees = $query->get();

        $total = 0;

        foreach ($employees as $employee) {
            $current = $accrual->currentWindow($employee);
            $accrual->getOrCreateBalance($employee, $current);

            $accrual->getOrCreateBalance($employee, $accrual->nextWindow($current));

            $total++;
        }

        $this->info("Ensured leave quota for {$total} employee(s).");

        return self::SUCCESS;
    }
}