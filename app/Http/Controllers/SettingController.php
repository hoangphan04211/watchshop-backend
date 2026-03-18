<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    // ---------- LẤY DANH SÁCH ----------
    public function index()
    {
        $settings = Setting::orderBy('id', 'desc')->get();
        return response()->json($settings);
    }

    // ---------- THÊM MỚI ----------
    public function store(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'email'     => 'required|email|max:255',
            'phone'     => 'nullable|string|max:20',
            'hotline'   => 'nullable|string|max:20',
            'address'   => 'nullable|string|max:255',
            'status'    => 'in:0,1',
        ]);

        $setting = Setting::create([
            'site_name' => $request->site_name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'hotline'   => $request->hotline,
            'address'   => $request->address,
            'status'    => $request->status ?? 1,
        ]);

        return response()->json([
            'message' => 'Thêm cấu hình thành công',
            'data' => $setting
        ]);
    }

    // ---------- LẤY CHI TIẾT ----------
    public function show(string $id)
    {
        $setting = Setting::findOrFail($id);
        return response()->json($setting);
    }

    // ---------- CẬP NHẬT ----------
    public function update(Request $request, string $id)
    {
        $setting = Setting::findOrFail($id);

        $request->validate([
            'site_name' => 'sometimes|required|string|max:255',
            'email'     => 'sometimes|required|email|max:255',
            'phone'     => 'nullable|string|max:20',
            'hotline'   => 'nullable|string|max:20',
            'address'   => 'nullable|string|max:255',
            'status'    => 'in:0,1',
        ]);

        $setting->update($request->only([
            'site_name',
            'email',
            'phone',
            'hotline',
            'address',
            'status'
        ]));

        return response()->json([
            'message' => 'Cập nhật cấu hình thành công',
            'data' => $setting
        ]);
    }

    // ---------- XOÁ ----------
    public function destroy(string $id)
    {
        $setting = Setting::findOrFail($id);
        $setting->delete();

        return response()->json(['message' => 'Đã xoá cấu hình thành công']);
    }

    // ---------- LẤY CẤU HÌNH HOẠT ĐỘNG (status = 1) ----------
    public function active()
    {
        $setting = Setting::where('status', 1)->first();
        return response()->json($setting);
    }

    // ---------- LẤY CẤU HÌNH HIỂN THỊ TRÊN TRANG NGƯỜI DÙNG ----------
    public function getClientSetting()
    {
        // Lấy bản ghi setting đang hoạt động
        $setting = Setting::where('status', 1)->first([
            'site_name',
            'email',
            'phone',
            'hotline',
            'address'
        ]);

        if (!$setting) {
            return response()->json([
                'message' => 'Không tìm thấy cấu hình website hoạt động'
            ], 404);
        }

        return response()->json($setting);
    }
}
