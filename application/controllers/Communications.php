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
        // Automatically check and queue any due follow-ups (next_followup_date <= today)
        $this->Bulk_mail_model->auto_queue_due_followups();

        $stats = $this->Bulk_mail_model->get_stats();
        $queue_sync = $this->Bulk_mail_model->get_queue_sync_status();
        $recent_campaigns = $this->Bulk_mail_model->get_recent_campaigns(50);
        $distinct_senders = $this->Bulk_mail_model->get_distinct_senders();
        $initial_logs = $this->Bulk_mail_model->get_delivery_logs([], 50, 0);
        $templates = $this->Bulk_mail_model->get_templates_by_product();
        $is_queue_paused = (int)$this->App_setting_model->get_by_key('queue_is_paused') === 1;
        $this->load_view('communications/history', [
            'page_title'       => 'Mail Dispatch History & Delivery Logs',
            'stats'            => $stats,
            'queue_sync'       => $queue_sync,
            'recent_campaigns' => $recent_campaigns,
            'distinct_senders' => $distinct_senders,
            'delivery_logs'    => $initial_logs['rows'],
            'total_logs'       => $initial_logs['total'],
            'templates'        => $templates,
            'outreach_stages'  => $this->_get_outreach_stages(),
            'is_queue_paused'  => $is_queue_paused,
            'page_js'          => 'communications'
        ]);
    }

    /**
     * AJAX: Assign or Update Follow-Up Template for an existing Campaign
     */
    public function update_campaign_followup_template_ajax() {
        $campaign_id          = (int)$this->input->post('campaign_id');
        $followup_template_id = (int)$this->input->post('followup_template_id') ?: null;
        $queue_followup_now   = (int)$this->input->post('queue_followup_now');

        if (!$campaign_id) {
            $this->json_error('Invalid Campaign ID.');
            return;
        }

        // 1. Update campaign table
        $campUpdates = ['updated_at' => date('Y-m-d H:i:s')];
        if ($this->db->field_exists('followup_template_id', 'crm_bulk_mail_campaigns')) {
            $campUpdates['followup_template_id'] = $followup_template_id;
        }
        $next_followup_days = $this->input->post('next_followup_days');
        if ($next_followup_days !== null && $next_followup_days !== '') {
            $campUpdates['next_followup_days'] = max(1, (int)$next_followup_days);
        }
        $this->db->where('id', $campaign_id)->update('crm_bulk_mail_campaigns', $campUpdates);

        // 2. Update existing queue rows with new follow-up template ID and recomputed next_followup_date
        if ($this->db->field_exists('followup_template_id', 'crm_bulk_mail_queue')) {
            $this->db->where('campaign_id', $campaign_id)->update('crm_bulk_mail_queue', [
                'followup_template_id' => $followup_template_id
            ]);
        }

        // Recalculate outreach next_followup_date based on sent_at and updated cadence
        $cadenceDays = isset($campUpdates['next_followup_days']) ? (int)$campUpdates['next_followup_days'] : 2;
        $outreachSent = $this->db->select('id, sent_at')->from('crm_bulk_mail_queue')
            ->where('campaign_id', $campaign_id)
            ->where('campaign_type', 'outreach')
            ->where('status', 'sent')
            ->get()->result_array();
        foreach ($outreachSent as $oi) {
            if (!empty($oi['sent_at'])) {
                $newTarget = date('Y-m-d', strtotime("+{$cadenceDays} days", strtotime($oi['sent_at'])));
                $this->db->where('id', $oi['id'])->update('crm_bulk_mail_queue', ['next_followup_date' => $newTarget]);
            }
        }

        $queuedNewCount = 0;

        // 3. If user opted to queue follow-ups now, find delivered recipients who haven't had a follow-up queued yet
        if ($queue_followup_now && $followup_template_id) {
            $delivered = $this->db->select('recipient_email, recipient_name, lead_id, customer_id')
                ->from('crm_bulk_mail_queue')
                ->where('campaign_id', $campaign_id)
                ->where('status', 'sent')
                ->where('campaign_type', 'outreach')
                ->get()->result_array();

            if (!empty($delivered)) {
                $hasFollowupCol = $this->db->field_exists('followup_template_id', 'crm_bulk_mail_queue');
                $newItems = [];
                foreach ($delivered as $d) {
                    $exists = $this->db->where('campaign_id', $campaign_id)
                        ->where('recipient_email', $d['recipient_email'])
                        ->where_in('campaign_type', ['followup_1', 'followup_2'])
                        ->count_all_results('crm_bulk_mail_queue');

                    if ($exists == 0) {
                        $row = [
                            'campaign_id'        => $campaign_id,
                            'campaign_type'      => 'followup_1',
                            'recipient_email'    => $d['recipient_email'],
                            'recipient_name'     => $d['recipient_name'] ?? '',
                            'lead_id'            => $d['lead_id'] ?? null,
                            'customer_id'        => $d['customer_id'] ?? null,
                            'next_followup_date' => null,
                            'status'             => 'queued',
                            'created_at'         => date('Y-m-d H:i:s')
                        ];
                        if ($hasFollowupCol) {
                            $row['followup_template_id'] = $followup_template_id;
                        }
                        $newItems[] = $row;
                    }
                }

                if (!empty($newItems)) {
                    $totalEligible   = count($newItems);
                    $partition_mode  = $this->input->post('followup_partition_mode') ?: 'all';
                    $partition_limit = (int)$this->input->post('followup_partition_limit');
                    $remainingHeld   = 0;

                    if ($partition_mode === 'custom' && $partition_limit > 0 && $totalEligible > $partition_limit) {
                        $newItems      = array_slice($newItems, 0, $partition_limit);
                        $remainingHeld = $totalEligible - count($newItems);
                    }

                    $this->db->insert_batch('crm_bulk_mail_queue', $newItems);
                    $queuedNewCount = count($newItems);
                    $this->Bulk_mail_model->check_and_update_campaign($campaign_id);
                }
            }
        }

        $tpl = $followup_template_id ? $this->Bulk_mail_model->get_template_by_id($followup_template_id) : null;
        $tplName = $tpl ? $tpl['name'] : 'None';

        $msg = 'Follow-up template successfully saved for Campaign #' . $campaign_id . '.';
        if ($queuedNewCount > 0) {
            $msg .= " {$queuedNewCount} follow-up email(s) added to background queue!";
            if (!empty($remainingHeld) && $remainingHeld > 0) {
                $msg .= " ({$remainingHeld} held pending for future batch).";
            }
        }

        $this->json_success([
            'campaign_id'          => $campaign_id,
            'followup_template_id' => $followup_template_id,
            'template_name'        => $tplName,
            'queued_count'         => $queuedNewCount
        ], $msg);
    }

    /**
     * AJAX: Filter and search delivery logs
     */
    public function delivery_logs_ajax() {
        try {
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
        } catch (Exception $e) {
            $this->json_error($e->getMessage());
        }
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
                !empty($r['next_followup_date']) ? $r['next_followup_date'] : 'Instant',
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
            '{{lead_name}}'           => 'Valued Partner',
            '{{name}}'                => 'Valued Partner',
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
        $this->email->subject($renderedSubject);
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

        // Check if user chose a specific SMTP mailbox — REQUIRED, no auto-rotate allowed
        $sender_smtp_id = $this->input->post('sender_smtp_id');
        $forcedSmtp = null;
        if (empty($sender_smtp_id) || $sender_smtp_id === 'auto') {
            if ($this->input->is_ajax_request()) {
                $this->json_error('Please select a specific sender mailbox before sending. Auto-rotate is disabled.');
            }
            $this->session->set_flashdata('error', 'Please select a specific sender mailbox.');
            redirect('communications/bulk_mail');
            return;
        }
        $forcedSmtp = $this->Smtp_account_model->get_by_id((int)$sender_smtp_id);
        if (!$forcedSmtp) {
            if ($this->input->is_ajax_request()) {
                $this->json_error('Selected sender mailbox not found. Please select a valid mailbox.');
            }
            redirect('communications/bulk_mail');
            return;
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

        $followup_schedule = $this->input->post('followup_schedule');
        $followup_template_id = null;
        if ($followup_schedule === 'none' || $followup_schedule === '0' || empty($followup_schedule)) {
            $next_followup_days = 0;
            $next_followup_date = null;
            $followup_template_id = null;
        } elseif ($followup_schedule === 'custom') {
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
            $followup_template_id = (int)$this->input->post('followup_template_id') ?: null;
        } elseif ($followup_schedule === 'custom_days') {
            $custom_days = max(1, (int)$this->input->post('custom_followup_days'));
            $next_followup_days = $custom_days;
            $next_followup_date = date('Y-m-d', strtotime("+{$custom_days} days"));
            $followup_template_id = (int)$this->input->post('followup_template_id') ?: null;
        } else {
            $days = max(1, (int)$followup_schedule);
            $next_followup_days = $days;
            $next_followup_date = date('Y-m-d', strtotime("+{$days} days"));
            $followup_template_id = (int)$this->input->post('followup_template_id') ?: null;
        }

        // Custom Partition / Batch Count control
        $partition_mode  = $this->input->post('partition_mode') ?: 'all';
        $partition_limit = (int)$this->input->post('partition_limit');
        $dispatch_target = ($partition_mode === 'custom' && $partition_limit > 0) ? min($partition_limit, count($recipients)) : count($recipients);

        $campaign_data = [
            'subject'                => $subject,
            'message'                => $message,
            'recipient_type'         => $targetSummary . ($product_id ? ' (Product #' . $product_id . ')' : '') . ($forcedSmtp ? ' [Sender: ' . $forcedSmtp['sender_email'] . ']' : ''),
            'campaign_type'          => $campaign_type,
            'next_followup_days'     => $next_followup_days,
            'product_id'             => $product_id,
            'template_id'            => $template_id,
            'forced_smtp_account_id' => $forcedSmtp ? $forcedSmtp['id'] : null,
            'total_recipients'       => count($recipients),
            'status'                 => ($dispatch_mode === 'instant') ? 'completed' : 'pending',
            'created_at'             => date('Y-m-d H:i:s'),
            'updated_at'             => date('Y-m-d H:i:s')
        ];
        if ($this->db->field_exists('followup_template_id', 'crm_bulk_mail_campaigns')) {
            $campaign_data['followup_template_id'] = $followup_template_id;
        }

        $campaign_id = $this->Bulk_mail_model->create_campaign($campaign_data);
        $this->Bulk_mail_model->add_to_queue($campaign_id, $recipients, $campaign_type, $next_followup_date, $followup_template_id);

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
                    // Re-fetch from DB on every iteration so sent_today is always current
                    $forcedSmtp = $this->Smtp_account_model->get_by_id((int)$forcedSmtp['id']);
                    if (!$forcedSmtp) {
                        $quotaHalted = true;
                        break;
                    }
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
                    '{{lead_name}}'           => $item['recipient_name'] ?: 'Lead',
                    '{{name}}'                => $item['recipient_name'] ?: 'Customer',
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
                    $followupNotePart = !empty($next_followup_date) ? " Next follow-up on {$next_followup_date}." : "";
                    $activityNote = $sendOk 
                        ? "{$typeTitle} Dispatched Successfully via {$fromEmail} [Ref: #{$anti_spam_hash}]: \"{$subject}\" (Campaign #{$campaign_id}). Status: Send Success ({$nowFormatted}).{$followupNotePart}"
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
                $followupMsgPart = !empty($next_followup_date) ? " Next follow-up on {$next_followup_date}." : "";
                $msg = "Bulk mail campaign launched! Successfully dispatched to {$sentCount} recipient(s){$acctText}.{$followupMsgPart}";
            }
        } else {
            $followupMsgPart = !empty($next_followup_date) ? " Next follow-up scheduled for {$next_followup_date}." : "";
            $msg = "Campaign queued successfully! " . count($recipients) . " emails added to queue for background dispatch.{$followupMsgPart}";
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
     * AJAX: Live refresh stats and campaign history with date filtering
     */
    public function history_ajax() {
        try {
            $date_filter = $this->input->get('date_filter') ?: 'all';
            $from_date   = $this->input->get('from_date');
            $to_date     = $this->input->get('to_date');

            $stats       = $this->Bulk_mail_model->get_stats($date_filter, $from_date, $to_date);
            $campaigns   = $this->Bulk_mail_model->get_recent_campaigns(50, $date_filter, $from_date, $to_date);
            $isPaused    = (int)$this->App_setting_model->get_by_key('queue_is_paused') === 1;

            $this->json_success([
                'stats'           => $stats,
                'campaigns'       => $campaigns,
                'is_queue_paused' => $isPaused
            ]);
        } catch (Exception $e) {
            $this->json_error($e->getMessage());
        }
    }

    /**
     * AJAX: Live SMTP Pool Quota & Status check for Bulk Mail Hub
     */
    public function smtp_pool_status_ajax() {
        $status = $this->Smtp_account_model->get_pool_status();
        $this->json_success($status);
    }

    /**
     * Internal / Centralized queue dispatcher proxy (default: 2 emails per minute)
     */
    public function _execute_queue_batch($limit = 2, $force = false) {
        return $this->Bulk_mail_model->execute_queue_batch($limit, $force);
    }

    /**
     * Cron Job / CLI Trigger for Background Queue Dispatch
     * CLI: php index.php communications process_queue_cron
     * Hostinger cPanel Web Cron: wget -q -O /dev/null "https://crm.zazutech.in/communications/process_queue_cron"
     */
    public function process_queue_cron() {
        $limit = (int)($this->input->get('limit') ?: 2); // 2 emails per 1 min = balanced safe pacing
        if ($limit < 1) $limit = 1;
        if ($limit > 10) $limit = 10;

        $result = $this->Bulk_mail_model->execute_queue_batch($limit, false);

        if (is_cli()) {
            echo "[" . date('Y-m-d H:i:s') . "] Status: " . $result['status'] 
               . " | Processed: " . $result['processed'] 
               . " | Remaining Queued: " . ($result['remaining'] ?? 0) 
               . (isset($result['paused']) ? " | Paused: " . $result['paused'] : "")
               . " | Message: " . $result['message'] . PHP_EOL;
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
        $force = (int)$this->input->get('force') === 1;
        $result = $this->_execute_queue_batch(2, $force);
        $this->json_success($result);
    }

    /**
     * AJAX: Get real-time synchronized background queue timing and counts from server
     */
    public function queue_sync_status_ajax() {
        // Automatically check and queue any due follow-ups (next_followup_date <= today)
        $this->Bulk_mail_model->auto_queue_due_followups();

        $sync = $this->Bulk_mail_model->get_queue_sync_status();
        $this->json_success($sync);
    }

    /**
     * AJAX: Toggle Global Background Queue Play / Pause
     */
    public function toggle_global_queue_ajax() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $action = $this->input->post('action'); // 'pause', 'resume', or 'toggle'
        $current = (int)$this->App_setting_model->get_by_key('queue_is_paused');

        if ($action === 'pause') {
            $new = 1;
        } elseif ($action === 'resume') {
            $new = 0;
        } else {
            $new = ($current === 1) ? 0 : 1;
        }

        $this->App_setting_model->set('queue_is_paused', (string)$new);
        $queuedCount = $this->db->where('status', 'queued')->count_all_results('crm_bulk_mail_queue');
        $pausedCount = $this->db->where('status', 'paused')->count_all_results('crm_bulk_mail_queue');

        $this->json_success([
            'is_paused'    => $new === 1,
            'queued_count' => $queuedCount,
            'paused_count' => $pausedCount
        ], $new === 1 ? 'Background queue paused. Automatic cron and browser timer dispatches are stopped.' : 'Background queue resumed. Dispatches will process normally.');
    }

    /**
     * AJAX: Pause or Resume Specific Campaign Queue with Custom Partition Support
     */
    public function toggle_campaign_queue_pause_ajax() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $campaign_id     = (int)$this->input->post('campaign_id');
        $action          = strtolower(trim($this->input->post('action'))); // 'pause' or 'resume'
        $partition_mode  = strtolower(trim($this->input->post('partition_mode') ?: 'all')); // 'all' or 'custom'
        $partition_limit = (int)$this->input->post('partition_limit');

        if (!$campaign_id) {
            $this->json_error('Invalid Campaign ID.');
            return;
        }

        $campaign = $this->db->where('id', $campaign_id)->get('crm_bulk_mail_campaigns')->row_array();
        if (!$campaign) {
            $this->json_error('Campaign not found.');
            return;
        }

        if ($action === 'pause') {
            $queuedCount = $this->db->where(['campaign_id' => $campaign_id, 'status' => 'queued'])->count_all_results('crm_bulk_mail_queue');
            if ($queuedCount === 0) {
                $this->json_error('No queued emails found to pause for this campaign.');
                return;
            }

            $pauseLimit = $queuedCount;
            if ($partition_mode === 'custom' && $partition_limit > 0) {
                $pauseLimit = min($partition_limit, $queuedCount);
            }

            // Update queue items
            if ($pauseLimit >= $queuedCount) {
                $this->db->where(['campaign_id' => $campaign_id, 'status' => 'queued'])
                    ->update('crm_bulk_mail_queue', ['status' => 'paused']);
            } else {
                $this->db->query("UPDATE crm_bulk_mail_queue SET status = 'paused' WHERE campaign_id = ? AND status = 'queued' ORDER BY id ASC LIMIT ?", [$campaign_id, $pauseLimit]);
            }

            $this->Bulk_mail_model->check_and_update_campaign($campaign_id);

            $newQueued = $this->db->where(['campaign_id' => $campaign_id, 'status' => 'queued'])->count_all_results('crm_bulk_mail_queue');
            $newPaused = $this->db->where(['campaign_id' => $campaign_id, 'status' => 'paused'])->count_all_results('crm_bulk_mail_queue');

            $msg = ($pauseLimit >= $queuedCount)
                ? "Campaign #{$campaign_id} paused completely ({$pauseLimit} emails paused)."
                : "Partition applied: Paused {$pauseLimit} emails for Campaign #{$campaign_id}. {$newQueued} emails remain active in processing queue.";

            $this->json_success([
                'campaign_id' => $campaign_id,
                'action'      => 'pause',
                'paused_now'  => $pauseLimit,
                'count_queued'=> $newQueued,
                'count_paused'=> $newPaused
            ], $msg);

        } elseif ($action === 'resume') {
            $pausedCount = $this->db->where(['campaign_id' => $campaign_id, 'status' => 'paused'])->count_all_results('crm_bulk_mail_queue');
            if ($pausedCount === 0) {
                $this->json_error('No paused emails found to resume for this campaign.');
                return;
            }

            $resumeLimit = $pausedCount;
            if ($partition_mode === 'custom' && $partition_limit > 0) {
                $resumeLimit = min($partition_limit, $pausedCount);
            }

            // Update queue items
            if ($resumeLimit >= $pausedCount) {
                $this->db->where(['campaign_id' => $campaign_id, 'status' => 'paused'])
                    ->update('crm_bulk_mail_queue', ['status' => 'queued']);
            } else {
                $this->db->query("UPDATE crm_bulk_mail_queue SET status = 'queued' WHERE campaign_id = ? AND status = 'paused' ORDER BY id ASC LIMIT ?", [$campaign_id, $resumeLimit]);
            }

            $this->Bulk_mail_model->check_and_update_campaign($campaign_id);

            $newQueued = $this->db->where(['campaign_id' => $campaign_id, 'status' => 'queued'])->count_all_results('crm_bulk_mail_queue');
            $newPaused = $this->db->where(['campaign_id' => $campaign_id, 'status' => 'paused'])->count_all_results('crm_bulk_mail_queue');

            $msg = ($resumeLimit >= $pausedCount)
                ? "Resumed all {$resumeLimit} paused emails for Campaign #{$campaign_id} into active queue."
                : "Partition applied: Resumed {$resumeLimit} emails for Campaign #{$campaign_id} into active queue. {$newPaused} emails remain paused.";

            $this->json_success([
                'campaign_id' => $campaign_id,
                'action'      => 'resume',
                'resumed_now' => $resumeLimit,
                'count_queued'=> $newQueued,
                'count_paused'=> $newPaused
            ], $msg);

        } else {
            $this->json_error('Invalid action specified.');
        }
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

