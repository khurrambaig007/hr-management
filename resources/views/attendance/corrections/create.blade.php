@extends('hr::layouts.app')

@section('title', 'Request Correction')

@section('hrcontent')
    <h1>Request Attendance Correction</h1>

    <div class="card">
        <p class="muted">
            Select the attendance record you need to fix, then fill in only the
            time(s) that are wrong. You can fill in a missing clock-in or clock-out,
            or correct a wrong one. Leave a field empty if that time is correct.
        </p>

        <form method="POST" action="{{ route('hr.attendance.corrections.store') }}">
            @csrf

            <div class="form-group">
                <label>Attendance record</label>
                <select name="attendance_id" required>
                    <option value="" disabled selected>Select a record</option>
                    @foreach($attendances as $attendance)
                        <option value="{{ $attendance->id }}"
                                @selected(old('attendance_id') == $attendance->id)>
                            {{ $attendance->date->format('d M Y') }}
                            &mdash; in: {{ $attendance->clock_in?->format('H:i') ?? 'missing' }}
                            / out: {{ $attendance->clock_out?->format('H:i') ?? 'missing' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Corrected clock in (optional)</label>
                    <input type="datetime-local" name="requested_clock_in" value="{{ old('requested_clock_in') }}">
                </div>
                <div class="form-group">
                    <label>Corrected clock out (optional)</label>
                    <input type="datetime-local" name="requested_clock_out" value="{{ old('requested_clock_out') }}">
                </div>
            </div>

            <div class="form-group">
                <label>Reason</label>
                <textarea name="reason" rows="3" required>{{ old('reason') }}</textarea>
            </div>

            <button class="btn" type="submit">Submit correction request</button>
        </form>
    </div>
@endsection