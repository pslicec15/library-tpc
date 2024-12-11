<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Department;
use App\Models\Instructor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class InstructorController extends Controller
{
    public function index()
    {
        $departments = Course::all();
        $instructors = DB::table('instructors')
            ->join('courses', 'instructors.departmentID', '=', 'courses.id')
            ->select('instructors.*', 'courses.code as department')
            ->get();
        foreach ($instructors as $instructor) {
            $birthdate = Carbon::parse($instructor->birthdate);
            $today = Carbon::now();
            $age = $birthdate->diffInYears($today);
            $instructor->age = $age;
        }
        return view('instructor.index', ['instructors' => $instructors, 'departments' => $departments]);
    }

    public function create()
    {
        $departments = Course::all();
        return view('instructor.create', ['departments' => $departments]);
    }

    public function store(Request $request)
    {
        $capturedImageData = $request->input('photo');
        $decodedImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $capturedImageData));
        $imagePath = 'img' . DIRECTORY_SEPARATOR . 'instructor';
        $fileName = $request->input('instructorNo') . '.png';
        $filePath = public_path($imagePath . DIRECTORY_SEPARATOR . $fileName);
        // Save the image to the specified path
        if (!File::isDirectory(public_path($imagePath))) {
            File::makeDirectory(public_path($imagePath), 0777, true, true);
        }
        file_put_contents($filePath, $decodedImageData);

        $validated = $request->validate([
            'instructorNo' => ['required', Rule::unique('instructors', 'instructorNo')],
            'firstName' => 'required',
            'middleName' => 'nullable',
            'lastName' => 'required',
            'sex' => 'required',
            'birthdate' => 'required',
            'departmentID' => 'required',
            'position' => 'required',
            'contactNo' => 'nullable|numeric|digits:11',
            'photo' => 'nullable',
        ]);

        $validated['photo'] = $filePath;

        Instructor::create($validated);

        return redirect('/instructor')->with('success', 'New instructor was added successfully!');
    }

    public function show($id)
    {
        $data = DB::table('instructors')
            ->join('courses', 'instructors.departmentID', '=', 'courses.id')
            ->select('instructors.*', 'courses.description as department')
            ->where('instructors.id', $id)
            ->first();
        $data->birthdate = Carbon::parse($data->birthdate);
        $now = Carbon::now();
        $age = $now->diff($data->birthdate)->y;

        return view('instructor.view', ['instructor' => $data, 'age' => $age]);
    }

    public function edit($id)
    {
        $departments = Course::all();

        $data = DB::table('instructors')
            ->join('courses', 'instructors.departmentID', '=', 'courses.id')
            ->select('instructors.*', 'courses.description as department')
            ->where('instructors.id', $id)
            ->first();

        return view('instructor.edit', ['instructor' => $data, 'departments' => $departments]);
    }

    public function update(Request $request, $id)
    {
        $data = Instructor::find($id);
        $validatedData = $request->validate([
            'firstName' => 'required',
            'middleName' => 'nullable',
            'lastName' => 'required',
            'sex' => 'required',
            'birthdate' => 'required',
            'departmentID' => 'required',
            'position' => 'required',
            'contactNo' => 'nullable',
            'photo' => 'nullable',
        ]);

        if ($data->photo) {
            File::delete(public_path($data->photo));

            $capturedImageData = $request->input('photo');
            $decodedImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $capturedImageData));
            $imagePath = 'img' . DIRECTORY_SEPARATOR . 'instructor';
            $fileName = $data->instructorNo . '.png';
            $filePath = public_path($imagePath . DIRECTORY_SEPARATOR . $fileName);

            if (!File::isDirectory(public_path($imagePath))) {
                File::makeDirectory(public_path($imagePath), 0777, true, true);
            }

            file_put_contents($filePath, $decodedImageData);

            $validatedData['photo'] = $filePath;
        } else {
            $capturedImageData = $request->input('photo');
            $decodedImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $capturedImageData));
            $imagePath = 'img' . DIRECTORY_SEPARATOR . 'instructor';
            $fileName = $request->input('instructorNo') . '.png';
            $filePath = public_path($imagePath . DIRECTORY_SEPARATOR . $fileName);
            if (!File::isDirectory(public_path($imagePath))) {
                File::makeDirectory(public_path($imagePath), 0777, true, true);
            }

            file_put_contents($filePath, $decodedImageData);
            $validatedData['photo'] = $filePath;
        }

        $data->update($validatedData);

        // Redirect back to the edit view with updated data and success message
        return redirect('/instructor')->with('success', 'Instructor updated successfully');
    }

    public function destroy($id)
    {
        $instructor = Instructor::findOrFail($id);
        $instructor->delete();
        return back()->with('success', 'Instructor deleted successfully');
    }

    public function print($id)
    {
        $data = DB::table('instructors')
            ->join('courses', 'instructors.departmentID', '=', 'courses.id')
            ->select('instructors.*', 'courses.description as department')
            ->where('instructors.id', $id)
            ->first();

        return view('components.print-pass', ['instructor' => $data]);
    }
}
