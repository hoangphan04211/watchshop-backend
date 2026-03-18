<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder {
    public function run(): void {
        for ($i = 1; $i <= 5; $i++) {
            DB::table('users')->insert([
                'name'       => "User $i",
                'email'      => "user$i@example.com",
                'phone'      => "09000000$i",
                'username'   => "user$i",
                'password'   => Hash::make('password'),
                'roles'      => $i === 1 ? 'admin' : 'customer',
                'avatar'     => null,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
