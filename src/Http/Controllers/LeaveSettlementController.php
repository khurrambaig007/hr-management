<?php

namespace Khurrambaig\HRManagement\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\Rule;
use Khurrambaig\HRManagement\Http\Traits\ProvidesEmployeeContext;
use Khurrambaig\HRManagement\Models\LeaveSettlement;

class LeaveSettlementController extends BaseController
{
    use ProvidesEmployeeContext;

    public function index()
    {
        $this->requireAdmin();

        $settlements = LeaveSettlement::query()->latest('year')->paginate(15);

        return view('hr::settlements.index', compact('settlements'));
    }

    public function create()
    {
        $this->requireAdmin();

        return view('hr::settlements.create');
    }

    public function store()
    {
        $this->requireAdmin();

        $data = request()->validate($this->rules());

        $active = $data['is_active'] ?? false;

        if ($active) {
            LeaveSettlement::query()->update(['is_active' => false]);
        }

        LeaveSettlement::create($data + ['is_active' => $active]);

        return redirect()->route('hr.settlements.index')->with('success', 'Leave settlement created.');
    }

    public function edit(LeaveSettlement $settlement)
    {
        $this->requireAdmin();

        return view('hr::settlements.edit', compact('settlement'));
    }

    public function update(LeaveSettlement $settlement)
    {
        $this->requireAdmin();

        $data = request()->validate($this->rules($settlement->id));

        $active = $data['is_active'] ?? false;

        if ($active) {
            LeaveSettlement::query()->where('id', '!=', $settlement->id)->update(['is_active' => false]);
        }

        $settlement->update($data + ['is_active' => $active]);

        return redirect()->route('hr.settlements.index')->with('success', 'Leave settlement updated.');
    }

    public function destroy(LeaveSettlement $settlement)
    {
        $this->requireAdmin();

        $settlement->delete();

        return redirect()->route('hr.settlements.index')->with('success', 'Leave settlement deleted.');
    }

    protected function rules(?int $ignoreId = null): array
    {
        return [
            'year' => ['required', 'integer', 'between:2000,2100', Rule::unique('leave_settlements', 'year')->ignore($ignoreId)],
            'title' => ['nullable', 'string', 'max:255'],
            'total_days' => ['required', 'numeric', 'min:0', 'max:365'],
            'probation_cap' => ['required', 'numeric', 'min:0', 'max:365'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}