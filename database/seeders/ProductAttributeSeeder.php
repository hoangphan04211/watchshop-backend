<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Đồng hồ 1 (Rolex Submariner)
            ['product_id' => 1, 'attribute_id' => 1, 'value' => 'Đen'],           // Màu sắc
            ['product_id' => 1, 'attribute_id' => 2, 'value' => '40mm'],          // Kích thước mặt
            ['product_id' => 1, 'attribute_id' => 3, 'value' => 'Thép không gỉ'], // Chất liệu dây
            ['product_id' => 1, 'attribute_id' => 4, 'value' => 'Automatic'],     // Loại máy

            // Đồng hồ 2 (Omega Speedmaster)
            ['product_id' => 2, 'attribute_id' => 1, 'value' => 'Trắng'],         // Màu sắc
            ['product_id' => 2, 'attribute_id' => 2, 'value' => '42mm'],          // Kích thước mặt
            ['product_id' => 2, 'attribute_id' => 3, 'value' => 'Da'],            // Chất liệu dây
            ['product_id' => 2, 'attribute_id' => 4, 'value' => 'Quartz'],        // Loại máy
        ];

        DB::table('product_attributes')->insert($data);
    }
}
