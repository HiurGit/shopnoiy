<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ho so mo rong cua khach hang (tier, tong chi tieu, thong tin marketing...).
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('gender', 20)->nullable();
            $table->date('birthday')->nullable();
            $table->string('tier', 20)->default('new');
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->unsignedInteger('total_orders')->default(0);
            $table->boolean('marketing_opt_in')->default(false);
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index('tier', 'idx_customer_profiles_tier');
        });

        // Dia chi giao/nhan cua khach hang, ho tro mac dinh theo user.
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('recipient_name', 150);
            $table->string('recipient_phone', 30);
            $table->string('province', 120);
            $table->string('district', 120);
            $table->string('ward', 120)->nullable();
            $table->string('address_line', 255);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('user_id', 'idx_customer_addresses_user_id');
            $table->index(['user_id', 'is_default'], 'idx_customer_addresses_default');
        });

        // Danh sach cua hang/offline point de giao nhan, pickup va van hanh.
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 180);
            $table->string('phone', 30)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('province', 120);
            $table->string('district', 120);
            $table->string('ward', 120)->nullable();
            $table->string('address_line', 255);
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->boolean('pickup_enabled')->default(true);
            $table->integer('priority_order')->default(0);
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index(['status', 'priority_order'], 'idx_stores_status_priority');
        });

        // Gio mo cua theo tung ngay trong tuan cho moi cua hang.
        Schema::create('store_business_hours', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedTinyInteger('weekday');
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->unique(['store_id', 'weekday'], 'uk_store_business_hours');
            $table->index('store_id', 'idx_store_business_hours_store');
        });

        // Cau hinh he thong dang key-value (setting) cho toan website.
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 120)->unique();
            $table->text('setting_value')->nullable();
            $table->string('setting_group', 80)->default('general');
            $table->string('description', 255)->nullable();
            $table->string('updated_by', 120)->nullable();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('setting_group', 'idx_site_settings_group');
        });

        // Nhom link chan trang (footer) de quan tri noi dung tinh.
        Schema::create('footer_links', function (Blueprint $table) {
            $table->id();
            $table->string('group_name', 80);
            $table->string('title', 180);
            $table->string('url', 255);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['group_name', 'is_active', 'sort_order'], 'idx_footer_links_group_active');
        });

        // Cay danh muc san pham (co parent_id de tao cap cha/con).
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->string('icon_class', 80)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->string('description', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index('parent_id', 'idx_categories_parent');
            $table->index(['status', 'sort_order'], 'idx_categories_status_sort');
        });

        // Danh muc mau de dung chung cho san pham.
        Schema::create('product_colors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 120)->unique();
            $table->string('hex_code', 20)->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index(['status', 'sort_order'], 'idx_product_colors_status_sort');
        });

        // Danh muc kich thuoc de dung chung cho san pham.
        Schema::create('product_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40);
            $table->string('slug', 80)->unique();
            $table->integer('sort_order')->default(0);
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index(['status', 'sort_order'], 'idx_product_sizes_status_sort');
        });

        // Thong tin SP goc (noi dung, mo ta, gia, ton kho, danh gia tong).
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('name', 220);
            $table->string('slug', 220)->unique();
            $table->string('sku', 100)->nullable()->unique();
            $table->string('barcode', 120)->nullable()->unique();
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('stock_qty')->default(0);
            $table->integer('weight_gram')->nullable();
            $table->string('brand', 120)->nullable();
            $table->string('short_description', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('care_instructions')->nullable();
            $table->text('return_policy')->nullable();
            $table->json('specs_json')->nullable();
            $table->string('status', 30)->default('active');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('view_count')->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedInteger('sold_count')->default(0);
            $table->timestamps();

            $table->index('category_id', 'idx_products_category_id');
            $table->index(['status', 'is_featured'], 'idx_products_status_featured');
            $table->index('price', 'idx_products_price');
            $table->index('stock_qty', 'idx_products_stock_qty');
        });

        // Lien ket nhieu-nhieu giua san pham va mau sac.
        Schema::create('product_color_map', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('color_id');
            $table->timestamps();

            $table->unique(['product_id', 'color_id'], 'uk_product_color_map');
            $table->index('product_id', 'idx_product_color_map_product');
            $table->index('color_id', 'idx_product_color_map_color');

            $table->foreign('product_id', 'fk_product_color_map_product')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('color_id', 'fk_product_color_map_color')
                ->references('id')
                ->on('product_colors')
                ->cascadeOnDelete();
        });

        // Lien ket nhieu-nhieu giua san pham va kich thuoc.
        Schema::create('product_size_map', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('size_id');
            $table->timestamps();

            $table->unique(['product_id', 'size_id'], 'uk_product_size_map');
            $table->index('product_id', 'idx_product_size_map_product');
            $table->index('size_id', 'idx_product_size_map_size');

            $table->foreign('product_id', 'fk_product_size_map_product')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('size_id', 'fk_product_size_map_size')
                ->references('id')
                ->on('product_sizes')
                ->cascadeOnDelete();
        });

        // Ton kho theo bien the va theo cua hang/kho.
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->integer('on_hand_qty')->default(0);
            $table->integer('reserved_qty')->default(0);
            $table->integer('safety_stock')->default(0);
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['variant_id', 'store_id'], 'uk_inventories_variant_store');
            $table->index('store_id', 'idx_inventories_store');
            $table->index(['variant_id', 'on_hand_qty', 'reserved_qty'], 'idx_inventories_available');
        });

        // Hinh anh san pham/bien the, ho tro sap xep va anh dai dien.
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->string('image_url', 500);
            $table->string('alt_text', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['product_id', 'sort_order'], 'idx_product_images_product');
            $table->index('variant_id', 'idx_product_images_variant');
            $table->index(['product_id', 'is_primary'], 'idx_product_images_primary');
        });

        // Danh gia/nhan xet cua khach cho san pham.
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('customer_name', 150)->nullable();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('review_text')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamps();

            $table->index(['product_id', 'status'], 'idx_product_reviews_product');
            $table->index('variant_id', 'idx_product_reviews_variant');
            $table->index('user_id', 'idx_product_reviews_user');
        });

        // Gio hang cua user/guest, luu tong tien truoc va sau giam gia.
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('coupon_code', 80)->nullable();
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->string('status', 30)->default('active');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['user_id', 'status'], 'idx_carts_user_status');
            $table->index('coupon_code', 'idx_carts_coupon');
            $table->index('promotion_id', 'idx_carts_promotion');
            $table->index('created_at', 'idx_carts_created_at');
        });

        // Chi tiet tung dong san pham trong gio hang.
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id');
            $table->string('product_name_snapshot', 220);
            $table->string('variant_name_snapshot', 120)->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->boolean('selected')->default(true);
            $table->timestamps();

            $table->index('cart_id', 'idx_cart_items_cart');
            $table->index(['product_id', 'variant_id'], 'idx_cart_items_product_variant');
            $table->index(['cart_id', 'selected'], 'idx_cart_items_selected');
        });

        // Don hang chot cua khach: giao hang, thanh toan, khuyen mai, tong tien.
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 80)->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('customer_name', 150);
            $table->string('customer_phone', 30);
            $table->string('customer_email', 190)->nullable();
            $table->string('delivery_type', 20)->default('delivery');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('shipping_address_text', 500)->nullable();
            $table->string('payment_method', 30)->default('cod');
            $table->string('order_status', 30)->default('pending');
            $table->string('payment_status', 30)->default('unpaid');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->string('coupon_code', 80)->nullable();
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('note', 500)->nullable();
            $table->timestamps();

            $table->index('user_id', 'idx_orders_user_id');
            $table->index('store_id', 'idx_orders_store_id');
            $table->index('coupon_code', 'idx_orders_coupon');
            $table->index('promotion_id', 'idx_orders_promotion');
            $table->index(['order_status', 'payment_status'], 'idx_orders_status');
            $table->index('created_at', 'idx_orders_created_at');
        });

        // Dong san pham cua don hang, luu snapshot de tranh sai lech du lieu sau nay.
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id');
            $table->string('sku_snapshot', 100)->nullable();
            $table->string('product_name_snapshot', 220);
            $table->string('variant_name_snapshot', 120)->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->timestamps();

            $table->index('order_id', 'idx_order_items_order_id');
            $table->index(['product_id', 'variant_id'], 'idx_order_items_product_variant');
        });

        // Nhat ky chuyen trang thai don hang (audit trail).
        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->string('changed_by', 120)->nullable();
            $table->string('note', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['order_id', 'created_at'], 'idx_order_status_logs_order');
        });

        // Lich su thanh toan cua don hang (co the nhieu giao dich).
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('payment_method', 30);
            $table->string('transaction_code', 120)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status', 30)->default('pending');
            $table->dateTime('paid_at')->nullable();
            $table->json('raw_response_json')->nullable();
            $table->timestamps();

            $table->index('order_id', 'idx_order_payments_order');
            $table->index('status', 'idx_order_payments_status');
            $table->index('transaction_code', 'idx_order_payments_txn');
        });

        // Van don/giao nhan cua don hang: nha van chuyen, tracking, trang thai.
        Schema::create('order_shipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('carrier_name', 120)->nullable();
            $table->string('tracking_code', 120)->nullable();
            $table->string('shipping_status', 30)->default('ready');
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->dateTime('shipped_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index('order_id', 'idx_order_shipments_order');
            $table->index('shipping_status', 'idx_order_shipments_status');
            $table->index('tracking_code', 'idx_order_shipments_tracking');
        });

        // Chuong trinh khuyen mai: voucher/flash sale/banner discount.
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 220);
            $table->string('code', 80)->nullable()->unique();
            $table->string('promotion_type', 40);
            $table->string('channel', 40)->default('all');
            $table->string('discount_type', 30)->default('none');
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('min_order_value', 15, 2)->default(0);
            $table->decimal('max_discount_value', 15, 2)->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->string('status', 30)->default('active');
            $table->string('description', 500)->nullable();
            $table->timestamps();

            $table->index(['status', 'start_at', 'end_at'], 'idx_promotions_status_time');
            $table->index('channel', 'idx_promotions_channel');
        });

        // Pham vi ap dung khuyen mai (all/category/product/customer_tier...).
        Schema::create('promotion_scopes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promotion_id');
            $table->string('scope_type', 40);
            $table->unsignedBigInteger('scope_ref_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('promotion_id', 'idx_promotion_scopes_promotion');
            $table->index(['scope_type', 'scope_ref_id'], 'idx_promotion_scopes_scope');
        });

        // Log su dung coupon theo user/don hang.
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promotion_id');
            $table->string('coupon_code', 80);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->dateTime('used_at')->useCurrent();
            $table->string('status', 30)->default('used');

            $table->index('coupon_code', 'idx_coupon_usages_coupon');
            $table->index('order_id', 'idx_coupon_usages_order');
            $table->index('user_id', 'idx_coupon_usages_user');
            $table->index('promotion_id', 'idx_coupon_usages_promotion');
        });

        // Thanh thong bao/promo ticker hien thi tren frontend.
        Schema::create('promo_tickers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->string('name', 180);
            $table->text('content_text');
            $table->string('background_style', 255)->nullable();
            $table->string('text_color', 30)->nullable();
            $table->unsignedInteger('speed_seconds')->default(18);
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index('promotion_id', 'idx_promo_tickers_promotion');
            $table->index(['status', 'start_at', 'end_at'], 'idx_promo_tickers_status_time');
        });

        // Khoi noi dung tren trang chu (hero, featured, news...).
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_key', 80)->unique();
            $table->string('title', 180);
            $table->string('section_type', 40);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('config_json')->nullable();
            $table->string('updated_by', 120)->nullable();
            $table->timestamps();

            $table->index(['is_active', 'sort_order'], 'idx_home_sections_active_sort');
        });

        // Item ben trong moi home section (banner/san pham/bai viet...).
        Schema::create('home_section_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->string('item_type', 40);
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->string('title', 220)->nullable();
            $table->string('subtitle', 255)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->string('target_url', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->json('meta_json')->nullable();
            $table->timestamps();

            $table->index(['section_id', 'is_active', 'sort_order'], 'idx_home_section_items_section');
            $table->index(['item_type', 'ref_id'], 'idx_home_section_items_ref');
            $table->index(['start_at', 'end_at'], 'idx_home_section_items_time');
        });

        // Bai viet blog/tin tuc cho CMS va SEO.
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->string('thumbnail_url', 500)->nullable();
            $table->string('excerpt', 500)->nullable();
            $table->longText('content_html')->nullable();
            $table->boolean('is_published')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->string('author_name', 120)->nullable();
            $table->timestamps();

            $table->index(['is_published', 'published_at'], 'idx_blog_posts_publish');
        });

        // Trang noi dung tinh (chinh sach, gioi thieu, huong dan...).
        Schema::create('content_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_key', 100)->unique();
            $table->string('title', 220);
            $table->string('slug', 220)->unique();
            $table->longText('content_html')->nullable();
            $table->boolean('is_published')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->string('updated_by', 120)->nullable();
            $table->timestamps();

            $table->index(['is_published', 'published_at'], 'idx_content_pages_publish');
        });

        // Danh sach yeu thich cua nguoi dung.
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name', 120)->default('Yeu thich');
            $table->boolean('is_default')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_default'], 'idx_wishlists_user_default');
        });

        // San pham duoc luu trong danh sach yeu thich.
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wishlist_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->unique(['wishlist_id', 'product_id', 'variant_id'], 'uk_wishlist_item');
            $table->index('wishlist_id', 'idx_wishlist_items_wishlist');
            $table->index(['product_id', 'variant_id'], 'idx_wishlist_items_product_variant');
        });

        // Lich su bien dong ton kho (nhap/xuat/giu cho don), phuc vu doi soat.
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('movement_type', 30);
            $table->integer('qty_change');
            $table->integer('qty_before')->default(0);
            $table->integer('qty_after')->default(0);
            $table->string('reference_type', 40)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('note', 255)->nullable();
            $table->string('created_by', 120)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['variant_id', 'created_at'], 'idx_inventory_movements_variant');
            $table->index(['store_id', 'created_at'], 'idx_inventory_movements_store');
            $table->index(['reference_type', 'reference_id'], 'idx_inventory_movements_ref');
        });

        // Quy trinh hoan tra/hoan tien cho don hang.
        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('return_code', 80)->unique();
            $table->string('status', 30)->default('requested');
            $table->string('reason', 255)->nullable();
            $table->decimal('refund_amount', 15, 2)->default(0);
            $table->dateTime('requested_at')->useCurrent();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status'], 'idx_order_returns_order_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_returns');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('wishlist_items');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('content_pages');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('home_section_items');
        Schema::dropIfExists('home_sections');
        Schema::dropIfExists('promo_tickers');
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('promotion_scopes');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('order_shipments');
        Schema::dropIfExists('order_payments');
        Schema::dropIfExists('order_status_logs');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('product_reviews');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('product_size_map');
        Schema::dropIfExists('product_color_map');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_sizes');
        Schema::dropIfExists('product_colors');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('footer_links');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('store_business_hours');
        Schema::dropIfExists('stores');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customer_profiles');
    }
};
