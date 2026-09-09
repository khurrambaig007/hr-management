@extends('hr::layouts.app')

@section('title', 'Attendance')

@section('hrcontent')
    <h1>Attendance</h1>

    <div class="grid mt">
        <div class="stat">
            <div class="label">Today</div>
            <div class="num">
                @if($today)
                    @if($today->is_clocked_in) <span style="color: var(--green)">Clocked in</span>
                    @else <span style="color: var(--blue)">{{ ucfirst(str_replace('_', ' ', $today->status)) }}</span> @endif
                @else <span class="muted">Not clocked in</span> @endif
            </div>
            <div class="actions mt">
                @if(! $today || ! $today->clock_in)
                    <form method="POST" action="{{ route('hr.attendance.clock-in') }}">
                        @csrf
                        <button class="btn btn-green btn-sm" type="submit">Clock In</button>
                    </form>
                @elseif($today->is_clocked_in)
                    <form method="POST" action="{{ route('hr.attendance.clock-out') }}">
                        @csrf
                        <button class="btn btn-sm" type="submit">Clock Out</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if($employee->isAdmin())
        <div class="card mt">
            <h2>Record attendance manually</h2>
            <form method="POST" action="{{ route('hr.attendance.store') }}" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end;">
                @csrf
                <div class="form-group" style="margin-bottom:0; min-width:220px;">
                    <label>Employee</label>
                    <select name="user_id" required>
                        @foreach($employeesList as $employeeOption)
                            <option value="{{ $employeeOption->user_id }}">{{ $employeeOption->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Date</label>
                    <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Clock in</label>
                    <input type="datetime-local" name="clock_in" value="{{ old('clock_in') }}">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Clock out</label>
                    <input type="datetime-local" name="clock_out" value="{{ old('clock_out') }}">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Status</label>
                    <select name="status">
                        @foreach(['present', 'absent', 'late', 'half_day'] as $status)
                            <option value="{{ $status }}" @selected(old('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn" type="submit">Save</button>
            </form>
        </div>
    @endif

    <div class="card mt">
        <form method="GET" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end;">
            <div class="form-group" style="margin-bottom:0;">
                <label>Date</label>
                <input type="date" name="date" value="{{ request('date', now()->format('Y-m-d')) }}">
            </div>
            <button class="btn btn-outline" type="submit">Filter</button>
            <a class="btn btn-outline" href="{{ route('hr.attendance.index') }}">Reset</a>
        </form>
    </div>

    <div class="card">
        @if($attendances->isEmpty())
            <p class="muted mb-0">No attendance records found.</p>
        @else
            <table>
                <thead>
                    <tr>
                        @if($employee->canManageTeam())<th>Employee</th>@endif
                        <th>Date</th><th>Clock in</th><th>Clock out</th><th>Status</th><th>Notes</th><th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $attendance)
                        <tr>
                            @if($employee->canManageTeam())
                                <td>{{ $attendance->employee?->display_name ?? '—' }}</td>
                            @endif
                            <td>{{ $attendance->date->format('d M Y') }}</td>
                            <td>{{ $attendance->clock_in?->format('h:i A') ?? '—' }}</td>
                            <td>{{ $attendance->clock_out?->format('h:i A') ?? '—' }}</td>
                            <td>
                                @php $color = $attendance->status === 'present' ? 'green' : ($attendance->status === 'absent' ? 'red' : ($attendance->status === 'late' ? 'yellow' : 'blue')); @endphp
                                <span class="badge badge-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}</span>
                            </td>
                            <td class="muted">{{ $attendance->notes ?? '—' }}</td>
                            <td class="text-right">
                                <div class="actions" style="justify-content:flex-end;">
                                    @if($attendance->user_id === $employee->user_id || $employee->canManageTeam())
                                        <a class="btn btn-outline btn-sm" href="{{ route('hr.attendance.corrections.create') }}">Request correction</a>
                                    @endif
                                    @if($employee->isAdmin())
                                        <a class="btn btn-outline btn-sm" href="{{ route('hr.attendance.edit', $attendance) }}">Edit</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="paginator">{{ $attendances->links(config('hr-management.pagination_view')) }}</div>
        @endif
    </div>
@endsection