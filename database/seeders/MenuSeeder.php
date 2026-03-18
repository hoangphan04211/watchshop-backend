<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('menus')->truncate(); // Xóa dữ liệu cũ trước khi seeding
        $now = now();

        $menus = [
            // ===================== MENU CHÍNH =====================
            [
                'name'       => 'Trang chủ',
                'link'       => '/',
                'type'       => 'custom',
                'parent_id'  => 0,
                'sort_order' => 1,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Sản phẩm',
                'link'       => '/products',
                'type'       => 'category',
                'parent_id'  => 0,
                'sort_order' => 2,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Khuyến mãi',
                'link'       => '/sale',
                'type'       => 'custom',
                'parent_id'  => 0,
                'sort_order' => 3,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Bài viết',
                'link'       => '/posts',
                'type'       => 'topic',
                'parent_id'  => 0,
                'sort_order' => 4,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Liên hệ',
                'link'       => '/contact',
                'type'       => 'page',
                'parent_id'  => 0,
                'sort_order' => 5,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ===================== FOOTER GROUP =====================
            [
                'name'       => 'Về Chúng Tôi',
                'link'       => '#',
                'type'       => 'custom',
                'parent_id'  => 0,
                'sort_order' => 6,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ], // ID 6
            [
                'name'       => 'Chính Sách & Hướng Dẫn',
                'link'       => '#',
                'type'       => 'custom',
                'parent_id'  => 0,
                'sort_order' => 7,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ], // ID 7
            [
                'name'       => 'Hỗ Trợ Khách Hàng',
                'link'       => '#',
                'type'       => 'custom',
                'parent_id'  => 0,
                'sort_order' => 8,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ], // ID 8

            // ===================== FOOTER CON - VỀ CHÚNG TÔI =====================
            [
                'name'       => 'Giới thiệu',
                'link'       => '/posts/about-us',
                'type'       => 'page',
                'parent_id'  => 6,
                'sort_order' => 1,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Địa chỉ & liên hệ',
                'link'       => '/posts/contact-info',
                'type'       => 'page',
                'parent_id'  => 6,
                'sort_order' => 2,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Giờ làm việc',
                'link'       => '/posts/working-hours',
                'type'       => 'page',
                'parent_id'  => 6,
                'sort_order' => 3,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ===================== FOOTER CON - CHÍNH SÁCH =====================
            [
                'name'       => 'Chính sách bảo mật',
                'link'       => '/posts/privacy-policy',
                'type'       => 'page',
                'parent_id'  => 7,
                'sort_order' => 1,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Điều khoản sử dụng',
                'link'       => '/posts/terms-of-service',
                'type'       => 'page',
                'parent_id'  => 7,
                'sort_order' => 2,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Chính sách đổi trả',
                'link'       => '/posts/return-policy',
                'type'       => 'page',
                'parent_id'  => 7,
                'sort_order' => 3,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Chính sách vận chuyển',
                'link'       => '/posts/shipping-policy',
                'type'       => 'page',
                'parent_id'  => 7,
                'sort_order' => 4,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Phương thức thanh toán',
                'link'       => '/posts/payment-methods',
                'type'       => 'page',
                'parent_id'  => 7,
                'sort_order' => 5,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ===================== FOOTER CON - HỖ TRỢ KHÁCH HÀNG =====================
            [
                'name'       => 'Câu hỏi thường gặp (FAQ)',
                'link'       => '/posts/faq',
                'type'       => 'page',
                'parent_id'  => 8,
                'sort_order' => 1,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Trung tâm hỗ trợ / khiếu nại',
                'link'       => '/posts/support-center',
                'type'       => 'page',
                'parent_id'  => 8,
                'sort_order' => 2,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Hướng dẫn bảo hành',
                'link'       => '/posts/warranty-guide',
                'type'       => 'page',
                'parent_id'  => 8,
                'sort_order' => 3,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Chat trực tuyến',
                'link'       => '/posts/live-chat',
                'type'       => 'page',
                'parent_id'  => 8,
                'sort_order' => 4,
                'created_by' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('menus')->insert($menus);
    }
}
