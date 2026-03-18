<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            ['name' => 'Màu sắc'],          // Ví dụ: Đen, Trắng, Vàng hồng
            ['name' => 'Kích thước mặt'],   // Ví dụ: 40mm, 42mm
            ['name' => 'Chất liệu dây'],    // Ví dụ: Da, Thép không gỉ, Cao su
            ['name' => 'Loại máy'],         // Automatic, Quartz, Solar
        ];

        DB::table('attributes')->insert($attributes);
    }
}
