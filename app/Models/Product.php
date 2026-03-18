<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;


class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'product';
    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'brand_id',
        'image',
        'price',
        'price_sale',
        'description',
        'status',
        'created_by',
        'updated_by',
        'deleted_at'
    ];

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }



    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class, 'product_id');
    }

    public function sales()
    {
        return $this->hasMany(ProductSale::class, 'product_id');
    }

    public function stores()
    {
        return $this->hasMany(ProductStore::class, 'product_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'product_id');
    }


    // ================= SỰ KIỆN XÓA =================
    protected static function booted()
    {
        // Khi xóa mềm product
        static::deleting(function ($product) {
            if (! $product->isForceDeleting()) {
                $product->images()->delete();
                $product->attributes()->delete();
                $product->stores()->delete();
            }
        });

        // Khi khôi phục product
        static::restoring(function ($product) {
            $product->images()->withTrashed()->restore();
            $product->attributes()->withTrashed()->restore();
            $product->stores()->withTrashed()->restore();
        });

        // Khi xóa vĩnh viễn
        static::forceDeleted(function ($product) {
            $product->images()->forceDelete();
            $product->attributes()->forceDelete();
            $product->sales()->delete();
            $product->stores()->withTrashed()->forceDelete();
        });
    }
}
