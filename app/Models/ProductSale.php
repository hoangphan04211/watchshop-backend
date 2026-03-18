<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSale extends Model
{
     protected $table = 'product_sales';

     protected $fillable = [
          'name',
          'product_id',
          'price_sale',
          'date_begin',
          'date_end',
          'status',
          'created_by',
          'updated_by',
     ];

     public function product()
     {
          return $this->belongsTo(Product::class, 'product_id');
     }
}
