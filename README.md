# Library-TPC

A web-based library management system for a school or college library, built with Laravel 11.

Librarians use it to catalog books, register students and instructors, issue and return loans, log who enters the library, and print barcode ID passes. An admin dashboard summarizes circulation activity.

## Features

### Catalog

- Book records with title, author, category, and copy counts
- Categories, courses, departments, and year levels as reference data
- Available-copy counts computed from open loans
- Export the catalog to Excel or PDF, or send it to a print view

### Borrowers

- Student records (student number, name, sex, birthdate, course, year level, contact, address, photo)
- Instructor records tied to a department
- Photo upload and capture for ID passes

### Circulation

- Issue several books to one borrower in a single transaction
- Return books, with an optional flag that increments a damaged-copy counter
- Borrowers list and returned-books list, both showing who issued and who received each book

### Attendance

- A tap-in / tap-out screen that reads a scanned barcode in the form `<idNumber>/student` or `<idNumber>/instructor`
- The first scan opens an attendance record, the next scan closes it
- Attendance history for students and instructors, timestamped in `Asia/Manila`

### ID passes

- Print a single pass for one student or instructor
- Bulk print: pick many students and instructors, print their passes together

### Users and access

- Username and password login, throttled to 3 attempts per minute
- Every route behind `auth`
- User management (`/user/*`) restricted to the `Administrator` and `Librarian` roles

### Dashboard

- Totals for books, students, instructors, and active borrowers
- Top 5 borrowed books
- Borrowing activity by month
- Courses and departments that borrow most often

## Tech stack

| Layer | Choice |
|---|---|
| Framework | Laravel 11 (PHP 8.2+) |
| Auth scaffolding | Jetstream 5 + Fortify + Sanctum, Livewire 3 stack |
| Frontend | Blade, Tailwind CSS 3, Vite 5 |
| Tests | Pest 3 |
| Database | MySQL (the dashboard uses `DATE_FORMAT`, a MySQL function) |

## Getting started

```bash
composer install
npm install

# the repo has no .env.example yet, so write a .env by hand
# (copy one from a fresh `laravel new` project and set DB_* for MySQL)
php artisan key:generate

php artisan migrate
php artisan storage:link

composer run dev     # serve + queue worker + vite
```

`composer run dev` runs `php artisan serve`, `php artisan queue:listen`, and `npm run dev` together. To run them separately, use `php artisan serve` and `npm run dev` in two terminals.

For production assets:

```bash
npm run build
```

Run the test suite with:

```bash
php artisan test
```

## Routes

| Area | Path |
|---|---|
| Dashboard | `/` |
| Login | `/login` |
| Students | `/student`, `/student/add`, `/student/{id}` |
| Instructors | `/instructor`, `/instructor/add`, `/instructor/{id}` |
| Books | `/book`, `/book/add`, `/book/{id}` |
| Categories | `/category` |
| Courses | `/course` |
| Departments | `/department` |
| Issue a loan | `/borrowers-form` |
| Open loans | `/borrowers-list` |
| Returned books | `/return-book-list` |
| Tap in / tap out | `/tap-in-tap-out` |
| Attendance history | `/history` |
| Bulk print passes | `/bulk-print` |
| Users (admin) | `/user` |
| Exports | `/export-books-excel`, `/export-books-pdf`, `/print-book` |

Full definitions live in `routes/web.php`.

## Layout

```text
app/
  Http/Controllers/Controllers/   controllers for every domain area
  Models/                         User (domain models pending, see below)
  Actions/                        Fortify and Jetstream actions
database/migrations/              framework tables (domain tables pending)
resources/views/                  Blade templates
routes/web.php                    all application routes
```

## Repository status

The first commit carries the Laravel skeleton, the Jetstream scaffolding, and the domain controllers. Several pieces the controllers depend on are not committed yet:

- **Eloquent models.** `app/Models/` holds only `User`. The controllers reference `Book`, `Student`, `Instructor`, `Borrowed`, `Category`, `Course`, `Department`, `YearLevel`, `Attendance`, and `DamagedBooks`.
- **Domain migrations.** `database/migrations/` covers users, cache, jobs, two-factor columns, and access tokens. The tables the app queries (`books`, `students`, `instructors`, `borrowed`, `attendance`, `courses`, `year_levels`, `category`) have no migrations.
- **Domain views.** The controllers render `book.*`, `student.*`, `transaction.*`, `attendance.*`, `dashboard.dashboard`, `user.*`, and `components.print-pass`, none of which are in `resources/views/`.
- **Controller directory.** The files sit in `app/Http/Controllers/Controllers/` while declaring `namespace App\Http\Controllers`, so PSR-4 autoloading will not find them. Move them up one level to `app/Http/Controllers/`.
- **`maatwebsite/excel`.** `BookController` imports it for the Excel and PDF exports, but `composer.json` does not require it. Add it with `composer require maatwebsite/excel`.
- **`role` middleware.** `routes/web.php` applies `role:Administrator,Librarian`, and no alias is registered in `bootstrap/app.php`. Register it there, or the user routes will throw.
- **`App\Exports\BooksExport` and `App\Http\Controllers\DamageBookController`** are imported but missing.
- **`.env.example`.** No template ships with the repo, so a new clone has nothing to copy from.

Anyone cloning this repo should expect to supply those pieces before the app boots.
