<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CartDetail;

class Cart extends Model
{
    protected $table = 'carts';

    protected $fillable = [
        'user_id'
    ];

    // Một cart thuộc về một user
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    // Một cart có nhiều chi tiết
    public function details()
    {
        return $this->hasMany(CartDetail::class, 'cart_id');
    }
}
