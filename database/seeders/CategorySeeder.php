<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder {
    public function run(): void {
        for ($i=1;$i<=10;$i++) {
            DB::table('category')->insert([
                'name'=>"Danh mục $i",
                'slug'=>"danh-muc-$i",
                'image'=>null,
                'parent_id'=>0,
                'sort_order'=>$i,
                'description'=>"Mô tả danh mục $i",
                'created_by'=>1,
                'updated_by'=>null,
                'status'=>1,
                'created_at'=>now(),
                'updated_at'=>now()
            ]);
        }
    }
}

