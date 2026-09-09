@extends('hr::layouts.app')

@section('title', 'Apply for Leave')

@section('hrcontent')
    <h1>Apply for Leave</h1>

    <div class="card">
        <form method="POST" action="{{ route('hr.leaves.store') }}">
            @csrf

            <div class="form-group">
                <label>Leave type</label>
                <select name="type" required>
                    @foreach(config('hr-management.leave_types') as $type)
                        <option value="{{ $type }}" @selected(old('type') === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Start date</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required>
                </div>
                <div class="form-group">
                    <label>End date</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label>Reason</label>
                <textarea name="reason" rows="3" required>{{ old('reason') }}</textarea>
            </div>

            <button class="btn" type="submit">Submit request</button>
        </form>
    </div>
@endsection