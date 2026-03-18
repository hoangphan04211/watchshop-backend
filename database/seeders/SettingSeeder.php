<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('settings')->insert([
            'site_name' => 'HoangWatch',
            'email'     => 'contact@hoangwatch.vn',
            'phone'     => '0909123456',
            'hotline'   => '18001234',
            'address'   => '123 Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh',
            'status'    => 1,
        ]);
    }
}
