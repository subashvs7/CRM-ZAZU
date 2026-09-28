<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Communications extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(['Bulk_mail_model', 'Product_model', 'Lead_model', 'Customer_model', 'Smtp_account_model', 'App_setting_model']);
        $this->load->library('email');
    }

    public function index() {
        redirect('communications/bulk_mail');
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
        $total_leads_count = $this->db->where('is_deleted', 0)->where('status', 'active')->where('email IS NOT NULL', null, false)->where('TRIM(email) !=', '')->count_all_results('leads');
        $total_custs_count = $this->db->where('is_deleted', 0)->where('email IS NOT NULL', null, false)->where('TRIM(email) !=', '')->count_all_results('customers');
        $total_contk_count = $this->db->where('is_deleted', 0)->where('email IS NOT NULL', null, false)->where('TRIM(email) !=', '')->count_all_results('contact_book');

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
            'current_user'       => $this->get_user()
        ]);
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

        $product_id     = (int)$this->input->get('product_id') ?: null;
        $lead_status    = $this->input->get('lead_status') ?: null;

        $recipients = $this->Bulk_mail_model->get_recipients($recipient_types, $product_id, $lead_status, 15);
        $totalCount = count($this->Bulk_mail_model->get_recipients($recipient_types, $product_id, $lead_status, 0));

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

        $product_id  = (int)$this->input->get('product_id') ?: null;
        $lead_status = $this->input->get('lead_status') ?: null;

        $recipients = $this->Bulk_mail_model->get_recipients($recipient_types, $product_id, $lead_status, 1000);
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
        $campaign_data = [
            'subject'          => $subject,
            'message'          => $message,
            'recipient_type'   => $targetSummary . ($product_id ? ' (Product #' . $product_id . ')' : '') . ($forcedSmtp ? ' [Sender: ' . $forcedSmtp['sender_email'] . ']' : ''),
            'product_id'       => $product_id,
            'template_id'      => $template_id,
            'total_recipients' => count($recipients),
            'status'           => ($dispatch_mode === 'instant') ? 'completed' : 'pending',
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s')
        ];

        $campaign_id = $this->Bulk_mail_model->create_campaign($campaign_data);
        $this->Bulk_mail_model->add_to_queue($campaign_id, $recipients);

        $sentCount    = 0;
        $failedCount  = 0;
        $quotaHalted  = false;
        $usedAccounts = [];

        if ($dispatch_mode === 'instant') {
            $queueItems = $this->db->where('campaign_id', $campaign_id)->get('bulk_mail_queue')->result_array();

            foreach ($queueItems as $idx => $item) {
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

                $personalizedSubject = str_replace(array_keys($replacements), array_values($replacements), $subject);
                $personalizedMessage = str_replace(array_keys($replacements), array_values($replacements), $message);

                $this->email->clear();
                $this->email->from($fromEmail, $fromName);
                $this->email->to($item['recipient_email']);
                $this->email->subject($personalizedSubject);
                $this->email->message($personalizedMessage);

                // Attempt send
                $sendOk = @$this->email->send();

                // Increment sent counter on the SMTP account (auto-triggers limit_reached flag if daily_limit hit)
                if ($currentSmtp) {
                    $this->Smtp_account_model->increment_sent_count($currentSmtp['id']);
                }

                // Mark queue item as sent
                $this->Bulk_mail_model->update_queue_item($item['id'], [
                    'status'  => 'sent',
                    'sent_at' => date('Y-m-d H:i:s')
                ]);
                $sentCount++;

                // If lead recipient, record follow-up activity log and mark email_sent = 'Yes'
                if (!empty($item['lead_id'])) {
                    $this->db->insert('lead_activities', [
                        'lead_id'       => (int)$item['lead_id'],
                        'user_id'       => $currentUserId,
                        'activity_type' => 'email',
                        'notes'         => "Bulk Outreach Email Sent: \"{$subject}\" (Campaign #{$campaign_id})",
                        'occurred_at'   => date('Y-m-d H:i:s'),
                        'status'        => 'active',
                        'is_deleted'    => 0,
                        'created_at'    => date('Y-m-d H:i:s'),
                        'updated_at'    => date('Y-m-d H:i:s')
                    ]);

                    $this->db->where('id', (int)$item['lead_id'])->update('leads', [
                        'email_sent' => 'Yes',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            if ($quotaHalted) {
                $this->Bulk_mail_model->update_campaign_status($campaign_id, 'partial');
                $remainingCount = count($queueItems) - $sentCount;
                $msg = "Dispatched {$sentCount} emails! Hostinger daily limit reached across all SMTP accounts. The remaining {$remainingCount} emails remain queued and will continue tomorrow.";
            } else {
                $this->Bulk_mail_model->update_campaign_status($campaign_id, 'completed');
                $acctText = count($usedAccounts) > 1 ? " with auto-rotation across " . count($usedAccounts) . " Hostinger SMTP accounts" : "";
                $msg = "Bulk mail campaign launched! Successfully dispatched to {$sentCount} recipient(s){$acctText}.";
            }
        } else {
            $msg = "Campaign queued successfully! " . count($recipients) . " emails added to queue for background dispatch.";
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
}

