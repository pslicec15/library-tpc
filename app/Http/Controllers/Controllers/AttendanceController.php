<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Instructor;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('attendance.tap-in-tap-out');
    }

    public function show()
    {
        $studentAttendance = DB::table('attendance')
            ->join('students', 'attendance.studentId', '=', 'students.id')
            ->join('courses', 'students.courseID', '=', 'courses.id')
            ->join('year_levels', 'students.yearLevelID', '=', 'year_levels.id')
            ->select(
                'attendance.*',
                'students.studentNo as studentNo',
                'students.firstName as studentFirstName',
                'students.middleName as studentmiddleName',
                'students.lastName as studentLastName',
                'courses.description as course',
                'year_levels.yearLevel as yearLevel'
            )
            ->orderBy('attendance.timedOut', 'desc')
            ->orderBy('attendance.updated_at', 'desc')
            ->get();
        foreach ($studentAttendance as $attendance) {
            $attendance->timedIn = Carbon::parse($attendance->timedIn)->format('h:i A');
            $attendance->timedOut = Carbon::parse($attendance->timedOut)->format('h:i A');
        }

        $instructorAttendance = DB::table('attendance')
            ->join('instructors', 'attendance.instructorId', '=', 'instructors.id')
            ->join('courses', 'instructors.departmentID', '=', 'courses.id')
            ->select(
                'attendance.*',
                'instructors.instructorNo as instructorNo',
                'instructors.firstName as instructorFirstName',
                'instructors.middleName as instructormiddleName',
                'instructors.lastName as instructorLastName',
                'courses.description as department'
            )
            ->orderBy('attendance.timedOut', 'desc')
            ->orderBy('attendance.updated_at', 'desc')
            ->get();
        foreach ($instructorAttendance as $attendance) {
            $attendance->timedIn = Carbon::parse($attendance->timedIn)->format('h:i A');
            $attendance->timedOut = Carbon::parse($attendance->timedOut)->format('h:i A');
        }

        return view('attendance.history', ['studentAttendance' => $studentAttendance, 'instructorAttendance' => $instructorAttendance]);
    }

    public function store(Request $request)
    {
        $inputBarcodeData = $request->input('barcode_data');
        $barcodeParts = explode('/', $inputBarcodeData);
        $barcodeData = null;
        $currentUser = null;
        $data = null;

        if (count($barcodeParts) >= 2) {
            $barcodeData = $barcodeParts[0];
            $currentUser = $barcodeParts[1];
        }

        if ($currentUser == 'student') {
            $student = Student::where('studentNo', $barcodeData)->first();
            $studentAttendance = Attendance::where('attendance.studentId', $student->id)
                ->where('attendance.timedOut', NULL)
                ->first();
            if (!$studentAttendance) {
                // No existing record with timedIn and without timedOut, create a new record
                $student = Student::where('studentNo', $barcodeData)->first();

                if ($student) {
                    Attendance::create([
                        'date' => now('Asia/Manila'),
                        'studentId' => $student->id,
                        'instructorId' => null,
                        'timedIn' => now('Asia/Manila'),
                        'timedOut' => null
                    ]);
                    $data = DB::table('attendance')
                        ->join('students', 'attendance.studentId', '=', 'students.id')
                        ->select('attendance.*', 'students.studentNo as studentNo', 'students.firstName as firstName', 'students.lastName as lastName', 'students.photo as photo')
                        ->where('attendance.studentId',  $student->id)
                        ->where('attendance.timedOut', NULL)
                        ->first();
                    return back()->with(['success' => 'Tap in successfully.', 'attendance' => $data, 'student' => 1]);
                } else {
                    return abort(404);
                }
            } else {
                // Student has already timed in, update the timedOut field
                $studentAttendance->update(['timedOut' => now('Asia/Manila')]);
                $data = DB::table('attendance')
                    ->join('students', 'attendance.studentId', '=', 'students.id')
                    ->select('attendance.*', 'students.studentNo as studentNo', 'students.firstName as firstName', 'students.lastName as lastName', 'students.photo as photo')
                    ->where('attendance.id',  $studentAttendance->id)
                    ->first();
                return back()->with(['success' => 'Tap out successfully.', 'attendance' => $data, 'student' => 1]);
            }
        }


        if ($currentUser == 'instructor') {
            $instructor = Instructor::where('instructorNo', $barcodeData)->first();
            $instructorAttendance = Attendance::where('attendance.instructorId', $instructor->id)
                ->where('attendance.timedOut', NULL)
                ->first();
            if (!$instructorAttendance) {
                // No existing record with timedIn and without timedOut, create a new record
                $instructor = Instructor::where('instructorNo', $barcodeData)->first();

                if ($instructor) {
                    Attendance::create([
                        'date' => now('Asia/Manila'),
                        'studentId' => null,
                        'instructorId' => $instructor->id,
                        'timedIn' => now('Asia/Manila'),
                        'timedOut' => null
                    ]);

                    $data = DB::table('attendance')
                        ->join('instructors', 'attendance.instructorId', '=', 'instructors.id')
                        ->select('attendance.*', 'instructors.instructorNo as instructorNo', 'instructors.firstName as firstName', 'instructors.lastName as lastName','instructors.photo as photo')
                        ->where('attendance.instructorId',  $instructor->id)
                        ->where('attendance.timedOut', NULL)
                        ->first();
                    return back()->with(['success' => 'Tap in successfully.', 'attendance' => $data, 'instructor' => 1]);
                } else {
                    return abort(404);
                }
            } else {
                // Instructor has already timed in, update the timedOut field
                $instructorAttendance->update(['timedOut' => now('Asia/Manila')]);
                $data = DB::table('attendance')
                    ->join('instructors', 'attendance.instructorId', '=', 'instructors.id')
                    ->select('attendance.*', 'instructors.instructorNo as instructorNo', 'instructors.firstName as firstName', 'instructors.lastName as lastName', 'instructors.photo as photo')
                    ->where('attendance.id',  $instructorAttendance->id)
                    ->first();
                return back()->with(['success' => 'Tap out successfully.', 'attendance' => $data, 'instructor' => 1]);
            }
        }
    }
}
