@extends('hr::layouts.app')

@section('title', 'Employees')

@section('hrcontent')
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
        <h1>Employees</h1>
        <a class="btn" href="{{ route('hr.employees.create') }}">Add Employee</a>
    </div>

    <div class="card mt">
        <form method="GET" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end;">
            <div class="form-group" style="margin-bottom:0; min-width:180px;">
                <label>Department</label>
                <select name="department_id">
                    <option value="">All departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0; min-width:160px;">
                <label>Role</label>
                <select name="role">
                    <option value="">All roles</option>
                    @foreach(config('hr-management.roles') as $key => $value)
                        <option value="{{ $value }}" @selected(request('role') === $value)>{{ ucfirst($value) }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-outline" type="submit">Filter</button>
            <a class="btn btn-outline" href="{{ route('hr.employees.index') }}">Reset</a>
        </form>
    </div>

    <div class="card">
        @if($employees->isEmpty())
            <p class="muted mb-0">No employees found.</p>
        @else
            <table>
                <thead>
                    <tr><th>Name</th><th>Department</th><th>Position</th><th>Role</th><th>Status</th><th class="text-right">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($employees as $employee)
                        <tr>
                            <td>{{ $employee->display_name }}</td>
                            <td>{{ $employee->department?->name ?? '—' }}</td>
                            <td>{{ $employee->position ?? '—' }}</td>
                            <td>
                                @if($employee->isAdmin()) <span class="badge badge-red">Admin</span>
                                @elseif($employee->isManager()) <span class="badge badge-blue">Manager</span>
                                @else <span class="badge badge-gray">Employee</span> @endif
                            </td>
                            <td>
                                @if($employee->is_permanent) <span class="badge badge-green">Permanent</span>
                                @else <span class="badge badge-yellow">Probation</span> @endif
                            </td>
                            <td class="text-right">
                                <div class="actions" style="justify-content:flex-end;">
                                    <a class="btn btn-outline btn-sm" href="{{ route('hr.employees.show', $employee) }}">View</a>
                                    <a class="btn btn-outline btn-sm" href="{{ route('hr.employees.edit', $employee) }}">Edit</a>
                                    <form method="POST" action="{{ route('hr.employees.destroy', $employee) }}"
                                          onsubmit="return confirm('Delete this employee profile?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm" style="background:var(--red)" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="paginator">{{ $employees->links(config('hr-management.pagination_view')) }}</div>
        @endif
    </div>
@endsection