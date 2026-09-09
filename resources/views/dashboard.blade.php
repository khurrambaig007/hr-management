@extends('hr::layouts.app')

@section('title', 'Dashboard')

@section('hrcontent')
    <h1>Welcome, {{ $employee->display_name }}</h1>

    <div class="grid mt">
        <div class="stat">
            <div class="label">Today's Attendance</div>
            <div class="num">
                @if($today)
                    @if($today->is_clocked_in)
                        <span style="color: var(--green)">Clocked in</span>
                    @else
                        <span style="color: var(--blue)">{{ ucfirst(str_replace('_', ' ', $today->status)) }}</span>
                    @endif
                @else
                    <span class="muted">Not started</span>
                @endif
            </div>
            <div class="actions mt">
                @if(! $today || ! $today->clock_in)
                    <form method="POST" action="{{ route('hr.attendance.clock-in') }}">
                        @csrf
                        <button class="btn btn-green" type="submit">Clock In</button>
                    </form>
                @elseif($today->is_clocked_in)
                    <form method="POST" action="{{ route('hr.attendance.clock-out') }}">
                        @csrf
                        <button class="btn" type="submit">Clock Out</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="stat">
            <div class="label">Leave Balance (current year)</div>
            <div class="num">
                @if($balance)
                    {{ (float) $balance->remaining_days }}<span class="muted"> / {{ (float) $balance->total_days }}</span>
                @else
                    <span class="muted">—</span>
                @endif
            </div>
            <div class="mt">
                <div class="progress">
                    @if($balance && (float) $balance->total_days > 0)
                        <div class="progress-inner" style="width: {{ min(100, ((float) $balance->used_days / (float) $balance->total_days) * 100) }}%"></div>
                    @endif
                </div>
            </div>
            <a class="btn btn-outline btn-sm mt" href="{{ route('hr.leaves.balance') }}">View balance</a>
        </div>

        <div class="stat">
            <div class="label">Pending approvals</div>
            <div class="num">{{ $pendingCount ?? 0 }}</div>
            @if($pendingCount)
                <a class="btn btn-outline btn-sm mt" href="{{ route('hr.leaves.index') }}">Review</a>
            @endif
        </div>

        <div class="stat">
            <div class="label">Pending corrections</div>
            <div class="num">{{ $pendingCorrectionCount ?? 0 }}</div>
            @if($pendingCorrectionCount)
                <a class="btn btn-outline btn-sm mt" href="{{ route('hr.attendance.corrections.index') }}">Review</a>
            @endif
        </div>
    </div>

    <div class="card mt">
        <div class="flex" style="display:flex; align-items:center; justify-content:space-between;">
            <h2 class="mb-0">Recent leave requests</h2>
            <a class="btn btn-sm" href="{{ route('hr.leaves.create') }}">Apply for leave</a>
        </div>
        @if($recentLeaves->isEmpty())
            <p class="muted mb-0">No leave requests yet.</p>
        @else
            <table class="mt">
                <thead>
                    <tr><th>Type</th><th>Dates</th><th>Days</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach($recentLeaves as $leave)
                        <tr>
                            <td>{{ ucfirst($leave->type) }}</td>
                            <td>{{ $leave->start_date->format('d M Y') }} &rarr; {{ $leave->end_date->format('d M Y') }}</td>
                            <td>{{ (float) $leave->days }}</td>
                            <td>
                                @if($leave->status === 'approved') <span class="badge badge-green">Approved</span>
                                @elseif($leave->status === 'rejected') <span class="badge badge-red">Rejected</span>
                                @else <span class="badge badge-yellow">Pending</span> @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection