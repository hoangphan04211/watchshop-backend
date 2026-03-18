<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Danh sách user (admin)
     */
    public function index()
    {
        $users = User::orderBy('id', 'desc')->get();
        return response()->json($users);
    }

    /**
     * Kiểm tra xem email, phone hoặc username đã tồn tại chưa
     */
    public function check(Request $request)
    {
        $field = $request->query('field');
        $value = $request->query('value');

        // Chỉ cho phép kiểm tra 3 trường này
        if (!in_array($field, ['email', 'phone', 'username'])) {
            return response()->json([
                'message' => 'Trường không hợp lệ',
                'exists' => false,
            ], 400);
        }

        // Kiểm tra trong DB
        $exists = User::where($field, $value)->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists
                ? ucfirst($field) . ' đã tồn tại'
                : ucfirst($field) . ' có thể sử dụng',
        ]);
    }

    /**
     * Đăng ký user mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|max:15',
            'username' => 'required|string|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'roles' => 'in:admin,customer'
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['created_by'] = 1;
        $user = User::create($validated);

        return response()->json(['message' => 'Tạo tài khoản thành công', 'user' => $user]);
    }

    /**
     * Xem chi tiết user
     */
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy user'], 404);
        }
        return response()->json($user);
    }

    /**
     * Cập nhật user
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy user'], 404);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:15',
            'roles' => 'in:admin,customer',
            'status' => 'in:0,1',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return response()->json(['message' => 'Cập nhật thành công', 'user' => $user]);
    }

    /**
     * Xóa user
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy user'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'Xóa user thành công']);
    }

    /**
     * 🔐 Đăng nhập
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Sai tên đăng nhập hoặc mật khẩu'], 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'username' => $user->username,
                'roles' => $user->roles,
            ],
        ]);
    }


    /**
     * 🚪 Đăng xuất
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Đăng xuất thành công']);
    }

    // --- Lấy thông tin người dùng (Profile) + vài đơn hàng gần nhất ---
    public function profile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Vui lòng đăng nhập để xem thông tin',
            ], 401);
        }

        // Lấy 5 đơn hàng gần nhất
        $recentOrders = $user->orders() // giả sử mối quan hệ User -> Order đã có
            ->with(['details.product'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'order_id'     => $order->id,
                    'status'       => $order->status,
                    'status_text'  => match ($order->status) {
                        0 => 'Chờ xác nhận',
                        1 => 'Đang xử lý',
                        2 => 'Đang giao hàng',
                        3 => 'Hoàn thành',
                        4 => 'Đã hủy',
                        default => 'Không xác định',
                    },
                    'total_amount' => $order->details->sum('amount'),
                    'created_at'   => $order->created_at->format('d/m/Y H:i'),
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
            'message' => 'Thông tin người dùng',
            'data' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'phone'    => $user->phone,
                'username' => $user->username,
                'roles'    => $user->roles,
                'avatar'   => $user->avatar ? asset('images/users/' . $user->avatar) : null,
                'recent_orders' => $recentOrders,
            ],
        ]);
    }


    // --- Cập nhật profile ---
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Vui lòng đăng nhập để cập nhật thông tin',
            ], 401);
        }

        $validated = $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'email'    => 'sometimes|required|email|unique:users,email,' . $user->id,
            'phone'    => 'sometimes|required|string|max:20',
            'current_password' => 'required_with:password|string',
            'password' => 'sometimes|nullable|string|min:6|confirmed',
            'avatar'   => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:20000',
        ]);

        // --- Cập nhật mật khẩu nếu có ---
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Mật khẩu cũ không đúng',
                ], 400);
            }
            $user->password = Hash::make($request->password);
        }

        // --- Cập nhật avatar ---
        if ($request->hasFile('avatar')) {
            // Xóa avatar cũ nếu có
            if ($user->avatar && file_exists(public_path('images/users/' . $user->avatar))) {
                unlink(public_path('images/users/' . $user->avatar));
            }

            $file = $request->file('avatar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/users'), $filename);
            $user->avatar = $filename;
        }

        // --- Cập nhật các trường khác ---
        $user->fill($request->only(['name', 'email', 'phone']));
        $user->save();

        return response()->json([
            'message' => 'Cập nhật thông tin thành công',
            'data' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'phone'  => $user->phone,
                'avatar' => $user->avatar ? asset('images/users/' . $user->avatar) : null,
            ],
        ]);
    }
}
