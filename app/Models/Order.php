<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
     use HasFactory, SoftDeletes;
     protected $table = 'orders';

     protected $fillable = [
          'user_id',
          'name',
          'email',
          'phone',
          'address',
          'note',
          'created_by',
          'updated_by',
          'status'
     ];

     public function details()
     {
          return $this->hasMany(OrderDetail::class);
     }

     public function user()
     {
          return $this->belongsTo(User::class, 'user_id');
     }
}
