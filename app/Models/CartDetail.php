<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cart;
use App\Models\Product;

class CartDetail extends Model
{
    protected $table = 'cart_details';

    protected $fillable = [
        'cart_id',
        'product_id',
        'attributes',
        'qty',
        'price',
        'price_sale',
    ];

    protected $casts = [
        'attributes' => 'array', // JSON convert thành array
    ];

    // Quan hệ tới cart
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    // Quan hệ tới sản phẩm
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
