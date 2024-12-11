<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::all();
        return view('department.index', ['departments' => $departments]);

    }

    public function create()
    {
        return view('department.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'departmentName' => ['required', Rule::unique('departments', 'departmentName')]
        ]);

        Department::create($validated);

        return redirect('/department')->with('success', 'New department was added successfully!');
    }

    public function edit($id)
    {
        $data = DB::table('departments')
            ->select('departments.*')
            ->where('departments.id', $id)
            ->first();

        return view('department.edit', ['department' => $data]);
    }

    public function update(Request $request, $id)
    {
        $data = Department::find($id);
        $validatedData = $request->validate([
            'departmentName' => ['required', Rule::unique('departments', 'departmentName')]
        ]);
        $data->update($validatedData);

        // Redirect back to the edit view with updated data and success message
        return redirect('/department')->with('success', 'Department updated successfully');
    }

    public function destroy($id)
    {
        $departmnent = Department::findOrFail($id);
        $departmnent->delete();

        return back()->with('success', 'Department deleted successfully');
    }
}
