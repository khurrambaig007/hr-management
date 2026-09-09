<?php

namespace Khurrambaig\HRManagement\Tests\Feature;

use Khurrambaig\HRManagement\Models\Attendance;
use Khurrambaig\HRManagement\Tests\TestCase;

class ThemingTest extends TestCase
{
    public function test_uses_builtin_layout_when_host_layout_is_missing(): void
    {
        config(['hr-management.layout' => 'hr_theming_nonexistent']);

        $employee = $this->makeEmployee(['role' => 'admin']);

        $this->actingAs($employee->user)
            ->get(route('hr.dashboard'))
            ->assertOk()
            ->assertSee('class="topnav"', false)
            ->assertDontSee('HOST-LAYOUT-MARKER');
    }

    public function test_extends_host_layout_when_it_exists(): void
    {
        $this->app['view']->addLocation(__DIR__.'/../views');

        $employee = $this->makeEmployee(['role' => 'admin']);

        $this->actingAs($employee->user)
            ->get(route('hr.dashboard'))
            ->assertOk()
            ->assertSee('HOST-LAYOUT-MARKER')
            ->assertDontSee(':root');
    }

    public function test_host_layout_can_be_forced_off_via_config(): void
    {
        $this->app['view']->addLocation(__DIR__.'/../views');
        config(['hr-management.layout' => false]);

        $employee = $this->makeEmployee(['role' => 'admin']);

        $this->actingAs($employee->user)
            ->get(route('hr.dashboard'))
            ->assertOk()
            ->assertDontSee('HOST-LAYOUT-MARKER')
            ->assertSee(':root');
    }

    public function test_pagination_view_config_defaults_to_null(): void
    {
        $this->assertNull(config('hr-management.pagination_view'));
    }

    public function test_pagination_view_config_switches_framework(): void
    {
        $employee = $this->makeEmployee();

        for ($i = 0; $i < 16; $i++) {
            Attendance::forceCreate([
                'user_id' => $employee->user_id,
                'date' => now()->subDays($i)->format('Y-m-d'),
                'status' => 'present',
            ]);
        }

        $paginator = Attendance::query()->paginate(15);

        config(['hr-management.pagination_view' => 'pagination::bootstrap-5']);
        $bootstrap = $paginator->links(config('hr-management.pagination_view'));
        $this->assertStringContainsString('page-link', $bootstrap);
        $this->assertStringNotContainsString('inline-flex', $bootstrap);

        config(['hr-management.pagination_view' => null]);
        $default = $paginator->links(config('hr-management.pagination_view'));
        $this->assertStringContainsString('inline-flex', $default);
    }
}