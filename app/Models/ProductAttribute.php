<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttribute extends Model
{
     use SoftDeletes;
     protected $table = 'product_attributes';

     // Cho phép mass assignment
     protected $fillable = ['product_id', 'attribute_id', 'value'];

     public $timestamps = true; // tạo tự động created_at và updated_at

     // Quan hệ ngược: thuộc tính này thuộc về 1 sản phẩm
     public function product()
     {
          return $this->belongsTo(Product::class, 'product_id');
     }

     // Quan hệ với bảng attributes (để lấy tên thuộc tính)
     public function attribute()
     {
          return $this->belongsTo(Attribute::class, 'attribute_id');
     }
}
