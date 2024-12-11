<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('category.index', ['categories' => $categories]);

    }

    public function create()
    {
        return view('category.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'categoryCode' => ['required', Rule::unique('category', 'categoryCode')],
            'categoryDesc' => ['required', Rule::unique('category', 'categoryDesc')]
        ]);

        Category::create($validated);

        return redirect('/category')->with('success', 'New category was added successfully!');
    }

    public function edit($id)
    {
        $data = DB::table('category')
            ->select('category.*')
            ->where('category.id', $id)
            ->first();

        return view('category.edit', ['category' => $data]);
    }

    public function update(Request $request, $id)
    {
        $data = Category::find($id);
        $validatedData = $request->validate([
            'categoryCode' => [Rule::unique('category', 'categoryCode')],
            'categoryDesc' => [Rule::unique('category', 'categoryDesc')]
        ]);
        $data->update($validatedData);

        // Redirect back to the edit view with updated data and success message
        return redirect('/category')->with('success', 'Category updated successfully');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return back()->with('success', 'Category deleted successfully');
    }
}
