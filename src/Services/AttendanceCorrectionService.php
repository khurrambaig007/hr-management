<?php

namespace Khurrambaig\HRManagement\Services;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Khurrambaig\HRManagement\Models\Attendance;
use Khurrambaig\HRManagement\Models\AttendanceCorrection;

class AttendanceCorrectionService
{
    public function __construct(protected AttendanceService $attendanceService)
    {
    }

    /**
     * @throws ValidationException
     */
    public function apply(int $userId, array $data): AttendanceCorrection
    {
        $attendance = Attendance::find($data['attendance_id']);

        if (! $attendance || (int) $attendance->user_id !== (int) $userId) {
            throw ValidationException::withMessages([
                'attendance_id' => 'You can only request a correction for your own attendance records.',
            ]);
        }

        if (! $this->isWithinCorrectionWindow($attendance)) {
            throw ValidationException::withMessages([
                'attendance_id' => 'Attendance records older than the allowed correction window cannot be corrected.',
            ]);
        }

        if (AttendanceCorrection::where('attendance_id', $attendance->id)->where('status', AttendanceCorrection::STATUS_PENDING)->exists()) {
            throw ValidationException::withMessages([
                'attendance_id' => 'A pending correction already exists for this attendance record.',
            ]);
        }

        $requestedIn = isset($data['requested_clock_in']) ? Carbon::parse($data['requested_clock_in']) : null;
        $requestedOut = isset($data['requested_clock_out']) ? Carbon::parse($data['requested_clock_out']) : null;

        if ($requestedIn && $requestedOut && $requestedOut->lt($requestedIn)) {
            throw ValidationException::withMessages([
                'requested_clock_out' => 'Clock out must be after clock in.',
            ]);
        }

        $oldIn = $attendance->clock_in;
        $oldOut = $attendance->clock_out;

        $inChanged = $requestedIn && (! $oldIn || ! $requestedIn->equalTo($oldIn));
        $outChanged = $requestedOut && (! $oldOut || ! $requestedOut->equalTo($oldOut));

        if (! $inChanged && ! $outChanged) {
            throw ValidationException::withMessages([
                'requested_clock_in' => 'At least one clock time must change or be filled in.',
            ]);
        }

        return AttendanceCorrection::create([
            'user_id' => $userId,
            'attendance_id' => $attendance->id,
            'requested_clock_in' => $requestedIn,
            'requested_clock_out' => $requestedOut,
            'old_clock_in' => $oldIn,
            'old_clock_out' => $oldOut,
            'reason' => $data['reason'],
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function approve(AttendanceCorrection $correction, int $approverUserId): AttendanceCorrection
    {
        if (! $correction->isPending()) {
            throw ValidationException::withMessages([
                'correction' => 'Only pending corrections can be approved.',
            ]);
        }

        $attendance = $correction->attendance;

        $newIn = $correction->requested_clock_in ?? $attendance->clock_in;
        $newOut = $correction->requested_clock_out ?? $attendance->clock_out;

        $oldInLabel = $attendance->clock_in?->format('H:i') ?? '—';
        $oldOutLabel = $attendance->clock_out?->format('H:i') ?? '—';
        $newInLabel = $newIn?->format('H:i') ?? '—';
        $newOutLabel = $newOut?->format('H:i') ?? '—';

        $note = trim(sprintf(
            "%s\n[Correction] clock_in: %s → %s, clock_out: %s → %s",
            (string) $attendance->notes,
            $oldInLabel,
            $newInLabel,
            $oldOutLabel,
            $newOutLabel
        ));

        $attendance->update([
            'clock_in' => $newIn,
            'clock_out' => $newOut,
            'status' => $this->attendanceService->statusFor($attendance),
            'notes' => $note,
        ]);

        $correction->update([
            'status' => AttendanceCorrection::STATUS_APPROVED,
            'approved_by' => $approverUserId,
            'reviewed_at' => Carbon::now(),
        ]);

        return $correction;
    }

    /**
     * @throws ValidationException
     */
    public function reject(AttendanceCorrection $correction, int $approverUserId): AttendanceCorrection
    {
        if (! $correction->isPending()) {
            throw ValidationException::withMessages([
                'correction' => 'Only pending corrections can be rejected.',
            ]);
        }

        $correction->update([
            'status' => AttendanceCorrection::STATUS_REJECTED,
            'approved_by' => $approverUserId,
            'reviewed_at' => Carbon::now(),
        ]);

        return $correction;
    }

    public function correctableFor(int $userId, int $withinDays = 0)
    {
        $query = Attendance::query()
            ->mine($userId)
            ->whereDoesntHave('corrections', function ($query) {
                $query->where('status', AttendanceCorrection::STATUS_PENDING);
            });

        if ($withinDays > 0) {
            $query->whereDate('date', '>=', Carbon::today()->subDays($withinDays)->toDateString());
        }

        return $query->latest('date')->limit(50)->get();
    }

    protected function isWithinCorrectionWindow(Attendance $attendance): bool
    {
        $days = (int) config('hr-management.corrections.allow_within_days', 7);

        if ($days <= 0) {
            return true;
        }

        return Carbon::parse($attendance->date)->gte(Carbon::today()->subDays($days));
    }
}