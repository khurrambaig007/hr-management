@extends('hr::layouts.app')

@section('title', 'Leaves')

@section('hrcontent')
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
        <h1>My Leaves</h1>
        <a class="btn" href="{{ route('hr.leaves.create') }}">Apply for leave</a>
    </div>

    @if($pendingLeaves && $pendingLeaves->isNotEmpty())
        <div class="card mt">
            <h2>Pending decisions</h2>
            <table>
                <thead>
                    <tr><th>Employee</th><th>Type</th><th>Dates</th><th>Days</th><th>Reason</th><th class="text-right">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($pendingLeaves as $leave)
                        <tr>
                            <td>{{ $leave->employee?->display_name ?? '—' }}</td>
                            <td>{{ ucfirst($leave->type) }}</td>
                            <td>{{ $leave->start_date->format('d M Y') }} &rarr; {{ $leave->end_date->format('d M Y') }}</td>
                            <td>{{ (float) $leave->days }}</td>
                            <td class="muted">{{ \Illuminate\Support\Str::limit($leave->reason, 40) }}</td>
                            <td class="text-right">
                                <div class="actions" style="justify-content:flex-end;">
                                    <a class="btn btn-outline btn-sm" href="{{ route('hr.leaves.show', $leave) }}">View</a>
                                    <form method="POST" action="{{ route('hr.leaves.approve', $leave) }}">
                                        @csrf
                                        <button class="btn btn-green btn-sm" type="submit">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('hr.leaves.reject', $leave) }}">
                                        @csrf
                                        <button class="btn btn-sm" style="background:var(--red)" type="submit">Reject</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="card mt">
        @if($leaves->isEmpty())
            <p class="muted mb-0">No leave requests yet.</p>
        @else
            <table>
                <thead>
                    <tr><th>Type</th><th>Dates</th><th>Days</th><th>Status</th><th>Approved by</th><th class="text-right">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($leaves as $leave)
                        <tr>
                            <td>{{ ucfirst($leave->type) }}</td>
                            <td>{{ $leave->start_date->format('d M Y') }} &rarr; {{ $leave->end_date->format('d M Y') }}</td>
                            <td>{{ (float) $leave->days }}</td>
                            <td>
                                @if($leave->status === 'approved') <span class="badge badge-green">Approved</span>
                                @elseif($leave->status === 'rejected') <span class="badge badge-red">Rejected</span>
                                @else <span class="badge badge-yellow">Pending</span> @endif
                            </td>
                            <td>{{ $leave->approver?->name ?? '—' }}</td>
                            <td class="text-right"><a class="btn btn-outline btn-sm" href="{{ route('hr.leaves.show', $leave) }}">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="paginator">{{ $leaves->links(config('hr-management.pagination_view')) }}</div>
        @endif
    </div>
@endsection