<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryController extends Controller
{
    // Danh sách
    public function index(Request $request)
    {
        $limit = $request->input('limit', 100);

        $categories = Category::orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($categories);
    }

    // Thêm mới
    public function store(Request $request)
    {
        $faker= Faker::create();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20000',
            'parent_id'   => 'nullable|integer',
            'sort_order'  => 'nullable|integer',
            'description' => 'nullable|string',
            'status'      => 'nullable|integer'
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $ext      = $file->getClientOriginalExtension();
            $filename = $validated['slug'] . '_' . time() . '.' . $ext;
            $file->move(public_path('images/categories'), $filename);
            $validated['image'] = $filename;
        }

        $validated['created_by'] = $faker->numberBetween(1, 10);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    // Chi tiết
    public function show(string $id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        return response()->json($category);
    }

    // Cập nhật
    public function update(Request $request, string $id)
    {
        $faker = Faker::create();
        $category = Category::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20000',
            'parent_id'   => 'nullable|integer',
            'sort_order'  => 'nullable|integer',
            'description' => 'nullable|string',
            'status'      => 'nullable|integer'
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['updated_by'] = $faker->numberBetween(1, 10);

        if ($request->hasFile('image')) {
            if ($category->image && File::exists(public_path('images/categories/' . $category->image))) {
                File::delete(public_path('images/categories/' . $category->image));
            }
            $file     = $request->file('image');
            $ext      = $file->getClientOriginalExtension();
            $filename = $validated['slug'] . '_' . time() . '.' . $ext;
            $file->move(public_path('images/categories'), $filename);
            $validated['image'] = $filename;
        }

        $category->update($validated);

        return response()->json($category);
    }

    // Soft delete
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Đã xoá danh mục']);
    }

    // Danh mục đã xoá
    public function trash()
    {
        $categories = Category::onlyTrashed()->get();
        return response()->json($categories);
    }

    // Khôi phục
    public function restore(string $id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();
        return response()->json(['message' => 'Khôi phục thành công']);
    }

    // Xoá vĩnh viễn
    public function forceDelete(string $id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);

        if ($category->image && File::exists(public_path('images/categories/' . $category->image))) {
            File::delete(public_path('images/categories/' . $category->image));
        }

        $category->forceDelete();
        return response()->json(['message' => 'Đã xoá vĩnh viễn']);
    }
}
