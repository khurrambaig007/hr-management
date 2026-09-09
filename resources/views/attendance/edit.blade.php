@extends('hr::layouts.app')

@section('title', 'Edit Attendance')

@section('hrcontent')
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
        <h1>Edit Attendance</h1>
        <a class="btn btn-outline btn-sm" href="{{ route('hr.attendance.index') }}">Back</a>
    </div>

    <div class="card mt">
        <form method="POST" action="{{ route('hr.attendance.update', $attendance) }}">
            @csrf @method('PUT')

            <div class="form-group">
                <label>Employee</label>
                <select name="user_id" required>
                    @foreach($employeesList as $employeeOption)
                        <option value="{{ $employeeOption->user_id }}"
                                @selected(old('user_id', $attendance->user_id) == $employeeOption->user_id)>
                            {{ $employeeOption->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" value="{{ old('date', optional($attendance->date)->format('Y-m-d')) }}" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Clock in</label>
                    <input type="datetime-local" name="clock_in" value="{{ old('clock_in', optional($attendance->clock_in)->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="form-group">
                    <label>Clock out</label>
                    <input type="datetime-local" name="clock_out" value="{{ old('clock_out', optional($attendance->clock_out)->format('Y-m-d\TH:i')) }}">
                </div>
            </div>

            <div class="form-group">
                <label>Status (leave empty to auto-recompute)</label>
                <select name="status">
                    <option value="">Auto</option>
                    @foreach(['present' => 'green', 'absent' => 'red', 'late' => 'yellow', 'half_day' => 'blue'] as $status => $color)
                        <option value="{{ $status }}" @selected(old('status', $attendance->status) === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="3">{{ old('notes', $attendance->notes) }}</textarea>
            </div>

            <button class="btn" type="submit">Save Changes</button>
        </form>
    </div>
@endsection