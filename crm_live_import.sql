-- =========================================================================
-- CRM-ZAZU COMPLETE PRODUCTION DATABASE SCHEMA & SEED DATA
-- Target Database: u206223007_crmdb
-- Exported: 2026-09-28 12:22:28
-- =========================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+05:30";

-- ---------------------------------------------------------
-- Table structure for table `crm_alert_rules`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_alert_rules`;
CREATE TABLE `crm_alert_rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `geofence_zone_id` int(10) unsigned DEFAULT NULL,
  `event_type` enum('enter','exit','offline','speeding') NOT NULL,
  `notify_roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`notify_roles`)),
  `cooldown_minutes` int(10) unsigned NOT NULL DEFAULT 30,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_app_settings`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_app_settings`;
CREATE TABLE `crm_app_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  `description` text DEFAULT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_attendance`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_attendance`;
CREATE TABLE `crm_attendance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `punch_in_at` datetime DEFAULT NULL,
  `punch_out_at` datetime DEFAULT NULL,
  `punch_in_lat` decimal(10,8) DEFAULT NULL,
  `punch_in_lng` decimal(11,8) DEFAULT NULL,
  `punch_out_lat` decimal(10,8) DEFAULT NULL,
  `punch_out_lng` decimal(11,8) DEFAULT NULL,
  `punch_in_selfie` varchar(255) DEFAULT NULL,
  `punch_out_selfie` varchar(255) DEFAULT NULL,
  `punch_in_address` varchar(255) DEFAULT NULL,
  `punch_out_address` varchar(255) DEFAULT NULL,
  `attendance_status` enum('present','absent','half_day','on_leave','holiday','week_off') NOT NULL DEFAULT 'absent',
  `working_hours` decimal(4,2) NOT NULL DEFAULT 0.00,
  `overtime_hours` decimal(4,2) NOT NULL DEFAULT 0.00,
  `is_regularized` tinyint(1) NOT NULL DEFAULT 0,
  `regularized_by` int(10) unsigned DEFAULT NULL,
  `regularized_reason` text DEFAULT NULL,
  `face_verified` tinyint(1) NOT NULL DEFAULT 0,
  `face_confidence_score` decimal(4,3) DEFAULT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_date` (`user_id`,`date`),
  KEY `idx_att` (`user_id`,`date`,`is_deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_bulk_mail_campaigns`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_bulk_mail_campaigns`;
CREATE TABLE `crm_bulk_mail_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `recipient_type` varchar(50) NOT NULL,
  `product_id` int(10) unsigned DEFAULT NULL,
  `template_id` int(10) unsigned DEFAULT NULL,
  `total_recipients` int(11) DEFAULT 0,
  `status` enum('pending','processing','completed') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_bulk_mail_queue`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_bulk_mail_queue`;
CREATE TABLE `crm_bulk_mail_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) DEFAULT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `recipient_name` varchar(255) DEFAULT NULL,
  `sender_email` varchar(150) DEFAULT NULL,
  `smtp_account_id` int(10) unsigned DEFAULT NULL,
  `anti_spam_hash` varchar(50) DEFAULT NULL,
  `lead_id` int(10) unsigned DEFAULT NULL,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `status` enum('queued','sent','failed') DEFAULT 'queued',
  `error_message` text DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `next_followup_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `campaign_id` (`campaign_id`),
  CONSTRAINT `crm_bulk_mail_queue_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `crm_bulk_mail_campaigns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_ci_sessions`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_ci_sessions`;
CREATE TABLE `crm_ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL DEFAULT 0,
  `data` blob NOT NULL,
  KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_contact_book`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_contact_book`;
CREATE TABLE `crm_contact_book` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_contact_persons`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_contact_persons`;
CREATE TABLE `crm_contact_persons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cp_customer` (`customer_id`,`is_deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_customers`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_customers`;
CREATE TABLE `crm_customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_type` enum('primary','followup') NOT NULL DEFAULT 'primary',
  `customer_org_name` varchar(200) NOT NULL DEFAULT '',
  `customer_name` varchar(150) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `gst_number` varchar(20) DEFAULT NULL,
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `product_ids` text DEFAULT NULL,
  `package_split_ids` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cust_assigned` (`assigned_to`,`is_deleted`),
  KEY `idx_cust_status` (`status`,`is_deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_geo_alerts`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_geo_alerts`;
CREATE TABLE `crm_geo_alerts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `geofence_zone_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `alert_type` enum('enter','exit','speeding','offline') NOT NULL,
  `triggered_at` datetime NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `resolved_by` int(10) unsigned DEFAULT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_geofence_zones`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_geofence_zones`;
CREATE TABLE `crm_geofence_zones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `zone_type` enum('customer','office','restricted','territory') NOT NULL,
  `center_lat` decimal(10,8) DEFAULT NULL,
  `center_lng` decimal(11,8) DEFAULT NULL,
  `radius_meters` int(10) unsigned DEFAULT NULL,
  `polygon_coords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`polygon_coords`)),
  `customer_id` int(10) unsigned DEFAULT NULL,
  `auto_checkin` tinyint(1) NOT NULL DEFAULT 0,
  `alert_on_exit` tinyint(1) NOT NULL DEFAULT 0,
  `alert_on_enter` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_gps_tracks`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_gps_tracks`;
CREATE TABLE `crm_gps_tracks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `accuracy` decimal(6,2) DEFAULT NULL,
  `speed` decimal(6,2) DEFAULT NULL,
  `battery_level` tinyint(3) unsigned DEFAULT NULL,
  `recorded_at` datetime NOT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_gps_user_time` (`user_id`,`recorded_at`,`is_deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=697 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_holidays`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_holidays`;
CREATE TABLE `crm_holidays` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `holiday_type` enum('national','regional','optional') NOT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_lead_activities`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_lead_activities`;
CREATE TABLE `crm_lead_activities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `activity_type` enum('call','email','visit','note','status_change') NOT NULL,
  `notes` text DEFAULT NULL,
  `occurred_at` datetime NOT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_la_lead` (`lead_id`,`is_deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_leads`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_leads`;
CREATE TABLE `crm_leads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `source` enum('walk_in','call','referral','field','online') NOT NULL DEFAULT 'field',
  `lead_status` enum('new','contacted','qualified','proposal','negotiation','won','lost') NOT NULL DEFAULT 'new',
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `expected_value` bigint(20) unsigned DEFAULT NULL,
  `expected_close_date` date DEFAULT NULL,
  `lost_reason` text DEFAULT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `company_name` varchar(200) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `email_status` varchar(50) DEFAULT NULL,
  `secondary_email` varchar(150) DEFAULT NULL,
  `corporate_phone` varchar(50) DEFAULT NULL,
  `account_owner` varchar(150) DEFAULT NULL,
  `employees_count` varchar(50) DEFAULT NULL,
  `industry` varchar(150) DEFAULT NULL,
  `keywords` text DEFAULT NULL,
  `person_linkedin_url` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `company_linkedin_url` varchar(255) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `company_address` text DEFAULT NULL,
  `company_city` varchar(100) DEFAULT NULL,
  `company_state` varchar(100) DEFAULT NULL,
  `company_country` varchar(100) DEFAULT NULL,
  `company_phone` varchar(50) DEFAULT NULL,
  `technologies` text DEFAULT NULL,
  `annual_revenue` varchar(100) DEFAULT NULL,
  `email_sent` varchar(50) DEFAULT NULL,
  `email_open` varchar(50) DEFAULT NULL,
  `email_bounced` varchar(50) DEFAULT NULL,
  `product_demo` varchar(100) DEFAULT NULL,
  `quotation` varchar(100) DEFAULT NULL,
  `product_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_leads_stage` (`lead_status`,`status`,`is_deleted`),
  KEY `idx_leads_cust` (`customer_id`,`is_deleted`),
  KEY `idx_leads_assigned` (`assigned_to`,`is_deleted`),
  KEY `idx_leads_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_leave_balances`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_leave_balances`;
CREATE TABLE `crm_leave_balances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `leave_type_id` int(10) unsigned NOT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `total_days` int(10) unsigned NOT NULL,
  `used_days` int(10) unsigned NOT NULL DEFAULT 0,
  `pending_days` int(10) unsigned NOT NULL DEFAULT 0,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_type_year` (`user_id`,`leave_type_id`,`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_leave_requests`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_leave_requests`;
CREATE TABLE `crm_leave_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `leave_type_id` int(10) unsigned NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `days` int(10) unsigned NOT NULL,
  `reason` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `leave_status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `approved_by` int(10) unsigned DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_leave_types`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_leave_types`;
CREATE TABLE `crm_leave_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `days_allowed_per_year` int(10) unsigned NOT NULL,
  `carry_forward` tinyint(1) NOT NULL DEFAULT 0,
  `paid` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_notification_templates`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_notification_templates`;
CREATE TABLE `crm_notification_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `channel` varchar(20) NOT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'general',
  `product_id` int(10) unsigned DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variables`)),
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_notifications`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_notifications`;
CREATE TABLE `crm_notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `notif_type` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`,`is_read`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_order_items`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_order_items`;
CREATE TABLE `crm_order_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `qty` int(10) unsigned NOT NULL,
  `unit_price` bigint(20) unsigned NOT NULL,
  `discount_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `line_total` bigint(20) unsigned NOT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_oi_order` (`order_id`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_orders`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_orders`;
CREATE TABLE `crm_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(30) NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `lead_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `order_status` enum('draft','pending_approval','approved','dispatched','delivered','cancelled') NOT NULL DEFAULT 'draft',
  `approved_by` int(10) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `total_amount` bigint(20) unsigned NOT NULL DEFAULT 0,
  `discount_amount` bigint(20) unsigned NOT NULL DEFAULT 0,
  `final_amount` bigint(20) unsigned NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `idx_orders_status` (`order_status`,`status`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_package_tiers`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_package_tiers`;
CREATE TABLE `crm_package_tiers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `badge_color` varchar(50) NOT NULL DEFAULT 'gray',
  `icon` varchar(50) NOT NULL DEFAULT 'fa-cube',
  `description` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_product_addons`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_product_addons`;
CREATE TABLE `crm_product_addons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `monthly_price` bigint(20) NOT NULL DEFAULT 0,
  `icon` varchar(50) NOT NULL DEFAULT 'fa-cube',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `fk_p_addons_prod` FOREIGN KEY (`product_id`) REFERENCES `crm_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_product_assets`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_product_assets`;
CREATE TABLE `crm_product_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `asset_type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_link` text NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_product_categories`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_product_categories`;
CREATE TABLE `crm_product_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_product_core_modules`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_product_core_modules`;
CREATE TABLE `crm_product_core_modules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `icon` varchar(50) NOT NULL DEFAULT 'fa-check-circle',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `fk_p_cmod_prod` FOREIGN KEY (`product_id`) REFERENCES `crm_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_product_package_splits`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_product_package_splits`;
CREATE TABLE `crm_product_package_splits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `package_tier_id` int(10) unsigned NOT NULL,
  `tier_subtitle` varchar(150) DEFAULT NULL,
  `price` bigint(20) NOT NULL DEFAULT 0,
  `monthly_price` bigint(20) NOT NULL DEFAULT 0,
  `yearly_price` bigint(20) NOT NULL DEFAULT 0,
  `discount_label` varchar(50) DEFAULT 'Save 17%',
  `min_price` bigint(20) NOT NULL DEFAULT 0,
  `billing_cycle` enum('one_time','annual','monthly','quarterly') NOT NULL DEFAULT 'annual',
  `amc_percentage` decimal(5,2) NOT NULL DEFAULT 18.00,
  `implementation_fee` bigint(20) NOT NULL DEFAULT 0,
  `user_limit` varchar(50) NOT NULL DEFAULT '5 Users',
  `storage_limit` varchar(50) NOT NULL DEFAULT '10 GB',
  `support_type` varchar(100) DEFAULT 'Standard Support',
  `reports_type` varchar(100) DEFAULT 'Basic Reports',
  `features_included` text DEFAULT NULL,
  `core_modules_included` text DEFAULT NULL,
  `is_popular` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `package_tier_id` (`package_tier_id`),
  CONSTRAINT `fk_pps_prod` FOREIGN KEY (`product_id`) REFERENCES `crm_products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pps_tier` FOREIGN KEY (`package_tier_id`) REFERENCES `crm_package_tiers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_products`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_products`;
CREATE TABLE `crm_products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `sku` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(50) NOT NULL DEFAULT 'pcs',
  `price` bigint(20) unsigned NOT NULL DEFAULT 0,
  `min_price` bigint(20) unsigned NOT NULL DEFAULT 0,
  `stock` int(10) unsigned NOT NULL DEFAULT 0,
  `category_id` int(10) unsigned DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `theme_color` varchar(50) NOT NULL DEFAULT 'blue',
  `website_url` varchar(255) DEFAULT NULL,
  `why_points` text DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `idx_prod_cat` (`category_id`,`is_deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_role_permissions`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_role_permissions`;
CREATE TABLE `crm_role_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role` varchar(100) NOT NULL,
  `module` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_shift_assignments`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_shift_assignments`;
CREATE TABLE `crm_shift_assignments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `shift_id` int(10) unsigned NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sa_user` (`user_id`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_shifts`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_shifts`;
CREATE TABLE `crm_shifts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `grace_minutes` int(10) unsigned NOT NULL DEFAULT 15,
  `half_day_hours` decimal(4,2) NOT NULL DEFAULT 4.00,
  `full_day_hours` decimal(4,2) NOT NULL DEFAULT 8.00,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_smtp_accounts`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_smtp_accounts`;
CREATE TABLE `crm_smtp_accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `sender_email` varchar(150) NOT NULL,
  `sender_name` varchar(150) NOT NULL,
  `smtp_host` varchar(150) NOT NULL DEFAULT 'smtp.hostinger.com',
  `smtp_port` int(11) NOT NULL DEFAULT 465,
  `smtp_crypto` enum('ssl','tls','none') NOT NULL DEFAULT 'ssl',
  `smtp_user` varchar(150) NOT NULL,
  `smtp_pass` varchar(255) NOT NULL,
  `daily_limit` int(11) NOT NULL DEFAULT 100,
  `sent_today` int(11) NOT NULL DEFAULT 0,
  `last_reset_date` date NOT NULL,
  `status` enum('active','limit_reached','disabled','error') NOT NULL DEFAULT 'active',
  `last_error` text DEFAULT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_smtp_status` (`status`,`is_deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_teams`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_teams`;
CREATE TABLE `crm_teams` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `manager_id` int(10) unsigned DEFAULT NULL,
  `territory` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_territories`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_territories`;
CREATE TABLE `crm_territories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `geojson_boundary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`geojson_boundary`)),
  `manager_id` int(10) unsigned DEFAULT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_user`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_user`;
CREATE TABLE `crm_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','field_staff') NOT NULL DEFAULT 'field_staff',
  `phone` varchar(20) DEFAULT NULL,
  `team_id` int(10) unsigned DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `fcm_token` varchar(255) DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_role` (`role`,`status`,`is_deleted`),
  KEY `idx_users_team` (`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_user_permissions`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_user_permissions`;
CREATE TABLE `crm_user_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `permission` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_perm` (`user_id`,`permission`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_visit_logs`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_visit_logs`;
CREATE TABLE `crm_visit_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `visit_plan_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `check_in_at` datetime DEFAULT NULL,
  `check_out_at` datetime DEFAULT NULL,
  `check_in_lat` decimal(10,8) DEFAULT NULL,
  `check_in_lng` decimal(11,8) DEFAULT NULL,
  `check_out_lat` decimal(10,8) DEFAULT NULL,
  `check_out_lng` decimal(11,8) DEFAULT NULL,
  `check_in_address` varchar(255) DEFAULT NULL,
  `check_out_address` varchar(255) DEFAULT NULL,
  `selfie_photo` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `visit_outcome` varchar(100) DEFAULT NULL,
  `related_follow_ups` varchar(255) DEFAULT NULL,
  `distance_from_customer` int(10) unsigned DEFAULT NULL,
  `is_auto_checkin` tinyint(1) NOT NULL DEFAULT 0,
  `geofence_zone_id` int(10) unsigned DEFAULT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vl_user` (`user_id`,`is_deleted`),
  KEY `idx_vl_cust` (`customer_id`,`is_deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------
-- Table structure for table `crm_visit_plans`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `crm_visit_plans`;
CREATE TABLE `crm_visit_plans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `planned_date` date NOT NULL,
  `planned_time` time DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `visit_status` enum('planned','completed','missed','rescheduled') NOT NULL DEFAULT 'planned',
  `lead_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vp_user_date` (`user_id`,`planned_date`,`is_deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================================================================
-- SYSTEM SEED DATA & ESSENTIALS
-- =========================================================================

-- 1. Default Role Permissions
INSERT INTO `crm_role_permissions` (`role`, `module`) VALUES
('admin', '["dashboard","customers","leads","orders","visits","tracking\\/live","geofence","attendance","shifts","leave","selfie\\/log","reports","admin","communications"]'),
('manager', '["dashboard","customers","leads","orders","visits","tracking\\/live","geofence","attendance","shifts","leave","selfie\\/log","reports","admin","communications"]'),
('field_staff', '["dashboard","customers","leads","orders","visits","attendance","leave"]')
ON DUPLICATE KEY UPDATE `module`=VALUES(`module`);

-- 2. Default Package Tiers
INSERT INTO `crm_package_tiers` (`id`, `name`, `slug`, `badge_color`, `icon`, `description`, `sort_order`, `status`) VALUES
(1, 'Bronze', 'bronze', 'amber', 'fa-shield', 'Essential entry plan for small operations', 1, 'active'),
(2, 'Silver', 'silver', 'slate', 'fa-star-half-o', 'Growing teams requiring expanded feature set', 2, 'active'),
(3, 'Gold', 'gold', 'yellow', 'fa-star', 'High performance tier for established businesses', 3, 'active'),
(4, 'Platinum', 'platinum', 'purple', 'fa-diamond', 'Full automation and multi-branch management', 4, 'active'),
(5, 'Enterprise', 'enterprise', 'blue', 'fa-building', 'Unlimited scalability, dedicated support & customization', 5, 'active')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

SET FOREIGN_KEY_CHECKS = 1;
