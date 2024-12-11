<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowed;
use App\Models\Course;
use App\Models\DamagedBooks;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\YearLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BorrowedController extends Controller
{
    public function index()
    {
        $borrowed = Borrowed::with(['book', 'issuedByUser', 'returnedToUser', 'student', 'instructor'])
        ->orderBy('updated_at', 'desc')
        ->get();
        return view('transaction.index', ['borrowed' => $borrowed]);
    }


    public function create()
    {
        $students = DB::table('students')
            ->join('courses', 'students.courseID', '=', 'courses.id')
            ->join('year_levels', 'students.yearLevelID', '=', 'year_levels.id')
            ->select('students.*', 'courses.code as course', 'year_levels.yearLevel as yearLevel')
            ->get();

        $books = DB::table('books')
            ->join('category', 'books.categoryId', '=', 'category.id')
            ->select('books.*', 'category.categoryDesc as category')
            ->get();

            $instructors = DB::table('instructors')
            ->join('courses', 'instructors.departmentID', '=', 'courses.id')
            ->select('instructors.*', 'courses.code as department')
            ->get();

        foreach ($books as $book) {
            $borrowedCount = DB::table('borrowed')
                ->where('bookID', $book->id)
                ->whereNull('dateReturned')
                ->count();
            $book->available_copies = $book->copies - $borrowedCount;
        }
        return view('transaction.create', ['books' => $books, 'students' => $students, 'instructors' => $instructors]);
    }

    public function store(Request $request)
    {
        // Validate if 'selected_books' and 'selected_borrower' are not null
        $request->validate([
            'selected_books' => 'required|array|min:1',
            'selected_borrower' => 'required',
        ], [
            'selected_books.required' => 'Please select a book.',
            'selected_books.min' => 'Please select at least one book.',
            'selected_borrower.required' => 'Please select a borrower.',
        ]);
        
        $selectedBooks = $request->input('selected_books', []);
        foreach ($selectedBooks as $selectedBookId) {
            $book = Book::find($selectedBookId);
            
            if (!$book) {
                return redirect()->back()->withErrors(['bookID' => 'Book not found.']);
            }

            $student = Student::where('studentNo', $request->input('selected_borrower'))->first();

            if ($student) {
                Borrowed::create([
                    'bookID' => $book->id,
                    'studentId' => $student->id,
                    'notes' => $request->input('notes'),
                    'dateBorrowed' => now(),
                    'dateReturned' => null,
                    'delayedDay' => null,
                    'issuedBy' => Auth::id(),
                    'returnedTo' => null,
                    'status' => 'Borrowed',
                    'remarks' => 'null'
                ]);
                $book->update(['borrowed' => 1]);
            } else {
                $instructor = Instructor::where('instructorNo', $request->input('selected_borrower'))->first();
                if ($instructor) {
                    Borrowed::create([
                        'bookID' => $book->id,
                        'instructorId' => $instructor->id,
                        'notes' => $request->input('notes'),
                        'dateBorrowed' => now(),
                        'dateReturned' => null,
                        'delayedDay' => null,
                        'issuedBy' => Auth::id(),
                        'returnedTo' => null,
                        'status' => 'Borrowed',
                        'remarks' => 'null'
                    ]);
                } else {
                    return redirect()->back()->withErrors(['borrower' => 'Borrower not found.']);
                }
                $book->update(['borrowed' => 1]);
            }
        }
        return back()->with('success', 'Record created successfully.');
    }


    public function returnBook(Request $request, $id)
    {
        $data = Borrowed::find($id);
        $isDamaged = $request->input('damaged');
        if ($data) {
            $data->update(['status' => 'Returned', 'remarks' => $request->input('remarks'), 'dateReturned' => now(), 'returnedTo' => Auth::id()]);
            $book = $data->book;
            if ($book) {
                $book->update(['borrowed' => 0]);
            }

            if ($isDamaged == "on") {
                $book->update(['borrowed' => 0, 'damaged' => ($book->damaged + 1)]);
            }

            return back()->with('success', 'Book was returned successfully.');
        }

        return back()->withErrors(['id' => 'Record not found.']);
    }

    public function returnedBooks()
    {
        $borrowed = Borrowed::with(['book', 'issuedByUser', 'returnedToUser', 'student', 'instructor'])
        ->orderBy('updated_at', 'desc')
        ->get();
        return view('transaction.return-book-list', ['borrowed' => $borrowed]);
    }
}
