<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Hiển thị danh sách tất cả menu.
     */
    public function index()
    {
        $menus = Menu::orderBy('parent_id')->orderBy('sort_order')->get();
        return response()->json($menus);
    }

    /**
     * Thêm menu mới.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'link'       => 'required|string|max:255',
            'type'       => 'required|in:category,page,topic,custom',
            'parent_id'  => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'table_id'   => 'nullable|integer',
            'status'     => 'required|in:0,1',
        ]);

        $menu = Menu::create($validated);
        return response()->json([
            'message' => 'Thêm menu thành công!',
            'menu' => $menu
        ], 201);
    }

    /**
     * Xem chi tiết 1 menu.
     */
    public function show(string $id)
    {
        $menu = Menu::find($id);
        if (!$menu) {
            return response()->json(['message' => 'Không tìm thấy menu!'], 404);
        }

        return response()->json($menu);
    }

    /**
     * Cập nhật menu.
     */
    public function update(Request $request, string $id)
    {
        $menu = Menu::find($id);
        if (!$menu) {
            return response()->json(['message' => 'Không tìm thấy menu!'], 404);
        }

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'link'       => 'required|string|max:255',
            'type'       => 'required|in:category,page,topic,custom',
            'parent_id'  => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'table_id'   => 'nullable|integer',
            'status'     => 'required|in:0,1',
        ]);

        $menu->update($validated);
        return response()->json([
            'message' => 'Cập nhật menu thành công!',
            'menu' => $menu
        ]);
    }

    /**
     * Xóa menu.
     */
    public function destroy(string $id)
    {
        $menu = Menu::find($id);
        if (!$menu) {
            return response()->json(['message' => 'Không tìm thấy menu!'], 404);
        }

        $menu->delete();
        return response()->json(['message' => 'Xóa menu thành công!']);
    }

    /**
     * Hàm lấy menu cho phía người dùng (client).
     */
    public function getClientMenu()
    {
        // Lấy các menu đang hiển thị
        $menus = Menu::where('status', 1)
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->get();

        // Nếu muốn hiển thị dạng cây (nested menu):
        $tree = $this->buildTree($menus);

        return response()->json($tree);
    }

    /**
     * Dựng cây menu đệ quy.
     */
    private function buildTree($menus, $parentId = 0)
    {
        $branch = [];
        foreach ($menus as $menu) {
            if ($menu->parent_id == $parentId) {
                $children = $this->buildTree($menus, $menu->id);
                if ($children) {
                    $menu->children = $children;
                }
                $branch[] = $menu;
            }
        }
        return $branch;
    }
}
