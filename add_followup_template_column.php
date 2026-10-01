<?php
// One-time migration: add followup_template_id to crm_bulk_mail_campaigns and crm_bulk_mail_queue
header('Content-Type: text/html; charset=utf-8');

mysqli_report(MYSQLI_REPORT_OFF);

// Try Live DB credentials first, fallback to local root
$conn = @new mysqli('localhost', 'u1138516290_zazu_user', 'ZAZU@123456789', 'u1138516290_zazu_db');
if (!$conn || $conn->connect_errno) {
    $conn = @new mysqli('localhost', 'root', '', 'u206223007_crmdb');
}
if (!$conn || $conn->connect_errno) {
    die("<b style='color:red'>Database connection failed: " . ($conn ? $conn->connect_error : 'Unknown error') . "</b>");
}

echo "<h2>CRM-ZAZU Migration: Follow-Up Template Columns</h2>";

// 1. Check crm_bulk_mail_campaigns
$check1 = $conn->query("SHOW COLUMNS FROM crm_bulk_mail_campaigns LIKE 'followup_template_id'");
if ($check1 && $check1->num_rows === 0) {
    $sql1 = "ALTER TABLE crm_bulk_mail_campaigns ADD COLUMN followup_template_id INT(11) DEFAULT NULL AFTER template_id";
    if ($conn->query($sql1)) {
        echo "<p style='color:green'>✅ <b>crm_bulk_mail_campaigns:</b> Added column <code>followup_template_id</code></p>";
    } else {
        echo "<p style='color:red'>❌ <b>crm_bulk_mail_campaigns Error:</b> " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:orange'>ℹ️ <b>crm_bulk_mail_campaigns:</b> Column <code>followup_template_id</code> already exists.</p>";
}

// 2. Check crm_bulk_mail_queue
$check2 = $conn->query("SHOW COLUMNS FROM crm_bulk_mail_queue LIKE 'followup_template_id'");
if ($check2 && $check2->num_rows === 0) {
    $sql2 = "ALTER TABLE crm_bulk_mail_queue ADD COLUMN followup_template_id INT(11) DEFAULT NULL AFTER next_followup_date";
    if ($conn->query($sql2)) {
        echo "<p style='color:green'>✅ <b>crm_bulk_mail_queue:</b> Added column <code>followup_template_id</code></p>";
    } else {
        echo "<p style='color:red'>❌ <b>crm_bulk_mail_queue Error:</b> " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:orange'>ℹ️ <b>crm_bulk_mail_queue:</b> Column <code>followup_template_id</code> already exists.</p>";
}

$conn->close();
echo "<hr><p><b>Migration Completed. Please delete this file after running.</b></p>";
?>
