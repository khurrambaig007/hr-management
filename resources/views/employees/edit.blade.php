@extends('hr::layouts.app')

@section('title', 'Edit Employee')

@section('hrcontent')
    <h1>Edit Employee &mdash; {{ $employee->display_name }}</h1>

    <div class="card">
        <form method="POST" action="{{ route('hr.employees.update', $employee) }}">
            @csrf @method('PUT')

            <div class="form-group">
                <label>User account</label>
                <select name="user_id" required>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('user_id', $employee->user_id) == $user->id)>{{ $user->name ?? $user->email }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Department</label>
                    <select name="department_id">
                        <option value="">None</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id', $employee->department_id ?? '') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Position</label>
                    <input type="text" name="position" value="{{ old('position', $employee->position) }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Salary</label>
                    <input type="number" step="0.01" min="0" name="salary" value="{{ old('salary', $employee->salary) }}">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}">
                </div>
            </div>

            <div class="form-group">
                <label>Address</label>
                <textarea name="address" rows="2">{{ old('address', $employee->address) }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Hire date</label>
                    <input type="date" name="hire_date" value="{{ old('hire_date', optional($employee->hire_date)->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label>Probation end date</label>
                    <input type="date" name="probation_end_date" value="{{ old('probation_end_date', optional($employee->probation_end_date)->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" required>
                        @foreach(config('hr-management.roles') as $key => $value)
                            <option value="{{ $value }}" @selected(old('role', $employee->role) === $value)>{{ ucfirst($value) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_permanent" value="1" @checked(old('is_permanent', $employee->is_permanent))
                               style="width:auto; display:inline-block; margin-right:0.4rem;">
                        Permanent (confirmed)
                    </label>
                </div>
            </div>

            <button class="btn" type="submit">Update Employee</button>
        </form>
    </div>
@endsection