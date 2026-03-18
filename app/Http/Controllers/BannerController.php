<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BannerController extends Controller
{
    // ---------- LẤY DANH SÁCH ----------
    public function index()
    {
        $banners = Banner::orderBy('sort_order', 'asc')->get();
        return response()->json($banners);
    }

    // ---------- THÊM BANNER ----------
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'image'      => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:20000',
            'link'       => 'nullable|string|max:255',
            'position'   => 'required|in:slideshow,ads',
            'sort_order' => 'integer|min:0',
            'status'     => 'in:0,1'
        ]);

        $banner = new Banner();
        $banner->name       = $request->name;
        $banner->link       = $request->link;
        $banner->position   = $request->position;
        $banner->sort_order = $request->sort_order ?? 0;
        $banner->description = $request->description;
        $banner->status     = $request->status ?? 1;
        $banner->created_by = 1;

        // upload ảnh
        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/banners'), $filename);
            $banner->image = $filename;
        }

        $banner->save();

        return response()->json(['message' => 'Thêm banner thành công', 'data' => $banner]);
    }

    // ---------- LẤY CHI TIẾT ----------
    public function show(string $id)
    {
        $banner = Banner::findOrFail($id);
        return response()->json($banner);
    }

    // ---------- CẬP NHẬT ----------
    public function update(Request $request, string $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'name'       => 'sometimes|required|string|max:255',
            'image'      => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:20000',
            'link'       => 'nullable|string|max:255',
            'position'   => 'in:slideshow,ads',
            'sort_order' => 'integer|min:0',
            'status'     => 'in:0,1'
        ]);

        $banner->fill($request->only(['name', 'link', 'position', 'sort_order', 'description', 'status']));
        $banner->updated_by = 1;

        // update ảnh mới
        if ($request->hasFile('image')) {
            // xoá ảnh cũ
            if ($banner->image && File::exists(public_path('images/banners/' . $banner->image))) {
                File::delete(public_path('images/banners/' . $banner->image));
            }
            $file     = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/banners'), $filename);
            $banner->image = $filename;
        }

        $banner->save();

        return response()->json(['message' => 'Cập nhật banner thành công', 'data' => $banner]);
    }

    // ---------- XOÁ MỀM ----------
    public function destroy(string $id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();
        return response()->json(['message' => 'Đã xoá banner']);
    }

    // ---------- DANH SÁCH TRASH ----------
    public function trash()
    {
        $banners = Banner::onlyTrashed()->orderBy('sort_order', 'asc')->get();
        return response()->json($banners);
    }

    // ---------- KHÔI PHỤC ----------
    public function restore(string $id)
    {
        $banner = Banner::onlyTrashed()->findOrFail($id);
        $banner->restore();
        return response()->json(['message' => 'Khôi phục banner thành công']);
    }

    // ---------- XOÁ VĨNH VIỄN ----------
    public function forceDelete(string $id)
    {
        $banner = Banner::onlyTrashed()->findOrFail($id);

        if ($banner->image && File::exists(public_path('images/banners/' . $banner->image))) {
            File::delete(public_path('images/banners/' . $banner->image));
        }

        $banner->forceDelete();
        return response()->json(['message' => 'Đã xoá banner vĩnh viễn']);
    }

    // ---------- LẤY BANNER CHO SLIDER ----------
    public function slider()
    {
        $banners = Banner::where('position', 'slideshow')
            ->where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json($banners);
    }
}
