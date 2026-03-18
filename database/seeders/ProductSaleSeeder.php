<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSaleSeeder extends Seeder {
    public function run(): void {
        for ($i = 1; $i <= 10; $i++) {
            DB::table('product_sales')->insert([
                'name'       => "Khuyến mãi $i",
                'product_id' => 1,
                'price_sale' => rand(50000, 200000),
                'date_begin' => now(),
                'date_end'   => now()->addDays(30),
                'created_by' => 1,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
