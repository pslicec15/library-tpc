<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Book;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $studentQuery = $request->input('student_query');
        $bookQuery = $request->input('book_query');

        $students = Student::where('name', 'like', "%{$studentQuery}%")
                            ->orWhere('student_id', 'like', "%{$studentQuery}%")
                            ->get();

        $books = Book::where('title', 'like', "%{$bookQuery}%")
                     ->orWhere('author', 'like', "%{$bookQuery}%")
                     ->get();

        return view('search.results', compact('students', 'books'));
    }
}
