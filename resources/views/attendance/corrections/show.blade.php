@extends('hr::layouts.app')

@section('title', 'Correction Request')

@section('hrcontent')
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
        <h1>Correction Request</h1>
        <a class="btn btn-outline btn-sm" href="{{ route('hr.attendance.corrections.index') }}">Back</a>
    </div>

    <div class="card mt">
        <table>
            <tbody>
                <tr><th style="width:200px">Employee</th><td>{{ $correction->attendance?->employee?->display_name ?? '—' }}</td></tr>
                <tr><th>Date</th><td>{{ $correction->attendance?->date?->format('d M Y') ?? '—' }}</td></tr>
                <tr><th>Current clock in</th><td>{{ $correction->old_clock_in?->format('d M Y H:i') ?? '—' }}</td></tr>
                <tr><th>Current clock out</th><td>{{ $correction->old_clock_out?->format('d M Y H:i') ?? '—' }}</td></tr>
                <tr><th>Requested clock in</th><td>{{ $correction->requested_clock_in?->format('d M Y H:i') ?? '—' }}</td></tr>
                <tr><th>Requested clock out</th><td>{{ $correction->requested_clock_out?->format('d M Y H:i') ?? '—' }}</td></tr>
                <tr><th>Reason</th><td>{{ $correction->reason }}</td></tr>
                <tr><th>Status</th><td>
                    @if($correction->status === 'approved') <span class="badge badge-green">Approved</span>
                    @elseif($correction->status === 'rejected') <span class="badge badge-red">Rejected</span>
                    @else <span class="badge badge-yellow">Pending</span> @endif
                </td></tr>
                <tr><th>Reviewed by</th><td>{{ $correction->approvedBy?->name ?? '—' }}</td></tr>
                @if($correction->reviewed_at)
                    <tr><th>Reviewed at</th><td>{{ $correction->reviewed_at->format('d M Y H:i') }}</td></tr>
                @endif
            </tbody>
        </table>
    </div>

    @if($correction->isPending() && $employee->canManageTeam())
        <div class="card actions">
            <form method="POST" action="{{ route('hr.attendance.corrections.approve', $correction) }}">
                @csrf
                <button class="btn btn-green" type="submit">Approve &amp; update</button>
            </form>
            <form method="POST" action="{{ route('hr.attendance.corrections.reject', $correction) }}">
                @csrf
                <button class="btn" style="background:var(--red)" type="submit">Reject</button>
            </form>
        </div>
    @endif
@endsection