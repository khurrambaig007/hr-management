<nav class="topnav">
    <span class="brand">HR</span>
    @if($hrEmployee ?? null)
        <a href="{{ route('hr.dashboard') }}">Dashboard</a>
        <a href="{{ route('hr.attendance.index') }}">Attendance</a>
        <a href="{{ route('hr.attendance.corrections.index') }}">Corrections</a>
        <a href="{{ route('hr.leaves.index') }}">Leaves</a>
        <a href="{{ route('hr.leaves.create') }}">Apply Leave</a>
        <a href="{{ route('hr.leaves.balance') }}">Balance</a>
        @if($hrEmployee->canManageTeam())
            <a href="{{ route('hr.attendance.report') }}">Report</a>
        @endif
        @if($hrEmployee->isAdmin())
            <a href="{{ route('hr.employees.index') }}">Employees</a>
            <a href="{{ route('hr.settlements.index') }}">Settlements</a>
        @endif
    @endif
    <span class="spacer"></span>
    @auth
        <span class="muted">{{ auth()->user()->name ?? '' }}</span>
        @if(Route::has('logout'))
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-gray btn-sm" type="submit">Logout</button>
            </form>
        @endif
    @endauth
</nav>