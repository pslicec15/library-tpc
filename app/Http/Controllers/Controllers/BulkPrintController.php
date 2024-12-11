<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkPrintController extends Controller
{
    public function index()
    {
        $students = DB::table('students')
            ->join('courses', 'students.courseID', '=', 'courses.id')
            ->join('year_levels', 'students.yearLevelID', '=', 'year_levels.id')
            ->select('students.*', 'courses.description as course', 'year_levels.yearLevel as yearLevel')
            ->get();

        $instructors = DB::table('instructors')
            ->join('courses', 'instructors.departmentID', '=', 'courses.id')
            ->select('instructors.*', 'courses.description as department')
            ->get();

        return view('bulk-print.bulk-print', ['students' => $students, 'instructors' => $instructors]);
    }

    public function print(Request $request)
    {
        $selectedStudents = $request->input('selected_students', []);
        $selectedInstructors = $request->input('selected_instructors', []);
        $studentsData = [];
        $instructorsData = [];

        foreach ($selectedStudents as $selectedStudent) {
            $student = DB::table('students')
                ->join('courses', 'students.courseID', '=', 'courses.id')
                ->join('year_levels', 'students.yearLevelID', '=', 'year_levels.id')
                ->select('students.*', 'courses.description as course', 'year_levels.yearLevel as yearLevel')
                ->where('students.studentNo', '=', $selectedStudent)
                ->first();

            $studentsData[] = $student;
        }
        foreach ($selectedInstructors as $selectedInstructor) {
            $instructor = DB::table('instructors')
                ->join('courses', 'instructors.departmentID', '=', 'courses.id')
                ->select('instructors.*', 'courses.description as department')
                ->where('instructors.instructorNo','=',$selectedInstructor)
                ->first();

            $instructorsData[] = $instructor;
        }

        return view('components.bulk-print-pass', ['selectedStudents' => $studentsData, 'selectedInstructors' => $instructorsData]);
    }
}
