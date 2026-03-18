<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 1; $i <= 10; $i++) {
            DB::table('orders')->insert([
                'user_id'    => $faker->numberBetween(1, 5),
                'name'       => $faker->name,
                'email'      => $faker->safeEmail,
                'phone'      => $faker->phoneNumber,
                'address'    => $faker->address,
                'note'       => $faker->boolean(30) ? $faker->sentence(8) : null,
                'created_at' => now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status'     => $faker->randomElement([0, 1, 2]), // 0: Hủy, 1: Mới, 2: Đang giao
            ]);
        }
    }
}
