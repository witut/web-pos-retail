<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

/**
 * CategoryController
 * 
 * Controller untuk CRUD kategori produk
 * Support hierarchical categories (parent-child)
 */
class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     * Tampilkan semua kategori dengan struktur tree
     */
    public function index()
    {
        $categories = Category::with('parent', 'children')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        // Get all parent categories (top-level)
        $parentCategories = Category::parents()->orderBy('name')->get();

        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
        ]);

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan');
    }

    /**
     * Show the form for editing a category
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::parents()
            ->where('id', '!=', $category->id) // Exclude self
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
        ]);

        // Prevent circular reference (kategori jadi parent diri sendiri)
        if ($validated['parent_id'] == $category->id) {
            return back()->withErrors(['parent_id' => 'Kategori tidak bisa menjadi parent diri sendiri']);
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diupdate');
    }

    /**
     * Remove the specified category
     */
    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return back()->withErrors(['error' => 'Kategori tidak bisa dihapus karena masih memiliki produk']);
        }

        // Check if category has children
        if ($category->hasChildren()) {
            return back()->withErrors(['error' => 'Kategori tidak bisa dihapus karena masih memiliki sub-kategori']);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus');
    }
}
