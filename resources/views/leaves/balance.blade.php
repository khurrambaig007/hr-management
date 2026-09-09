@extends('hr::layouts.app')

@section('title', 'Leave Balance')

@section('hrcontent')
    <h1>Leave Balance</h1>

    <div class="grid mt">
        <div class="stat">
            <div class="label">Total (this leave year)</div>
            <div class="num">{{ $balance ? (float) $balance->total_days : 0 }}</div>
        </div>
        <div class="stat">
            <div class="label">Used</div>
            <div class="num">{{ $balance ? (float) $balance->used_days : 0 }}</div>
        </div>
        <div class="stat">
            <div class="label">Remaining</div>
            <div class="num" style="color: var(--green)">{{ $balance ? (float) $balance->remaining_days : 0 }}</div>
        </div>
    </div>

    @if($balance)
        <div class="card mt">
            <h2>{{ $balance->year_start->format('d M Y') }} &rarr; {{ $balance->year_end->format('d M Y') }}</h2>
            <div class="progress mt">
                <div class="progress-inner" style="width: {{ min(100, ((float) $balance->used_days / max(1, (float) $balance->total_days)) * 100) }}%"></div>
            </div>
            <p class="muted mt mb-0">Unused days do not carry over to the next leave year and are not paid out.</p>
        </div>
    @endif

    <div class="card mt">
        <h2>Usage by type (approved)</h2>
        @if($usage->isEmpty())
            <p class="muted mb-0">No approved leaves in the current window.</p>
        @else
            <table>
                <thead><tr><th>Type</th><th>Days used</th></tr></thead>
                <tbody>
                    @foreach($usage as $type => $days)
                        <tr>
                            <td>{{ ucfirst($type) }}</td>
                            <td>{{ (float) $days }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td><strong>Unpaid</strong></td>
                        <td><span class="muted">unlimited</span></td>
                    </tr>
                </tbody>
            </table>
        @endif
    </div>
@endsection