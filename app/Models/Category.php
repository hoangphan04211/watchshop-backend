<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
     use HasFactory, SoftDeletes;
     protected $table = 'category';
     protected $fillable = [
          'name',
          'slug',
          'image',
          'parent_id',
          'sort_order',
          'description',
          'created_by',
          'updated_by',
          'status'
     ];
     protected $dates = ['deleted_at'];
}
