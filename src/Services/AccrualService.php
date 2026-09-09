<?php

namespace Khurrambaig\HRManagement\Services;

use Carbon\Carbon;
use Khurrambaig\HRManagement\Models\Employee;
use Khurrambaig\HRManagement\Models\LeaveBalance;
use Khurrambaig\HRManagement\Models\LeaveSettlement;

class AccrualService
{
    /**
     * The 12-month leave window that contains the given date, anchored to the
     * employee's hire anniversary.
     *
     * @return array{year_start: string, year_end: string}
     */
    public function windowContaining(Employee $employee, Carbon|string $date): array
    {
        return $this->windowForDate($employee, Carbon::parse($date));
    }

    /**
     * The leave window the employee is currently inside.
     *
     * @return array{year_start: string, year_end: string}
     */
    public function currentWindow(Employee $employee): array
    {
        return $this->windowForDate($employee, Carbon::today());
    }

    /**
     * The window immediately following the given one.
     *
     * @return array{year_start: string, year_end: string}
     */
    public function nextWindow(array $window): array
    {
        $start = Carbon::parse($window['year_start'])->addYear();

        return [
            'year_start' => $start->toDateString(),
            'year_end' => $start->copy()->addYear()->subDay()->toDateString(),
        ];
    }

    /**
     * Resolve the settlement that governs a given window. Falls back to config.
     */
    public function settlementFor(array $window): LeaveSettlement
    {
        $year = Carbon::parse($window['year_start'])->year;

        return LeaveSettlement::forYear($year)
            ?? $this->fallbackSettlement();
    }

    /**
     * The number of leave days an employee may currently take inside a window.
     */
    public function allowanceFor(Employee $employee, array $window): float
    {
        $settlement = $this->settlementFor($window);

        if ($this->isPermanent($employee)) {
            return (float) $settlement->total_days;
        }

        return (float) $settlement->probation_cap;
    }

    /**
     * Get (or lazily create) the balance ledger row for an employee + window.
     */
    public function getOrCreateBalance(Employee $employee, array $window): LeaveBalance
    {
        $balance = LeaveBalance::query()
            ->where('user_id', $employee->user_id)
            ->whereDate('year_start', Carbon::parse($window['year_start'])->toDateString())
            ->first();

        if (! $balance) {
            $balance = LeaveBalance::create([
                'user_id' => $employee->user_id,
                'year_start' => $window['year_start'],
                'year_end' => $window['year_end'],
                'total_days' => $this->allowanceFor($employee, $window),
                'used_days' => 0,
            ]);
        }

        return $balance;
    }

    /**
     * Keep the ledger in sync with the employee's current status
     * (e.g. unlock the full quota once the employee becomes permanent).
     */
    public function syncQuota(Employee $employee): LeaveBalance
    {
        $balance = $this->getOrCreateBalance($employee, $this->currentWindow($employee));

        $allowance = $this->allowanceFor($employee, $this->currentWindow($employee));

        if ((float) $balance->total_days !== $allowance) {
            $balance->total_days = $allowance;
            $balance->save();
        }

        return $balance;
    }

    /**
     * Split a leave span into the days chargeable per leave window.
     *
     * @return array<int, array{year_start: string, year_end: string, days: int}>
     */
    public function splitAcrossWindows(Employee $employee, Carbon|string $start, Carbon|string $end): array
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        $chunks = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $window = $this->windowContaining($employee, $cursor);
            $windowEnd = Carbon::parse($window['year_end']);
            $chunkEnd = $end->lt($windowEnd) ? $end->copy() : $windowEnd->copy();

            $days = (int) $cursor->diffInDays($chunkEnd) + 1;
            $key = $window['year_start'];

            if (! isset($chunks[$key])) {
                $chunks[$key] = [
                    'year_start' => $window['year_start'],
                    'year_end' => $window['year_end'],
                    'days' => 0,
                ];
            }

            $chunks[$key]['days'] += $days;
            $cursor = $chunkEnd->addDay();
        }

        return array_values($chunks);
    }

    public function isPermanent(Employee $employee): bool
    {
        return (bool) $employee->is_permanent;
    }

    /**
     * @return array{year_start: string, year_end: string}
     */
    protected function windowForDate(Employee $employee, Carbon $date): array
    {
        $anchor = $employee->hire_date
            ? Carbon::parse($employee->hire_date)
            : ($employee->created_at?->toDateString() ? Carbon::parse($employee->created_at) : Carbon::today());

        $anniversary = $anchor->copy()->month((int) $anchor->month)->day(min((int) $anchor->day, 28))->year((int) $date->year);

        if ($date->lt($anniversary)) {
            $yearStart = $anniversary->copy()->subYear();
        } else {
            $yearStart = $anniversary->copy();
        }

        return [
            'year_start' => $yearStart->toDateString(),
            'year_end' => $yearStart->copy()->addYear()->subDay()->toDateString(),
        ];
    }

    protected function fallbackSettlement(): LeaveSettlement
    {
        $settlement = new LeaveSettlement;

        $settlement->year = Carbon::today()->year;
        $settlement->total_days = config('hr-management.quota.total_days', 20);
        $settlement->probation_cap = config('hr-management.quota.probation_cap', 2);
        $settlement->is_active = true;

        return $settlement;
    }
}