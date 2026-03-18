<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $fillable = ['name'];
    protected $table='attributes';
    public $timestamps = true;
    // Quan hệ: Attribute có thể gán cho nhiều sản phẩm

    public function productAttributes()
    {
        return $this->hasMany(ProductAttribute::class, 'attribute_id');
    }
}
