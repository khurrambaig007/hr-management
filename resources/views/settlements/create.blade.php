@extends('hr::layouts.app')

@section('title', 'New Leave Settlement')

@section('hrcontent')
    <h1>New Leave Settlement</h1>

    <div class="card">
        <form method="POST" action="{{ route('hr.settlements.store') }}">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label>Year</label>
                    <input type="number" name="year" min="2000" max="2100" value="{{ old('year', now()->year) }}" required>
                </div>
                <div class="form-group">
                    <label>Title (optional)</label>
                    <input type="text" name="title" value="{{ old('title') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Total days per year</label>
                    <input type="number" step="0.5" min="0" max="365" name="total_days" value="{{ old('total_days', 20) }}" required>
                </div>
                <div class="form-group">
                    <label>Probation cap</label>
                    <input type="number" step="0.5" min="0" max="365" name="probation_cap" value="{{ old('probation_cap', 2) }}" required>
                </div>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))
                           style="width:auto; display:inline-block; margin-right:0.4rem;">
                    Active policy (activating this deactivates the others)
                </label>
            </div>

            <div class="form-group">
                <label>Notes (optional)</label>
                <textarea name="notes" rows="3">{{ old('notes') }}</textarea>
            </div>

            <button class="btn" type="submit">Create Settlement</button>
        </form>
    </div>
@endsection