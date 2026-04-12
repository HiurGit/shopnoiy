<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EcommerceSampleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        $tables = [
            'wishlist_items',
            'wishlists',
            'order_returns',
            'inventory_movements',
            'order_shipments',
            'order_payments',
            'order_status_logs',
            'order_items',
            'orders',
            'cart_items',
            'carts',
            'coupon_usages',
            'promotion_scopes',
            'promo_tickers',
            'promotions',
            'product_reviews',
            'product_images',
            'inventories',
            'product_size_map',
            'product_color_map',
            'products',
            'product_sizes',
            'product_colors',
            'categories',
            'home_section_items',
            'home_sections',
            'blog_posts',
            'content_pages',
            'footer_links',
            'site_settings',
            'store_business_hours',
            'stores',
            'customer_addresses',
            'customer_profiles',
            'users',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('users')->insert([
            [
                'id' => 1,
                'full_name' => 'Admin Shopnoiy',
                'email' => 'admin@shopnoiy.com',
                'phone' => '0900000001',
                'password_hash' => Hash::make('12345678'),
                'role' => 'admin',
                'status' => 'active',
                'last_login_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'full_name' => 'Nguyen An',
                'email' => 'nguyenan@shopnoiy.com',
                'phone' => '0900000002',
                'password_hash' => Hash::make('12345678'),
                'role' => 'customer',
                'status' => 'active',
                'last_login_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'full_name' => 'Tran Binh',
                'email' => 'tranbinh@shopnoiy.com',
                'phone' => '0900000003',
                'password_hash' => Hash::make('12345678'),
                'role' => 'customer',
                'status' => 'active',
                'last_login_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'full_name' => 'Le Minh',
                'email' => 'leminh@shopnoiy.com',
                'phone' => '0900000004',
                'password_hash' => Hash::make('12345678'),
                'role' => 'customer',
                'status' => 'inactive',
                'last_login_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('customer_profiles')->insert([
            ['id' => 1, 'user_id' => 2, 'gender' => 'male', 'birthday' => '1997-03-20', 'tier' => 'gold', 'total_spent' => 8450000, 'total_orders' => 12, 'marketing_opt_in' => 1, 'note' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'user_id' => 3, 'gender' => 'female', 'birthday' => '2000-08-11', 'tier' => 'silver', 'total_spent' => 3120000, 'total_orders' => 5, 'marketing_opt_in' => 1, 'note' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'user_id' => 4, 'gender' => 'male', 'birthday' => '2002-12-01', 'tier' => 'new', 'total_spent' => 399000, 'total_orders' => 1, 'marketing_opt_in' => 0, 'note' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('customer_addresses')->insert([
            ['id' => 1, 'user_id' => 2, 'recipient_name' => 'Nguyen An', 'recipient_phone' => '0900000002', 'province' => 'Ha Noi', 'district' => 'Cau Giay', 'ward' => 'Dich Vong', 'address_line' => '12 Nguyen Phong Sac', 'is_default' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'user_id' => 3, 'recipient_name' => 'Tran Binh', 'recipient_phone' => '0900000003', 'province' => 'Da Nang', 'district' => 'Hai Chau', 'ward' => 'Binh Hien', 'address_line' => '29 Tran Phu', 'is_default' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'user_id' => 4, 'recipient_name' => 'Le Minh', 'recipient_phone' => '0900000004', 'province' => 'TP.HCM', 'district' => 'Phu Nhuan', 'ward' => 'Ward 8', 'address_line' => '88 Le Van Sy', 'is_default' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('stores')->insert([
            ['id' => 1, 'code' => 'HN01', 'name' => 'Ha Noi - Cau Giay', 'phone' => '02430000001', 'email' => 'hn01@shopnoiy.com', 'province' => 'Ha Noi', 'district' => 'Cau Giay', 'ward' => 'Dich Vong', 'address_line' => '12 Nguyen Phong Sac', 'open_time' => '08:00:00', 'close_time' => '22:00:00', 'pickup_enabled' => 1, 'priority_order' => 1, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'code' => 'DN01', 'name' => 'Da Nang - Nguyen Van Linh', 'phone' => '02363000001', 'email' => 'dn01@shopnoiy.com', 'province' => 'Da Nang', 'district' => 'Hai Chau', 'ward' => 'Binh Hien', 'address_line' => '29 Tran Phu', 'open_time' => '09:00:00', 'close_time' => '21:30:00', 'pickup_enabled' => 1, 'priority_order' => 2, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'code' => 'HCM01', 'name' => 'HCM - Le Van Sy', 'phone' => '02873000001', 'email' => 'hcm01@shopnoiy.com', 'province' => 'TP.HCM', 'district' => 'Phu Nhuan', 'ward' => 'Ward 8', 'address_line' => '88 Le Van Sy', 'open_time' => '08:30:00', 'close_time' => '22:00:00', 'pickup_enabled' => 1, 'priority_order' => 3, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ]);

        $hours = [];
        $hourId = 1;
        foreach ([1, 2, 3] as $storeId) {
            for ($weekday = 0; $weekday <= 6; $weekday++) {
                $hours[] = [
                    'id' => $hourId++,
                    'store_id' => $storeId,
                    'weekday' => $weekday,
                    'open_time' => '08:00:00',
                    'close_time' => '22:00:00',
                    'is_closed' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        DB::table('store_business_hours')->insert($hours);

        DB::table('site_settings')->insert([
            ['id' => 1, 'setting_key' => 'site_name', 'setting_value' => 'Shopnoiy', 'setting_group' => 'general', 'description' => 'Ten website', 'updated_by' => 'admin', 'updated_at' => $now],
            ['id' => 2, 'setting_key' => 'hotline', 'setting_value' => '1900 9999', 'setting_group' => 'contact', 'description' => 'Hotline CSKH', 'updated_by' => 'admin', 'updated_at' => $now],
            ['id' => 3, 'setting_key' => 'facebook_url', 'setting_value' => 'https://facebook.com/shopnoiy', 'setting_group' => 'social', 'description' => 'Fanpage', 'updated_by' => 'admin', 'updated_at' => $now],
        ]);

        DB::table('footer_links')->insert([
            ['id' => 1, 'group_name' => 'company', 'title' => 'Gioi thieu', 'url' => '/gioi-thieu', 'sort_order' => 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'group_name' => 'company', 'title' => 'Tuyen dung', 'url' => '/tuyen-dung', 'sort_order' => 2, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'group_name' => 'support', 'title' => 'Chinh sach doi tra', 'url' => '/doi-tra', 'sort_order' => 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'group_name' => 'support', 'title' => 'Chinh sach van chuyen', 'url' => '/van-chuyen', 'sort_order' => 2, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'group_name' => 'contact', 'title' => 'Lien he', 'url' => '/lien-he', 'sort_order' => 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('categories')->insert([
            ['id' => 1, 'parent_id' => null, 'name' => 'Ao', 'slug' => 'ao', 'icon_class' => 'bi bi-shirt', 'image_url' => null, 'description' => 'Danh muc ao', 'sort_order' => 1, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'parent_id' => null, 'name' => 'Quan', 'slug' => 'quan', 'icon_class' => 'bi bi-bag', 'image_url' => null, 'description' => 'Danh muc quan', 'sort_order' => 2, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'parent_id' => 1, 'name' => 'Ao Polo', 'slug' => 'ao-polo', 'icon_class' => null, 'image_url' => null, 'description' => null, 'sort_order' => 1, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'parent_id' => 2, 'name' => 'Quan Short', 'slug' => 'quan-short', 'icon_class' => null, 'image_url' => null, 'description' => null, 'sort_order' => 1, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('product_colors')->insert([
            ['id' => 1, 'name' => 'Den', 'slug' => 'den', 'hex_code' => '#111111', 'sort_order' => 1, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Trang', 'slug' => 'trang', 'hex_code' => '#ffffff', 'sort_order' => 2, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Navy', 'slug' => 'navy', 'hex_code' => '#001f54', 'sort_order' => 3, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'Kaki', 'slug' => 'kaki', 'hex_code' => '#c3b091', 'sort_order' => 4, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('product_sizes')->insert([
            ['id' => 1, 'name' => 'S', 'slug' => 's', 'sort_order' => 1, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'M', 'slug' => 'm', 'sort_order' => 2, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'L', 'slug' => 'l', 'sort_order' => 3, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'XL', 'slug' => 'xl', 'sort_order' => 4, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('products')->insert([
            ['id' => 101, 'category_id' => 3, 'name' => 'Ao polo basic', 'slug' => 'ao-polo-basic', 'sku' => 'POLO-001', 'barcode' => '893000000001', 'price' => 299000, 'stock_qty' => 42, 'weight_gram' => 300, 'brand' => 'Shopnoiy', 'description' => 'Chat lieu cotton mem mai', 'care_instructions' => 'Giat lanh', 'return_policy' => 'Doi tra 30 ngay', 'specs_json' => json_encode(['material' => 'cotton']), 'status' => 'active', 'is_featured' => 1, 'view_count' => 12500, 'rating_avg' => 4.7, 'rating_count' => 320, 'sold_count' => 7000, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 102, 'category_id' => 4, 'name' => 'Quan short kaki', 'slug' => 'quan-short-kaki', 'sku' => 'SHORT-014', 'barcode' => '893000000003', 'price' => 359000, 'stock_qty' => 8, 'weight_gram' => 250, 'brand' => 'Shopnoiy', 'description' => 'Vai kaki co gian nhe', 'care_instructions' => 'Khong say nong', 'return_policy' => 'Doi tra 30 ngay', 'specs_json' => json_encode(['fit' => 'regular']), 'status' => 'active', 'is_featured' => 1, 'view_count' => 8300, 'rating_avg' => 4.5, 'rating_count' => 140, 'sold_count' => 1800, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 103, 'category_id' => 1, 'name' => 'Ao thun oversize', 'slug' => 'ao-thun-oversize', 'sku' => 'TSHIRT-009', 'barcode' => '893000000004', 'price' => 219000, 'stock_qty' => 15, 'weight_gram' => 220, 'brand' => 'Shopnoiy', 'description' => 'Vai cotton day dan', 'care_instructions' => 'Giat voi mau tuong tu', 'return_policy' => 'Doi tra 30 ngay', 'specs_json' => json_encode(['style' => 'oversize']), 'status' => 'active', 'is_featured' => 0, 'view_count' => 2400, 'rating_avg' => 4.4, 'rating_count' => 60, 'sold_count' => 950, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('product_color_map')->insert([
            ['id' => 1, 'product_id' => 101, 'color_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'product_id' => 101, 'color_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'product_id' => 102, 'color_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'product_id' => 103, 'color_id' => 3, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('product_size_map')->insert([
            ['id' => 1, 'product_id' => 101, 'size_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'product_id' => 101, 'size_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'product_id' => 102, 'size_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'product_id' => 103, 'size_id' => 2, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('inventories')->insert([
            ['id' => 1, 'variant_id' => 1001, 'store_id' => 1, 'on_hand_qty' => 25, 'reserved_qty' => 3, 'safety_stock' => 5, 'updated_at' => $now],
            ['id' => 2, 'variant_id' => 1001, 'store_id' => 2, 'on_hand_qty' => 17, 'reserved_qty' => 1, 'safety_stock' => 3, 'updated_at' => $now],
            ['id' => 3, 'variant_id' => 1002, 'store_id' => 1, 'on_hand_qty' => 20, 'reserved_qty' => 2, 'safety_stock' => 4, 'updated_at' => $now],
            ['id' => 4, 'variant_id' => 1003, 'store_id' => 3, 'on_hand_qty' => 8, 'reserved_qty' => 1, 'safety_stock' => 2, 'updated_at' => $now],
            ['id' => 5, 'variant_id' => 1004, 'store_id' => 2, 'on_hand_qty' => 15, 'reserved_qty' => 0, 'safety_stock' => 3, 'updated_at' => $now],
        ]);

        DB::table('product_images')->insert([
            ['id' => 1, 'product_id' => 101, 'variant_id' => 1001, 'image_url' => '/images/products/polo-black-1.jpg', 'alt_text' => 'Polo den', 'sort_order' => 1, 'is_primary' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'product_id' => 101, 'variant_id' => 1002, 'image_url' => '/images/products/polo-white-1.jpg', 'alt_text' => 'Polo trang', 'sort_order' => 2, 'is_primary' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'product_id' => 102, 'variant_id' => 1003, 'image_url' => '/images/products/short-kaki-1.jpg', 'alt_text' => 'Short kaki', 'sort_order' => 1, 'is_primary' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'product_id' => 103, 'variant_id' => 1004, 'image_url' => '/images/products/tshirt-navy-1.jpg', 'alt_text' => 'Tshirt navy', 'sort_order' => 1, 'is_primary' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('product_reviews')->insert([
            ['id' => 1, 'product_id' => 101, 'variant_id' => 1001, 'user_id' => 2, 'customer_name' => 'Nguyen An', 'rating' => 5, 'review_text' => 'Ao mac rat thoai mai', 'status' => 'approved', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'product_id' => 102, 'variant_id' => 1003, 'user_id' => 3, 'customer_name' => 'Tran Binh', 'rating' => 4, 'review_text' => 'Form dep, gia hop ly', 'status' => 'approved', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('promotions')->insert([
            ['id' => 1, 'name' => 'Sale thang 3', 'code' => 'MARCH50', 'promotion_type' => 'voucher', 'channel' => 'all', 'discount_type' => 'percent', 'discount_value' => 10, 'min_order_value' => 299000, 'max_discount_value' => 100000, 'start_at' => '2026-03-01 00:00:00', 'end_at' => '2026-03-31 23:59:59', 'status' => 'active', 'description' => 'Giam 10% toi da 100k', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Freeship weekend', 'code' => 'FREESHIP39', 'promotion_type' => 'voucher', 'channel' => 'checkout', 'discount_type' => 'fixed', 'discount_value' => 39000, 'min_order_value' => 300000, 'max_discount_value' => null, 'start_at' => '2026-03-07 00:00:00', 'end_at' => '2026-03-09 23:59:59', 'status' => 'active', 'description' => 'Mien phi van chuyen', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('promotion_scopes')->insert([
            ['id' => 1, 'promotion_id' => 1, 'scope_type' => 'all_products', 'scope_ref_id' => null, 'created_at' => $now],
            ['id' => 2, 'promotion_id' => 2, 'scope_type' => 'all_products', 'scope_ref_id' => null, 'created_at' => $now],
        ]);

        DB::table('promo_tickers')->insert([
            ['id' => 1, 'promotion_id' => 1, 'name' => 'Thong bao sale', 'content_text' => 'Sale 10% cho don tu 299k voi ma MARCH50', 'background_style' => 'linear-gradient(90deg,#f97316,#ef4444)', 'text_color' => '#ffffff', 'speed_seconds' => 20, 'start_at' => '2026-03-01 00:00:00', 'end_at' => '2026-03-31 23:59:59', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'promotion_id' => 2, 'name' => 'Thong bao freeship', 'content_text' => 'Cuoi tuan freeship 39k voi ma FREESHIP39', 'background_style' => '#0ea5e9', 'text_color' => '#ffffff', 'speed_seconds' => 18, 'start_at' => '2026-03-07 00:00:00', 'end_at' => '2026-03-09 23:59:59', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('home_sections')->insert([
            ['id' => 1, 'section_key' => 'hero', 'title' => 'Hero Banner', 'section_type' => 'hero', 'sort_order' => 1, 'is_active' => 1, 'config_json' => json_encode(['autoplay' => true]), 'updated_by' => 'admin', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'section_key' => 'featured-products', 'title' => 'San pham noi bat', 'section_type' => 'featured_products', 'sort_order' => 2, 'is_active' => 1, 'config_json' => null, 'updated_by' => 'admin', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'section_key' => 'news', 'title' => 'Tin tuc', 'section_type' => 'news', 'sort_order' => 3, 'is_active' => 1, 'config_json' => null, 'updated_by' => 'admin', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('home_section_items')->insert([
            ['id' => 11, 'section_id' => 1, 'item_type' => 'banner', 'ref_id' => null, 'title' => 'Mega Sale 50%', 'subtitle' => 'Chi trong thang 3', 'image_url' => '/images/banners/hero-1.jpg', 'target_url' => '/collections/sale', 'sort_order' => 1, 'is_active' => 1, 'start_at' => '2026-03-01 00:00:00', 'end_at' => '2026-03-31 23:59:59', 'meta_json' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 12, 'section_id' => 2, 'item_type' => 'product', 'ref_id' => 101, 'title' => 'Ao polo basic', 'subtitle' => null, 'image_url' => '/images/products/polo-black-1.jpg', 'target_url' => '/products/ao-polo-basic', 'sort_order' => 1, 'is_active' => 1, 'start_at' => null, 'end_at' => null, 'meta_json' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 13, 'section_id' => 3, 'item_type' => 'article', 'ref_id' => 1, 'title' => 'Xu huong xuan he 2026', 'subtitle' => null, 'image_url' => '/images/blog/trend-2026.jpg', 'target_url' => '/blog/xu-huong-xuan-he-2026', 'sort_order' => 1, 'is_active' => 1, 'start_at' => null, 'end_at' => null, 'meta_json' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('blog_posts')->insert([
            ['id' => 1, 'title' => 'Xu huong xuan he 2026', 'slug' => 'xu-huong-xuan-he-2026', 'thumbnail_url' => '/images/blog/trend-2026.jpg', 'excerpt' => 'Tong hop xu huong thoi trang moi', 'content_html' => '<p>Noi dung bai viet mau</p>', 'is_published' => 1, 'published_at' => $now, 'author_name' => 'Admin', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'title' => 'Bi quyet bao quan ao polo', 'slug' => 'bi-quyet-bao-quan-ao-polo', 'thumbnail_url' => '/images/blog/care-polo.jpg', 'excerpt' => 'Meo giat ui giu form ao', 'content_html' => '<p>Noi dung bai viet mau</p>', 'is_published' => 1, 'published_at' => $now, 'author_name' => 'Admin', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('content_pages')->insert([
            ['id' => 1, 'page_key' => 'about-us', 'title' => 'Gioi thieu', 'slug' => 'gioi-thieu', 'content_html' => '<p>Thong tin gioi thieu cong ty</p>', 'is_published' => 1, 'published_at' => $now, 'updated_by' => 'admin', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'page_key' => 'privacy-policy', 'title' => 'Chinh sach bao mat', 'slug' => 'chinh-sach-bao-mat', 'content_html' => '<p>Noi dung chinh sach bao mat</p>', 'is_published' => 1, 'published_at' => $now, 'updated_by' => 'admin', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('carts')->insert([
            ['id' => 1, 'user_id' => 2, 'coupon_code' => 'MARCH50', 'promotion_id' => 1, 'status' => 'active', 'subtotal' => 598000, 'discount_amount' => 59800, 'shipping_fee' => 30000, 'total_amount' => 568200, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('cart_items')->insert([
            ['id' => 1, 'cart_id' => 1, 'product_id' => 101, 'variant_id' => 1001, 'product_name_snapshot' => 'Ao polo basic', 'variant_name_snapshot' => 'Den - M', 'unit_price' => 299000, 'qty' => 2, 'line_total' => 598000, 'selected' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('orders')->insert([
            ['id' => 1001, 'order_code' => 'ODR-20260308-001', 'user_id' => 2, 'customer_name' => 'Nguyen An', 'customer_phone' => '0900000002', 'customer_email' => 'nguyenan@shopnoiy.com', 'delivery_type' => 'delivery', 'store_id' => null, 'shipping_address_text' => '12 Nguyen Phong Sac, Cau Giay, Ha Noi', 'payment_method' => 'cod', 'order_status' => 'pending', 'payment_status' => 'unpaid', 'subtotal' => 450000, 'discount_amount' => 45000, 'promotion_id' => 1, 'coupon_code' => 'MARCH50', 'shipping_fee' => 30000, 'total_amount' => 435000, 'note' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 1002, 'order_code' => 'ODR-20260308-002', 'user_id' => 3, 'customer_name' => 'Tran Binh', 'customer_phone' => '0900000003', 'customer_email' => 'tranbinh@shopnoiy.com', 'delivery_type' => 'pickup', 'store_id' => 2, 'shipping_address_text' => null, 'payment_method' => 'card', 'order_status' => 'shipping', 'payment_status' => 'paid', 'subtotal' => 790000, 'discount_amount' => 0, 'promotion_id' => null, 'coupon_code' => null, 'shipping_fee' => 0, 'total_amount' => 790000, 'note' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('order_items')->insert([
            ['id' => 1, 'order_id' => 1001, 'product_id' => 101, 'variant_id' => 1001, 'sku_snapshot' => 'POLO-001-BLK-M', 'product_name_snapshot' => 'Ao polo basic', 'variant_name_snapshot' => 'Den - M', 'unit_price' => 299000, 'qty' => 1, 'discount_amount' => 29900, 'line_total' => 269100, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'order_id' => 1001, 'product_id' => 103, 'variant_id' => 1004, 'sku_snapshot' => 'TSHIRT-009-NAVY-M', 'product_name_snapshot' => 'Ao thun oversize', 'variant_name_snapshot' => 'Navy - M', 'unit_price' => 219000, 'qty' => 1, 'discount_amount' => 15100, 'line_total' => 203900, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'order_id' => 1002, 'product_id' => 102, 'variant_id' => 1003, 'sku_snapshot' => 'SHORT-014-KAKI-L', 'product_name_snapshot' => 'Quan short kaki', 'variant_name_snapshot' => 'Kaki - L', 'unit_price' => 359000, 'qty' => 2, 'discount_amount' => 0, 'line_total' => 718000, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('order_status_logs')->insert([
            ['id' => 1, 'order_id' => 1001, 'from_status' => null, 'to_status' => 'pending', 'changed_by' => 'system', 'note' => 'Khoi tao don', 'created_at' => $now],
            ['id' => 2, 'order_id' => 1002, 'from_status' => 'pending', 'to_status' => 'shipping', 'changed_by' => 'admin', 'note' => 'Ban giao cho DVVC', 'created_at' => $now],
        ]);

        DB::table('order_payments')->insert([
            ['id' => 1, 'order_id' => 1001, 'payment_method' => 'cod', 'transaction_code' => null, 'amount' => 435000, 'status' => 'pending', 'paid_at' => null, 'raw_response_json' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'order_id' => 1002, 'payment_method' => 'card', 'transaction_code' => 'TXN-1002-001', 'amount' => 790000, 'status' => 'paid', 'paid_at' => $now, 'raw_response_json' => json_encode(['gateway' => 'demo']), 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('order_shipments')->insert([
            ['id' => 1, 'order_id' => 1001, 'carrier_name' => 'GHN', 'tracking_code' => 'GHN1001', 'shipping_status' => 'ready', 'shipping_fee' => 30000, 'shipped_at' => null, 'delivered_at' => null, 'note' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'order_id' => 1002, 'carrier_name' => null, 'tracking_code' => null, 'shipping_status' => 'delivered', 'shipping_fee' => 0, 'shipped_at' => $now, 'delivered_at' => $now, 'note' => 'Nhan tai cua hang', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('coupon_usages')->insert([
            ['id' => 1, 'promotion_id' => 1, 'coupon_code' => 'MARCH50', 'order_id' => 1001, 'user_id' => 2, 'used_at' => $now, 'status' => 'used'],
        ]);

        DB::table('wishlists')->insert([
            ['id' => 1, 'user_id' => 2, 'name' => 'Yeu thich cua toi', 'is_default' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('wishlist_items')->insert([
            ['id' => 1, 'wishlist_id' => 1, 'product_id' => 101, 'variant_id' => 1001, 'note' => 'Mau den dep', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'wishlist_id' => 1, 'product_id' => 102, 'variant_id' => 1003, 'note' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('inventory_movements')->insert([
            ['id' => 1, 'variant_id' => 1001, 'store_id' => 1, 'movement_type' => 'import', 'qty_change' => 50, 'qty_before' => 0, 'qty_after' => 50, 'reference_type' => 'stock_import', 'reference_id' => 1, 'note' => 'Nhap kho dau ky', 'created_by' => 'admin', 'created_at' => $now],
            ['id' => 2, 'variant_id' => 1001, 'store_id' => 1, 'movement_type' => 'reserve', 'qty_change' => -3, 'qty_before' => 50, 'qty_after' => 47, 'reference_type' => 'order', 'reference_id' => 1001, 'note' => 'Giu hang cho don 1001', 'created_by' => 'system', 'created_at' => $now],
        ]);

        DB::table('order_returns')->insert([
            ['id' => 1, 'order_id' => 1002, 'return_code' => 'RET-202603-001', 'status' => 'requested', 'reason' => 'Sai kich thuoc', 'refund_amount' => 359000, 'requested_at' => $now, 'approved_at' => null, 'completed_at' => null, 'note' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
