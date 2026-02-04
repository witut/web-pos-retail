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
     */
    public function index()
    {
        $categories = Category::with('parent')
            ->withCount(['products', 'children'])
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $categories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('admin.categories.create', compact('categories'));
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
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
        $category->loadCount(['products', 'children']);

        $categories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id) // Exclude self
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', compact('category', 'categories'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
        ]);

        // Prevent circular reference (kategori jadi parent diri sendiri)
        if ($validated['parent_id'] == $category->id) {
            return back()->withErrors(['parent_id' => 'Kategori tidak bisa menjadi parent diri sendiri']);
        }

        // Prevent setting child as parent (circular reference)
        if ($validated['parent_id']) {
            $descendants = $this->getAllDescendants($category);
            if (in_array($validated['parent_id'], $descendants)) {
                return back()->withErrors(['parent_id' => 'Tidak bisa memilih sub-kategori sebagai parent']);
            }
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
        if ($category->children()->count() > 0) {
            return back()->withErrors(['error' => 'Kategori tidak bisa dihapus karena masih memiliki sub-kategori']);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus');
    }

    /**
     * Get all descendant category IDs (children, grandchildren, etc.)
     */
    private function getAllDescendants(Category $category): array
    {
        $descendants = [];

        foreach ($category->children as $child) {
            $descendants[] = $child->id;
            $descendants = array_merge($descendants, $this->getAllDescendants($child));
        }

        return $descendants;
    }
}
