<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductSale;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProductSaleController extends Controller
{
    /**
     *  Danh sách chương trình khuyến mãi (gộp theo tên, có tìm kiếm + phân trang)
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 5);
        $keyword = $request->input('keyword', '');

        // Gộp theo tên chương trình (1 dòng cho mỗi chương trình)
        $query = ProductSale::select(
            'name',
            DB::raw('MIN(id) as id'),
            DB::raw('MIN(date_begin) as date_begin'),
            DB::raw('MAX(date_end) as date_end'),
            DB::raw('MAX(status) as status')
        )
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%");
            })
            ->groupBy('name')
            ->orderBy(DB::raw('MIN(id)'), 'desc');

        $sales = $query->paginate($limit);

        // Giữ nguyên cấu trúc phân trang của Laravel
        return response()->json($sales);
    }

    /**
     * Xem chi tiết 1 chương trình khuyến mãi (gồm danh sách sản phẩm)
     */
    public function show($id)
    {
        $sale = ProductSale::find($id);
        if (!$sale) {
            return response()->json(['error' => 'Không tìm thấy chương trình giảm giá'], 404);
        }

        // ✅ Lấy toàn bộ sản phẩm thuộc cùng chương trình
        $saleProducts = ProductSale::where('name', $sale->name)
            ->with('product:id,name,price,image')
            ->get();

        // ✅ Trả về dữ liệu chi tiết
        return response()->json([
            'sale' => [
                'id'         => $sale->id,
                'name'       => $sale->name,
                'price_sale' => $sale->price_sale,
                'date_begin' => $sale->date_begin,
                'date_end'   => $sale->date_end,
                'status'     => $sale->status,
            ],
            'products' => $saleProducts->map(function ($item) {
                return [
                    'id'    => $item->product->id ?? null,
                    'name'  => $item->product->name ?? 'Sản phẩm không tồn tại',
                    'price' => $item->product->price ?? 0,
                    'price_sale' => $item->price_sale ?? 0,
                    'image' => $item->product->image ?? null,
                ];
            }),
        ]);
    }


    /**
     *  Thêm mới chương trình giảm giá
     */
    public function store(Request $request)
    {
        //  Xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date_begin' => 'required|date',
            'date_end'   => 'required|date|after:date_begin',
            'status'     => 'nullable|in:0,1',
            'product_sales' => 'required|array|min:1',
            'product_sales.*.product_id' => 'exists:product,id',
            'product_sales.*.price_sale' => 'required|numeric|min:0',
        ]);

        $validated['status'] = $validated['status'] ?? 1;
        $createdBy = 1; // hoặc Auth::id() nếu có đăng nhập

        $inserted = [];

        //  Lặp qua danh sách sản phẩm và thêm từng dòng
        foreach ($validated['product_sales'] as $ps) {
            $sale = ProductSale::create([
                'name'        => $validated['name'],
                'product_id'  => $ps['product_id'],
                'price_sale'  => $ps['price_sale'],
                'date_begin'  => $validated['date_begin'],
                'date_end'    => $validated['date_end'],
                'status'      => $validated['status'],
                'created_by'  => $createdBy,
            ]);
            $inserted[] = $sale;
        }

        //  Ghi log (để debug)
        Log::info('Tạo chương trình giảm giá', [
            'name' => $validated['name'],
            'products' => array_map(fn($ps) => $ps['product_id'], $validated['product_sales']),
        ]);

        //  Trả JSON về frontend
        return response()->json([
            'message' => 'Tạo chương trình giảm giá thành công',
            'count'   => count($inserted),
            'data'    => $inserted,
        ], 201);
    }

    /**
     * Cập nhật chương trình giảm giá
     */
    public function update(Request $request, $id)
    {
        $sale = ProductSale::find($id);

        if (!$sale) {
            return response()->json(['error' => 'Không tìm thấy chương trình giảm giá'], 404);
        }

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'price_sale'   => 'required|numeric|min:0',
            'date_begin'   => 'required|date',
            'date_end'     => 'required|date|after:date_begin',
            'status'       => 'nullable|in:0,1',
            'product_ids'  => 'required|array',
            'product_ids.*' => 'exists:product,id',
        ]);

        $validated['status'] = $validated['status'] ?? 1;
        $updatedBy = 1;
        $oldName = $sale->name;

        //  Cập nhật thông tin chung của chương trình
        ProductSale::where('name', $oldName)->update([
            'name'        => $validated['name'],
            'price_sale'  => $validated['price_sale'],
            'date_begin'  => $validated['date_begin'],
            'date_end'    => $validated['date_end'],
            'status'      => $validated['status'],
            'updated_by'  => $updatedBy,
            'updated_at'  => now(),
        ]);

        // Lấy product_id hiện có trong chương trình
        $existingProductIds = ProductSale::where('name', $validated['name'])->pluck('product_id')->toArray();

        //  Xóa sản phẩm không còn trong danh sách mới
        $toDelete = array_diff($existingProductIds, $validated['product_ids']);
        if (!empty($toDelete)) {
            ProductSale::where('name', $validated['name'])
                ->whereIn('product_id', $toDelete)
                ->delete();
        }

        // Thêm sản phẩm mới (bỏ qua các sản phẩm đã tồn tại)
        $toAdd = array_diff($validated['product_ids'], $existingProductIds);
        foreach ($toAdd as $pid) {
            ProductSale::create([
                'name'        => $validated['name'],
                'product_id'  => $pid,
                'price_sale'  => $validated['price_sale'],
                'date_begin'  => $validated['date_begin'],
                'date_end'    => $validated['date_end'],
                'status'      => $validated['status'],
                'created_by'  => $updatedBy,
            ]);
        }

        return response()->json([
            'message' => "Cập nhật chương trình '{$validated['name']}' thành công",
            'total_products' => count($validated['product_ids']),
        ]);
    }


    /**
     *  Xóa tất cả chương trình giảm giá cùng tên
     */
    public function destroy($id)
    {
        // Lấy chương trình giảm giá theo id
        $sale = ProductSale::find($id);

        if (!$sale) {
            return response()->json(['error' => 'Không tìm thấy chương trình giảm giá'], 404);
        }

        $name = $sale->name;

        //  Xóa tất cả bản ghi cùng tên chương trình
        $deletedCount = ProductSale::where('name', $name)->delete();

        //  Ghi log
        Log::warning("🗑️ Đã xóa {$deletedCount} bản ghi chương trình giảm giá có tên '{$name}'");

        //  Trả về JSON
        return response()->json([
            'message' => "Đã xóa tất cả chương trình giảm giá có tên '{$name}'",
            'deleted_count' => $deletedCount
        ]);
    }
}
