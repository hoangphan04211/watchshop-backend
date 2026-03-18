<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TopicSeeder extends Seeder
{
    public function run(): void
    {
        $topics = [
            'Đồng hồ nam',
            'Đồng hồ nữ',
            'Đồng hồ đôi',
            'Đồng hồ cơ',
            'Đồng hồ pin (Quartz)',
            'Đồng hồ thông minh',
            'Rolex Collection',
            'Omega Collection',
            'Casio Collection',
            'Tin tức & xu hướng'
        ];

        foreach ($topics as $index => $name) {
            DB::table('topics')->insert([
                'name'        => $name,
                'slug'        => Str::slug($name),
                'sort_order'  => $index + 1,
                'description' => "Chủ đề: $name",
                'created_at'  => now(),
                'created_by'  => 1,
                'updated_at'  => null,
                'updated_by'  => null,
                'status'      => 1,
            ]);
        }
    }
}
