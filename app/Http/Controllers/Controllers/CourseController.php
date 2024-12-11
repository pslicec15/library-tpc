<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::all();
        return view('course.index', ['courses' => $courses]);

    }

    public function create()
    {
        return view('course.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', Rule::unique('courses', 'code')],
            'description' => ['required', Rule::unique('courses', 'description')]
        ]);

        Course::create($validated);

        return redirect('/course')->with('success', 'New course was added successfully!');
    }

    public function edit($id)
    {
        $data = DB::table('courses')
            ->select('courses.*')
            ->where('courses.id', $id)
            ->first();

        return view('course.edit', ['course' => $data]);
    }

    public function update(Request $request, $id)
    {
        $data = Course::find($id);
        $validatedData = $request->validate([
            'code' => ['required', Rule::unique('courses', 'code')],
            'description' => ['required', Rule::unique('courses', 'description')]
        ]);
        $data->update($validatedData);
        
        return redirect('/course')->with('success', 'Course updated successfully');
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return back()->with('success', 'Course deleted successfully');
    }
}
