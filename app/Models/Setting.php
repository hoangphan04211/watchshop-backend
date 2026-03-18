<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    public $timestamps = false;

    protected $fillable = [
        'site_name',
        'email',
        'phone',
        'hotline',
        'address',
        'status',
    ];
}
