<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class OrderDetailSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Giả sử products có id 1 → 5 (Rolex, Omega, Casio, Seiko, Tissot)
        $productIds = [1, 2, 3, 4, 5];

        // Lấy danh sách order_id đã tạo
        $orderIds = DB::table('orders')->pluck('id')->toArray();

        foreach ($orderIds as $orderId) {
            $numItems = $faker->numberBetween(1, 3);

            for ($i = 0; $i < $numItems; $i++) {
                $productId = $faker->randomElement($productIds);
                $price     = $faker->randomElement([5000, 8000, 12000, 20000, 35000]);
                $qty       = $faker->numberBetween(1, 3);
                $discount  = $faker->randomElement([0, 500, 1000]);

                DB::table('order_details')->insert([
                    'order_id'   => $orderId,
                    'product_id' => $productId,
                    'price'      => $price,
                    'qty'        => $qty,
                    'amount'     => ($price * $qty) - $discount,
                    'discount'   => $discount,
                ]);
            }
        }
    }
}
