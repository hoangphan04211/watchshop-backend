<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\ProductSale;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\ProductImage;
use App\Models\ProductAttribute;
use App\Models\Attribute;
use Faker\Factory as Faker;
// use Illuminate\Database\Eloquent\SoftDeletes;

class ProductController extends Controller
{
    /**
     * Danh sách sản phẩm (có phân trang + tìm kiếm + lọc)
     */
    public function index(Request $request)
    {
        $limit = (int) $request->input('limit', 8);
        $query = Product::query();

        // Tìm kiếm theo tên
        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        // Lọc theo category_id
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate($limit);

        return response()->json($products);
    }

    public function getAllAttributes()
    {
        $attributes = Attribute::all();
        return response()->json($attributes);
    }

    // Tạo attribute mới
    public function createAttribute(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $attribute = Attribute::create($validated);

        return response()->json([
            'message' => 'Attribute đã tạo thành công',
            'data' => $attribute
        ]);
    }


    /**
     * Chi tiết sản phẩm
     */
    public function show($id)
    {
        $product = Product::with(['images', 'attributes.attribute'])->findOrFail($id);

        return response()->json($product);
    }




    public function store(Request $request)
    {
        $faker = Faker::create();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'price_sale'  => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:category,id',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,gif|max:20000',
            'images.*'    => 'nullable|image|mimes:jpg,jpeg,png,gif|max:20000', // nhiều ảnh phụ
            'attributes'  => 'nullable|array', // thêm validation cho attributes
            'attributes.*.attribute_id' => 'required|exists:attributes,id',
            'attributes.*.value'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        // ====================
        // Upload ảnh chính
        // ====================
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $ext = $image->getClientOriginalExtension();
            $imageName = $validated['slug'] . '_' . time() . '.' . $ext;

            $uploadPath = public_path('images/products');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $image->move($uploadPath, $imageName);
            $validated['image'] = $imageName;
            Log::info("Ảnh chính đã upload", ['file' => $imageName]);
        }

        // giả lập created_by
        $validated['created_by'] = $faker->numberBetween(1, 10);

        // Lưu sản phẩm
        $product = Product::create($validated);
        Log::info("Sản phẩm đã lưu", $product->toArray());

        // ====================
        // Upload nhiều ảnh phụ
        // ====================
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $ext = $file->getClientOriginalExtension();
                $fileName = $validated['slug'] . '_sub_' . uniqid() . '.' . $ext;

                $uploadPath = public_path('images/products');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $file->move($uploadPath, $fileName);

                $subImg = ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $fileName,
                    'alt'        => $product->name,
                    'title'      => $product->name,
                ]);

                Log::info("Ảnh phụ đã lưu", $subImg->toArray());
            }
        } else {
            Log::warning("Không có ảnh phụ trong request");
        }

        // ====================
        // Lưu attributes
        // ====================
        if ($request->has('attributes')) {
            foreach ($request->input('attributes') as $attr) {
                ProductAttribute::create([
                    'product_id'   => $product->id,
                    'attribute_id' => $attr['attribute_id'],
                    'value'        => $attr['value'],
                ]);
            }
            Log::info("Attributes đã lưu cho sản phẩm {$product->id}", $request->input('attributes'));
        } else {
            Log::warning("Không có attributes trong request");
        }

        return response()->json([
            'message' => 'Thêm sản phẩm thành công',
            'data'    => $product->load('images', 'attributes.attribute')
        ]);
    }




    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Không tìm thấy sản phẩm'], 404);
        }

        // Validate cơ bản
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'price_sale' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:category,id',
            'status' => 'required|in:0,1',
        ]);

        // Cập nhật thông tin chính
        $product->update($validated + [
            'description' => $request->description ?? '',
        ]);

        // === ẢNH CHÍNH ===
        if ($request->hasFile('image')) {
            $oldPath = public_path('images/products/' . $product->image);
            if (file_exists($oldPath)) unlink($oldPath);

            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/products'), $fileName);
            $product->image = $fileName;
            $product->save();
        }

        // === XOÁ ẢNH PHỤ ===
        if ($request->has('removed_images')) {
            foreach ($request->removed_images as $imgId) {
                $subImg = \App\Models\ProductImage::find($imgId);
                if ($subImg) {
                    $path = public_path('images/products/' . $subImg->image);
                    if (file_exists($path)) unlink($path);
                    $subImg->delete();
                }
            }
        }

        // === THÊM ẢNH PHỤ MỚI ===
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('images/products'), $fileName);

                \App\Models\ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $fileName
                ]);
            }
        }

        /// === CẬP NHẬT THUỘC TÍNH ===
        if ($request->has('attributes')) {
            $attrData = $request->input('attributes', []); // lấy đúng mảng dữ liệu
            $existingIds = [];

            foreach ($attrData as $attr) {
                // Nếu client gửi tên thuộc tính mới (chưa có trong bảng attributes)
                if (!empty($attr['name']) && empty($attr['attribute_id'])) {
                    $attributeModel = \App\Models\Attribute::firstOrCreate([
                        'name' => $attr['name']
                    ]);
                    $attr['attribute_id'] = $attributeModel->id;
                }

                // === CẬP NHẬT ===
                if (!empty($attr['id'])) {
                    $existingAttr = $product->attributes()->withTrashed()->find($attr['id']);
                    // withTrashed() để phục hồi lại nếu bị xóa mềm trước đó
                    if ($existingAttr) {
                        $existingAttr->restore(); // khôi phục nếu đang bị xóa mềm
                        $existingAttr->update([
                            'attribute_id' => $attr['attribute_id'],
                            'value'        => $attr['value'],
                        ]);
                        $existingIds[] = $existingAttr->id;
                    }
                }
                // === THÊM MỚI ===
                else {
                    if (!empty($attr['attribute_id']) && isset($attr['value'])) {
                        $newAttr = $product->attributes()->create([
                            'attribute_id' => $attr['attribute_id'],
                            'value'        => $attr['value'],
                        ]);
                        $existingIds[] = $newAttr->id;
                    }
                }
            }

            // === XÓA CÁC THUỘC TÍNH KHÔNG CÒN TRONG REQUEST ===
            if (!empty($existingIds)) {
                $product->attributes()
                    ->whereNotIn('id', $existingIds)
                    ->delete(); // xóa mềm
            } else {
                // Nếu không còn attribute nào trong form → xóa tất cả
                $product->attributes()->delete();
            }
        }



        return response()->json(['message' => 'Cập nhật sản phẩm thành công']);
    }


    /**
     * Xóa sản phẩm (chuyển vào thùng rác)
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Đã đưa sản phẩm vào thùng rác']);
    }

    /**
     * Danh sách sản phẩm trong thùng rác
     */
    public function trash()
    {
        $products = Product::onlyTrashed()->get();
        return response()->json($products);
    }

    /**
     * Khôi phục sản phẩm trong thùng rác
     */
    public function restore(string $id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();

        return response()->json(['message' => 'Khôi phục sản phẩm thành công']);
    }

    /**
     * Xóa vĩnh viễn sản phẩm
     */
    public function forceDelete(string $id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->forceDelete();

        return response()->json(['message' => 'Đã xóa vĩnh viễn sản phẩm']);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////


    //**
    //  * Lấy  4 sản phẩm mới nhất (kể cả không có tồn kho, ưu tiên giá sale nếu có)
    //  */
    public function product_new(Request $request)
    {
        $limit = $request->limit ?? 4;
        $now = now();

        $products = Product::where('status', 1)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($p) use ($now) {
                // Tính tồn kho
                $stock = ProductStore::where('product_id', $p->id)->sum('qty');

                // Lấy giá sale nếu có
                $sale = ProductSale::where('product_id', $p->id)
                    ->where('date_begin', '<=', $now)
                    ->where('date_end', '>', $now)
                    ->where('status', 1)
                    ->first();

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'price' => $p->price,
                    'price_sale' => $sale->price_sale ?? $p->price, // ✅ nếu không có sale thì lấy giá gốc
                    'image' => $p->image,
                    'image_url' => $p->image ? url("images/products/{$p->image}") : null,
                    'stock' => (int) $stock,
                ];
            });

        return response()->json($products);
    }




    /**
     * Lấy sản phẩm khuyến mãi (kể cả không có tồn kho)
     */
    public function product_sale(Request $request)
    {
        $limit = $request->input('limit', 10);
        $now = now();

        // Lấy danh sách chương trình khuyến mãi đang hoạt động
        $sales = ProductSale::where('date_begin', '<=', $now)
            ->where('date_end', '>', $now)
            ->where('status', 1)
            ->with(['product' => function ($q) {
                $q->select('id', 'name', 'slug', 'image', 'price')
                    ->whereNull('deleted_at')
                    ->where('status', 1);
            }])
            ->get();

        // Gom nhóm theo tên chương trình khuyến mãi
        $grouped = $sales->groupBy('name')->map(function ($items, $saleName) {
            $first = $items->first();

            $products = $items->filter(fn($i) => $i->product !== null)->map(function ($item) {
                $product = $item->product;

                // ✅ Tính tổng tồn kho
                $stock = \App\Models\ProductStore::where('product_id', $product->id)->sum('qty');

                return [
                    'id'         => $product->id,
                    'name'       => $product->name,
                    'slug'       => $product->slug,
                    'price'      => $product->price,
                    'price_sale' => $item->price_sale,
                    'image'      => $product->image,
                    'image_url'  => $product->image ? url("images/products/{$product->image}") : null,
                    'stock'      => (int) $stock, // ✅ luôn có stock
                ];
            })->values();

            return [
                'sale_name'  => $saleName,
                'price_sale' => $first->price_sale,
                'date_begin' => $first->date_begin,
                'date_end'   => $first->date_end,
                'products'   => $products,
            ];
        })->values();

        return response()->json($grouped->take($limit));
    }






    /**
     * Lấy sản phẩm theo category (slug)
     */
    public function product_by_category(Request $request, $slug)
    {
        $limit = $request->limit ?? 10;

        $category = Category::where('slug', $slug)->firstOrFail();

        $productStore = ProductStore::select('product_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_id');

        $products = Product::query()
            ->joinSub($productStore, 'ps', function ($join) {
                $join->on('ps.product_id', '=', 'product.id')
                    ->where('ps.total_qty', '>', 0);
            })
            ->where('product.category_id', $category->id)
            ->select('product.id', 'product.name', 'product.image', 'product.price', 'ps.total_qty')
            ->orderBy('product.created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($products);
    }

    /**
     * Lấy tất cả sản phẩm (cho phía người dùng)
     * Bỏ bớt thông tin nội bộ, chỉ lấy thông tin cần thiết
     */
    public function product_all(Request $request)
    {
        $limit = $request->input('limit', 100);
        $now = now();

        // Lấy sản phẩm (status = 1, chưa xóa mềm)
        $products = Product::with(['category:id,name,slug', 'attributes.attribute:id,name'])
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $data = $products->map(function ($p) use ($now) {
            // Tính tồn kho đơn giản
            $stock = ProductStore::where('product_id', $p->id)->sum('qty');

            // Giá sale hiện tại
            $sale = ProductSale::where('product_id', $p->id)
                ->where('date_begin', '<=', $now)
                ->where('date_end', '>', $now)
                ->where('status', 1)
                ->first();

            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => $p->price,
                'price_sale' => $sale->price_sale ?? null,
                'description' => $p->description,
                'image' => $p->image,
                'image_url' => $p->image ? url("images/products/{$p->image}") : null,
                'attributes' => $p->attributes->map(function ($a) {
                    return [
                        'id' => $a->id,
                        'attribute_id' => $a->attribute_id,
                        'name' => optional($a->attribute)->name,
                        'value' => $a->value,
                    ];
                }),
                'category' => [
                    'id' => $p->category->id ?? null,
                    'name' => $p->category->name ?? null,
                    'slug' => $p->category->slug ?? null,
                ],
                'stock' => (int) $stock, // =0 nếu không có tồn kho
            ];
        });

        return response()->json($data);
    }






    /**
     * Lấy chi tiết sản phẩm theo slug (cho frontend)
     */
    public function product_by_slug($slug)
    {
        $now = now();

        // Lấy tên bảng đúng từ model (trong project của bạn là 'product')
        $productTable = (new Product)->getTable(); // ví dụ: 'product'
        $imageBaseUrl = url('images/products');

        // Lấy sản phẩm (status = 1)
        $product = Product::with(['images', 'attributes.attribute', 'category:id,name,slug'])
            ->where('slug', $slug)
            ->where('status', 1)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
        }

        // Tính tồn kho
        $stock = ProductStore::where('product_id', $product->id)->sum('qty');

        // Kiểm tra khuyến mãi đang hoạt động
        $sale = ProductSale::where('product_id', $product->id)
            ->where('date_begin', '<=', $now)
            ->where('date_end', '>', $now)
            ->where('status', 1)
            ->first();

        // Chuẩn hóa đường dẫn ảnh
        $mainImageUrl = $product->image ? url("images/products/{$product->image}") : null;
        $images = $product->images->map(fn($i) => [
            'id' => $i->id,
            'image' => $i->image,
            'url' => $i->image ? url("images/products/{$i->image}") : null,
            'alt' => $i->alt ?? $product->name,
        ])->values();

        $attributes = $product->attributes->map(function ($a) {
            return [
                'id' => $a->id,
                'attribute_id' => $a->attribute_id,
                'name' => optional($a->attribute)->name,
                'value' => $a->value,
            ];
        });

        $data = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'price_sale' => $sale->price_sale ?? null,
            'description' => $product->description,
            'image' => $product->image,
            'image_url' => $mainImageUrl,
            'images' => $images,
            'attributes' => $attributes,
            'category' => [
                'id' => $product->category->id ?? null,
                'name' => $product->category->name ?? null,
                'slug' => $product->category->slug ?? null,
            ],
            'stock' => (int)$stock,
        ];

        // --- Related products: dùng table động để tránh mismatch tên bảng ---
        $productStoreSub = ProductStore::select('product_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_id');

        $productSaleSub = ProductSale::select('product_id', 'price_sale')
            ->where('date_begin', '<=', $now)
            ->where('date_end', '>', $now)
            ->where('status', 1);

        // Bắt buộc đặt FROM đúng table của model để các tham chiếu sau khớp nhau
        $relatedQuery = Product::query()->from($productTable)
            ->where('category_id', $product->category_id)
            ->where($productTable . '.id', '!=', $product->id)
            ->where($productTable . '.status', 1)
            ->joinSub($productStoreSub, 'ps', function ($join) use ($productTable) {
                $join->on('ps.product_id', '=', $productTable . '.id')
                    ->where('ps.total_qty', '>', 0);
            })
            ->leftJoinSub($productSaleSub, 'psale', function ($join) use ($productTable) {
                $join->on('psale.product_id', '=', $productTable . '.id');
            })
            ->select(
                $productTable . '.id',
                $productTable . '.name',
                $productTable . '.slug',
                $productTable . '.image',
                $productTable . '.price',
                'psale.price_sale'
            )
            ->orderBy($productTable . '.created_at', 'desc')
            ->limit(6);

        $related = $relatedQuery->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'image' => $p->image,
                'image_url' => $p->image ? url("images/products/{$p->image}") : null,
                'price' => $p->price,
                'price_sale' => $p->price_sale ?? null,
            ];
        });

        return response()->json([
            'product' => $data,
            'related' => $related,
        ]);
    }
}
