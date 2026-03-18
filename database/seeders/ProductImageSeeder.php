<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductImageSeeder extends Seeder {
    public function run(): void {
        for ($i = 1; $i <= 10; $i++) {
            DB::table('product_images')->insert([
                'product_id' => 1,
                'image'      => "product_image_$i.jpg",
                'alt'        => "alt image $i",
                'title'      => "title image $i",
            ]);
        }
    }
}
