<?php

namespace App\Http\Controllers;

use App\Models\ProductStore;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Faker\Factory as Faker;

class ProductStoreController extends Controller
{
    /**
     * Danh sách tồn kho (phân trang)
     */
    public function index(Request $request)
    {
        $limit = (int) $request->input('limit', 5);

        $data = ProductStore::with('product')
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return response()->json($data);
    }


    /**
     * Thêm mới bản ghi tồn kho (nhập hàng)
     */
    public function store(Request $request)
    {
        $faker = Faker::create();

        $validated = $request->validate([
            'product_id' => 'required|exists:product,id',
            'price_root' => 'required|numeric|min:0',
            'qty'        => 'required|integer|min:1',
            'status'     => 'nullable|in:0,1',
        ]);

        $validated['created_by'] = $faker->numberBetween(1, 10);

        $store = ProductStore::create($validated);

        Log::info("Đã nhập kho sản phẩm {$store->product_id}", $store->toArray());

        return response()->json([
            'message' => 'Thêm tồn kho thành công',
            'data'    => $store->load('product')
        ]);
    }

    /**
     * Xem chi tiết bản ghi tồn kho
     */
    public function show($id)
    {
        $store = ProductStore::with('product')->findOrFail($id);
        return response()->json($store);
    }

    /**
     * Cập nhật bản ghi tồn kho
     */
    public function update(Request $request, $id)
    {
        $store = ProductStore::findOrFail($id);

        $validated = $request->validate([
            'price_root' => 'nullable|numeric|min:0',
            'qty'        => 'nullable|integer|min:0',
            'status'     => 'nullable|in:0,1',
        ]);

        $store->update($validated + ['updated_by' => 1]);

        return response()->json([
            'message' => 'Cập nhật tồn kho thành công',
            'data'    => $store->load('product')
        ]);
    }

    /**
     * Xóa mềm bản ghi tồn kho
     */
    public function destroy($id)
    {
        $store = ProductStore::findOrFail($id);
        $store->delete();

        return response()->json(['message' => 'Đã xóa bản ghi tồn kho']);
    }

    /**
     * Danh sách bản ghi đã xóa mềm (thùng rác)
     */
    public function trash()
    {
        $data = ProductStore::onlyTrashed()
            ->with('product')
            ->orderBy('deleted_at', 'desc')
            ->get();

        return response()->json($data);
    }

    /**
     * Khôi phục bản ghi đã xóa
     */
    public function restore($id)
    {
        $store = ProductStore::onlyTrashed()->findOrFail($id);
        $store->restore();

        return response()->json(['message' => 'Khôi phục thành công']);
    }

    /**
     * Xóa vĩnh viễn bản ghi
     */
    public function forceDelete($id)
    {
        $store = ProductStore::onlyTrashed()->findOrFail($id);
        $store->forceDelete();

        return response()->json(['message' => 'Đã xóa vĩnh viễn bản ghi tồn kho']);
    }
}
