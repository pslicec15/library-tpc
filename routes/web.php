<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BorrowedController;
use App\Http\Controllers\BulkPrintController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DamageBookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\SearchController;




Route::get('/', [DashboardController::class, 'index'])->middleware('auth');
Route::get('/register', [UserController::class, 'register']);
Route::get('/login', [UserController::class, 'login'])->name('login')->middleware('guest');
Route::post('/login/process', [UserController::class, 'process'])->middleware("throttle:3, 0.2");
Route::post('/logout', [UserController::class, 'logout']);
Route::post('/store', [UserController::class, 'store']);

//Student Route
Route::get('/student', [StudentController::class, 'index'])->middleware('auth');
Route::get('/student/add', [StudentController::class, 'create'])->middleware('auth');
Route::post('/student/add', [StudentController::class, 'store'])->middleware('auth');
Route::get('/student/{id}', [StudentController::class, 'show'])->middleware('auth');
Route::get('/student/{id}/edit', [StudentController::class, 'edit'])->middleware('auth');
Route::put('/student/{id}/edit', [StudentController::class, 'update'])->middleware('auth');
Route::delete('/student/{id}', [StudentController::class, 'destroy'])->middleware('auth');

Route::get('/print-pass/student/{id}', [StudentController::class, 'print'])->middleware('auth')->name('print.pass.student');
Route::get('/print-pass/instructor/{id}', [InstructorController::class, 'print'])->middleware('auth')->name('print.pass.instructor');
Route::post('/save-image', [StudentController::class, 'saveImage'])->middleware('auth');
Route::get('image-upload', [ImageController::class, 'index'])->name('image.upload');
Route::post('image-upload', [ImageController::class, 'upload'])->name('image.upload.post');

//Instructor Route
Route::get('/instructor', [InstructorController::class, 'index'])->middleware('auth');
Route::get('/instructor/add', [InstructorController::class, 'create'])->middleware('auth');
Route::post('/instructor/add', [InstructorController::class, 'store'])->middleware('auth');
Route::get('/instructor/{id}', [InstructorController::class, 'show'])->middleware('auth');
Route::get('/instructor/{id}/edit', [InstructorController::class, 'edit'])->middleware('auth');
Route::put('/instructor/{id}/edit', [InstructorController::class, 'update'])->middleware('auth');
Route::delete('/instructor/{id}', [InstructorController::class, 'destroy'])->middleware('auth');

//Course Route
Route::get('/course', [CourseController::class, 'index'])->middleware('auth');
Route::get('/course/add', [CourseController::class, 'create'])->middleware('auth');
Route::post('/course/add', [CourseController::class, 'store'])->middleware('auth');
Route::get('/course/{id}/edit', [CourseController::class, 'edit'])->middleware('auth');
Route::put('/course/{id}/edit', [CourseController::class, 'update'])->middleware('auth');
Route::delete('/course/{id}', [CourseController::class, 'destroy'])->middleware('auth');

//Department Route
Route::get('/department', [DepartmentController::class, 'index'])->middleware('auth');
Route::get('/department/add', [DepartmentController::class, 'create'])->middleware('auth');
Route::post('/department/add', [DepartmentController::class, 'store'])->middleware('auth');
Route::get('/department/{id}/edit', [DepartmentController::class, 'edit'])->middleware('auth');
Route::put('/department/{id}/edit', [DepartmentController::class, 'update'])->middleware('auth');
Route::delete('/department/{id}', [DepartmentController::class, 'destroy'])->middleware('auth');

//Books Route
Route::get('/book', [BookController::class, 'index'])->middleware('auth');
Route::get('/book/add', [BookController::class, 'create'])->middleware('auth');
Route::post('/book/add', [BookController::class, 'store'])->middleware('auth');
Route::get('/book/{id}', [BookController::class, 'show'])->middleware('auth');
Route::get('/book/{id}/edit', [BookController::class, 'edit'])->middleware('auth');
Route::put('/book/{id}/edit', [BookController::class, 'update'])->middleware('auth');

//Export To Excel
Route::get('/export-books-excel', [BookController::class, 'exportToExcel']);
Route::get('/export-books-pdf', [BookController::class, 'exportToPdf']);
Route::get('/print-book', [BookController::class, 'printBook']);


//Categories Route
Route::get('/category', [CategoryController::class, 'index'])->middleware('auth');
Route::get('/category/add', [CategoryController::class, 'create'])->middleware('auth');
Route::post('/category/add', [CategoryController::class, 'store'])->middleware('auth');
Route::get('/category/{id}', [CategoryController::class, 'show'])->middleware('auth');
Route::get('/category/{id}/edit', [CategoryController::class, 'edit'])->middleware('auth');
Route::put('/category/{id}/edit', [CategoryController::class, 'update'])->middleware('auth');
Route::delete('/category/{id}', [CategoryController::class, 'destroy'])->middleware('auth');

//Transaction Route
Route::get('/borrowers-list', [BorrowedController::class, 'index'])->middleware('auth');
Route::get('/borrowers-form', [BorrowedController::class, 'create'])->middleware('auth');
Route::put('/borrowed/{id}/return-book', [BorrowedController::class, 'returnBook'])->middleware('auth');
Route::post('/borrowers-form', [BorrowedController::class, 'store'])->middleware('auth');
Route::get('/return-book-list', [BorrowedController::class, 'returnedBooks'])->middleware('auth');

//Attendance Route
Route::get('/history', [AttendanceController::class, 'show'])->middleware('auth');
Route::get('/tap-in-tap-out', [AttendanceController::class, 'index'])->middleware('auth');
Route::get('/tap-in-tap-out/save', [AttendanceController::class, 'store'])->middleware('auth');

Route::middleware(['auth', 'role:Administrator,Librarian'])->group(function () {
  Route::get('/user', [UserController::class, 'index'])->middleware('auth');
  Route::get('/user/add', [UserController::class, 'create'])->middleware('auth');
  Route::post('/user/add', [UserController::class, 'store'])->middleware('auth');
  Route::post('/user/save', [UserController::class, 'save'])->middleware('auth');
  Route::get('/user/{id}', [UserController::class, 'edit'])->middleware('auth');
  Route::get('/user/{id}/edit', [UserController::class, 'edit'])->middleware('auth');
  Route::put('/user/{id}/edit', [UserController::class, 'update'])->middleware('auth');
  Route::delete('/user/{id}', [UserController::class, 'destroy'])->middleware('auth');
});



Route::get('/bulk-print', [BulkPrintController::class, 'index'])->middleware('auth');
Route::post('/bulk-print/print', [BulkPrintController::class, 'print'])->middleware('auth');
