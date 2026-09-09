@extends('hr::layouts.app')

@section('title', 'Employee Details')

@section('hrcontent')
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
        <h1>{{ $employee->display_name }}</h1>
        <div class="actions">
            <a class="btn btn-outline btn-sm" href="{{ route('hr.employees.index') }}">Back</a>
            <a class="btn btn-sm" href="{{ route('hr.employees.edit', $employee) }}">Edit</a>
        </div>
    </div>

    <div class="grid mt">
        <div class="stat"><div class="label">Department</div><div class="num" style="font-size:1.1rem">{{ $employee->department?->name ?? '—' }}</div></div>
        <div class="stat"><div class="label">Position</div><div class="num" style="font-size:1.1rem">{{ $employee->position ?? '—' }}</div></div>
        <div class="stat"><div class="label">Role</div>
            <div class="num" style="font-size:1.1rem">
                @if($employee->isAdmin()) <span class="badge badge-red">Admin</span>
                @elseif($employee->isManager()) <span class="badge badge-blue">Manager</span>
                @else <span class="badge badge-gray">Employee</span> @endif
            </div>
        </div>
        <div class="stat"><div class="label">Employment</div>
            <div class="num" style="font-size:1.1rem">
                @if($employee->is_permanent) <span class="badge badge-green">Permanent</span>
                @else <span class="badge badge-yellow">Probation</span> @endif
            </div>
        </div>
    </div>

    <div class="card mt">
        <h2>Profile</h2>
        <table>
            <tbody>
                <tr><th style="width:200px">Email</th><td>{{ $employee->user?->email ?? '—' }}</td></tr>
                <tr><th>Phone</th><td>{{ $employee->phone ?? '—' }}</td></tr>
                <tr><th>Salary</th><td>{{ $employee->salary !== null ? number_format((float) $employee->salary, 2) : '—' }}</td></tr>
                <tr><th>Address</th><td>{{ $employee->address ?? '—' }}</td></tr>
                <tr><th>Hire date</th><td>{{ $employee->hire_date?->format('d M Y') ?? '—' }}</td></tr>
                <tr><th>Probation end</th><td>{{ $employee->probation_end_date?->format('d M Y') ?? '—' }}</td></tr>
                <tr><th>Attendance (this year)</th><td>{{ $attendanceCount }}</td></tr>
                <tr><th>Leave summary</th>
                    <td>
                        @foreach($leaveStats ?? [] as $status => $count)
                            <span class="badge badge-gray">{{ ucfirst($status) }}: {{ $count }}</span>
                        @endforeach
                        @if(($leaveStats ?? collect())->isEmpty()) <span class="muted">No leaves</span> @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection