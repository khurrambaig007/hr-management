@extends('hr::layouts.app')

@section('title', 'Leave Settlements')

@section('hrcontent')
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
        <h1>Leave Settlements</h1>
        <a class="btn" href="{{ route('hr.settlements.create') }}">New Settlement</a>
    </div>

    <div class="card mt">
        <p class="muted mb-0">
            Each settlement defines the company-wide yearly leave quota.
            Ensure the correct year is marked <strong>active</strong> — it is what employees accrue against.
        </p>
    </div>

    <div class="card">
        @if($settlements->isEmpty())
            <p class="muted mb-0">No settlements yet. <a href="{{ route('hr.settlements.create') }}">Create one.</a></p>
        @else
            <table>
                <thead>
                    <tr><th>Year</th><th>Title</th><th>Total days</th><th>Probation cap</th><th>Status</th><th class="text-right">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($settlements as $settlement)
                        <tr>
                            <td>{{ $settlement->year }}</td>
                            <td>{{ $settlement->title ?? '—' }}</td>
                            <td>{{ (float) $settlement->total_days }}</td>
                            <td>{{ (float) $settlement->probation_cap }}</td>
                            <td>
                                @if($settlement->is_active) <span class="badge badge-green">Active</span>
                                @else <span class="badge badge-gray">Inactive</span> @endif
                            </td>
                            <td class="text-right">
                                <div class="actions" style="justify-content:flex-end;">
                                    <a class="btn btn-outline btn-sm" href="{{ route('hr.settlements.edit', $settlement) }}">Edit</a>
                                    <form method="POST" action="{{ route('hr.settlements.destroy', $settlement) }}"
                                          onsubmit="return confirm('Delete this settlement?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm" style="background:var(--red)" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="paginator">{{ $settlements->links(config('hr-management.pagination_view')) }}</div>
        @endif
    </div>
@endsection