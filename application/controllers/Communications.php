<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Communications extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $method = strtolower($this->router->fetch_method());
        if ($method !== 'process_queue_cron') {
            $this->require_login();
        }
        $this->load->model(['Bulk_mail_model', 'Product_model', 'Lead_model', 'Customer_model', 'Smtp_account_model', 'App_setting_model']);
        $this->load->library('email');
    }

    public function index() {
        redirect('communications/bulk_mail');
    }

    /**
     * Retrieve configured outreach stages from crm_app_settings or fallback defaults
     */
    public function _get_outreach_stages() {
        $raw = $this->App_setting_model->get_by_key('outreach_stages');
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }
        return [
            ['id' => 'outreach', 'name' => '🚀 Initial Outreach (First Pitch)', 'days' => 3, 'locked' => true],
            ['id' => 'followup_1', 'name' => '🔁 Follow-Up #1 (Gentle Reminder)', 'days' => 3, 'locked' => false],
            ['id' => 'followup_2', 'name' => '⚡ Follow-Up #2 (Last Call & Offer)', 'days' => 2, 'locked' => false],
            ['id' => 'retry', 'name' => '🛠️ Retry / Resend Failed Dispatches', 'days' => 1, 'locked' => false],
            ['id' => 'announcement', 'name' => '📢 Announcement / Product Update', 'days' => 7, 'locked' => false]
        ];
    }

    /**
     * Primary Bulk Mail Hub View (All-In-One Unified Hub)
     */
    public function bulk_mail() {
        $products = $this->Product_model->get_active();
        $templates = $this->Bulk_mail_model->get_templates_by_product();
        $stats = $this->Bulk_mail_model->get_stats();
        $recent_campaigns = $this->Bulk_mail_model->get_recent_campaigns(10);
        $smtp_pool = $this->Smtp_account_model->get_pool_status();
        $company_name = $this->App_setting_model->get_by_key('company_name') ?: 'CRM-ZAZU';

        // Preload active leads and customers counts for audience badge display
        $total_leads_count = $this->db->where('is_deleted', 0)->where('status', 'active')->where('email IS NOT NULL', null, false)->where('TRIM(email) !=', '')->count_all_results('crm_leads');
        $total_custs_count = $this->db->where('is_deleted', 0)->where('email IS NOT NULL', null, false)->where('TRIM(email) !=', '')->count_all_results('crm_customers');
        $total_contk_count = $this->db->where('is_deleted', 0)->where('email IS NOT NULL', null, false)->where('TRIM(email) !=', '')->count_all_results('crm_contact_book');

        $this->load_view('communications/bulk_mail', [
            'page_title'         => 'Bulk Mail Hub',
            'products'           => $products,
            'templates'          => $templates,
            'stats'              => $stats,
            'recent_campaigns'   => $recent_campaigns,
            'total_leads_count'  => $total_leads_count,
            'total_custs_count'  => $total_custs_count,
            'total_contk_count'  => $total_contk_count,
            'company_name'       => $company_name,
            'smtp_pool'          => $smtp_pool,
            'outreach_stages'    => $this->_get_outreach_stages(),
            'current_user'       => $this->get_user()
        ]);
    }

    /**
     * Dedicated Email Templates Library & Editor
     */
    public function mail_templates() {
        $products = $this->Product_model->get_active();
        $templates = $this->Bulk_mail_model->get_templates_by_product();
        $this->load_view('communications/templates', [
            'page_title' => 'Email Templates Library',
            'products'   => $products,
            'templates'  => $templates
        ]);
    }

    /**
     * Dedicated Mail Dispatch History & Logs
     */
    /**
     * Dedicated Mail Dispatch History & Logs
     */
    public function mail_history() {
        $stats = $this->Bulk_mail_model->get_stats();
        $recent_campaigns = $this->Bulk_mail_model->get_recent_campaigns(50);
        $distinct_senders = $this->Bulk_mail_model->get_distinct_senders();
        $initial_logs = $this->Bulk_mail_model->get_delivery_logs([], 50, 0);
        $this->load_view('communications/history', [
            'page_title'       => 'Mail Dispatch History & Delivery Logs',
            'stats'            => $stats,
            'recent_campaigns' => $recent_campaigns,
            'distinct_senders' => $distinct_senders,
            'delivery_logs'    => $initial_logs['rows'],
            'total_logs'       => $initial_logs['total'],
            'outreach_stages'  => $this->_get_outreach_stages(),
            'page_js'          => 'communications'
        ]);
    }

    /**
     * AJAX: Filter and search delivery logs
     */
    public function delivery_logs_ajax() {
        $params = [
            'from_date'     => $this->input->get('from_date'),
            'to_date'       => $this->input->get('to_date'),
            'sender_email'  => $this->input->get('sender_email'),
            'status'        => $this->input->get('status'),
            'campaign_type' => $this->input->get('campaign_type'),
            'followup_due'  => $this->input->get('followup_due'),
            'search'        => $this->input->get('search')
        ];
        $limit  = (int)($this->input->get('limit') ?: 50);
        $offset = (int)($this->input->get('offset') ?: 0);
        $logs   = $this->Bulk_mail_model->get_delivery_logs($params, $limit, $offset);
        $this->json_success($logs);
    }

    /**
     * Export Filtered Delivery Logs to CSV
     */
    public function export_delivery_logs_csv() {
        $params = [
            'from_date'     => $this->input->get('from_date'),
            'to_date'       => $this->input->get('to_date'),
            'sender_email'  => $this->input->get('sender_email'),
            'status'        => $this->input->get('status'),
            'campaign_type' => $this->input->get('campaign_type'),
            'followup_due'  => $this->input->get('followup_due'),
            'search'        => $this->input->get('search')
        ];

        // Fetch logs matching filter (up to 10,000 records)
        $res = $this->Bulk_mail_model->get_delivery_logs($params, 10000, 0);
        $rows = $res['rows'] ?? [];

        $filename = 'crm_outreach_history_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Microsoft Excel

        fputcsv($out, [
            'Log ID',
            'Recipient Name',
            'Recipient Email',
            'Outreach Purpose / Stage',
            'Campaign Subject',
            'Product',
            'Sender Mailbox',
            'Anti-Spam Ref',
            'Dispatched At',
            'Next Follow-Up Date',
            'Delivery Status',
            'Error Note'
        ]);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],
                $r['recipient_name'] ?: 'Customer',
                $r['recipient_email'],
                ucfirst(str_replace('_', ' ', $r['campaign_type'] ?: 'outreach')),
                $r['campaign_subject'] ?: 'Direct Outreach',
                $r['product_name'] ?: '-',
                $r['sender_email'] ?: ($r['sender_mailbox_name'] ?: '-'),
                !empty($r['anti_spam_hash']) ? '#' . $r['anti_spam_hash'] : '-',
                !empty($r['sent_at']) ? $r['sent_at'] : 'In Queue',
                !empty($r['next_followup_date']) ? $r['next_followup_date'] : '-',
                strtoupper($r['status'] ?: 'QUEUED'),
                $r['error_message'] ?: ''
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Dedicated Hostinger SMTP Settings Page (Accessible directly by staff and admin)
     */
    public function smtp_settings() {
        $smtp_pool = $this->Smtp_account_model->get_pool_status();
        $this->load_view('communications/smtp_settings', [
            'page_title' => 'Hostinger SMTP Mail Pool',
            'page_js'    => 'admin',
            'smtp_pool'  => $smtp_pool
        ]);
    }

    /**
     * AJAX: Get templates filtered by Product and/or Category
     */
    public function get_templates_ajax() {
        $product_id = $this->input->get('product_id');
        $category   = $this->input->get('category');

        $templates = $this->Bulk_mail_model->get_templates_by_product($product_id ?: null, $category ?: null);
        $this->json_success($templates);
    }

    /**
     * AJAX: Get single template detail
     */
    public function get_template_ajax($id) {
        $template = $this->Bulk_mail_model->get_template_by_id((int)$id);
        if (!$template) {
            $this->json_error('Template not found.', 404);
        }
        $this->json_success($template);
    }

    /**
     * AJAX: Save or Create Template
     */
    public function save_template_ajax() {
        $id         = (int)$this->input->post('id');
        $name       = trim($this->input->post('name'));
        $category   = trim($this->input->post('category')) ?: 'general';
        $product_id = (int)$this->input->post('product_id') ?: null;
        $subject    = trim($this->input->post('subject'));
        $body       = trim($this->input->post('body'));

        if (!$name) {
            $this->json_error('Template Name is required.');
        }
        if (!$subject) {
            $this->json_error('Subject line is required.');
        }
        if (!$body) {
            $this->json_error('Email body content cannot be empty.');
        }

        // Detect merge tags present in subject and body
        preg_match_all('/\{\{([a-zA-Z0-9_\-]+)\}\}/', $subject . ' ' . $body, $matches);
        $vars = array_unique($matches[1] ?? []);

        $saveData = [
            'id'         => $id ?: null,
            'name'       => $name,
            'channel'    => 'email',
            'category'   => $category,
            'product_id' => $product_id,
            'subject'    => $subject,
            'body'       => $body,
            'variables'  => json_encode(array_values($vars)),
            'status'     => 'active'
        ];

        $savedId = $this->Bulk_mail_model->save_template($saveData);
        $template = $this->Bulk_mail_model->get_template_by_id($savedId);

        $this->json_success($template, 'Template saved successfully.');
    }

    /**
     * AJAX: Delete Template
     */
    public function delete_template_ajax() {
        $id = (int)$this->input->post('id');
        if (!$id) {
            $this->json_error('Invalid template ID.');
        }

        $this->Bulk_mail_model->delete_template($id);
        $this->json_success([], 'Template deleted successfully.');
    }

    /**
     * AJAX: Get real-time audience recipient count and preview
     */
    public function audience_count_ajax() {
        $recipient_types = $this->input->get('recipient_types');
        if (empty($recipient_types)) {
            $single = $this->input->get('recipient_type');
            $recipient_types = $single ? (is_array($single) ? $single : explode(',', $single)) : ['All Leads'];
        }

        $product_id   = (int)$this->input->get('product_id') ?: null;
        $lead_status  = $this->input->get('lead_status') ?: null;
        $reach_filter = $this->input->get('reach_filter') ?: 'all';

        $recipients = $this->Bulk_mail_model->get_recipients($recipient_types, $product_id, $lead_status, 15, $reach_filter);
        $totalCount = count($this->Bulk_mail_model->get_recipients($recipient_types, $product_id, $lead_status, 0, $reach_filter));

        $this->json_success([
            'total'   => $totalCount,
            'samples' => $recipients
        ]);
    }

    /**
     * AJAX: Get full recipients list for granular multi-select checkboxes
     */
    public function get_audience_recipients_ajax() {
        $recipient_types = $this->input->get('recipient_types');
        if (empty($recipient_types)) {
            $single = $this->input->get('recipient_type');
            $recipient_types = $single ? (is_array($single) ? $single : explode(',', $single)) : ['leads', 'customers', 'contact_book'];
        }

        $product_id   = (int)$this->input->get('product_id') ?: null;
        $lead_status  = $this->input->get('lead_status') ?: null;
        $reach_filter = $this->input->get('reach_filter') ?: 'all';

        $recipients = $this->Bulk_mail_model->get_recipients($recipient_types, $product_id, $lead_status, 1500, $reach_filter);
        $this->json_success($recipients);
    }

    /**
     * AJAX: Handle Summernote Image Drag & Drop / File Upload
     */
    public function upload_image() {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->json_error('No valid image file uploaded.');
        }

        $file = $_FILES['file'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed)) {
            $this->json_error('Only JPG, PNG, GIF, or WebP images are allowed.');
        }

        if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
            $this->json_error('Image exceeds maximum allowed size of 10MB.');
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = 'mail_img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $uploadDir = FCPATH . 'uploads/email_assets/';

        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $destination = $uploadDir . $newFilename;
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $publicUrl = base_url('uploads/email_assets/' . $newFilename);
            $this->json_success(['url' => $publicUrl], 'Image uploaded successfully.');
        } else {
            $this->json_error('Failed to move uploaded image.');
        }
    }

    /**
     * AJAX: Send a quick 1-click Test Email to preview render
     */
    public function send_test_email() {
        $testEmail  = trim($this->input->post('test_email'));
        $subject    = trim($this->input->post('subject'));
        $body       = trim($this->input->post('body'));
        $product_id = (int)$this->input->post('product_id') ?: null;

        if (!$testEmail || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $this->json_error('Please enter a valid test email address.');
        }
        if (!$subject || !$body) {
            $this->json_error('Subject and message body are required.');
        }

        // Product details for variable substitution
        $productName  = 'CRM-ZAZU Solutions';
        $productPrice = '₹ 1,29,999';
        if ($product_id) {
            $prod = $this->Product_model->get_by_id($product_id);
            if ($prod) {
                $productName  = $prod['name'];
                $productPrice = $prod['price'] ? inr_paise_to_rupees($prod['price']) : '';
            }
        }

        $currentUser = $this->get_user();
        $senderName  = $currentUser['name'] ?? 'CRM Team';
        $senderPhone = $currentUser['phone'] ?? '+91 9876543210';

        // Sample merge tags substitution
        $replacements = [
            '{{customer_name}}'       => 'Valued Partner',
            '{{first_name}}'          => 'Valued',
            '{{last_name}}'           => 'Partner',
            '{{company_name}}'        => 'Acme Technologies Pvt Ltd',
            '{{email}}'               => $testEmail,
            '{{phone}}'               => '+91 9123456789',
            '{{product_name}}'        => $productName,
            '{{product_price}}'       => $productPrice,
            '{{login_url}}'           => base_url('auth/login'),
            '{{login_email}}'         => $testEmail,
            '{{temporary_password}}'  => 'Zazu@' . rand(1000, 9999),
            '{{sender_name}}'         => $senderName,
            '{{sender_phone}}'        => $senderPhone,
            '{{current_date}}'        => date('d M Y')
        ];

        $renderedSubject = str_replace(array_keys($replacements), array_values($replacements), $subject);
        $renderedBody    = str_replace(array_keys($replacements), array_values($replacements), $body);

        // Fetch selected or active SMTP account for test dispatch
        $sender_smtp_id = $this->input->post('sender_smtp_id');
        $smtp = null;
        if (!empty($sender_smtp_id) && $sender_smtp_id !== 'auto') {
            $smtp = $this->Smtp_account_model->get_by_id((int)$sender_smtp_id);
        }
        if (!$smtp) {
            $smtp = $this->Smtp_account_model->get_next_available_account();
        }
        $fromEmail = 'outreach@crm-zazu.local';
        $fromName  = $senderName . ' via ZAZU CRM';

        if ($smtp) {
            $smtpConfig = [
                'protocol'    => 'smtp',
                'smtp_host'   => $smtp['smtp_host'] ?: 'smtp.hostinger.com',
                'smtp_port'   => (int)($smtp['smtp_port'] ?: 465),
                'smtp_user'   => $smtp['smtp_user'],
                'smtp_pass'   => $smtp['smtp_pass'],
                'smtp_crypto' => strtolower($smtp['smtp_crypto'] ?: 'ssl'),
                'mailtype'    => 'html',
                'charset'     => 'utf-8',
                'newline'     => "\r\n",
                'crlf'        => "\r\n"
            ];
            $this->email->initialize($smtpConfig);
            $fromEmail = $smtp['sender_email'];
            $fromName  = $smtp['sender_name'] ?: $fromName;
        }

        $this->email->clear();
        $this->email->from($fromEmail, $fromName);
        $this->email->to($testEmail);
        $this->email->subject('[TEST PREVIEW] ' . $renderedSubject);
        $this->email->message($renderedBody);

        // In local XAMPP without live credentials, mail() returns false but renders preview
        $sent = @$this->email->send();
        if ($sent) {
            if ($smtp) {
                $this->Smtp_account_model->increment_sent_count($smtp['id']);
            }
            $this->json_success([], 'Test email sent successfully to ' . esc_html($testEmail) . ($smtp ? " via Hostinger SMTP ({$smtp['sender_email']})" : '') . '!');
        } else {
            $debug = $this->email->print_debugger(['headers']);
            $this->json_success([
                'note'  => 'Test rendered correctly. (Local SMTP note: Live mail delivery requires valid Hostinger credentials in Settings).',
                'debug' => $debug
            ], 'Test email rendered and processed for ' . esc_html($testEmail) . '.');
        }
    }

    /**
     * Process and Dispatch Bulk Mail Campaign with Multi-Audience and Dynamic SMTP Auto-Switching
     */
    public function process_bulk_mail() {
        $recipient_types = $this->input->post('recipient_types');
        if (empty($recipient_types)) {
            $single = $this->input->post('recipient_type');
            $recipient_types = $single ? (is_array($single) ? $single : explode(',', $single)) : ['All Leads'];
        }

        $product_id     = (int)$this->input->post('product_id') ?: null;
        $lead_status    = $this->input->post('lead_status') ?: null;
        $template_id    = (int)$this->input->post('template_id') ?: null;
        $subject        = trim($this->input->post('subject'));
        $message        = trim($this->input->post('message'));
        $dispatch_mode  = $this->input->post('dispatch_mode') ?: 'instant'; // 'instant' or 'queue'

        if (!$subject || !$message) {
            if ($this->input->is_ajax_request()) {
                $this->json_error('Subject and message content are required.');
            }
            $this->session->set_flashdata('error', 'Subject and message are required.');
            redirect('communications/bulk_mail');
        }

        // Fetch deduplicated recipients from all selected audiences
        $recipients = $this->Bulk_mail_model->get_recipients($recipient_types, $product_id, $lead_status, 2000);

        // Granular Filter: If user selected specific leads/customers via checkboxes
        $selected_recipient_keys = $this->input->post('selected_recipient_keys');
        $isGranular = false;
        if (!empty($selected_recipient_keys)) {
            if (is_string($selected_recipient_keys)) {
                $selected_recipient_keys = array_filter(array_map('trim', explode(',', $selected_recipient_keys)));
            }
            if (is_array($selected_recipient_keys) && count($selected_recipient_keys) > 0) {
                $filtered = [];
                foreach ($recipients as $rec) {
                    if (in_array($rec['key'], $selected_recipient_keys)) {
                        $filtered[] = $rec;
                    }
                }
                $recipients = $filtered;
                $isGranular = true;
            }
        }

        if (empty($recipients)) {
            if ($this->input->is_ajax_request()) {
                $this->json_error('No valid recipients with email addresses found for the selected criteria.');
            }
            $this->session->set_flashdata('error', 'No valid recipients with email addresses found.');
            redirect('communications/bulk_mail');
        }

        // Product details for variable substitution
        $productName  = 'CRM-ZAZU Solutions';
        $productPrice = '₹ 1,29,999';
        if ($product_id) {
            $prod = $this->Product_model->get_by_id($product_id);
            if ($prod) {
                $productName  = $prod['name'];
                $productPrice = $prod['price'] ? inr_paise_to_rupees($prod['price']) : '';
            }
        }

        $currentUser = $this->get_user();
        $senderName  = $currentUser['name'] ?? 'CRM Team';
        $senderPhone = $currentUser['phone'] ?? '+91 9876543210';
        $currentUserId = $this->get_user_id() ?: 1;

        // Check if user chose an instant specific SMTP mailbox or auto-rotate pool
        $sender_smtp_id = $this->input->post('sender_smtp_id');
        $forcedSmtp = null;
        if (!empty($sender_smtp_id) && $sender_smtp_id !== 'auto') {
            $forcedSmtp = $this->Smtp_account_model->get_by_id((int)$sender_smtp_id);
        }

        $targetSummary = is_array($recipient_types) ? implode(', ', $recipient_types) : $recipient_types;
        if ($isGranular) {
            $targetSummary .= ' (' . count($recipients) . ' Handpicked)';
        }

        $stages = $this->_get_outreach_stages();
        $valid_types = array_column($stages, 'id');
        $campaign_type = $this->input->post('campaign_type') ?: 'outreach';
        if (!in_array($campaign_type, $valid_types)) {
            $campaign_type = 'outreach';
        }

        $typeTitle = 'Outreach';
        foreach ($stages as $st) {
            if ($st['id'] === $campaign_type) {
                $typeTitle = $st['name'];
                break;
            }
        }

        $followup_schedule = $this->input->post('followup_schedule') ?: '3';
        if ($followup_schedule === 'custom') {
            $custom_date = trim($this->input->post('custom_followup_date') ?: '');
            if (!empty($custom_date) && strtotime($custom_date)) {
                $next_followup_date = date('Y-m-d', strtotime($custom_date));
                $todayTs = strtotime(date('Y-m-d'));
                $targetTs = strtotime($next_followup_date);
                $diffDays = (int)round(($targetTs - $todayTs) / 86400);
                $next_followup_days = max(1, $diffDays);
            } else {
                $next_followup_days = 3;
                $next_followup_date = date('Y-m-d', strtotime('+3 days'));
            }
        } elseif ($followup_schedule === 'custom_days') {
            $custom_days = max(1, (int)$this->input->post('custom_followup_days'));
            $next_followup_days = $custom_days;
            $next_followup_date = date('Y-m-d', strtotime("+{$custom_days} days"));
        } else {
            $days = max(1, (int)$followup_schedule);
            $next_followup_days = $days;
            $next_followup_date = date('Y-m-d', strtotime("+{$days} days"));
        }

        // Custom Partition / Batch Count control
        $partition_mode  = $this->input->post('partition_mode') ?: 'all';
        $partition_limit = (int)$this->input->post('partition_limit');
        $dispatch_target = ($partition_mode === 'custom' && $partition_limit > 0) ? min($partition_limit, count($recipients)) : count($recipients);

        $campaign_data = [
            'subject'             => $subject,
            'message'             => $message,
            'recipient_type'      => $targetSummary . ($product_id ? ' (Product #' . $product_id . ')' : '') . ($forcedSmtp ? ' [Sender: ' . $forcedSmtp['sender_email'] . ']' : ''),
            'campaign_type'       => $campaign_type,
            'next_followup_days'  => $next_followup_days,
            'product_id'          => $product_id,
            'template_id'         => $template_id,
            'total_recipients'    => count($recipients),
            'status'              => ($dispatch_mode === 'instant') ? 'completed' : 'pending',
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s')
        ];

        $campaign_id = $this->Bulk_mail_model->create_campaign($campaign_data);
        $this->Bulk_mail_model->add_to_queue($campaign_id, $recipients, $campaign_type, $next_followup_date);

        $sentCount    = 0;
        $failedCount  = 0;
        $quotaHalted  = false;
        $usedAccounts = [];

        if ($dispatch_mode === 'instant') {
            $queueItems = $this->db->where('campaign_id', $campaign_id)->get('crm_bulk_mail_queue')->result_array();

            foreach ($queueItems as $idx => $item) {
                // If custom partition limit reached, keep remaining items in queue for future dispatch
                if ($partition_mode === 'custom' && $sentCount >= $dispatch_target) {
                    break;
                }

                // SENDER SELECTION: Use chosen specific mailbox OR auto-rotate fair-share pool
                if ($forcedSmtp) {
                    $rem = max(0, (int)$forcedSmtp['daily_limit'] - (int)$forcedSmtp['sent_today']);
                    if ($rem <= 0) {
                        $quotaHalted = true;
                        break;
                    }
                    $currentSmtp = $forcedSmtp;
                } else {
                    $currentSmtp = $this->Smtp_account_model->get_next_available_account();
                    $poolStatus = $this->Smtp_account_model->get_pool_status();
                    if ($poolStatus['total_accounts'] > 0 && !$currentSmtp) {
                        $quotaHalted = true;
                        break;
                    }
                }

                $fromEmail = 'outreach@crm-zazu.local';
                $fromName  = $senderName . ' via ZAZU CRM';

                if ($currentSmtp) {
                    $smtpConfig = [
                        'protocol'    => 'smtp',
                        'smtp_host'   => $currentSmtp['smtp_host'] ?: 'smtp.hostinger.com',
                        'smtp_port'   => (int)($currentSmtp['smtp_port'] ?: 465),
                        'smtp_user'   => $currentSmtp['smtp_user'],
                        'smtp_pass'   => $currentSmtp['smtp_pass'],
                        'smtp_crypto' => strtolower($currentSmtp['smtp_crypto'] ?: 'ssl'),
                        'mailtype'    => 'html',
                        'charset'     => 'utf-8',
                        'newline'     => "\r\n",
                        'crlf'        => "\r\n"
                    ];
                    $this->email->initialize($smtpConfig);
                    $fromEmail = $currentSmtp['sender_email'];
                    $fromName  = $currentSmtp['sender_name'] ?: $senderName;
                    $usedAccounts[$currentSmtp['id']] = $currentSmtp['name'];
                }

                // Match recipient metadata
                $r = $recipients[$idx] ?? [];

                $replacements = [
                    '{{customer_name}}'       => $item['recipient_name'] ?: 'Customer',
                    '{{first_name}}'          => $r['first_name'] ?? ($item['recipient_name'] ?: 'Customer'),
                    '{{last_name}}'           => $r['last_name'] ?? '',
                    '{{company_name}}'        => $r['company'] ?? ($r['name'] ?? 'Company'),
                    '{{email}}'               => $item['recipient_email'],
                    '{{phone}}'               => $r['phone'] ?? '',
                    '{{product_name}}'        => $productName,
                    '{{product_price}}'       => $productPrice,
                    '{{login_url}}'           => base_url('auth/login'),
                    '{{login_email}}'         => $item['recipient_email'],
                    '{{temporary_password}}'  => 'Zazu@' . rand(1000, 9999),
                    '{{sender_name}}'         => $fromName,
                    '{{sender_phone}}'        => $senderPhone,
                    '{{current_date}}'        => date('d M Y')
                ];

                $anti_spam_hash = $this->Bulk_mail_model->generate_anti_spam_hash();

                $personalizedSubject = str_replace(array_keys($replacements), array_values($replacements), $subject);
                $personalizedMessage = str_replace(array_keys($replacements), array_values($replacements), $message);

                // Anti-Spam unique hash fingerprint to bypass byte-level deduplication filters
                $antiSpamFootnote = '<div style="display:none;font-size:1px;color:#f8fafc;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;">Ref: #' . $anti_spam_hash . '-' . time() . '</div>';
                $personalizedMessage .= "\n" . $antiSpamFootnote;

                $this->email->clear();
                $this->email->from($fromEmail, $fromName);
                $this->email->to($item['recipient_email']);
                $this->email->subject($personalizedSubject);
                $this->email->message($personalizedMessage);

                // Attempt send
                $sendOk = @$this->email->send();
                $nowFormatted = date('d M Y, h:i A');

                if ($sendOk) {
                    $statusText = 'Send Success - ' . $nowFormatted;
                    $queueStatus = 'sent';
                    $sentCount++;

                    // Increment sent counter on the SMTP account (auto-triggers limit_reached flag if daily_limit hit)
                    if ($currentSmtp) {
                        $this->Smtp_account_model->increment_sent_count($currentSmtp['id']);
                    }
                } else {
                    $statusText = 'Send Failed - ' . $nowFormatted;
                    $queueStatus = 'failed';
                }

                // Mark queue item with rich audit trail
                $this->Bulk_mail_model->update_queue_item($item['id'], [
                    'sender_email'       => $fromEmail,
                    'smtp_account_id'   => $currentSmtp ? $currentSmtp['id'] : null,
                    'anti_spam_hash'     => $anti_spam_hash,
                    'status'             => $queueStatus,
                    'sent_at'            => date('Y-m-d H:i:s'),
                    'next_followup_date' => $next_followup_date
                ]);

                // If lead recipient, record follow-up activity log and update email_sent status
                if (!empty($item['lead_id'])) {
                    $activityNote = $sendOk 
                        ? "{$typeTitle} Dispatched Successfully via {$fromEmail} [Ref: #{$anti_spam_hash}]: \"{$subject}\" (Campaign #{$campaign_id}). Status: Send Success ({$nowFormatted}). Next follow-up on {$next_followup_date}."
                        : "{$typeTitle} Dispatch Failed via {$fromEmail} [Ref: #{$anti_spam_hash}]: \"{$subject}\" (Campaign #{$campaign_id}). Status: Send Failed ({$nowFormatted}).";

                    $this->db->insert('crm_lead_activities', [
                        'lead_id'       => (int)$item['lead_id'],
                        'user_id'       => $currentUserId,
                        'activity_type' => 'email',
                        'notes'         => $activityNote,
                        'occurred_at'   => date('Y-m-d H:i:s'),
                        'status'        => 'active',
                        'is_deleted'    => 0,
                        'created_at'    => date('Y-m-d H:i:s'),
                        'updated_at'    => date('Y-m-d H:i:s')
                    ]);

                    $this->db->where('id', (int)$item['lead_id'])->update('crm_leads', [
                        'email_sent' => $statusText,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            $remainingCount = count($queueItems) - $sentCount;
            if ($quotaHalted) {
                $this->Bulk_mail_model->update_campaign_status($campaign_id, 'partial');
                $msg = "Dispatched {$sentCount} emails! Hostinger daily limit reached across all SMTP accounts. The remaining {$remainingCount} emails remain queued and will continue tomorrow.";
            } elseif ($partition_mode === 'custom' && $remainingCount > 0) {
                $this->Bulk_mail_model->update_campaign_status($campaign_id, 'partial');
                $msg = "Custom Partition Dispatched! Successfully sent {$sentCount} email(s) now. Remaining {$remainingCount} email(s) are held in queue ready for next dispatch.";
            } else {
                $this->Bulk_mail_model->update_campaign_status($campaign_id, 'completed');
                $acctText = count($usedAccounts) > 1 ? " with auto-rotation across " . count($usedAccounts) . " Hostinger SMTP accounts" : "";
                $msg = "Bulk mail campaign launched! Successfully dispatched to {$sentCount} recipient(s){$acctText}. Next follow-up on {$next_followup_date}.";
            }
        } else {
            $msg = "Campaign queued successfully! " . count($recipients) . " emails added to queue for background dispatch. Next follow-up scheduled for {$next_followup_date}.";
        }

        if ($this->input->is_ajax_request()) {
            $stats = $this->Bulk_mail_model->get_stats();
            $smtpStatus = $this->Smtp_account_model->get_pool_status();
            $this->json_success([
                'campaign_id'  => $campaign_id,
                'sent_count'   => $sentCount,
                'total'        => count($recipients),
                'quota_halted' => $quotaHalted,
                'stats'        => $stats,
                'smtp_pool'    => $smtpStatus
            ], $msg);
        }

        $this->session->set_flashdata('success', $msg);
        redirect('communications/bulk_mail');
    }

    /**
     * AJAX: Get Campaign Detail & Recipient Queue
     */
    public function campaign_detail_ajax($id) {
        $detail = $this->Bulk_mail_model->get_campaign_detail((int)$id);
        if (!$detail) {
            $this->json_error('Campaign not found.', 404);
        }
        $this->json_success($detail);
    }

    /**
     * AJAX: Live refresh stats and campaign history
     */
    public function history_ajax() {
        $stats     = $this->Bulk_mail_model->get_stats();
        $campaigns = $this->Bulk_mail_model->get_recent_campaigns(15);
        $this->json_success([
            'stats'     => $stats,
            'campaigns' => $campaigns
        ]);
    }

    /**
     * AJAX: Live SMTP Pool Quota & Status check for Bulk Mail Hub
     */
    public function smtp_pool_status_ajax() {
        $status = $this->Smtp_account_model->get_pool_status();
        $this->json_success($status);
    }

    /**
     * Internal: Process background queue items (1 item default for 2-minute safe anti-ban pacing)
     */
    protected function _execute_queue_batch($limit = 1) {
        $this->load->library('email');

        // Fetch up to $limit pending items
        $items = $this->db->select('q.*, c.subject, c.message, c.product_id, c.template_id, c.recipient_type')
            ->from('crm_bulk_mail_queue q')
            ->join('crm_bulk_mail_campaigns c', 'c.id = q.campaign_id')
            ->where('q.status', 'queued')
            ->order_by('q.id', 'ASC')
            ->limit($limit)
            ->get()->result_array();

        if (empty($items)) {
            $queuedCount = $this->db->where('status', 'queued')->count_all_results('crm_bulk_mail_queue');
            return [
                'status'    => 'idle',
                'message'   => 'Queue is empty. No pending emails to dispatch.',
                'processed' => 0,
                'remaining' => $queuedCount
            ];
        }

        $processed = 0;
        $details = [];

        foreach ($items as $item) {
            // Check SMTP account auto-rotation
            $currentSmtp = $this->Smtp_account_model->get_next_available_account();
            $poolStatus = $this->Smtp_account_model->get_pool_status();

            if ($poolStatus['total_accounts'] > 0 && !$currentSmtp) {
                return [
                    'status'    => 'quota_exhausted',
                    'message'   => 'All Hostinger SMTP mailboxes have reached their daily sending limits. Queued emails will resume automatically.',
                    'processed' => $processed,
                    'remaining' => $this->db->where('status', 'queued')->count_all_results('crm_bulk_mail_queue'),
                    'details'   => $details
                ];
            }

            $senderName = 'Outreach Team';
            $fromEmail  = 'outreach@crm-zazu.local';
            $fromName   = $senderName . ' via ZAZU CRM';

            if ($currentSmtp) {
                $smtpConfig = [
                    'protocol'    => 'smtp',
                    'smtp_host'   => $currentSmtp['smtp_host'] ?: 'smtp.hostinger.com',
                    'smtp_port'   => (int)($currentSmtp['smtp_port'] ?: 465),
                    'smtp_user'   => $currentSmtp['smtp_user'],
                    'smtp_pass'   => $currentSmtp['smtp_pass'],
                    'smtp_crypto' => strtolower($currentSmtp['smtp_crypto'] ?: 'ssl'),
                    'mailtype'    => 'html',
                    'charset'     => 'utf-8',
                    'newline'     => "\r\n",
                    'crlf'        => "\r\n"
                ];
                $this->email->initialize($smtpConfig);
                $fromEmail = $currentSmtp['sender_email'];
                $fromName  = $currentSmtp['sender_name'] ?: $senderName;
            }

            // Product info if attached
            $productName  = 'Our Solution';
            $productPrice = '';
            if (!empty($item['product_id'])) {
                $prod = $this->db->get_where('crm_products', ['id' => (int)$item['product_id']])->row_array();
                if ($prod) {
                    $productName  = $prod['name'];
                    $productPrice = format_inr($prod['price']);
                }
            }

            // Lead info
            $leadData = null;
            if (!empty($item['lead_id'])) {
                $leadData = $this->db->get_where('crm_leads', ['id' => (int)$item['lead_id']])->row_array();
            }

            // Customer info
            $custData = null;
            if (!empty($item['customer_id'])) {
                $custData = $this->db->get_where('crm_customers', ['id' => (int)$item['customer_id']])->row_array();
            }

            $anti_spam_hash = $this->Bulk_mail_model->generate_anti_spam_hash();
            $next_followup_date = !empty($item['next_followup_date']) ? $item['next_followup_date'] : date('Y-m-d', strtotime('+3 days'));

            $replacements = [
                '{{customer_name}}'       => $item['recipient_name'] ?: ($custData['customer_name'] ?? ($leadData['contact_person'] ?? 'Customer')),
                '{{first_name}}'          => $leadData['first_name'] ?? ($item['recipient_name'] ?: 'Customer'),
                '{{last_name}}'           => $leadData['last_name'] ?? '',
                '{{company_name}}'        => $custData['customer_org_name'] ?? ($leadData['company_name'] ?? 'Company'),
                '{{email}}'               => $item['recipient_email'],
                '{{phone}}'               => $custData['phone'] ?? ($leadData['phone'] ?? ''),
                '{{product_name}}'        => $productName,
                '{{product_price}}'       => $productPrice,
                '{{login_url}}'           => base_url('auth/login'),
                '{{login_email}}'         => $item['recipient_email'],
                '{{temporary_password}}'  => 'Zazu@' . rand(1000, 9999),
                '{{sender_name}}'         => $fromName,
                '{{sender_phone}}'        => '',
                '{{current_date}}'        => date('d M Y')
            ];

            $personalizedSubject = str_replace(array_keys($replacements), array_values($replacements), $item['subject']);
            $personalizedMessage = str_replace(array_keys($replacements), array_values($replacements), $item['message']);

            // Anti-Spam fingerprint injection (invisible HTML footer with unique hash and timestamp)
            $antiSpamFootnote = '<div style="display:none;font-size:1px;color:#f8fafc;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;">Ref: #' . $anti_spam_hash . '-' . time() . '</div>';
            $personalizedMessage .= "\n" . $antiSpamFootnote;

            $this->email->clear();
            $this->email->from($fromEmail, $fromName);
            $this->email->to($item['recipient_email']);
            $this->email->subject($personalizedSubject);
            $this->email->message($personalizedMessage);

            $sendOk = @$this->email->send();
            $nowFormatted = date('d M Y, h:i A');

            if ($sendOk) {
                $statusText = 'Send Success - ' . $nowFormatted;
                $queueStatus = 'sent';
                if ($currentSmtp) {
                    $this->Smtp_account_model->increment_sent_count($currentSmtp['id']);
                }
            } else {
                $statusText = 'Send Failed - ' . $nowFormatted;
                $queueStatus = 'failed';
            }

            // Update queue item with sender, anti-spam hash, and follow-up date
            $this->Bulk_mail_model->update_queue_item($item['id'], [
                'sender_email'       => $fromEmail,
                'smtp_account_id'   => $currentSmtp ? $currentSmtp['id'] : null,
                'anti_spam_hash'     => $anti_spam_hash,
                'status'             => $queueStatus,
                'sent_at'            => date('Y-m-d H:i:s'),
                'next_followup_date' => $next_followup_date
            ]);

            // Update campaign status
            $this->Bulk_mail_model->check_and_update_campaign($item['campaign_id']);

            // Lead activity logging
            if (!empty($item['lead_id'])) {
                $typeTitle = 'Outreach';
                if (!empty($item['campaign_type'])) {
                    $typeLabels = [
                        'outreach'     => 'Initial Outreach',
                        'followup_1'   => 'Follow-Up #1',
                        'followup_2'   => 'Follow-Up #2',
                        'retry'        => 'Retry Resend',
                        'announcement' => 'Announcement'
                    ];
                    $typeTitle = $typeLabels[$item['campaign_type']] ?? 'Outreach';
                }

                $activityNote = $sendOk 
                    ? "Queued {$typeTitle} Dispatched Successfully via {$fromEmail} [Ref: #{$anti_spam_hash}]. Status: Send Success ({$nowFormatted}). Next follow-up on {$next_followup_date}."
                    : "Queued {$typeTitle} Dispatch Failed via {$fromEmail} [Ref: #{$anti_spam_hash}]. Status: Send Failed ({$nowFormatted}).";

                $this->db->insert('crm_lead_activities', [
                    'lead_id'       => (int)$item['lead_id'],
                    'user_id'       => 1, // System / Cron
                    'activity_type' => 'email',
                    'notes'         => $activityNote,
                    'occurred_at'   => date('Y-m-d H:i:s'),
                    'status'        => 'active',
                    'is_deleted'    => 0,
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s')
                ]);

                $this->db->where('id', (int)$item['lead_id'])->update('crm_leads', [
                    'email_sent' => $statusText,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            $processed++;
            $details[] = [
                'queue_id'        => $item['id'],
                'recipient_email' => $item['recipient_email'],
                'sender_email'    => $fromEmail,
                'anti_spam_hash'  => $anti_spam_hash,
                'sent_at'         => date('Y-m-d H:i:s')
            ];
        }

        $remaining = $this->db->where('status', 'queued')->count_all_results('crm_bulk_mail_queue');

        return [
            'status'    => 'success',
            'message'   => "Successfully dispatched {$processed} queued email(s).",
            'processed' => $processed,
            'remaining' => $remaining,
            'details'   => $details
        ];
    }

    /**
     * Cron Job / CLI Trigger for Background Queue Dispatch
     * CLI: php index.php communications process_queue_cron
     * Hostinger cPanel Web Cron: wget -q -O /dev/null "https://crm.zazutech.in/communications/process_queue_cron?key=zazu_cron_secret"
     */
    public function process_queue_cron() {
        $limit = (int)($this->input->get('limit') ?: 1); // 1 email per 2 mins = safe pacing
        if ($limit < 1) $limit = 1;
        if ($limit > 10) $limit = 10;

        $result = $this->_execute_queue_batch($limit);

        if (is_cli()) {
            echo "[" . date('Y-m-d H:i:s') . "] Processed: " . $result['processed'] . " | Remaining: " . $result['remaining'] . " | Status: " . $result['status'] . PHP_EOL;
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    /**
     * AJAX Heartbeat: Trigger next item in background queue from CRM browser session
     */
    public function process_queue_batch_ajax() {
        $result = $this->_execute_queue_batch(1);
        $this->json_success($result);
    }

    /**
     * AJAX: Save Customized Outreach Stages
     * (Keeps 'outreach' fixed/locked, allows editing all others or adding new ones)
     */
    public function save_outreach_stages_ajax() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $stages = $this->input->post('stages');
        if (!is_array($stages) || empty($stages)) {
            $this->json_error('Invalid stages payload submitted.');
            return;
        }

        $cleaned = [];
        $hasOutreach = false;

        foreach ($stages as $s) {
            $id = strtolower(trim(preg_replace('/[^a-zA-Z0-9_-]/', '', $s['id'] ?? '')));
            $name = trim($s['name'] ?? '');
            $days = max(1, (int)($s['days'] ?? 3));
            $locked = !empty($s['locked']);

            if ($id === 'outreach') {
                $locked = true;
                $hasOutreach = true;
                if (empty($name)) {
                    $name = '🚀 Initial Outreach (First Pitch)';
                }
            }

            if (!empty($id) && !empty($name)) {
                $cleaned[] = [
                    'id'     => $id,
                    'name'   => $name,
                    'days'   => $days,
                    'locked' => $locked
                ];
            }
        }

        // Ensure Initial Outreach is always present as the first locked stage
        if (!$hasOutreach) {
            array_unshift($cleaned, [
                'id'     => 'outreach',
                'name'   => '🚀 Initial Outreach (First Pitch)',
                'days'   => 3,
                'locked' => true
            ]);
        }

        $this->App_setting_model->set('outreach_stages', json_encode($cleaned));

        $this->json_success([
            'stages' => $cleaned
        ], 'Outreach stages updated successfully!');
    }

    /**
     * AJAX: Get Detailed Stage Analytics & Logs
     */
    public function get_stage_logs_ajax() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $filter_stage = $this->input->get('stage') ?: 'all';
        $stages = $this->_get_outreach_stages();

        // 1. Stage statistics breakdown
        $this->db->select("campaign_type, status, COUNT(id) as count, MIN(next_followup_date) as next_due");
        $this->db->group_by(['campaign_type', 'status']);
        $statsRaw = $this->db->get('crm_bulk_mail_queue')->result_array();

        // Aggregate by stage
        $stageSummary = [];
        foreach ($stages as $st) {
            $stageSummary[$st['id']] = [
                'id'       => $st['id'],
                'name'     => $st['name'],
                'days'     => $st['days'],
                'sent'     => 0,
                'queued'   => 0,
                'failed'   => 0,
                'next_due' => null
            ];
        }

        foreach ($statsRaw as $sr) {
            $stId = $sr['campaign_type'] ?: 'outreach';
            if (!isset($stageSummary[$stId])) {
                $stageSummary[$stId] = [
                    'id'       => $stId,
                    'name'     => ucfirst(str_replace('_', ' ', $stId)),
                    'days'     => 3,
                    'sent'     => 0,
                    'queued'   => 0,
                    'failed'   => 0,
                    'next_due' => null
                ];
            }
            if ($sr['status'] === 'sent') {
                $stageSummary[$stId]['sent'] += (int)$sr['count'];
            } elseif ($sr['status'] === 'queued') {
                $stageSummary[$stId]['queued'] += (int)$sr['count'];
            } elseif ($sr['status'] === 'failed') {
                $stageSummary[$stId]['failed'] += (int)$sr['count'];
            }
            if (!empty($sr['next_due']) && (empty($stageSummary[$stId]['next_due']) || $sr['next_due'] < $stageSummary[$stId]['next_due'])) {
                $stageSummary[$stId]['next_due'] = $sr['next_due'];
            }
        }

        // 2. Recent dispatch logs for timeline review
        $this->db->select('q.id, q.campaign_id, q.campaign_type, q.recipient_email, q.recipient_name, q.sender_email, q.status, q.sent_at, q.next_followup_date, q.lead_id, c.subject')
                 ->from('crm_bulk_mail_queue q')
                 ->join('crm_bulk_mail_campaigns c', 'c.id = q.campaign_id', 'left')
                 ->order_by('q.id', 'DESC')
                 ->limit(50);

        if ($filter_stage !== 'all') {
            $this->db->where('q.campaign_type', $filter_stage);
        }
        $logs = $this->db->get()->result_array();

        $this->json_success([
            'stages'  => array_values($stageSummary),
            'logs'    => $logs
        ]);
    }
}

