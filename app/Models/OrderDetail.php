<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
     protected $table = 'order_details';
     protected $fillable = [
          'order_id',
          'product_id',
          'price',
          'qty',
          'amount',
          'discount',
          'attributes',
     ];

     public $timestamps = false;

     public function order()
     {
          return $this->belongsTo(Order::class);
     }

     public function product()
     {
          return $this->belongsTo(Product::class, 'product_id');
     }

     protected $casts = [
          'attributes' => 'array',
     ];
}
