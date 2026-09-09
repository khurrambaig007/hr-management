@extends('hr::layouts.app')

@section('title', 'Edit Leave Settlement')

@section('hrcontent')
    <h1>Edit Leave Settlement &mdash; {{ $settlement->year }}</h1>

    <div class="card">
        <form method="POST" action="{{ route('hr.settlements.update', $settlement) }}">
            @csrf @method('PUT')

            <div class="form-row">
                <div class="form-group">
                    <label>Year</label>
                    <input type="number" name="year" min="2000" max="2100" value="{{ old('year', $settlement->year) }}" required>
                </div>
                <div class="form-group">
                    <label>Title (optional)</label>
                    <input type="text" name="title" value="{{ old('title', $settlement->title) }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Total days per year</label>
                    <input type="number" step="0.5" min="0" max="365" name="total_days" value="{{ old('total_days', $settlement->total_days) }}" required>
                </div>
                <div class="form-group">
                    <label>Probation cap</label>
                    <input type="number" step="0.5" min="0" max="365" name="probation_cap" value="{{ old('probation_cap', $settlement->probation_cap) }}" required>
                </div>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $settlement->is_active))
                           style="width:auto; display:inline-block; margin-right:0.4rem;">
                    Active policy (activating this deactivates the others)
                </label>
            </div>

            <div class="form-group">
                <label>Notes (optional)</label>
                <textarea name="notes" rows="3">{{ old('notes', $settlement->notes) }}</textarea>
            </div>

            <button class="btn" type="submit">Update Settlement</button>
        </form>
    </div>
@endsection