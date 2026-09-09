@extends('hr::layouts.app')

@section('title', 'Leave Request')

@section('hrcontent')
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
        <h1>Leave Request</h1>
        <a class="btn btn-outline btn-sm" href="{{ route('hr.leaves.index') }}">Back</a>
    </div>

    <div class="card mt">
        <table>
            <tbody>
                <tr><th style="width:200px">Employee</th><td>{{ $leave->employee?->display_name ?? '—' }}</td></tr>
                <tr><th>Type</th><td>{{ ucfirst($leave->type) }}</td></tr>
                <tr><th>Dates</th><td>{{ $leave->start_date->format('d M Y') }} &rarr; {{ $leave->end_date->format('d M Y') }}</td></tr>
                <tr><th>Days</th><td>{{ (float) $leave->days }}</td></tr>
                <tr><th>Status</th><td>
                    @if($leave->status === 'approved') <span class="badge badge-green">Approved</span>
                    @elseif($leave->status === 'rejected') <span class="badge badge-red">Rejected</span>
                    @else <span class="badge badge-yellow">Pending</span> @endif
                </td></tr>
                <tr><th>Reason</th><td>{{ $leave->reason }}</td></tr>
                <tr><th>Reviewed by</th><td>{{ $leave->approver?->name ?? '—' }}</td></tr>
            </tbody>
        </table>
    </div>

    @if($leave->isPending() && $employee->canManageTeam())
        <div class="card actions">
            <form method="POST" action="{{ route('hr.leaves.approve', $leave) }}">
                @csrf
                <button class="btn btn-green" type="submit">Approve</button>
            </form>
            <form method="POST" action="{{ route('hr.leaves.reject', $leave) }}">
                @csrf
                <button class="btn" style="background:var(--red)" type="submit">Reject</button>
            </form>
        </div>
    @endif
@endsection