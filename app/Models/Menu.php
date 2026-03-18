<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
     use HasFactory;

     protected $table = 'menus';

     protected $fillable = [
          'name',
          'link',
          'type',
          'parent_id',
          'sort_order',
          'table_id',
          'created_by',
          'updated_by',
          'status',
     ];

     // Quan hệ menu con (nếu cần cho tree)
     public function children()
     {
          return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort_order', 'asc');
     }
}
