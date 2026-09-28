<?php
define('ENVIRONMENT', 'development');
define('BASEPATH', 'crm');
require_once __DIR__ . '/../application/config/database.php';
$cfg = $db['default'];
$conn = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
$conn->set_charset('utf8mb4');

$res = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $res->fetch_row()) {
    $tables[] = $row[0];
}

$out = "-- =========================================================================\n";
$out .= "-- CRM-ZAZU COMPLETE PRODUCTION DATABASE SCHEMA & SEED DATA\n";
$out .= "-- Target Database: {$cfg['database']}\n";
$out .= "-- Exported: " . date('Y-m-d H:i:s') . "\n";
$out .= "-- =========================================================================\n\n";
$out .= "SET FOREIGN_KEY_CHECKS = 0;\n";
$out .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$out .= "SET time_zone = \"+05:30\";\n\n";

foreach ($tables as $t) {
    $out .= "-- ---------------------------------------------------------\n";
    $out .= "-- Table structure for table `{$t}`\n";
    $out .= "-- ---------------------------------------------------------\n";
    $cres = $conn->query("SHOW CREATE TABLE `{$t}`");
    $crow = $cres->fetch_row();
    $createSql = $crow[1];
    
    // Normalize auto increment to clean initial state if preferred or keep structure
    $out .= "DROP TABLE IF EXISTS `{$t}`;\n";
    $out .= $createSql . ";\n\n";
}

// Add important seed data (role permissions, package tiers, initial admin user, starter smtp account)
$out .= "-- =========================================================================\n";
$out .= "-- SYSTEM SEED DATA & ESSENTIALS\n";
$out .= "-- =========================================================================\n\n";

// 1. Role permissions
$out .= "-- 1. Default Role Permissions\n";
$out .= "INSERT INTO `crm_role_permissions` (`role`, `module`) VALUES\n";
$out .= "('admin', '[\"dashboard\",\"customers\",\"leads\",\"orders\",\"visits\",\"tracking\\\/live\",\"geofence\",\"attendance\",\"shifts\",\"leave\",\"selfie\\\/log\",\"reports\",\"admin\",\"communications\"]'),\n";
$out .= "('manager', '[\"dashboard\",\"customers\",\"leads\",\"orders\",\"visits\",\"tracking\\\/live\",\"geofence\",\"attendance\",\"shifts\",\"leave\",\"selfie\\\/log\",\"reports\",\"admin\",\"communications\"]'),\n";
$out .= "('field_staff', '[\"dashboard\",\"customers\",\"leads\",\"orders\",\"visits\",\"attendance\",\"leave\"]')\n";
$out .= "ON DUPLICATE KEY UPDATE `module`=VALUES(`module`);\n\n";

// 2. Package tiers
$out .= "-- 2. Default Package Tiers\n";
$out .= "INSERT INTO `crm_package_tiers` (`id`, `name`, `slug`, `badge_color`, `icon`, `description`, `sort_order`, `status`) VALUES\n";
$out .= "(1, 'Bronze', 'bronze', 'amber', 'fa-shield', 'Essential entry plan for small operations', 1, 'active'),\n";
$out .= "(2, 'Silver', 'silver', 'slate', 'fa-star-half-o', 'Growing teams requiring expanded feature set', 2, 'active'),\n";
$out .= "(3, 'Gold', 'gold', 'yellow', 'fa-star', 'High performance tier for established businesses', 3, 'active'),\n";
$out .= "(4, 'Platinum', 'platinum', 'purple', 'fa-diamond', 'Full automation and multi-branch management', 4, 'active'),\n";
$out .= "(5, 'Enterprise', 'enterprise', 'blue', 'fa-building', 'Unlimited scalability, dedicated support & customization', 5, 'active')\n";
$out .= "ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);\n\n";

$out .= "SET FOREIGN_KEY_CHECKS = 1;\n";

file_put_contents(__DIR__ . '/../sql.txt', $out);
echo "Wrote " . strlen($out) . " bytes into sql.txt covering " . count($tables) . " tables.\n";
