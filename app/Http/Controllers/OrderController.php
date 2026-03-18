<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\ProductStore;
use Illuminate\Support\Facades\DB;
use Exception;


class OrderController extends Controller
{
    // ---------- LẤY DANH SÁCH ----------
    public function index()
    {
        $orders = Order::with('details')->orderBy('id', 'desc')->get();
        return response()->json($orders);
    }

    // ---------- THÊM ORDER + ORDERDETAIL ----------
    public function store(Request $request)
    {
        $request->validate([
            'user_id'   => 'required|integer',
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255',
            'phone'     => 'required|string|max:20',
            'address'   => 'required|string|max:255',
            'note'      => 'nullable|string|max:255',
            'status'    => 'in:0,1,2,3',
            'details'   => 'required|array|min:1',
            'details.*.product_id' => 'required|integer',
            'details.*.price'      => 'required|numeric',
            'details.*.qty'        => 'required|integer|min:1',
            'details.*.discount'   => 'nullable|numeric',
        ]);

        // Tạo đơn hàng
        $order = new Order();
        $order->fill($request->only([
            'user_id',
            'name',
            'email',
            'phone',
            'address',
            'note',
            'status'
        ]));
        $order->created_by = 1;
        $order->save();

        // Thêm chi tiết đơn hàng
        foreach ($request->details as $item) {
            OrderDetail::create([
                'order_id'   => $order->id,
                'product_id' => $item['product_id'],
                'price'      => $item['price'],
                'qty'        => $item['qty'],
                'amount'     => ($item['price'] * $item['qty']) - ($item['discount'] ?? 0),
                'discount'   => $item['discount'] ?? 0,
            ]);
        }

        return response()->json([
            'message' => 'Tạo đơn hàng thành công',
            'data'    => $order->load('details')
        ]);
    }

    // ---------- LẤY CHI TIẾT ----------
    public function show(string $id)
    {
        $order = Order::with('details')->findOrFail($id);
        return response()->json($order);
    }

    // ---------- CẬP NHẬT ----------
    public function update(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'name'    => 'sometimes|required|string|max:255',
            'email'   => 'sometimes|required|email|max:255',
            'phone'   => 'sometimes|required|string|max:20',
            'address' => 'sometimes|required|string|max:255',
            'note'    => 'nullable|string|max:255',
            'status'  => 'in:0,1,2,3',
            'details' => 'nullable|array',
        ]);

        $order->fill($request->only([
            'user_id',
            'name',
            'email',
            'phone',
            'address',
            'note',
            'status'
        ]));
        $order->updated_by = 1;
        $order->save();

        // Nếu có chi tiết đơn hàng mới -> xóa cũ và ghi lại
        if ($request->has('details')) {
            $order->details()->delete();
            foreach ($request->details as $item) {
                OrderDetail::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['product_id'],
                    'price'      => $item['price'],
                    'qty'        => $item['qty'],
                    'amount'     => ($item['price'] * $item['qty']) - ($item['discount'] ?? 0),
                    'discount'   => $item['discount'] ?? 0,
                ]);
            }
        }

        return response()->json([
            'message' => 'Cập nhật đơn hàng thành công',
            'data'    => $order->load('details')
        ]);
    }

    // ---------- XOÁ MỀM ----------
    public function destroy(string $id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        return response()->json(['message' => 'Đã xoá đơn hàng']);
    }

    // ---------- DANH SÁCH TRASH ----------
    public function trash()
    {
        $orders = Order::onlyTrashed()->orderBy('id', 'desc')->get();
        return response()->json($orders);
    }

    // ---------- KHÔI PHỤC ----------
    public function restore(string $id)
    {
        $order = Order::onlyTrashed()->findOrFail($id);
        $order->restore();
        return response()->json(['message' => 'Khôi phục đơn hàng thành công']);
    }

    // ---------- XOÁ VĨNH VIỄN ----------
    public function forceDelete(string $id)
    {
        $order = Order::onlyTrashed()->findOrFail($id);
        $order->forceDelete();
        return response()->json(['message' => 'Đã xoá đơn hàng vĩnh viễn']);
    }

    // ---------- Nguoi dung//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function checkout(Request $request)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'Vui lòng đăng nhập để thanh toán'], 401);
        }

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'note'    => 'nullable|string|max:255',
        ]);

        // === LẤY GIỎ HÀNG NGƯỜI DÙNG ===
        $cart = Cart::where('user_id', $user->id)
            ->with(['details.product'])
            ->first();

        if (!$cart || $cart->details->isEmpty()) {
            return response()->json(['message' => 'Giỏ hàng trống'], 400);
        }

        // === LẤY TỒN KHO TẤT CẢ SP 1 LẦN ===
        $productStores = ProductStore::whereIn(
            'product_id',
            $cart->details->pluck('product_id')
        )->get()->keyBy('product_id');

        // === KIỂM TRA TỒN KHO ===
        foreach ($cart->details as $item) {
            $store = $productStores[$item->product_id] ?? null;

            if (!$store) {
                return response()->json([
                    'message' => "Sản phẩm '{$item->product->name}' chưa có trong kho."
                ], 400);
            }

            if ($item->qty > $store->qty) {
                return response()->json([
                    'message' => "Sản phẩm '{$item->product->name}' chỉ còn {$store->qty} sản phẩm."
                ], 400);
            }
        }

        // === TẠO ĐƠN HÀNG ===
        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id'    => $user->id,
                'name'       => $validated['name'],
                'email'      => $validated['email'],
                'phone'      => $validated['phone'],
                'address'    => $validated['address'],
                'note'       => $validated['note'] ?? null,
                'created_by' => $user->id,
                'status'     => 1, // 1 = pending
            ]);

            $total = 0;

            foreach ($cart->details as $item) {
                $price = $item->price_sale ?? $item->price;
                $amount = $price * $item->qty;

                OrderDetail::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'price'      => $price,
                    'qty'        => $item->qty,
                    'amount'     => $amount,
                    'discount'   => 0,
                    'attributes' => $item->attributes,
                ]);

                // ✅ Cập nhật tồn kho
                $store = $productStores[$item->product_id];
                $store->qty = max(0, $store->qty - $item->qty);
                $store->save();

                $total += $amount;
            }

            // ✅ Xóa giỏ hàng
            $cart->details()->delete();
            $cart->delete();

            DB::commit();

            return response()->json([
                'message' => 'Đặt hàng thành công!',
                'order_id' => $order->id,
                'total_amount' => $total,
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Có lỗi khi tạo đơn hàng',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ---------- LỊCH SỬ ĐƠN HÀNG NGƯỜI DÙNG ----------
    public function history(Request $request)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'Vui lòng đăng nhập để xem lịch sử đơn hàng'], 401);
        }

        // Lấy toàn bộ đơn hàng của user kèm chi tiết sản phẩm
        $orders = Order::with(['details.product'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Danh sách trạng thái dạng text
        $statusText = [
            0 => 'Chờ xác nhận',
            1 => 'Đang xử lý',
            2 => 'Đang giao hàng',
            3 => 'Hoàn thành',
            4 => 'Đã hủy',
        ];

        // Xử lý dữ liệu trả về
        $data = $orders->map(function ($order) use ($statusText) {
            return [
                'order_id'     => $order->id,
                'name'         => $order->name,
                'email'        => $order->email,
                'phone'        => $order->phone,
                'address'      => $order->address,
                'note'         => $order->note,
                'status'       => $order->status,
                'status_text'  => $statusText[$order->status] ?? 'Không xác định',
                'created_at'   => $order->created_at->format('d/m/Y H:i'),
                'updated_at'   => $order->updated_at ? $order->updated_at->format('d/m/Y H:i') : null,
                'total_amount' => $order->details->sum('amount'),
                'details'      => $order->details->map(function ($detail) {
                    $product = $detail->product;
                    return [
                        'product_id'   => $product->id ?? null,
                        'product_name' => $product->name ?? 'Sản phẩm đã bị xóa',
                        'image'        => $product && $product->image
                            ? asset('images/products/' . $product->image)
                            : asset('images/no-image.jpg'),
                        'price'        => $detail->price,
                        'qty'          => $detail->qty,
                        'amount'       => $detail->amount,
                    ];
                }),
            ];
        });

        return response()->json([
            'message' => 'Lấy lịch sử đơn hàng thành công',
            'data'    => $data,
        ]);
    }

    // ---------- CHI TIẾT ĐƠN HÀNG NGƯỜI DÙNG ----------
    public function detail($id)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'Vui lòng đăng nhập để xem chi tiết đơn hàng'], 401);
        }

        // Lấy đơn hàng kèm chi tiết
        $order = Order::with(['details.product'])
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }

        // Danh sách trạng thái dạng text
        $statusText = [
            0 => 'Chờ xác nhận',
            1 => 'Đang xử lý',
            2 => 'Đang giao hàng',
            3 => 'Hoàn thành',
            4 => 'Đã hủy',
        ];

        // Chuẩn bị dữ liệu chi tiết
        $data = [
            'order_id'     => $order->id,
            'name'         => $order->name,
            'email'        => $order->email,
            'phone'        => $order->phone,
            'address'      => $order->address,
            'note'         => $order->note,
            'status'       => $order->status,
            'status_text'  => $statusText[$order->status] ?? 'Không xác định',
            'created_at'   => $order->created_at->format('d/m/Y H:i'),
            'updated_at'   => $order->updated_at ? $order->updated_at->format('d/m/Y H:i') : null,
            'total_amount' => $order->details->sum('amount'),
            'details'      => $order->details->map(function ($detail) {
                $product = $detail->product;
                return [
                    'product_id'   => $product->id ?? null,
                    'product_name' => $product->name ?? 'Sản phẩm đã bị xóa',
                    'image'        => $product && $product->image
                        ? asset('images/products/' . $product->image)
                        : asset('images/no-image.jpg'),
                    'price'        => $detail->price,
                    'qty'          => $detail->qty,
                    'amount'       => $detail->amount,
                    'attributes'   => $detail->attributes,
                ];
            }),
        ];

        return response()->json([
            'message' => 'Lấy chi tiết đơn hàng thành công',
            'data'    => $data,
        ]);
    }
}
