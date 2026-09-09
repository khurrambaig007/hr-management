<?php

namespace Khurrambaig\HRManagement\Services;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Khurrambaig\HRManagement\Models\Employee;
use Khurrambaig\HRManagement\Models\Leave;
use Khurrambaig\HRManagement\Models\LeaveBalance;

class LeaveService
{
    public function __construct(protected AccrualService $accrual)
    {
    }

    /**
     * Apply for a leave request.
     *
     * @throws ValidationException
     */
    public function apply(int $userId, array $data): Leave
    {
        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);

        if ($end->lt($start)) {
            throw ValidationException::withMessages([
                'end_date' => 'End date must be on or after the start date.',
            ]);
        }

        if ($this->hasOverlap($userId, $start, $end)) {
            throw ValidationException::withMessages([
                'start_date' => 'You already have an approved or pending leave overlapping these dates.',
            ]);
        }

        $employee = Employee::where('user_id', $userId)->first();

        if (! $employee) {
            throw ValidationException::withMessages([
                'user_id' => 'No employee profile exists for this user.',
            ]);
        }

        $type = $data['type'];

        if ($type !== Leave::TYPE_UNPAID) {
            $this->assertQuotaAvailable($employee, $start, $end);
        }

        return Leave::create([
            'user_id' => $userId,
            'type' => $type,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'days' => $start->diffInDays($end) + 1,
            'reason' => $data['reason'],
            'status' => Leave::STATUS_PENDING,
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function approve(Leave $leave, int $approverUserId): Leave
    {
        if (! $leave->isPending()) {
            throw ValidationException::withMessages([
                'leave' => 'Only pending leaves can be approved.',
            ]);
        }

        $leave->update([
            'status' => Leave::STATUS_APPROVED,
            'approved_by' => $approverUserId,
        ]);

        $employee = $leave->employee;

        if ($employee && $leave->type !== Leave::TYPE_UNPAID) {
            $this->chargeWindows($employee, $leave);
        }

        return $leave;
    }

    public function reject(Leave $leave, int $approverUserId): Leave
    {
        if (! $leave->isPending()) {
            throw ValidationException::withMessages([
                'leave' => 'Only pending leaves can be rejected.',
            ]);
        }

        $leave->update([
            'status' => Leave::STATUS_REJECTED,
            'approved_by' => $approverUserId,
        ]);

        return $leave;
    }

    /**
     * The balance ledger row for the employee's current leave window.
     */
    public function currentBalance(int $userId): ?LeaveBalance
    {
        $employee = Employee::where('user_id', $userId)->first();

        if (! $employee) {
            return null;
        }

        return $this->accrual->getOrCreateBalance($employee, $this->accrual->currentWindow($employee));
    }

    /**
     * Split an approved leave across windows and charge the used days.
     */
    protected function chargeWindows(Employee $employee, Leave $leave): void
    {
        foreach ($this->accrual->splitAcrossWindows($employee, $leave->start_date, $leave->end_date) as $chunk) {
            $balance = $this->accrual->getOrCreateBalance($employee, [
                'year_start' => $chunk['year_start'],
                'year_end' => $chunk['year_end'],
            ]);

            $balance->increment('used_days', $chunk['days']);
        }
    }

    protected function assertQuotaAvailable(Employee $employee, Carbon $start, Carbon $end): void
    {
        foreach ($this->accrual->splitAcrossWindows($employee, $start, $end) as $chunk) {
            $balance = $this->accrual->getOrCreateBalance($employee, [
                'year_start' => $chunk['year_start'],
                'year_end' => $chunk['year_end'],
            ]);

            $remaining = (float) $balance->total_days - (float) $balance->used_days;

            if ($chunk['days'] > $remaining) {
                throw ValidationException::withMessages([
                    'start_date' => sprintf(
                        'Insufficient leave balance for the window %s – %s. You have %s day(s) left, you requested %s.',
                        $chunk['year_start'],
                        $chunk['year_end'],
                        $remaining,
                        $chunk['days']
                    ),
                ]);
            }
        }
    }

    protected function hasOverlap(int $userId, Carbon $start, Carbon $end): bool
    {
        return Leave::query()
            ->where('user_id', $userId)
            ->whereIn('status', [Leave::STATUS_PENDING, Leave::STATUS_APPROVED])
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('end_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($query) use ($start, $end) {
                        $query->where('start_date', '<=', $start->toDateString())
                            ->where('end_date', '>=', $end->toDateString());
                    });
            })
            ->exists();
    }
}