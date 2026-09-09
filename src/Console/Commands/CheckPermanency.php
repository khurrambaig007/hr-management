<?php

namespace Khurrambaig\HRManagement\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Khurrambaig\HRManagement\Models\Employee;
use Khurrambaig\HRManagement\Services\AccrualService;

class CheckPermanency extends Command
{
    protected $signature = 'hr:check-permanency';

    protected $description = 'Confirm employees whose probation period has ended and unlock their full leave quota.';

    public function handle(AccrualService $accrual): int
    {
        $candidates = Employee::query()
            ->where('is_permanent', false)
            ->whereNotNull('probation_end_date')
            ->where('probation_end_date', '<=', Carbon::today()->toDateString())
            ->get();

        $confirmed = 0;

        foreach ($candidates as $employee) {
            $employee->is_permanent = true;
            $employee->save();

            $accrual->syncQuota($employee);
            $confirmed++;
        }

        $this->info("Confirmed {$confirmed} employee(s) as permanent.");

        return self::SUCCESS;
    }
}