-- =========================================================
-- Schema: Ecommerce demo (simple)
-- Note: KHONG tao FOREIGN KEY theo yeu cau
-- MySQL 8+
-- =========================================================

CREATE DATABASE IF NOT EXISTS yody_demo
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE yody_demo;

-- 1) Users
CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(30) NULL,
  avatar_url VARCHAR(500) NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(30) NOT NULL DEFAULT 'customer',
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_users_email (email),
  UNIQUE KEY uk_users_phone (phone),
  KEY idx_users_role_status (role, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_profiles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  gender VARCHAR(20) NULL,
  birthday DATE NULL,
  tier VARCHAR(20) NOT NULL DEFAULT 'new',
  total_spent DECIMAL(15,2) NOT NULL DEFAULT 0,
  total_orders INT UNSIGNED NOT NULL DEFAULT 0,
  marketing_opt_in TINYINT(1) NOT NULL DEFAULT 0,
  note VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_customer_profiles_user_id (user_id),
  KEY idx_customer_profiles_tier (tier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_addresses (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  recipient_name VARCHAR(150) NOT NULL,
  recipient_phone VARCHAR(30) NOT NULL,
  province VARCHAR(120) NOT NULL,
  district VARCHAR(120) NOT NULL,
  ward VARCHAR(120) NULL,
  address_line VARCHAR(255) NOT NULL,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_customer_addresses_user_id (user_id),
  KEY idx_customer_addresses_default (user_id, is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Store + system content
CREATE TABLE IF NOT EXISTS stores (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) NOT NULL,
  name VARCHAR(180) NOT NULL,
  phone VARCHAR(30) NULL,
  email VARCHAR(190) NULL,
  province VARCHAR(120) NOT NULL,
  district VARCHAR(120) NOT NULL,
  ward VARCHAR(120) NULL,
  address_line VARCHAR(255) NOT NULL,
  open_time TIME NULL,
  close_time TIME NULL,
  pickup_enabled TINYINT(1) NOT NULL DEFAULT 1,
  priority_order INT NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_stores_code (code),
  KEY idx_stores_status_priority (status, priority_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS store_business_hours (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  store_id BIGINT UNSIGNED NOT NULL,
  weekday TINYINT UNSIGNED NOT NULL COMMENT '0=Sunday, 6=Saturday',
  open_time TIME NULL,
  close_time TIME NULL,
  is_closed TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_store_business_hours (store_id, weekday),
  KEY idx_store_business_hours_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(120) NOT NULL,
  setting_value TEXT NULL,
  setting_group VARCHAR(80) NOT NULL DEFAULT 'general',
  description VARCHAR(255) NULL,
  updated_by VARCHAR(120) NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_site_settings_key (setting_key),
  KEY idx_site_settings_group (setting_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS footer_links (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  group_name VARCHAR(80) NOT NULL,
  title VARCHAR(180) NOT NULL,
  url VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_footer_links_group_active (group_name, is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Catalog
CREATE TABLE IF NOT EXISTS categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parent_id BIGINT UNSIGNED NULL,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(180) NOT NULL,
  icon_class VARCHAR(80) NULL,
  image_url VARCHAR(500) NULL,
  description VARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_categories_slug (slug),
  KEY idx_categories_parent (parent_id),
  KEY idx_categories_status_sort (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id BIGINT UNSIGNED NULL,
  name VARCHAR(220) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  brand VARCHAR(120) NULL,
  description TEXT NULL,
  care_instructions TEXT NULL,
  return_policy TEXT NULL,
  specs_json JSON NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  view_count INT UNSIGNED NOT NULL DEFAULT 0,
  rating_avg DECIMAL(3,2) NOT NULL DEFAULT 0,
  rating_count INT UNSIGNED NOT NULL DEFAULT 0,
  sold_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_products_slug (slug),
  KEY idx_products_category_id (category_id),
  KEY idx_products_status_featured (status, is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_variants (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT UNSIGNED NOT NULL,
  sku VARCHAR(100) NOT NULL,
  color_name VARCHAR(80) NULL,
  size_name VARCHAR(40) NULL,
  barcode VARCHAR(120) NULL,
  price DECIMAL(15,2) NOT NULL DEFAULT 0,
  sale_price DECIMAL(15,2) NULL,
  stock_qty INT NOT NULL DEFAULT 0,
  weight_gram INT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_product_variants_sku (sku),
  KEY idx_product_variants_product_id (product_id),
  KEY idx_product_variants_status (status),
  KEY idx_product_variants_price (price, sale_price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  variant_id BIGINT UNSIGNED NOT NULL,
  store_id BIGINT UNSIGNED NULL COMMENT 'NULL means central warehouse',
  on_hand_qty INT NOT NULL DEFAULT 0,
  reserved_qty INT NOT NULL DEFAULT 0,
  safety_stock INT NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_inventories_variant_store (variant_id, store_id),
  KEY idx_inventories_store (store_id),
  KEY idx_inventories_available (variant_id, on_hand_qty, reserved_qty)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_images (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT UNSIGNED NOT NULL,
  variant_id BIGINT UNSIGNED NULL,
  image_url VARCHAR(500) NOT NULL,
  alt_text VARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_product_images_product (product_id, sort_order),
  KEY idx_product_images_variant (variant_id),
  KEY idx_product_images_primary (product_id, is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_reviews (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT UNSIGNED NOT NULL,
  variant_id BIGINT UNSIGNED NULL,
  user_id BIGINT UNSIGNED NULL,
  customer_name VARCHAR(150) NULL,
  rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
  review_text TEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_product_reviews_product (product_id, status),
  KEY idx_product_reviews_variant (variant_id),
  KEY idx_product_reviews_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Cart + Order
CREATE TABLE IF NOT EXISTS carts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  coupon_code VARCHAR(80) NULL,
  promotion_id BIGINT UNSIGNED NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
  discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
  shipping_fee DECIMAL(15,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_carts_user_status (user_id, status),
  KEY idx_carts_coupon (coupon_code),
  KEY idx_carts_promotion (promotion_id),
  KEY idx_carts_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cart_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cart_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  variant_id BIGINT UNSIGNED NULL,
  product_name_snapshot VARCHAR(220) NOT NULL,
  variant_name_snapshot VARCHAR(120) NULL,
  unit_price DECIMAL(15,2) NOT NULL DEFAULT 0,
  qty INT UNSIGNED NOT NULL DEFAULT 1,
  line_total DECIMAL(15,2) NOT NULL DEFAULT 0,
  selected TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_cart_items_cart (cart_id),
  KEY idx_cart_items_product_variant (product_id, variant_id),
  KEY idx_cart_items_selected (cart_id, selected)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_code VARCHAR(80) NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  customer_name VARCHAR(150) NOT NULL,
  customer_phone VARCHAR(30) NOT NULL,
  customer_email VARCHAR(190) NULL,
  delivery_type VARCHAR(20) NOT NULL DEFAULT 'delivery',
  store_id BIGINT UNSIGNED NULL,
  shipping_address_text VARCHAR(500) NULL,
  payment_method VARCHAR(30) NOT NULL DEFAULT 'cod',
  order_status VARCHAR(30) NOT NULL DEFAULT 'pending',
  payment_status VARCHAR(30) NOT NULL DEFAULT 'unpaid',
  subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
  discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
  promotion_id BIGINT UNSIGNED NULL,
  coupon_code VARCHAR(80) NULL,
  shipping_fee DECIMAL(15,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
  note VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_orders_order_code (order_code),
  KEY idx_orders_user_id (user_id),
  KEY idx_orders_store_id (store_id),
  KEY idx_orders_coupon (coupon_code),
  KEY idx_orders_promotion (promotion_id),
  KEY idx_orders_status (order_status, payment_status),
  KEY idx_orders_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NULL,
  variant_id BIGINT UNSIGNED NULL,
  sku_snapshot VARCHAR(100) NULL,
  product_name_snapshot VARCHAR(220) NOT NULL,
  variant_name_snapshot VARCHAR(120) NULL,
  unit_price DECIMAL(15,2) NOT NULL DEFAULT 0,
  qty INT UNSIGNED NOT NULL DEFAULT 1,
  discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
  line_total DECIMAL(15,2) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_order_items_order_id (order_id),
  KEY idx_order_items_product_variant (product_id, variant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_status_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  from_status VARCHAR(30) NULL,
  to_status VARCHAR(30) NOT NULL,
  changed_by VARCHAR(120) NULL,
  note VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_order_status_logs_order (order_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_payments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  payment_method VARCHAR(30) NOT NULL,
  transaction_code VARCHAR(120) NULL,
  amount DECIMAL(15,2) NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  paid_at DATETIME NULL,
  raw_response_json JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_order_payments_order (order_id),
  KEY idx_order_payments_status (status),
  KEY idx_order_payments_txn (transaction_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_shipments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  carrier_name VARCHAR(120) NULL,
  tracking_code VARCHAR(120) NULL,
  shipping_status VARCHAR(30) NOT NULL DEFAULT 'ready',
  shipping_fee DECIMAL(15,2) NOT NULL DEFAULT 0,
  shipped_at DATETIME NULL,
  delivered_at DATETIME NULL,
  note VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_order_shipments_order (order_id),
  KEY idx_order_shipments_status (shipping_status),
  KEY idx_order_shipments_tracking (tracking_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) Promotions
CREATE TABLE IF NOT EXISTS promotions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(220) NOT NULL,
  code VARCHAR(80) NULL,
  promotion_type VARCHAR(40) NOT NULL,
  channel VARCHAR(40) NOT NULL DEFAULT 'all',
  discount_type VARCHAR(30) NOT NULL DEFAULT 'none',
  discount_value DECIMAL(15,2) NOT NULL DEFAULT 0,
  min_order_value DECIMAL(15,2) NOT NULL DEFAULT 0,
  max_discount_value DECIMAL(15,2) NULL,
  start_at DATETIME NULL,
  end_at DATETIME NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  description VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_promotions_code (code),
  KEY idx_promotions_status_time (status, start_at, end_at),
  KEY idx_promotions_channel (channel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS promotion_scopes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  promotion_id BIGINT UNSIGNED NOT NULL,
  scope_type VARCHAR(40) NOT NULL,
  scope_ref_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_promotion_scopes_promotion (promotion_id),
  KEY idx_promotion_scopes_scope (scope_type, scope_ref_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coupon_usages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  promotion_id BIGINT UNSIGNED NOT NULL,
  coupon_code VARCHAR(80) NOT NULL,
  order_id BIGINT UNSIGNED NULL,
  user_id BIGINT UNSIGNED NULL,
  used_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status VARCHAR(30) NOT NULL DEFAULT 'used',
  KEY idx_coupon_usages_coupon (coupon_code),
  KEY idx_coupon_usages_order (order_id),
  KEY idx_coupon_usages_user (user_id),
  KEY idx_coupon_usages_promotion (promotion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS promo_tickers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  promotion_id BIGINT UNSIGNED NULL,
  name VARCHAR(180) NOT NULL,
  content_text TEXT NOT NULL,
  background_style VARCHAR(255) NULL,
  text_color VARCHAR(30) NULL,
  speed_seconds INT UNSIGNED NOT NULL DEFAULT 18,
  start_at DATETIME NULL,
  end_at DATETIME NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_promo_tickers_promotion (promotion_id),
  KEY idx_promo_tickers_status_time (status, start_at, end_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6) Homepage CMS
CREATE TABLE IF NOT EXISTS home_sections (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  section_key VARCHAR(80) NOT NULL,
  title VARCHAR(180) NOT NULL,
  section_type VARCHAR(40) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  config_json JSON NULL,
  updated_by VARCHAR(120) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_home_sections_key (section_key),
  KEY idx_home_sections_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS home_section_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  section_id BIGINT UNSIGNED NOT NULL,
  item_type VARCHAR(40) NOT NULL,
  ref_id BIGINT UNSIGNED NULL,
  title VARCHAR(220) NULL,
  subtitle VARCHAR(255) NULL,
  image_url VARCHAR(500) NULL,
  target_url VARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  start_at DATETIME NULL,
  end_at DATETIME NULL,
  meta_json JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_home_section_items_section (section_id, is_active, sort_order),
  KEY idx_home_section_items_ref (item_type, ref_id),
  KEY idx_home_section_items_time (start_at, end_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_posts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL,
  thumbnail_url VARCHAR(500) NULL,
  excerpt VARCHAR(500) NULL,
  content_html LONGTEXT NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  published_at DATETIME NULL,
  author_name VARCHAR(120) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_blog_posts_slug (slug),
  KEY idx_blog_posts_publish (is_published, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS content_pages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_key VARCHAR(100) NOT NULL,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  content_html LONGTEXT NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  published_at DATETIME NULL,
  updated_by VARCHAR(120) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_content_pages_key (page_key),
  UNIQUE KEY uk_content_pages_slug (slug),
  KEY idx_content_pages_publish (is_published, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
