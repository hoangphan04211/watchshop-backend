<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContactSeeder extends Seeder {
    public function run(): void {
        for ($i=1; $i<=10; $i++) {
            DB::table('contact')->insert([
                'user_id' => null,
                'name' => "Khách $i",
                'email' => "khach$i@example.com",
                'phone' => "012345678$i",
                'content' => "Nội dung liên hệ $i",
                'reply_id' => 0,
                'created_by' => 1,
                'updated_by' => null,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
