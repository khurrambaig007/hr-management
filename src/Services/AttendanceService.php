<?php

namespace Khurrambaig\HRManagement\Services;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Khurrambaig\HRManagement\Models\Attendance;

class AttendanceService
{
    public function todayFor(int $userId): ?Attendance
    {
        return Attendance::query()
            ->mine($userId)
            ->onDate(Carbon::today()->toDateString())
            ->first();
    }

    /**
     * @throws ValidationException
     */
    public function clockIn(int $userId): Attendance
    {
        $today = $this->todayFor($userId);

        if ($today && $today->clock_in) {
            throw ValidationException::withMessages([
                'clock_in' => 'You have already clocked in today.',
            ]);
        }

        $now = Carbon::now();
        $status = $this->statusForClockIn($now);

        if ($today) {
            $today->update([
                'clock_in' => $now,
                'status' => $status,
            ]);

            return $today;
        }

        return Attendance::create([
            'user_id' => $userId,
            'date' => Carbon::today()->toDateString(),
            'clock_in' => $now,
            'clock_out' => null,
            'status' => $status,
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function clockOut(int $userId): Attendance
    {
        $today = $this->todayFor($userId);

        if (! $today || ! $today->clock_in) {
            throw ValidationException::withMessages([
                'clock_out' => 'You must clock in before clocking out.',
            ]);
        }

        if ($today->clock_out) {
            throw ValidationException::withMessages([
                'clock_out' => 'You have already clocked out today.',
            ]);
        }

        $now = Carbon::now();

        $today->update([
            'clock_out' => $now,
            'status' => $this->statusForClockOut($today->clock_in, $now),
        ]);

        return $today;
    }

    public function manualStore(array $data): Attendance
    {
        $data['status'] = $data['status'] ?? (
            isset($data['clock_in'])
                ? $this->statusForClockIn(Carbon::parse($data['clock_in']))
                : Attendance::STATUS_PRESENT
        );

        return Attendance::create($data);
    }

    /**
     * Recompute the status of an attendance record from its current times.
     */
    public function statusFor(Attendance $attendance): string
    {
        return $this->statusFromTimes($attendance->clock_in, $attendance->clock_out);
    }

    /**
     * @throws ValidationException
     */
    public function update(Attendance $attendance, array $data, bool $recomputeStatus = true): Attendance
    {
        $clockIn = blank($data['clock_in'] ?? null) ? null : Carbon::parse($data['clock_in']);
        $clockOut = blank($data['clock_out'] ?? null) ? null : Carbon::parse($data['clock_out']);

        if ($clockIn && $clockOut && $clockOut->lt($clockIn)) {
            throw ValidationException::withMessages([
                'clock_out' => 'Clock out must be after clock in.',
            ]);
        }

        $status = $recomputeStatus || blank($data['status'] ?? null)
            ? $this->statusFromTimes($clockIn, $clockOut)
            : $data['status'];

        $attendance->update([
            'date' => $data['date'] ?? $attendance->date?->toDateString(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status' => $status,
            'notes' => $data['notes'] ?? $attendance->notes,
        ]);

        return $attendance;
    }

    protected function statusFromTimes(?Carbon $clockIn, ?Carbon $clockOut): string
    {
        if ($clockOut) {
            return $this->statusForClockOut($clockIn ?? now(), $clockOut);
        }

        if ($clockIn) {
            return $this->statusForClockIn($clockIn);
        }

        return Attendance::STATUS_PRESENT;
    }

    /**
     * Aggregate monthly status counts per user.
     *
     * @return \Illuminate\Support\Collection<int, \Illuminate\Support\Collection>
     */
    public function monthlyReport(?string $month = null)
    {
        $month = $month ?: Carbon::today()->format('Y-m');

        [$year, $monthNumber] = array_map('intval', explode('-', $month));

        $rows = Attendance::query()
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNumber)
            ->select('user_id')
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('user_id', 'status')
            ->get();

        return $rows->groupBy('user_id');
    }

    protected function statusForClockIn(Carbon $clockIn): string
    {
        $grace = (int) config('hr-management.late_grace_minutes', 15);

        $startMinutes = $this->toMinutesOfDay($this->workingStart()) + $grace;
        $clockInMinutes = $clockIn->hour * 60 + $clockIn->minute;

        if ($clockInMinutes > $startMinutes) {
            return Attendance::STATUS_LATE;
        }

        return Attendance::STATUS_PRESENT;
    }

    protected function statusForClockOut(Carbon $clockIn, Carbon $clockOut): string
    {
        if ($this->toMinutesOfDay($clockOut) < $this->toMinutesOfDay($this->workingEnd())
            && $clockOut->diffInMinutes($clockIn) >= 240
        ) {
            return Attendance::STATUS_HALF_DAY;
        }

        return $this->statusForClockIn($clockIn);
    }

    protected function workingStart(): string
    {
        return (string) config('hr-management.working_hours.start', '09:00');
    }

    protected function workingEnd(): string
    {
        return (string) config('hr-management.working_hours.end', '17:00');
    }

    protected function toMinutesOfDay(Carbon|string $time): int
    {
        if ($time instanceof Carbon) {
            return $time->hour * 60 + $time->minute;
        }

        [$hour, $minute] = array_pad(explode(':', (string) $time), 2, '0');

        return (int) $hour * 60 + (int) $minute;
    }
}