<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductStoreSeeder extends Seeder {
    public function run(): void {
        for ($i = 1; $i <= 10; $i++) {
            DB::table('product_stores')->insert([
                'product_id' => 1,
                'price_root' => rand(50000, 200000),
                'qty'        => rand(1, 100),
                'created_by' => 1,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
