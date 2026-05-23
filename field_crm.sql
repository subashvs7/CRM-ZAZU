-- Field Staff CRM Database
-- Run: mysql -u root -p field_crm < field_crm.sql

CREATE DATABASE IF NOT EXISTS `field_crm` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `field_crm`;

-- CI3 Sessions
CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
  `data` blob NOT NULL,
  KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Teams
CREATE TABLE IF NOT EXISTS `teams` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `manager_id` INT UNSIGNED NULL,
  `territory` VARCHAR(200) NULL,
  `description` TEXT NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','manager','field_staff') NOT NULL DEFAULT 'field_staff',
  `phone` VARCHAR(20) NULL,
  `team_id` INT UNSIGNED NULL,
  `profile_photo` VARCHAR(255) NULL,
  `fcm_token` VARCHAR(255) NULL,
  `last_login_at` DATETIME NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  KEY `idx_users_role` (`role`,`status`,`is_deleted`),
  KEY `idx_users_team` (`team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User permissions
CREATE TABLE IF NOT EXISTS `user_permissions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `permission` VARCHAR(100) NOT NULL,
  UNIQUE KEY `user_perm` (`user_id`,`permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Territories
CREATE TABLE IF NOT EXISTS `territories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `geojson_boundary` JSON NULL,
  `manager_id` INT UNSIGNED NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Shifts
CREATE TABLE IF NOT EXISTS `shifts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `grace_minutes` INT UNSIGNED NOT NULL DEFAULT 15,
  `half_day_hours` DECIMAL(4,2) NOT NULL DEFAULT 4.00,
  `full_day_hours` DECIMAL(4,2) NOT NULL DEFAULT 8.00,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Shift Assignments
CREATE TABLE IF NOT EXISTS `shift_assignments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `shift_id` INT UNSIGNED NOT NULL,
  `effective_from` DATE NOT NULL,
  `effective_to` DATE NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  KEY `idx_sa_user` (`user_id`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Customers
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(150) NULL,
  `address` TEXT NULL,
  `city` VARCHAR(100) NULL,
  `state` VARCHAR(100) NULL,
  `pincode` VARCHAR(10) NULL,
  `gst_number` VARCHAR(20) NULL,
  `assigned_to` INT UNSIGNED NULL,
  `notes` TEXT NULL,
  `latitude` DECIMAL(10,8) NULL,
  `longitude` DECIMAL(11,8) NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  KEY `idx_cust_assigned` (`assigned_to`,`is_deleted`),
  KEY `idx_cust_status` (`status`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contact Persons
CREATE TABLE IF NOT EXISTS `contact_persons` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `designation` VARCHAR(100) NULL,
  `phone` VARCHAR(20) NULL,
  `email` VARCHAR(150) NULL,
  `is_primary` TINYINT(1) DEFAULT 0,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  KEY `idx_cp_customer` (`customer_id`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product Categories
CREATE TABLE IF NOT EXISTS `product_categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `parent_id` INT UNSIGNED NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products (prices in paise)
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(200) NOT NULL,
  `sku` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `unit` VARCHAR(50) NOT NULL DEFAULT 'pcs',
  `price` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `min_price` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `stock` INT UNSIGNED NOT NULL DEFAULT 0,
  `category_id` INT UNSIGNED NULL,
  `image` VARCHAR(255) NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  KEY `idx_prod_cat` (`category_id`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Leads
CREATE TABLE IF NOT EXISTS `leads` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NULL,
  `source` ENUM('walk_in','call','referral','field','online') NOT NULL DEFAULT 'field',
  `lead_status` ENUM('new','contacted','qualified','proposal','negotiation','won','lost') NOT NULL DEFAULT 'new',
  `assigned_to` INT UNSIGNED NULL,
  `expected_value` BIGINT UNSIGNED NULL,
  `expected_close_date` DATE NULL,
  `lost_reason` TEXT NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  KEY `idx_leads_stage` (`lead_status`,`status`,`is_deleted`),
  KEY `idx_leads_cust` (`customer_id`,`is_deleted`),
  KEY `idx_leads_assigned` (`assigned_to`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Lead Activities
CREATE TABLE IF NOT EXISTS `lead_activities` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `lead_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `activity_type` ENUM('call','email','visit','note','status_change') NOT NULL,
  `notes` TEXT NULL,
  `occurred_at` DATETIME NOT NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  KEY `idx_la_lead` (`lead_id`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Geofence Zones
CREATE TABLE IF NOT EXISTS `geofence_zones` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `zone_type` ENUM('customer','office','restricted','territory') NOT NULL,
  `center_lat` DECIMAL(10,8) NULL,
  `center_lng` DECIMAL(11,8) NULL,
  `radius_meters` INT UNSIGNED NULL,
  `polygon_coords` JSON NULL,
  `customer_id` INT UNSIGNED NULL,
  `auto_checkin` TINYINT(1) NOT NULL DEFAULT 0,
  `alert_on_exit` TINYINT(1) NOT NULL DEFAULT 0,
  `alert_on_enter` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Visit Plans
CREATE TABLE IF NOT EXISTS `visit_plans` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `planned_date` DATE NOT NULL,
  `planned_time` TIME NULL,
  `purpose` TEXT NULL,
  `visit_status` ENUM('planned','completed','missed','rescheduled') NOT NULL DEFAULT 'planned',
  `lead_id` INT UNSIGNED NULL,
  `created_by` INT UNSIGNED NOT NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  KEY `idx_vp_user_date` (`user_id`,`planned_date`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Visit Logs
CREATE TABLE IF NOT EXISTS `visit_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `visit_plan_id` INT UNSIGNED NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `check_in_at` DATETIME NULL,
  `check_out_at` DATETIME NULL,
  `check_in_lat` DECIMAL(10,8) NULL,
  `check_in_lng` DECIMAL(11,8) NULL,
  `check_out_lat` DECIMAL(10,8) NULL,
  `check_out_lng` DECIMAL(11,8) NULL,
  `check_in_address` VARCHAR(255) NULL,
  `check_out_address` VARCHAR(255) NULL,
  `selfie_photo` VARCHAR(255) NULL,
  `notes` TEXT NULL,
  `distance_from_customer` INT UNSIGNED NULL,
  `is_auto_checkin` TINYINT(1) NOT NULL DEFAULT 0,
  `geofence_zone_id` INT UNSIGNED NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  KEY `idx_vl_user` (`user_id`,`is_deleted`),
  KEY `idx_vl_cust` (`customer_id`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders (amounts in paise)
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(30) NOT NULL UNIQUE,
  `customer_id` INT UNSIGNED NOT NULL,
  `lead_id` INT UNSIGNED NULL,
  `created_by` INT UNSIGNED NOT NULL,
  `order_status` ENUM('draft','pending_approval','approved','dispatched','delivered','cancelled') NOT NULL DEFAULT 'draft',
  `approved_by` INT UNSIGNED NULL,
  `approved_at` DATETIME NULL,
  `total_amount` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `discount_amount` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `final_amount` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `notes` TEXT NULL,
  `delivery_date` DATE NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  KEY `idx_orders_status` (`order_status`,`status`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order Items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `qty` INT UNSIGNED NOT NULL,
  `unit_price` BIGINT UNSIGNED NOT NULL,
  `discount_pct` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `line_total` BIGINT UNSIGNED NOT NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  KEY `idx_oi_order` (`order_id`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- GPS Tracks
CREATE TABLE IF NOT EXISTS `gps_tracks` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `latitude` DECIMAL(10,8) NOT NULL,
  `longitude` DECIMAL(11,8) NOT NULL,
  `accuracy` DECIMAL(6,2) NULL,
  `speed` DECIMAL(6,2) NULL,
  `battery_level` TINYINT UNSIGNED NULL,
  `recorded_at` DATETIME NOT NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  KEY `idx_gps_user_time` (`user_id`,`recorded_at`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Geo Alerts
CREATE TABLE IF NOT EXISTS `geo_alerts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `geofence_zone_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `alert_type` ENUM('enter','exit','speeding','offline') NOT NULL,
  `triggered_at` DATETIME NOT NULL,
  `latitude` DECIMAL(10,8) NOT NULL,
  `longitude` DECIMAL(11,8) NOT NULL,
  `resolved_at` DATETIME NULL,
  `resolved_by` INT UNSIGNED NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Alert Rules
CREATE TABLE IF NOT EXISTS `alert_rules` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `geofence_zone_id` INT UNSIGNED NULL,
  `event_type` ENUM('enter','exit','offline','speeding') NOT NULL,
  `notify_roles` JSON NOT NULL,
  `cooldown_minutes` INT UNSIGNED NOT NULL DEFAULT 30,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Attendance
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `date` DATE NOT NULL,
  `punch_in_at` DATETIME NULL,
  `punch_out_at` DATETIME NULL,
  `punch_in_lat` DECIMAL(10,8) NULL,
  `punch_in_lng` DECIMAL(11,8) NULL,
  `punch_out_lat` DECIMAL(10,8) NULL,
  `punch_out_lng` DECIMAL(11,8) NULL,
  `punch_in_selfie` VARCHAR(255) NULL,
  `punch_out_selfie` VARCHAR(255) NULL,
  `punch_in_address` VARCHAR(255) NULL,
  `punch_out_address` VARCHAR(255) NULL,
  `attendance_status` ENUM('present','absent','half_day','on_leave','holiday','week_off') NOT NULL DEFAULT 'absent',
  `working_hours` DECIMAL(4,2) NOT NULL DEFAULT 0.00,
  `overtime_hours` DECIMAL(4,2) NOT NULL DEFAULT 0.00,
  `is_regularized` TINYINT(1) NOT NULL DEFAULT 0,
  `regularized_by` INT UNSIGNED NULL,
  `regularized_reason` TEXT NULL,
  `face_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `face_confidence_score` DECIMAL(4,3) NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  UNIQUE KEY `user_date` (`user_id`,`date`),
  KEY `idx_att` (`user_id`,`date`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Leave Types
CREATE TABLE IF NOT EXISTS `leave_types` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `days_allowed_per_year` INT UNSIGNED NOT NULL,
  `carry_forward` TINYINT(1) NOT NULL DEFAULT 0,
  `paid` TINYINT(1) NOT NULL DEFAULT 1,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Leave Balances
CREATE TABLE IF NOT EXISTS `leave_balances` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `leave_type_id` INT UNSIGNED NOT NULL,
  `year` SMALLINT UNSIGNED NOT NULL,
  `total_days` INT UNSIGNED NOT NULL,
  `used_days` INT UNSIGNED NOT NULL DEFAULT 0,
  `pending_days` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  UNIQUE KEY `user_type_year` (`user_id`,`leave_type_id`,`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Leave Requests
CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `leave_type_id` INT UNSIGNED NOT NULL,
  `from_date` DATE NOT NULL,
  `to_date` DATE NOT NULL,
  `days` INT UNSIGNED NOT NULL,
  `reason` TEXT NOT NULL,
  `attachment` VARCHAR(255) NULL,
  `leave_status` ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `approved_by` INT UNSIGNED NULL,
  `approval_notes` TEXT NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Holidays
CREATE TABLE IF NOT EXISTS `holidays` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `date` DATE NOT NULL,
  `holiday_type` ENUM('national','regional','optional') NOT NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `notif_type` VARCHAR(100) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `data` JSON NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `read_at` DATETIME NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  KEY `idx_notif_user` (`user_id`,`is_read`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notification Templates
CREATE TABLE IF NOT EXISTS `notification_templates` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `channel` VARCHAR(20) NOT NULL,
  `subject` VARCHAR(255) NULL,
  `body` TEXT NOT NULL,
  `variables` JSON NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- App Settings
CREATE TABLE IF NOT EXISTS `app_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
  `description` TEXT NULL,
  `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `app_settings` (`setting_key`,`setting_value`,`setting_group`,`description`,`created_at`,`updated_at`) VALUES
('gps_ping_interval','30','tracking','GPS ping interval seconds',NOW(),NOW()),
('face_match_threshold','0.75','attendance','Face confidence 0-1',NOW(),NOW()),
('attendance_window_hours','2','attendance','Hours before/after shift for punch',NOW(),NOW()),
('company_name','Field CRM Co.','general','Company name',NOW(),NOW()),
('company_logo','','general','Company logo path',NOW(),NOW()),
('timezone','Asia/Kolkata','general','Application timezone',NOW(),NOW()),
('currency','INR','general','Currency code',NOW(),NOW()),
('order_prefix','ORD','orders','Order number prefix',NOW(),NOW())
ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`);
