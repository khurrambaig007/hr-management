<?php

namespace Khurrambaig\HRManagement\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Khurrambaig\HRManagement\Models\Attendance;
use Khurrambaig\HRManagement\Models\LeaveBalance;
use Khurrambaig\HRManagement\Services\AccrualService;
use Khurrambaig\HRManagement\Services\AttendanceService;
use Khurrambaig\HRManagement\Services\LeaveService;
use Khurrambaig\HRManagement\Tests\TestCase;

class HRManagementTest extends TestCase
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

    public function test_probation_leave_is_limited_to_cap_and_unlocks_when_permanent(): void
    {
        $employee = $this->makeEmployee();
        $this->makeActiveSettlement(20, 2, 2026);

        $leaveService = $this->app->make(LeaveService::class);

        $leave = $leaveService->apply($employee->user_id, [
            'type' => 'annual',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-02',
            'reason' => 'Family trip',
        ]);

        $this->assertSame('pending', $leave->status);
        $this->assertEquals(2.0, (float) $leave->days);

        $leaveService->approve($leave, $this->makeApproverId());

        $balance = $leaveService->currentBalance($employee->user_id);
        $this->assertEquals(0, (float) $balance->remaining_days);

        $this->expectException(ValidationException::class);
        $leaveService->apply($employee->user_id, [
            'type' => 'annual',
            'start_date' => '2026-08-05',
            'end_date' => '2026-08-06',
            'reason' => 'More time',
        ]);
    }

    public function test_unlock_after_permanency_leaves_18_of_20(): void
    {
        $employee = $this->makeEmployee([
            'hire_date' => '2025-07-01',
            'is_permanent' => true,
        ]);
        $this->makeActiveSettlement(20, 2, 2026);

        $accrual = $this->app->make(AccrualService::class);
        $balance = $accrual->syncQuota($employee);

        $this->assertEquals(20, (float) $balance->total_days);
    }

    public function test_leave_spanning_anniversary_is_split_across_windows(): void
    {
        $employee = $this->makeEmployee([
            'hire_date' => '2025-07-01',
            'is_permanent' => true,
        ]);
        $this->makeActiveSettlement(20, 2, 2026);

        $accrual = $this->app->make(AccrualService::class);
        $leaveService = $this->app->make(LeaveService::class);

        $chunks = $accrual->splitAcrossWindows($employee, '2027-06-29', '2027-07-02');

        $this->assertCount(2, $chunks);
        $this->assertEquals(2, $chunks[0]['days']);
        $this->assertEquals(2, $chunks[1]['days']);

        $leave = $leaveService->apply($employee->user_id, [
            'type' => 'annual',
            'start_date' => '2027-06-29',
            'end_date' => '2027-07-02',
            'reason' => 'Long holiday',
        ]);

        $leaveService->approve($leave, $this->makeApproverId());

        $windowA = LeaveBalance::whereDate('year_start', '2026-07-01')->where('user_id', $employee->user_id)->first();
        $windowB = LeaveBalance::whereDate('year_start', '2027-07-01')->where('user_id', $employee->user_id)->first();

        $this->assertEquals(2, (float) $windowA->used_days);
        $this->assertNotNull($windowB);
        $this->assertEquals(2, (float) $windowB->used_days);
    }

    public function test_clock_in_late_status(): void
    {
        $employee = $this->makeEmployee();
        $service = $this->app->make(AttendanceService::class);

        Carbon::setTestNow(Carbon::parse('2026-09-10 10:30:00'));
        $service->clockIn($employee->user_id);

        $today = $service->todayFor($employee->user_id);
        $this->assertNotNull($today);
        $this->assertSame(Attendance::STATUS_LATE, $today->status);

        Carbon::setTestNow(Carbon::parse('2026-09-10 17:30:00'));
        $service->clockOut($employee->user_id);

        $this->assertNotNull($service->todayFor($employee->user_id)->clock_out);
    }

    public function test_commands_run(): void
    {
        $employee = $this->makeEmployee();
        $this->makeActiveSettlement(20, 2, 2026);

        $this->artisan('hr:grant-anniversary-quota')
            ->expectsOutputToContain('Ensured leave quota')
            ->assertExitCode(0);

        $this->artisan('hr:check-permanency')
            ->assertExitCode(0);
    }

    public function test_ensure_hr_role_redirects_without_employee_profile(): void
    {
        $user = \Khurrambaig\HRManagement\Tests\User::forceCreate([
            'name' => 'No Profile',
            'email' => 'noprofile'.uniqid().'@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->get('/hr')
            ->assertRedirect(route('hr.no-profile'));
    }

    public function test_no_profile_page_is_reachable(): void
    {
        $user = \Khurrambaig\HRManagement\Tests\User::forceCreate([
            'name' => 'No Profile',
            'email' => 'noprofile'.uniqid().'@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->get('/hr/no-profile')
            ->assertOk()
            ->assertSee('Waiting for your employee profile');
    }

    protected function makeApproverId(): int
    {
        return \Khurrambaig\HRManagement\Tests\User::forceCreate([
            'name' => 'Approver',
            'email' => 'approver'.uniqid().'@example.com',
            'password' => 'password',
        ])->id;
    }
}