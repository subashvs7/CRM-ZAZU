<?php
$p = new PDO('mysql:host=localhost;dbname=u128207985_crm_zazu', 'root', '');
$p->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "
CREATE TABLE IF NOT EXISTS smtp_accounts (
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  sender_email VARCHAR(150) NOT NULL,
  sender_name VARCHAR(150) NOT NULL,
  smtp_host VARCHAR(150) NOT NULL DEFAULT 'smtp.hostinger.com',
  smtp_port INT NOT NULL DEFAULT 465,
  smtp_crypto ENUM('ssl','tls','none') NOT NULL DEFAULT 'ssl',
  smtp_user VARCHAR(150) NOT NULL,
  smtp_pass VARCHAR(255) NOT NULL,
  daily_limit INT NOT NULL DEFAULT 100,
  sent_today INT NOT NULL DEFAULT 0,
  last_reset_date DATE NOT NULL,
  status ENUM('active','limit_reached','disabled','error') NOT NULL DEFAULT 'active',
  last_error TEXT NULL,
  last_used_at DATETIME NULL,
  is_deleted TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_smtp_status (status, is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$p->exec($sql);
echo "Created smtp_accounts table successfully.\n";

// Insert sample Hostinger starter account if table is empty
$count = $p->query("SELECT COUNT(*) FROM smtp_accounts WHERE is_deleted = 0")->fetchColumn();
if ($count == 0) {
    $now = date('Y-m-d H:i:s');
    $today = date('Y-m-d');
    $stmt = $p->prepare("INSERT INTO smtp_accounts (name, sender_email, sender_name, smtp_host, smtp_port, smtp_crypto, smtp_user, smtp_pass, daily_limit, sent_today, last_reset_date, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?)");
    
    // Seed 2 default slots so user sees the Hostinger setup right away
    $stmt->execute([
        'Hostinger Mail 1 (Outreach)',
        'outreach@crm-zazu.local',
        'CRM-ZAZU Outreach',
        'smtp.hostinger.com',
        465,
        'ssl',
        'outreach@crm-zazu.local',
        'password123',
        100,
        0,
        $today,
        $now,
        $now
    ]);
    
    $stmt->execute([
        'Hostinger Mail 2 (Sales)',
        'sales@crm-zazu.local',
        'CRM-ZAZU Sales',
        'smtp.hostinger.com',
        465,
        'ssl',
        'sales@crm-zazu.local',
        'password123',
        100,
        0,
        $today,
        $now,
        $now
    ]);
    echo "Seeded 2 initial Hostinger SMTP accounts.\n";
}
