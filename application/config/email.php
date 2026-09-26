<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['protocol']    = 'mail'; // 'mail', 'sendmail', or 'smtp'
$config['mailtype']    = 'html';
$config['charset']     = 'utf-8';
$config['wordwrap']    = TRUE;
$config['newline']     = "\r\n";
$config['crlf']        = "\r\n";
$config['validate']    = TRUE;

// Default sender
$config['from_email']  = 'outreach@crm-zazu.local';
$config['from_name']   = 'CRM-ZAZU Hub';
