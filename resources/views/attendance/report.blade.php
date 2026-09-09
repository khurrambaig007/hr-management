@extends('hr::layouts.app')

@section('title', 'Attendance Report')

@section('hrcontent')
    <h1>Attendance Report</h1>

    <div class="card">
        <form method="GET" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end;">
            <div class="form-group" style="margin-bottom:0;">
                <label>Month</label>
                <input type="month" name="month" value="{{ $month }}">
            </div>
            <button class="btn" type="submit">Generate</button>
        </form>
    </div>

    <div class="card">
        <p class="muted mb-0">Report for <strong>{{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</strong></p>
        <table class="mt">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Present</th>
                    <th>Late</th>
                    <th>Half day</th>
                    <th>Absent</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $employeeRow)
                    @php $employeeRows = $rows->get($employeeRow->user_id, collect()); @endphp
                    <tr>
                        <td>{{ $employeeRow->display_name }}</td>
                        <td>{{ $employeeRow->department?->name ?? '—' }}</td>
                        @foreach(['present' => 'green', 'late' => 'yellow', 'half_day' => 'blue', 'absent' => 'red'] as $status => $color)
                            @php $count = (int) $employeeRows->firstWhere('status', $status)?->total ?? 0; @endphp
                            <td><span class="badge badge-{{ $color }}">{{ $count }}</span></td>
                        @endforeach
                        <td><strong>{{ $employeeRows->sum('total') }}</strong></td>
                    </tr>
                @endforeach
                @if($employees->isEmpty())
                    <tr><td colspan="7" class="muted">No employees yet.</td></tr>
                @endif
            </tbody>
        </table>
    </div>
@endsection