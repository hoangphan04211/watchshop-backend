<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa dữ liệu cũ (tùy chọn)
        DB::table('banner')->truncate();

        // Vòng lặp tạo 10 banner mẫu
        for ($i = 1; $i <= 10; $i++) {
            DB::table('banner')->insert([
                'name'        => "Banner $i",
                'image'       => "banner$i.jpg",
                'link'        => "https://example.com/banner$i",
                'position'    => $i % 2 == 0 ? 'ads' : 'slideshow',
                'sort_order'  => $i,
                'description' => "Mô tả cho banner $i",
                'created_by'  => 1,
                'updated_by'  => null,
                'status'      => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
}

