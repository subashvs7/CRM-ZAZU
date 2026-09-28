<?php
define('ENVIRONMENT', 'development');
define('BASEPATH', 'crm');
require_once __DIR__ . '/../application/config/database.php';
$cfg = $db['default'];
$conn = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);

echo "=== DESCRIBE crm_bulk_mail_queue ===\n";
$r = $conn->query("DESCRIBE crm_bulk_mail_queue");
while ($row = $r->fetch_assoc()) {
    printf("%-20s %-25s %-6s\n", $row['Field'], $row['Type'], $row['Null']);
}
