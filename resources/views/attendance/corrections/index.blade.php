@extends('hr::layouts.app')

@section('title', 'Attendance Corrections')

@section('hrcontent')
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
        <h1>Attendance Corrections</h1>
        <a class="btn" href="{{ route('hr.attendance.corrections.create') }}">Request Correction</a>
    </div>

    @if($pendingQueue && $pendingQueue->isNotEmpty())
        <div class="card mt">
            <h2>Pending decisions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Employee</th><th>Date</th><th>Current in/out</th><th>Requested in/out</th><th>Reason</th><th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingQueue as $correction)
                        <tr>
                            <td>{{ $correction->attendance?->employee?->display_name ?? '—' }}</td>
                            <td>{{ $correction->attendance?->date?->format('d M Y') ?? '—' }}</td>
                            <td class="muted">
                                {{ $correction->old_clock_in?->format('H:i') ?? '—' }} / {{ $correction->old_clock_out?->format('H:i') ?? '—' }}
                            </td>
                            <td>
                                {{ $correction->requested_clock_in?->format('H:i') ?? '—' }} / {{ $correction->requested_clock_out?->format('H:i') ?? '—' }}
                            </td>
                            <td class="muted">{{ \Illuminate\Support\Str::limit($correction->reason, 40) }}</td>
                            <td class="text-right">
                                <div class="actions" style="justify-content:flex-end;">
                                    <a class="btn btn-outline btn-sm" href="{{ route('hr.attendance.corrections.show', $correction) }}">View</a>
                                    <form method="POST" action="{{ route('hr.attendance.corrections.approve', $correction) }}">
                                        @csrf
                                        <button class="btn btn-green btn-sm" type="submit">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('hr.attendance.corrections.reject', $correction) }}">
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
        @if($corrections->isEmpty())
            <p class="muted mb-0">No correction requests found.</p>
        @else
            <table>
                <thead>
                    <tr><th>Date</th><th>Current in/out</th><th>Requested in/out</th><th>Status</th><th class="text-right">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($corrections as $correction)
                        <tr>
                            <td>{{ $correction->attendance?->date?->format('d M Y') ?? '—' }}</td>
                            <td class="muted">{{ $correction->old_clock_in?->format('H:i') ?? '—' }} / {{ $correction->old_clock_out?->format('H:i') ?? '—' }}</td>
                            <td>{{ $correction->requested_clock_in?->format('H:i') ?? '—' }} / {{ $correction->requested_clock_out?->format('H:i') ?? '—' }}</td>
                            <td>
                                @if($correction->status === 'approved') <span class="badge badge-green">Approved</span>
                                @elseif($correction->status === 'rejected') <span class="badge badge-red">Rejected</span>
                                @else <span class="badge badge-yellow">Pending</span> @endif
                            </td>
                            <td class="text-right"><a class="btn btn-outline btn-sm" href="{{ route('hr.attendance.corrections.show', $correction) }}">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="paginator">{{ $corrections->links(config('hr-management.pagination_view')) }}</div>
        @endif
    </div>
@endsection