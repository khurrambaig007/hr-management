<?php

namespace Khurrambaig\HRManagement\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Khurrambaig\HRManagement\Models\Attendance;
use Khurrambaig\HRManagement\Models\AttendanceCorrection;
use Khurrambaig\HRManagement\Services\AttendanceCorrectionService;
use Khurrambaig\HRManagement\Services\AttendanceService;
use Khurrambaig\HRManagement\Tests\TestCase;

class AttendanceCorrectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-09-10 09:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    protected function makeAttendance(int $userId, string $date, $clockIn = null, $clockOut = null): Attendance
    {
        return Attendance::forceCreate([
            'user_id' => $userId,
            'date' => $date,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status' => $clockIn ? 'present' : 'absent',
        ]);
    }

    public function test_approval_updates_attendance_and_recomputes_status(): void
    {
        $employee = $this->makeEmployee();
        $attendance = $this->makeAttendance($employee->user_id, '2026-09-09', Carbon::parse('2026-09-09 09:00:00'));

        $service = $this->app->make(AttendanceCorrectionService::class);

        $correction = $service->apply($employee->user_id, [
            'attendance_id' => $attendance->id,
            'requested_clock_out' => '2026-09-09 17:00',
            'reason' => 'Forgot to clock out',
        ]);

        $this->assertTrue($correction->isPending());

        $service->approve($correction, $this->makeApproverId());

        $attendance->refresh();

        $this->assertEquals('2026-09-09 17:00:00', $attendance->clock_out->format('Y-m-d H:i:s'));
        $this->assertSame('present', $attendance->status);
        $this->assertStringContainsString('[Correction]', (string) $attendance->notes);

        $this->assertSame(AttendanceCorrection::STATUS_APPROVED, $correction->refresh()->status);
    }

    public function test_second_pending_correction_is_blocked(): void
    {
        $employee = $this->makeEmployee();
        $attendance = $this->makeAttendance($employee->user_id, '2026-09-09', Carbon::parse('2026-09-09 09:00:00'));

        $service = $this->app->make(AttendanceCorrectionService::class);

        $service->apply($employee->user_id, [
            'attendance_id' => $attendance->id,
            'requested_clock_out' => '2026-09-09 17:00',
            'reason' => 'First',
        ]);

        $this->expectException(ValidationException::class);
        $service->apply($employee->user_id, [
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '2026-09-09 08:55',
            'reason' => 'Second',
        ]);
    }

    public function test_correction_outside_window_is_rejected(): void
    {
        $employee = $this->makeEmployee();
        $attendance = $this->makeAttendance($employee->user_id, '2026-08-20', Carbon::parse('2026-08-20 09:00:00'));

        $service = $this->app->make(AttendanceCorrectionService::class);

        $this->expectException(ValidationException::class);
        $service->apply($employee->user_id, [
            'attendance_id' => $attendance->id,
            'requested_clock_out' => '2026-08-20 17:00',
            'reason' => 'Late request',
        ]);
    }

    public function test_rejection_leaves_attendance_untouched(): void
    {
        $employee = $this->makeEmployee();
        $attendance = $this->makeAttendance($employee->user_id, '2026-09-09', Carbon::parse('2026-09-09 09:00:00'));

        $service = $this->app->make(AttendanceCorrectionService::class);

        $correction = $service->apply($employee->user_id, [
            'attendance_id' => $attendance->id,
            'requested_clock_out' => '2026-09-09 17:00',
            'reason' => 'Please',
        ]);

        $service->reject($correction, $this->makeApproverId());

        $attendance->refresh();

        $this->assertNull($attendance->clock_out);
        $this->assertSame(AttendanceCorrection::STATUS_REJECTED, $correction->refresh()->status);
    }

    public function test_admin_direct_edit_recomputes_status(): void
    {
        $employee = $this->makeEmployee();
        $attendance = $this->makeAttendance($employee->user_id, '2026-09-09', Carbon::parse('2026-09-09 09:00:00'));

        $service = $this->app->make(AttendanceService::class);

        $service->update($attendance, [
            'clock_in' => '2026-09-09 10:30',
            'notes' => 'Adjusted',
        ]);

        $attendance->refresh();

        $this->assertEquals('Late', ucfirst($attendance->status));
    }

    public function test_employee_cannot_view_another_correction(): void
    {
        $employeeA = $this->makeEmployee();
        $employeeB = $this->makeEmployee();

        $attendance = $this->makeAttendance($employeeA->user_id, '2026-09-09', Carbon::parse('2026-09-09 09:00:00'));

        $correction = $this->app->make(AttendanceCorrectionService::class)->apply($employeeA->user_id, [
            'attendance_id' => $attendance->id,
            'requested_clock_out' => '2026-09-09 17:00',
            'reason' => 'Private',
        ]);

        $this->actingAs($employeeB->user)
            ->get(route('hr.attendance.corrections.show', $correction))
            ->assertForbidden();
    }

    public function test_employee_cannot_approve_corrections(): void
    {
        $employee = $this->makeEmployee();

        $attendance = $this->makeAttendance($employee->user_id, '2026-09-09', Carbon::parse('2026-09-09 09:00:00'));

        $correction = $this->app->make(AttendanceCorrectionService::class)->apply($employee->user_id, [
            'attendance_id' => $attendance->id,
            'requested_clock_out' => '2026-09-09 17:00',
            'reason' => 'Private',
        ]);

        $this->actingAs($employee->user)
            ->post(route('hr.attendance.corrections.approve', $correction))
            ->assertForbidden();
    }

    public function test_employee_cannot_request_correction_for_another_record(): void
    {
        $employeeA = $this->makeEmployee();
        $employeeB = $this->makeEmployee();

        $attendance = $this->makeAttendance($employeeA->user_id, '2026-09-09', Carbon::parse('2026-09-09 09:00:00'));

        $this->actingAs($employeeB->user)
            ->post(route('hr.attendance.corrections.store'), [
                'attendance_id' => $attendance->id,
                'requested_clock_out' => '2026-09-09 17:00',
                'reason' => 'Hack',
            ])
            ->assertSessionHasErrors('attendance_id');
    }

    protected function makeApproverId(): int
    {
        return \Khurrambaig\HRManagement\Tests\User::forceCreate([
            'name' => 'Approver',
            'email' => 'corr_approver'.uniqid().'@example.com',
            'password' => 'password',
        ])->id;
    }
}