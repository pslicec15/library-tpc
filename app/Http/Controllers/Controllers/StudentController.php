<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use App\Models\YearLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index()
    {
        $courses = Course::all();
        $yearLevels = YearLevel::all();
        $students = DB::table('students')
            ->join('courses', 'students.courseID', '=', 'courses.id')
            ->join('year_levels', 'students.yearLevelID', '=', 'year_levels.id')
            ->select('students.*', 'courses.code as course', 'year_levels.yearLevel as yearLevel')
            ->get();

        foreach ($students as $student) {
            $birthdate = Carbon::parse($student->birthdate);
            $today = Carbon::now();
            $age = $birthdate->diffInYears($today);
            $student->age = $age;
        }

        return view('student.index', ['students' => $students, 'courses' => $courses, 'yearLevels' => $yearLevels]);
    }

    public function create()
    {
        $courses = Course::all();
        $yearLevels = YearLevel::all();
        return view('student.create', ['courses' => $courses, 'yearLevels' => $yearLevels]);
    }

    public function store(Request $request)
    {
        $capturedImageData = $request->input('photo');
        $decodedImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $capturedImageData));
        $publicPath = public_path();
        // Specify the subdirectories within the public directory
        $imagePath = 'img' . DIRECTORY_SEPARATOR . 'student';
        $fileName = $request->input('studentNo') . '.png';
        $filePath = public_path($imagePath . DIRECTORY_SEPARATOR . $fileName);
        // Save the image to the specified path
        if (!File::isDirectory(public_path($imagePath))) {
            File::makeDirectory(public_path($imagePath), 0777, true, true);
        }

        file_put_contents($filePath, $decodedImageData);

        $validated = $request->validate([
            'studentNo' => ['required', Rule::unique('students', 'studentNo')],
            'firstName' => 'required',
            'middleName' => 'nullable',
            'lastName' => 'required',
            'sex' => 'required',
            'birthdate' => 'required',
            'courseID' => 'required',
            'yearLevelID' => 'required',
            'contactNo' => 'nullable|numeric|digits:11',
            'address' => 'required',
            'photo' => 'nullable',
        ]);

        $validated['photo'] = $filePath;

        Student::create($validated);

        return redirect('student/add')->with('success', 'New student was added successfully!');
    }

    public function show($id)
    {
        $data = DB::table('students')
            ->join('courses', 'students.courseID', '=', 'courses.id')
            ->join('year_levels', 'students.yearLevelID', '=', 'year_levels.id')
            ->select('students.*', 'courses.description as courseName', 'year_levels.yearLevel as yearLevel')
            ->where('students.id', $id)
            ->first();
        $data->birthdate = Carbon::parse($data->birthdate);
        $now = Carbon::now();
        $age = $now->diff($data->birthdate)->y;
        return view('student.view', ['student' => $data, 'age' => $age]);
    }

    public function edit($id)
    {
        $courses = Course::all();
        $yearLevels = YearLevel::all();

        $data = DB::table('students')
            ->join('courses', 'students.courseID', '=', 'courses.id')
            ->join('year_levels', 'students.yearLevelID', '=', 'year_levels.id')
            ->select('students.*', 'courses.description as courseName', 'year_levels.yearLevel as yearLevel')
            ->where('students.id', $id)
            ->first();
        $data->birthdate = Carbon::parse($data->birthdate);

        return view('student.edit', ['student' => $data, 'courses' => $courses, 'yearLevels' => $yearLevels]);
    }

    public function update(Request $request, $id)
    {
        $data = Student::find($id);
        $validatedData = $request->validate([
            'firstName' => 'required',
            'middleName' => 'nullable',
            'lastName' => 'required',
            'sex' => 'required',
            'birthdate' => 'required',
            'courseID' => 'required',
            'yearLevelID' => 'required',
            'contactNo' => 'nullable|numeric|digits:11',
            'address' => 'required',
            'photo' => 'nullable',
        ]);

        if ($data->photo) {
            File::delete(public_path($data->photo));

            $capturedImageData = $request->input('photo');
            $decodedImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $capturedImageData));
            $imagePath = 'img' . DIRECTORY_SEPARATOR . 'student';
            $fileName = $data->studentNo . '.png';
            $filePath = public_path($imagePath . DIRECTORY_SEPARATOR . $fileName);

            if (!File::isDirectory(public_path($imagePath))) {
                File::makeDirectory(public_path($imagePath), 0777, true, true);
            }

            file_put_contents($filePath, $decodedImageData);

            $validatedData['photo'] = $filePath;
        } else {
            $capturedImageData = $request->input('photo');
            $decodedImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $capturedImageData));
            $imagePath = 'img' . DIRECTORY_SEPARATOR . 'student';
            $fileName = $request->input('studentNo') . '.png';
            $filePath = public_path($imagePath . DIRECTORY_SEPARATOR . $fileName);
            if (!File::isDirectory(public_path($imagePath))) {
                File::makeDirectory(public_path($imagePath), 0777, true, true);
            }

            file_put_contents($filePath, $decodedImageData);
            $validatedData['photo'] = $filePath;
        }

        $data->update($validatedData);

        return redirect('/student')->with('success', 'Student updated successfully');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return back()->with('success', 'Student deleted successfully');
    }

    public function print($id)
    {
        $data = DB::table('students')
            ->join('courses', 'students.courseID', '=', 'courses.id')
            ->join('year_levels', 'students.yearLevelID', '=', 'year_levels.id')
            ->select('students.*', 'courses.description as courseName', 'year_levels.yearLevel as yearLevel')
            ->where('students.id', $id)
            ->first();
        return view('components.print-pass', ['student' => $data]);
    }
    public function addStudent(Request $request)
{
    $request->validate([
        'studentNo' => 'required|unique:students',
        'firstName' => 'required|string',
        'lastName' => 'required|string',
        'courseID' => 'required',
        'sex' => 'required',
        'birthdate' => 'required|date',
        'yearLevelID' => 'required',
        'contactNo' => 'required|regex:/^[0-9]{11}$/',
        'address' => 'required|string',
        'uploadedPhoto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Save student data
    $student = new Student();
    $student->studentNo = $request->input('studentNo');
    $student->firstName = $request->input('firstName');
    $student->lastName = $request->input('lastName');
    // Add other fields as needed

    // Handle captured image from webcam
    if ($request->filled('photo')) {
        $imageData = $request->input('photo');
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = base64_decode($imageData);
        $fileName = 'photos/' . uniqid() . '.png';
        Storage::disk('public')->put($fileName, $imageData);
        $student->photo = $fileName;
    }

    // Handle uploaded photo file
    if ($request->hasFile('uploadedPhoto')) {
        $fileName = $request->file('uploadedPhoto')->store('photos', 'public');
        $student->photo = $fileName;
    }

    $student->save();

    return redirect()->back()->with('success', 'Student added successfully');
}
}
