<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder {
    public function run(): void {
        for ($i=1;$i<=10;$i++) {
            DB::table('product')->insert([
                'name'=>"Sản phẩm $i",
                'slug'=>"san-pham-$i",
                'category_id'=>1,
                'brand_id'=>null,
                'image'=>null,
                'price'=>rand(100000,500000),
                'price_sale'=>rand(50000,300000),
                'description'=>"Mô tả sản phẩm $i",
                'created_by'=>1,
                'updated_by'=>null,
                'status'=>1,
                'created_at'=>now(),
                'updated_at'=>now()
            ]);
        }
    }
}

