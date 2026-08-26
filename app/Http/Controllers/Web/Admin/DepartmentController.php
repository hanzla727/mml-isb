<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Departments are a shared, org-wide list (Fundraising, Hospital, Mosque,
 * ...) — the same set applies across every UC. A Department is not
 * scoped to a UC/PP; Team is what actually belongs to a specific UC.
 */
class DepartmentController extends Controller
{
    public function index()
    {
        return view('admin.departments.index', [
            'departments' => Department::withCount(['users'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
            'description' => ['nullable', 'string'],
        ]);

        Department::create($validated);

        return back()->with('status', 'Department created.');
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($department->id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $department->update($validated);

        return back()->with('status', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return back()->with('status', 'Department deleted.');
    }
}
