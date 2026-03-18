<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class TopicController extends Controller
{
    // Danh sách
    public function index(Request $request)
    {
        $limit = $request->input('limit', 100);

        $topics = Topic::orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($topics);
    }

    // Thêm mới
    public function store(Request $request)
    {
        $faker = Faker::create();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string',
            'sort_order'  => 'nullable|integer',
            'description' => 'nullable|string',
            'status'      => 'nullable|integer'
        ]);

        // Tự sinh slug từ name (có xử lý tiếng Việt)
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name'], '-');

        $validated['created_by'] = $faker->numberBetween(1, 10);

        $topic = Topic::create($validated);

        return response()->json($topic, 201);
    }

    // Chi tiết
    public function show(string $id)
    {
        $topic = Topic::withTrashed()->findOrFail($id);
        return response()->json($topic);
    }

    // Cập nhật
    public function update(Request $request, string $id)
    {
        $faker = Faker::create();
        $topic = Topic::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string',
            'sort_order'  => 'nullable|integer',
            'description' => 'nullable|string',
            'status'      => 'nullable|integer'
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name'], '-');
        $validated['updated_by'] = $faker->numberBetween(1, 10);

        $topic->update($validated);

        return response()->json($topic);
    }

    // Xóa mềm
    public function destroy(string $id)
    {
        $topic = Topic::findOrFail($id);
        $topic->delete();
        return response()->json(['message' => 'Đã xoá topic']);
    }

    // Danh sách đã xóa
    public function trash()
    {
        $topics = Topic::onlyTrashed()->get();
        return response()->json($topics);
    }

    // Khôi phục
    public function restore(string $id)
    {
        $topic = Topic::onlyTrashed()->findOrFail($id);
        $topic->restore();
        return response()->json(['message' => 'Khôi phục thành công']);
    }

    // Xoá vĩnh viễn
    public function forceDelete(string $id)
    {
        $topic = Topic::onlyTrashed()->findOrFail($id);
        $topic->forceDelete();
        return response()->json(['message' => 'Đã xoá vĩnh viễn']);
    }
}
