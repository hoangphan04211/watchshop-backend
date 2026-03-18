<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Post;

class PostController extends Controller
{
    // ---------- LẤY DANH SÁCH ----------
    public function index()
    {
        $posts = Post::orderBy('created_at', 'desc')->get();
        return response()->json($posts);
    }

    // ---------- THÊM POST ----------
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:posts,slug',
            'topic_id'    => 'nullable|integer',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20000',
            'content'     => 'nullable|string',
            'description' => 'nullable|string',
            'status'      => 'in:0,1',
            'post_type'   => 'in:post,page'
        ]);

        $post = new Post();
        $post->title       = $request->title;
        $post->slug        = $request->slug;
        $post->topic_id    = $request->topic_id;
        $post->content     = $request->content;
        $post->description = $request->description;
        $post->status      = $request->status ?? 1;
        $post->post_type   = $request->post_type ?? 'post';
        $post->created_by  = 1; // TODO: sau này thay bằng id user đăng nhập

        // upload ảnh
        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/posts'), $filename);
            $post->image = $filename;
        }

        $post->save();

        return response()->json(['message' => 'Thêm bài viết thành công', 'data' => $post]);
    }

    // ---------- LẤY CHI TIẾT ----------
    public function show(string $id)
    {
        $post = Post::findOrFail($id);
        return response()->json($post);
    }

    // ---------- CẬP NHẬT ----------
    public function update(Request $request, string $id)
    {
        $post = Post::findOrFail($id);

        $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'slug'        => 'sometimes|required|string|max:255|unique:posts,slug,' . $id,
            'topic_id'    => 'nullable|integer',
            'image'       => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:20000',
            'content'     => 'nullable|string',
            'description' => 'nullable|string',
            'status'      => 'in:0,1',
            'post_type'   => 'in:post,page'
        ]);

        $post->fill($request->only(['title', 'slug', 'topic_id', 'content', 'description', 'status', 'post_type']));
        $post->updated_by = 1; // TODO: thay bằng user đăng nhập

        // update ảnh mới
        if ($request->hasFile('image')) {
            if ($post->image && File::exists(public_path('images/posts/' . $post->image))) {
                File::delete(public_path('images/posts/' . $post->image));
            }
            $file     = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/posts'), $filename);
            $post->image = $filename;
        }

        $post->save();

        return response()->json(['message' => 'Cập nhật bài viết thành công', 'data' => $post]);
    }

    // ---------- XOÁ MỀM ----------
    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        return response()->json(['message' => 'Đã xoá bài viết']);
    }

    // ---------- DANH SÁCH TRASH ----------
    public function trash()
    {
        $posts = Post::onlyTrashed()->orderBy('created_at', 'desc')->get();
        return response()->json($posts);
    }

    // ---------- KHÔI PHỤC ----------
    public function restore(string $id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);
        $post->restore();
        return response()->json(['message' => 'Khôi phục bài viết thành công']);
    }

    // ---------- XOÁ VĨNH VIỄN ----------
    public function forceDelete(string $id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);

        if ($post->image && File::exists(public_path('images/posts/' . $post->image))) {
            File::delete(public_path('images/posts/' . $post->image));
        }

        $post->forceDelete();
        return response()->json(['message' => 'Đã xoá bài viết vĩnh viễn']);
    }

    // ---------- LẤY BÀI VIẾT MỚI ----------
    public function postNew(Request $request)
    {
        $limit = $request->limit ?? 5;

        $posts = Post::with('topic:id,name,slug')
            ->where('status', 1)
            ->where('post_type', 'post')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['id', 'topic_id', 'title', 'slug', 'image', 'content', 'description', 'post_type', 'created_at']);


        return response()->json($posts);
    }

    // DANH SÁCH BÀI VIẾT PUBLIC
    public function indexClient()
    {
        $posts = Post::select('id', 'topic_id', 'title', 'slug', 'image', 'description', 'created_at')
            ->with('topic:id,name,slug')
            ->where('status', 1)
            ->where('post_type', 'post')
            ->orderBy('created_at', 'desc')
            ->get();


        return response()->json($posts);
    }

    // CHI TIẾT BÀI VIẾT PUBLIC
    public function showClient($slug)
    {
        $post = Post::with('topic:id,name,slug') // load topic
            ->where('slug', $slug)
            ->where('status', 1)
            ->firstOrFail();

        return response()->json($post);
    }
}
