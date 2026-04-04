<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::forUser()->orderBy('sort_order', 'asc')->paginate(20);

        return view('expenses.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('expenses.categories.form', ['category' => null]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        Category::create([
            'name' => $request->name,
            'name_es' => $request->name_es,
            'description' => $request->description,
            'color' => $request->color ?? '#6c757d',
            'icon' => $request->icon ?? '',
            'is_active' => $request->has('active') ? 1 : 0,
            'sort_order' => (int) $request->sort_order,
            'user_id' => Auth::id(),
        ]);

        return redirect('/categories')->with('flash', ['type' => 'success', 'message' => 'Category created successfully.']);
    }

    public function edit(int $id)
    {
        $category = Category::findOrFail($id);

        return view('expenses.categories.form', compact('category'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $category = Category::findOrFail($id);
        $category->update([
            'name' => $request->name,
            'name_es' => $request->name_es,
            'description' => $request->description,
            'color' => $request->color ?? '#6c757d',
            'icon' => $request->icon ?? '',
            'is_active' => $request->has('active') ? 1 : 0,
            'sort_order' => (int) $request->sort_order,
        ]);

        return redirect('/categories')->with('flash', ['type' => 'success', 'message' => 'Category updated successfully.']);
    }

    public function destroy(int $id)
    {
        Category::findOrFail($id)->delete();

        return redirect('/categories')->with('flash', ['type' => 'success', 'message' => 'Category deleted successfully.']);
    }

    public function reorder(Request $request)
    {
        $input = $request->json()->all();

        if (!is_array($input)) {
            return response()->json(['success' => false, 'message' => 'Invalid payload.'], 400);
        }

        $orders = [];
        foreach ($input as $item) {
            if (isset($item['id'], $item['position'])) {
                $orders[(int) $item['id']] = (int) $item['position'];
            }
        }

        Category::updateSortOrder($orders);

        return response()->json(['success' => true, 'message' => 'Sort order updated.']);
    }
}
