<?php
/**
 * Hostinger Cron Job Runner for CRM-ZAZU
 * Executes background mail queue safely without needing URL arguments
 */
date_default_timezone_set('Asia/Kolkata');

$_SERVER['PATH_INFO']   = 'communications/process_queue_cron';
$_SERVER['REQUEST_URI'] = 'communications/process_queue_cron';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

$_SERVER['argv'] = [
    __FILE__,
    'communications',
    'process_queue_cron'
];
$_SERVER['argc'] = 3;

require_once __DIR__ . '/index.php';
