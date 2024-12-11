<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalBooksCount = DB::table('books')->count();
        $totalStudentsCount = DB::table('students')->count();
        $uniqueStudentBorrowers = DB::table('borrowed')
            ->select('studentId')
            ->distinct()
            ->whereNotNull('studentId')
            ->where('borrowed.status','=', null)
            ->get();
        $uniqueInstructorBorrowers = DB::table('borrowed')
            ->select('instructorId')
            ->distinct()
            ->whereNotNull('instructorId')
            ->where('borrowed.status','=', null)
            ->get();

        $totalUniqueBorrowersCount = count($uniqueStudentBorrowers) + count($uniqueInstructorBorrowers);
        $totalInstructorsCount = DB::table('instructors')->count();

        $topBorrowedBooks = DB::table('books')
            ->select('books.id', 'books.bookTitle', 'books.author', DB::raw('COUNT(borrowed.bookId) AS borrowCount'))
            ->leftJoin('borrowed', 'books.id', '=', 'borrowed.bookId')
            ->groupBy('books.id', 'books.bookTitle', 'books.author')
            ->orderByDesc('borrowCount')
            ->limit(5)
            ->get();



        $top5MonthActivity = DB::table('borrowed')
            ->select(DB::raw('DATE_FORMAT(borrowed.created_at, "%b-%Y") as monthYear'), DB::raw('COUNT(*) as borrowCount'))
            ->groupBy('monthYear')
            ->orderBy('monthYear')
            ->limit(5)
            ->get();


        $topFrequentBorrowerCourses = DB::table('borrowed')
            ->select('courses.id', 'courses.code', DB::raw('COUNT(borrowed.studentId) as studentCount'))
            ->join('students', 'borrowed.studentId', '=', 'students.id')
            ->join('courses', 'students.courseID', '=', 'courses.id')
            ->groupBy('courses.id', 'courses.code')
            ->orderByDesc('studentCount')
            ->limit(5)
            ->get();


        $topFrequentBorrowerDepartments = DB::table('borrowed')
            ->select('courses.id', 'courses.code', DB::raw('COUNT(DISTINCT instructors.id) as instructorCount'))
            ->join('instructors', 'borrowed.instructorId', '=', 'instructors.id')
            ->join('courses', 'instructors.departmentID', '=', 'courses.id')
            ->groupBy('courses.id', 'courses.code')
            ->orderByDesc('instructorCount')
            ->limit(5)
            ->get();

        return view('dashboard.dashboard', [
            'totalBooksCount' => $totalBooksCount,
            'totalStudentsCount' => $totalStudentsCount,
            'totalUniqueBorrowersCount' => $totalUniqueBorrowersCount,
            'totalInstructorsCount' => $totalInstructorsCount,
            'topBorrowedBooks' => $topBorrowedBooks,
            'top5MonthActivity' => $top5MonthActivity,
            'topFrequentBorrowerCourses' => $topFrequentBorrowerCourses,
            'topFrequentBorrowerDepartments' => $topFrequentBorrowerDepartments
        ]);
    }
}
