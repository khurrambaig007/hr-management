@extends('hr::layouts.app')

@section('title', 'Add Employee')

@section('hrcontent')
    <h1>Add Employee</h1>

    <div class="card">
        <form method="POST" action="{{ route('hr.employees.store') }}">
            @csrf

            <div class="form-group">
                <label>User account</label>
                <select name="user_id" required>
                    <option value="" disabled selected>Select a user</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name ?? $user->email }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Department</label>
                    <select name="department_id">
                        <option value="">None</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Position</label>
                    <input type="text" name="position" value="{{ old('position') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Salary</label>
                    <input type="number" step="0.01" min="0" name="salary" value="{{ old('salary') }}">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}">
                </div>
            </div>

            <div class="form-group">
                <label>Address</label>
                <textarea name="address" rows="2">{{ old('address') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Hire date</label>
                    <input type="date" name="hire_date" value="{{ old('hire_date') }}">
                </div>
                <div class="form-group">
                    <label>Probation end date</label>
                    <input type="date" name="probation_end_date" value="{{ old('probation_end_date') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" required>
                        @foreach(config('hr-management.roles') as $key => $value)
                            <option value="{{ $value }}" @selected(old('role', 'employee') === $value)>{{ ucfirst($value) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_permanent" value="1" @checked(old('is_permanent'))
                               style="width:auto; display:inline-block; margin-right:0.4rem;">
                        Permanent (confirmed)
                    </label>
                </div>
            </div>

            <button class="btn" type="submit">Create Employee</button>
        </form>
    </div>
@endsection