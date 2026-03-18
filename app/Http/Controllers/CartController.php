<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Product;
use App\Models\ProductSale;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Lấy giỏ hàng của user
    public function index()
    {
        $user = Auth::user();
        $cart = Cart::with('details.product')->where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['cart' => []]);
        }

        return response()->json(['cart' => $cart]);
    }

    // Thêm sản phẩm vào giỏ hàng

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:product,id',
            'qty' => 'required|integer|min:1',
            'attributes' => 'nullable'
        ]);

        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $product = Product::findOrFail($request->product_id);

        // Lấy attributes
        $attributes = $request->input('attributes', []);
        if (is_string($attributes)) {
            $attributes = json_decode($attributes, true) ?? [];
        }

        // Lấy giá sale đang hoạt động
        $now = Carbon::now();
        $sale = ProductSale::where('product_id', $product->id)
            ->where('status', 1)
            ->where('date_begin', '<=', $now)
            ->where('date_end', '>', $now)
            ->first();

        $price_sale = $sale ? $sale->price_sale : null;

        // Kiểm tra sản phẩm cùng thuộc tính đã tồn tại trong giỏ
        $existing = $cart->details->first(function ($item) use ($product, $attributes) {
            return $item->product_id == $product->id && $item->attributes == $attributes;
        });

        if ($existing) {
            $existing->qty += $request->qty;
            $existing->save();
        } else {
            $cart->details()->create([
                'product_id' => $product->id,
                'attributes' => $attributes,
                'qty' => $request->qty,
                'price' => $product->price,
                'price_sale' => $price_sale, // <-- giá sale thực tế từ ProductSale
            ]);
        }

        return response()->json(['message' => 'Sản phẩm đã được thêm vào giỏ hàng']);
    }





    // Cập nhật số lượng
    public function update(Request $request, $id)
    {
        $request->validate(['qty' => 'required|integer|min:1']);

        $detail = CartDetail::findOrFail($id);
        $detail->qty = $request->qty;
        $detail->save();

        return response()->json(['message' => 'Cập nhật số lượng thành công']);
    }

    // Xóa sản phẩm khỏi giỏ
    public function remove($id)
    {
        $detail = CartDetail::findOrFail($id);
        $cart = $detail->cart; // lấy cart liên quan
        $detail->delete();

        // Nếu không còn sản phẩm nào, xóa luôn cart
        if ($cart->details()->count() === 0) {
            $cart->delete();
        }

        return response()->json(['message' => 'Đã xóa sản phẩm khỏi giỏ hàng']);
    }

    // Xóa toàn bộ giỏ hàng
    public function clear()
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();
        if ($cart) {
            $cart->details()->delete();
            $cart->delete(); // xóa luôn cart
        }

        return response()->json(['message' => 'Đã xóa toàn bộ giỏ hàng']);
    }
}
