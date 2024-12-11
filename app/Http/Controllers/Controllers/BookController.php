<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BooksExport;

class BookController extends Controller
{
  public function index()
  {
    $categories = Category::all();
    $books = DB::table('books')
      ->join('category', 'books.categoryId', '=', 'category.id')
      ->select('books.*', 'category.categoryDesc as category')
      ->orderBy('books.updated_at', 'desc')
      ->get();
    foreach ($books as $book) {
      $borrowedCount = DB::table('borrowed')
        ->where('bookID', $book->id)
        ->whereNull('dateReturned')
        ->count();

      $book->available_copies = $book->copies - $borrowedCount;
    }
    return view('book.index', ['books' => $books, 'categories' => $categories]);
  }

  public function exportToExcel()
  {
    return Excel::download(new BooksExport, 'books.xlsx');
  }

  public function exportToPdf()
  {
    return Excel::download(new BooksExport, 'books.pdf', \Maatwebsite\Excel\Excel::MPDF);
  }

  public function printBook()
  {
    $books = new BooksExport;
    return view('book.print-books', ['books'=> $books->collection()]);
  }

  public function create()
  {
    $categories = Category::all();
    return view('book.create', ['categories' => $categories]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'bookTitle' => 'required',
      'bookNum' => 'required',
      'edition' => 'nullable',
      'pages' => 'required',
      'author' => 'required',
      'publisher' => 'required',
      'placeOfPublication' => 'nullable',
      'copyrightDate' => 'nullable',
      'size' => 'nullable',
      'yearPublished' => 'required',
      'ISBN' => 'nullable',
      'callNumber' => 'nullable',
      'location' => 'nullable',
      'subjects' => 'nullable',
      'categoryId' => 'nullable',
      'volume' => 'nullable',
      'serialNumber' => 'nullable',
      'copies' => 'required',
      'price' => 'required',
      'sourceFund' => 'nullable',
      'remarks' => 'nullable',
    ]);

    $validated['addedOn'] = now();
    $validated['addedBy'] = Auth::id();
    Book::create($validated);

    return redirect('/book')->with('success', 'New book was added successfully!');
  }

  public function show($id)
  {
    $data = DB::table('books')
      ->join('category', 'books.categoryId', '=', 'category.id')
      ->join('users', 'books.addedBy', '=', 'users.id')
      ->select('books.*', 'category.categoryDesc as category', 'users.name as addedByUser')
      ->where('books.id', $id)
      ->first();
    $data->addedOn = Carbon::parse($data->addedOn);

    return view('book.view', ['book' => $data]);
  }

  public function edit($id)
  {
    $categories = Category::all();

    $data = DB::table('books')
      ->join('category', 'books.categoryId', '=', 'category.id')
      ->join('users', 'books.addedBy', '=', 'users.id')
      ->select('books.*', 'category.categoryDesc as category', 'users.name as addedByUser')
      ->where('books.id', $id)
      ->first();
    $data->addedOn = Carbon::parse($data->addedOn);

    return view('book.edit', ['book' => $data, 'categories' => $categories]);
  }

  public function update(Request $request, $id)
  {
    $data = Book::find($id);
    $validated = $request->validate([
      'bookTitle' => 'required',
      'bookNum' => 'required',
      'edition' => 'nullable',
      'pages' => 'required',
      'author' => 'required',
      'publisher' => 'required',
      'placeOfPublication' => 'nullable',
      'copyrightDate' => 'nullable',
      'size' => 'nullable',
      'yearPublished' => 'required',
      'ISBN' => 'nullable',
      'callNumber' => 'nullable',
      'location' => 'nullable',
      'subjects' => 'nullable',
      'categoryId' => 'nullable',
      'volume' => 'nullable',
      'serialNumber' => 'nullable',
      'copies' => 'required',
      'price' => 'required',
      'sourceFund' => 'nullable',
      'remarks' => 'nullable',
    ]);
    $data->update($validated);

    return redirect('/book')->with('success', 'Book updated successfully');
  }

  public function destroy($id)
  {
    $book = Book::findOrFail($id);
    $book->delete();

    return back()->with('success', 'Book deleted successfully');
  }
}
