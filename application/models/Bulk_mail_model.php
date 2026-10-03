<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bulk_mail_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->ensure_paused_status_schema();
    }

    /**
     * Ensure status columns support 'paused' status smoothly across environments
     */
    public function ensure_paused_status_schema() {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        if ($this->db->table_exists('crm_bulk_mail_queue')) {
            $fieldData = $this->db->field_data('crm_bulk_mail_queue');
            foreach ($fieldData as $field) {
                if ($field->name === 'status' && strtolower($field->type) === 'enum') {
                    $this->db->query("ALTER TABLE crm_bulk_mail_queue MODIFY COLUMN status VARCHAR(50) DEFAULT 'queued'");
                    break;
                }
            }
        }
        if ($this->db->table_exists('crm_bulk_mail_campaigns')) {
            $fieldData = $this->db->field_data('crm_bulk_mail_campaigns');
            foreach ($fieldData as $field) {
                if ($field->name === 'status' && strtolower($field->type) === 'enum') {
                    $this->db->query("ALTER TABLE crm_bulk_mail_campaigns MODIFY COLUMN status VARCHAR(50) DEFAULT 'pending'");
                    break;
                }
            }
        }
    }

    /**
     * Fetch active templates, optionally filtered by product and/or category.
     */
    public function get_templates_by_product($product_id = null, $category = null) {
        $this->db->select('t.*, p.name AS product_name, p.sku AS product_sku')
            ->from('crm_notification_templates t')
            ->join('crm_products p', 'p.id = t.product_id', 'left')
            ->where('t.channel', 'email')
            ->where('t.is_deleted', 0)
            ->where('t.status', 'active');

        if (!empty($product_id)) {
            $this->db->group_start()
                ->where('t.product_id', $product_id)
                ->or_where('t.product_id IS NULL', null, false)
                ->group_end();
        }

        if (!empty($category) && $category !== 'all') {
            $this->db->where('t.category', $category);
        }

        $this->db->order_by('t.product_id', 'DESC'); // Product-specific templates first
        $this->db->order_by('t.id', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Alias for get_templates_by_product
     */
    public function get_templates($product_id = null, $category = null) {
        return $this->get_templates_by_product($product_id, $category);
    }

    /**
     * Get single template by ID
     */
    public function get_template_by_id($id) {
        return $this->db->select('t.*, p.name AS product_name, p.price AS product_price, p.website_url AS product_website')
            ->from('crm_notification_templates t')
            ->join('crm_products p', 'p.id = t.product_id', 'left')
            ->where('t.id', (int)$id)
            ->where('t.is_deleted', 0)
            ->get()->row_array();
    }

    /**
     * Save / create or update email template
     */
    public function save_template($data) {
        $id = !empty($data['id']) ? (int)$data['id'] : null;
        unset($data['id']);

        $data['channel']    = 'email';
        $data['status']     = $data['status'] ?? 'active';
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($id) {
            $this->db->where('id', $id)->update('crm_notification_templates', $data);
            return $id;
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['is_deleted'] = 0;
            $this->db->insert('crm_notification_templates', $data);
            return $this->db->insert_id();
        }
    }

    /**
     * Soft delete template
     */
    public function delete_template($id) {
        return $this->db->where('id', (int)$id)->update('crm_notification_templates', [
            'is_deleted' => 1,
            'status'     => 'deleted',
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Query audience recipients dynamically based on group and filters
     * Supports single or multi-audience selection with automatic email deduplication
     * and reach filters (all, never_sent, already_sent, failed, inbound, outbound)
     */
    public function get_recipients($recipient_types, $product_id = null, $lead_status = null, $limit = 2000, $reach_filter = 'all') {
        $recipients = [];
        $seenEmails = [];

        if (is_string($recipient_types)) {
            $types = array_map('trim', explode(',', $recipient_types));
        } else {
            $types = (array)$recipient_types;
        }

        $hasLeads       = false;
        $hasCustomers   = false;
        $activeCustOnly = false;
        $hasContactBook = false;

        foreach ($types as $t) {
            $tNorm = strtolower($t);
            if (strpos($tNorm, 'lead') !== false) {
                $hasLeads = true;
            }
            if (strpos($tNorm, 'customer') !== false) {
                $hasCustomers = true;
                if (strpos($tNorm, 'active') !== false) {
                    $activeCustOnly = true;
                }
            }
            if (strpos($tNorm, 'contact') !== false) {
                $hasContactBook = true;
            }
        }

        $reach_filter = strtolower(trim($reach_filter ?: 'all'));

        // 1. Process Leads
        if ($hasLeads) {
            $this->db->select("l.id AS lead_id, l.email, 
                TRIM(CONCAT(COALESCE(l.first_name, ''), ' ', COALESCE(l.last_name, ''))) AS full_name,
                l.first_name, l.last_name, l.company_name, l.title, l.corporate_phone AS phone,
                l.product_id, p.name AS product_name, p.price AS product_price, l.lead_status,
                l.source AS lead_source, l.email_sent, l.email_bounced,
                (SELECT q.status FROM crm_bulk_mail_queue q WHERE q.lead_id = l.id ORDER BY q.id DESC LIMIT 1) AS last_delivery_status,
                (SELECT q.sent_at FROM crm_bulk_mail_queue q WHERE q.lead_id = l.id AND q.status = 'sent' ORDER BY q.id DESC LIMIT 1) AS last_sent_at,
                (SELECT COUNT(q.id) FROM crm_bulk_mail_queue q WHERE q.lead_id = l.id AND q.status = 'sent') AS outreach_count")
                ->from('crm_leads l')
                ->join('crm_products p', 'p.id = l.product_id', 'left')
                ->where('l.is_deleted', 0)
                ->where('l.status', 'active')
                ->where('l.email IS NOT NULL', null, false)
                ->where('TRIM(l.email) !=', '');

            if (!empty($product_id)) {
                $this->db->where('l.product_id', (int)$product_id);
            }
            if (!empty($lead_status) && $lead_status !== 'all') {
                $this->db->where('l.lead_status', $lead_status);
            }

            // Apply Reach & Direction Filters
            if ($reach_filter === 'never_sent' || $reach_filter === 'unsent') {
                $this->db->where("( (l.email_sent IS NULL OR l.email_sent = '' OR l.email_sent = 'No' OR l.email_sent LIKE '%Fail%') AND NOT EXISTS (SELECT 1 FROM crm_bulk_mail_queue q WHERE q.lead_id = l.id AND q.status = 'sent') )", null, false);
            } elseif ($reach_filter === 'already_sent' || $reach_filter === 'followup') {
                $this->db->where("( (l.email_sent = 'Yes' OR l.email_sent LIKE '%Success%') OR EXISTS (SELECT 1 FROM crm_bulk_mail_queue q WHERE q.lead_id = l.id AND q.status = 'sent') )", null, false);
            } elseif ($reach_filter === 'failed' || $reach_filter === 'retry') {
                $this->db->where("( (l.email_bounced = 'Yes' OR l.email_sent LIKE '%Fail%') OR EXISTS (SELECT 1 FROM crm_bulk_mail_queue q WHERE q.lead_id = l.id AND q.status = 'failed') )", null, false);
            } elseif ($reach_filter === 'inbound') {
                $this->db->where_in('l.source', ['walk_in', 'call', 'online']);
            } elseif ($reach_filter === 'outbound') {
                $this->db->where("(l.source IN ('field', 'referral') OR l.source IS NULL)", null, false);
            }

            $this->db->order_by('l.id', 'DESC');
            if ($limit > 0) $this->db->limit($limit);

            $rows = $this->db->get()->result_array();
            foreach ($rows as $r) {
                $email = strtolower(trim($r['email']));
                if (!isset($seenEmails[$email])) {
                    $seenEmails[$email] = true;
                    $name = !empty($r['full_name']) ? $r['full_name'] : ($r['title'] ?: ($r['company_name'] ?: 'Lead #' . $r['lead_id']));

                    $reach = 'never_sent';
                    if ($r['last_delivery_status'] === 'failed' || (!empty($r['email_bounced']) && $r['email_bounced'] === 'Yes') || (!empty($r['email_sent']) && stripos($r['email_sent'], 'fail') !== false)) {
                        $reach = 'failed';
                    } elseif ($r['last_delivery_status'] === 'sent' || (!empty($r['email_sent']) && (in_array(strtolower($r['email_sent']), ['yes', 'sent', 'true']) || stripos($r['email_sent'], 'success') !== false))) {
                        $reach = 'already_sent';
                    }

                    $direction = (!empty($r['lead_source']) && in_array($r['lead_source'], ['walk_in', 'call', 'online'])) ? 'inbound' : 'outbound';

                    $leadFirst = !empty($r['first_name']) ? trim(explode(' ', trim($r['first_name']))[0]) : trim(explode(' ', trim($name))[0]);
                    $recipients[] = [
                        'key'             => 'lead_' . $r['lead_id'],
                        'type'            => 'lead',
                        'source_type'     => 'Lead',
                        'lead_id'         => (int)$r['lead_id'],
                        'customer_id'     => null,
                        'email'           => trim($r['email']),
                        'name'            => $name,
                        'first_name'      => $leadFirst,
                        'last_name'       => $r['last_name'] ?? '',
                        'company'         => $r['company_name'] ?? '',
                        'phone'           => $r['phone'] ?? '',
                        'product_id'      => $r['product_id'],
                        'product_name'    => $r['product_name'] ?? '',
                        'product_price'   => $r['product_price'] ? format_inr($r['product_price']) : '',
                        'status'          => $r['lead_status'] ?? 'new',
                        'reach_status'    => $reach,
                        'direction'       => $direction,
                        'lead_source'     => $r['lead_source'] ?: 'Manual Lead',
                        'outreach_count'  => (int)($r['outreach_count'] ?? 0),
                        'last_sent_at'    => $r['last_sent_at'] ? date('d M Y', strtotime($r['last_sent_at'])) : null
                    ];
                }
            }
        }

        // 2. Process Customers
        if ($hasCustomers && $reach_filter !== 'inbound') {
            $this->db->select("c.id AS customer_id, c.email, c.customer_name, c.customer_org_name, c.phone, c.status,
                (SELECT q.status FROM crm_bulk_mail_queue q WHERE q.customer_id = c.id ORDER BY q.id DESC LIMIT 1) AS last_delivery_status,
                (SELECT q.sent_at FROM crm_bulk_mail_queue q WHERE q.customer_id = c.id AND q.status = 'sent' ORDER BY q.id DESC LIMIT 1) AS last_sent_at,
                (SELECT COUNT(q.id) FROM crm_bulk_mail_queue q WHERE q.customer_id = c.id AND q.status = 'sent') AS outreach_count")
                ->from('crm_customers c')
                ->where('c.is_deleted', 0)
                ->where('c.email IS NOT NULL', null, false)
                ->where('TRIM(c.email) !=', '');

            if ($activeCustOnly) {
                $this->db->where('c.status', 'active');
            }

            if ($reach_filter === 'never_sent' || $reach_filter === 'unsent') {
                $this->db->where("NOT EXISTS (SELECT 1 FROM crm_bulk_mail_queue q WHERE q.customer_id = c.id AND q.status = 'sent')", null, false);
            } elseif ($reach_filter === 'already_sent' || $reach_filter === 'followup') {
                $this->db->where("EXISTS (SELECT 1 FROM crm_bulk_mail_queue q WHERE q.customer_id = c.id AND q.status = 'sent')", null, false);
            } elseif ($reach_filter === 'failed' || $reach_filter === 'retry') {
                $this->db->where("EXISTS (SELECT 1 FROM crm_bulk_mail_queue q WHERE q.customer_id = c.id AND q.status = 'failed')", null, false);
            }

            $this->db->order_by('c.id', 'DESC');
            if ($limit > 0) $this->db->limit($limit);

            $rows = $this->db->get()->result_array();
            foreach ($rows as $r) {
                $email = strtolower(trim($r['email']));
                if (!isset($seenEmails[$email])) {
                    $seenEmails[$email] = true;
                    $name = !empty($r['customer_name']) ? $r['customer_name'] : ($r['customer_org_name'] ?: 'Customer #' . $r['customer_id']);

                    $reach = 'never_sent';
                    if ($r['last_delivery_status'] === 'failed') {
                        $reach = 'failed';
                    } elseif ($r['last_delivery_status'] === 'sent') {
                        $reach = 'already_sent';
                    }

                    $cParts = explode(' ', trim($name));
                    $custFirst = !empty($cParts[0]) ? trim($cParts[0]) : $name;
                    $custLast  = count($cParts) > 1 ? trim(implode(' ', array_slice($cParts, 1))) : '';

                    $recipients[] = [
                        'key'             => 'customer_' . $r['customer_id'],
                        'type'            => 'customer',
                        'source_type'     => 'Customer',
                        'lead_id'         => null,
                        'customer_id'     => (int)$r['customer_id'],
                        'email'           => trim($r['email']),
                        'name'            => $name,
                        'first_name'      => $custFirst,
                        'last_name'       => $custLast,
                        'company'         => $r['customer_org_name'] ?? '',
                        'phone'           => $r['phone'] ?? '',
                        'product_id'      => null,
                        'product_name'    => '',
                        'product_price'   => '',
                        'status'          => $r['status'] ?? 'active',
                        'reach_status'    => $reach,
                        'direction'       => 'outbound',
                        'lead_source'     => 'Customer Account',
                        'outreach_count'  => (int)($r['outreach_count'] ?? 0),
                        'last_sent_at'    => $r['last_sent_at'] ? date('d M Y', strtotime($r['last_sent_at'])) : null
                    ];
                }
            }
        }

        // 3. Process Contact Book
        if ($hasContactBook && in_array($reach_filter, ['all', 'never_sent', 'outbound'])) {
            $this->db->select("cb.id AS contact_id, cb.email, cb.name, cb.company_name, cb.phone, cb.job_title")
                ->from('crm_contact_book cb')
                ->where('cb.is_deleted', 0)
                ->where('cb.email IS NOT NULL', null, false)
                ->where('TRIM(cb.email) !=', '')
                ->order_by('cb.id', 'DESC');

            if ($limit > 0) $this->db->limit($limit);

            $rows = $this->db->get()->result_array();
            foreach ($rows as $r) {
                $email = strtolower(trim($r['email']));
                if (!isset($seenEmails[$email])) {
                    $seenEmails[$email] = true;
                    $cbParts = explode(' ', trim($r['name']));
                    $cbFirst = !empty($cbParts[0]) ? trim($cbParts[0]) : $r['name'];
                    $cbLast  = count($cbParts) > 1 ? trim(implode(' ', array_slice($cbParts, 1))) : '';

                    $recipients[] = [
                        'key'             => 'contact_' . $r['contact_id'],
                        'type'            => 'contact_book',
                        'source_type'     => 'Contact Book',
                        'lead_id'         => null,
                        'customer_id'     => null,
                        'contact_id'      => (int)$r['contact_id'],
                        'email'           => trim($r['email']),
                        'name'            => $r['name'],
                        'first_name'      => $cbFirst,
                        'last_name'       => $cbLast,
                        'company'         => $r['company_name'] ?? '',
                        'phone'           => $r['phone'] ?? '',
                        'product_id'      => null,
                        'product_name'    => '',
                        'product_price'   => '',
                        'status'          => 'active',
                        'reach_status'    => 'never_sent',
                        'direction'       => 'outbound',
                        'lead_source'     => 'Contact Book',
                        'outreach_count'  => 0,
                        'last_sent_at'    => null
                    ];
                }
            }
        }

        return $recipients;
    }

    /**
     * Create campaign and add recipients to queue
     */
    public function create_campaign($data) {
        $this->db->insert('crm_bulk_mail_campaigns', $data);
        return $this->db->insert_id();
    }

    public function add_to_queue($campaign_id, $recipients, $campaign_type = 'outreach', $next_followup_date = null, $followup_template_id = null) {
        if (empty($recipients)) return false;

        $hasFollowupCol = $this->db->field_exists('followup_template_id', 'crm_bulk_mail_queue');
        $batch_data = [];
        foreach ($recipients as $r) {
            $item = [
                'campaign_id'        => $campaign_id,
                'campaign_type'      => $campaign_type,
                'recipient_email'    => $r['email'],
                'recipient_name'     => $r['name'] ?? '',
                'lead_id'            => $r['lead_id'] ?? null,
                'customer_id'        => $r['customer_id'] ?? null,
                'next_followup_date' => $next_followup_date,
                'status'             => 'queued',
                'created_at'         => date('Y-m-d H:i:s')
            ];
            if ($hasFollowupCol && $followup_template_id !== null) {
                $item['followup_template_id'] = $followup_template_id;
            }
            $batch_data[] = $item;
        }

        // Chunk in blocks of 200 for safe SQL batch insert
        $chunks = array_chunk($batch_data, 200);
        foreach ($chunks as $chunk) {
            $this->db->insert_batch('crm_bulk_mail_queue', $chunk);
        }
        return true;
    }

    /**
     * Get aggregate statistics across campaigns with date filtering
     */
    public function get_stats($date_filter = 'all', $from_date = null, $to_date = null) {
        $stats = [
            'total'     => 0,
            'sent'      => 0,
            'queued'    => 0,
            'paused'    => 0,
            'failed'    => 0,
            'campaigns' => 0
        ];

        // 1. Queue statuses
        $this->db->select('status, count(id) as count');
        $this->db->from('crm_bulk_mail_queue');
        $this->_apply_stats_date_filter($this->db, $date_filter, $from_date, $to_date, 'queue');
        $this->db->group_by('status');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            if ($row->status == 'sent')   $stats['sent']   = (int)$row->count;
            if ($row->status == 'queued') $stats['queued'] = (int)$row->count;
            if ($row->status == 'paused') $stats['paused'] = (int)$row->count;
            if ($row->status == 'failed') $stats['failed'] = (int)$row->count;
        }
        $stats['total'] = $stats['sent'] + $stats['queued'] + $stats['paused'] + $stats['failed'];

        // 2. Total campaigns
        $this->db->from('crm_bulk_mail_campaigns');
        $this->_apply_stats_date_filter($this->db, $date_filter, $from_date, $to_date, 'campaign');
        $stats['campaigns'] = (int)$this->db->count_all_results();

        return $stats;
    }

    /**
     * Helper to apply date filtering for stats and campaigns
     */
    protected function _apply_stats_date_filter(&$db, $filter, $from_date = null, $to_date = null, $type = 'queue') {
        $today = date('Y-m-d');
        if ($type === 'queue') {
            $dateCol = 'COALESCE(sent_at, created_at)';
        } elseif ($type === 'campaign_table') {
            $dateCol = 'c.created_at';
        } else {
            $dateCol = 'created_at';
        }

        switch ($filter) {
            case 'today':
                $db->where("DATE({$dateCol}) =", $today);
                break;
            case 'yesterday':
                $yesterday = date('Y-m-d', strtotime('-1 day'));
                $db->where("DATE({$dateCol}) =", $yesterday);
                break;
            case 'this_week':
                $startOfWeek = date('Y-m-d', strtotime('monday this week'));
                $db->where("DATE({$dateCol}) >=", $startOfWeek);
                break;
            case 'this_month':
                $startOfMonth = date('Y-m-01');
                $db->where("DATE({$dateCol}) >=", $startOfMonth);
                break;
            case 'custom':
                if (!empty($from_date)) {
                    $db->where("DATE({$dateCol}) >=", $from_date);
                }
                if (!empty($to_date)) {
                    $db->where("DATE({$dateCol}) <=", $to_date);
                }
                break;
            case 'all':
            default:
                // No date restriction
                break;
        }
    }

    /**
     * Get list of recent campaigns with rich metadata, next follow-up dates, and optional date filter
     */
    public function get_recent_campaigns($limit = 50, $date_filter = 'all', $from_date = null, $to_date = null) {
        $hasFollowupCol = $this->db->field_exists('followup_template_id', 'crm_bulk_mail_campaigns');
        $ftSelect = $hasFollowupCol ? ', ft.name AS followup_template_name' : '';

        $this->db->select('c.*, p.name AS product_name, t.name AS template_name' . $ftSelect . ',
            COUNT(q.id) AS queue_total,
            SUM(CASE WHEN q.status = "sent" THEN 1 ELSE 0 END) AS count_sent,
            SUM(CASE WHEN q.status = "failed" THEN 1 ELSE 0 END) AS count_failed,
            SUM(CASE WHEN q.status = "queued" THEN 1 ELSE 0 END) AS count_queued,
            SUM(CASE WHEN q.status = "paused" THEN 1 ELSE 0 END) AS count_paused,
            MIN(CASE WHEN q.next_followup_date IS NOT NULL THEN q.next_followup_date END) AS next_followup_date')
            ->from('crm_bulk_mail_campaigns c')
            ->join('crm_products p', 'p.id = c.product_id', 'left')
            ->join('crm_notification_templates t', 't.id = c.template_id', 'left');

        if ($hasFollowupCol) {
            $this->db->join('crm_notification_templates ft', 'ft.id = c.followup_template_id', 'left');
        }

        $this->db->join('crm_bulk_mail_queue q', 'q.campaign_id = c.id', 'left');

        if (!empty($date_filter) && $date_filter !== 'all') {
            $this->_apply_stats_date_filter($this->db, $date_filter, $from_date, $to_date, 'campaign_table');
        }

        $this->db->group_by('c.id')
            ->order_by('c.created_at', 'DESC');

        if ($limit > 0) $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    /**
     * Get detailed campaign information and recipient queue
     */
    public function get_campaign_detail($campaign_id) {
        $hasFollowupCol = $this->db->field_exists('followup_template_id', 'crm_bulk_mail_campaigns');
        $ftSelect = $hasFollowupCol ? ', ft.name AS followup_template_name' : '';

        $this->db->select('c.*, p.name AS product_name, t.name AS template_name' . $ftSelect)
            ->from('crm_bulk_mail_campaigns c')
            ->join('crm_products p', 'p.id = c.product_id', 'left')
            ->join('crm_notification_templates t', 't.id = c.template_id', 'left');

        if ($hasFollowupCol) {
            $this->db->join('crm_notification_templates ft', 'ft.id = c.followup_template_id', 'left');
        }

        $campaign = $this->db->where('c.id', (int)$campaign_id)->get()->row_array();

        if (!$campaign) return null;

        $items = $this->db->select('q.*, l.title AS lead_title, cust.customer_name')
            ->from('crm_bulk_mail_queue q')
            ->join('crm_leads l', 'l.id = q.lead_id', 'left')
            ->join('crm_customers cust', 'cust.id = q.customer_id', 'left')
            ->where('q.campaign_id', (int)$campaign_id)
            ->order_by('q.id', 'ASC')
            ->get()->result_array();

        $campaign['items'] = $items;
        return $campaign;
    }

    /**
     * Get queue items pending for delivery
     */
    public function get_pending_queue($limit = 50) {
        $this->db->select('q.*, c.subject, c.message, c.product_id, c.template_id')
            ->from('crm_bulk_mail_queue q')
            ->join('crm_bulk_mail_campaigns c', 'c.id = q.campaign_id')
            ->where('q.status', 'queued')
            ->limit($limit);
        return $this->db->get()->result_array();
    }

    public function update_queue_item($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('crm_bulk_mail_queue', $data);
    }

    public function update_campaign_status($id, $status) {
        $this->db->where('id', $id);
        return $this->db->update('crm_bulk_mail_campaigns', ['status' => $status]);
    }

    public function check_and_update_campaign($campaign_id) {
        $queuedCount = $this->db->where(['campaign_id' => (int)$campaign_id, 'status' => 'queued'])->count_all_results('crm_bulk_mail_queue');
        $pausedCount = $this->db->where(['campaign_id' => (int)$campaign_id, 'status' => 'paused'])->count_all_results('crm_bulk_mail_queue');

        if ($queuedCount == 0 && $pausedCount == 0) {
            $this->update_campaign_status($campaign_id, 'completed');
        } elseif ($queuedCount == 0 && $pausedCount > 0) {
            $this->update_campaign_status($campaign_id, 'paused');
        } else {
            $this->update_campaign_status($campaign_id, 'processing');
        }
    }

    /**
     * Generate unique anti-spam reference code (e.g. ZAZU-8K2M)
     */
    public function generate_anti_spam_hash() {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $rand = '';
        for ($i = 0; $i < 5; $i++) {
            $rand .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return 'ZAZU-' . $rand;
    }

    /**
     * Get distinct sender emails used in dispatches for filter dropdown
     */
    public function get_distinct_senders() {
        $res = $this->db->distinct()
            ->select('sender_email')
            ->from('crm_bulk_mail_queue')
            ->where('sender_email IS NOT NULL', null, false)
            ->where('sender_email !=', '')
            ->get()->result_array();
        return array_column($res, 'sender_email');
    }

    /**
     * Get detailed delivery logs with filters for History page
     */
    public function get_delivery_logs($params = [], $limit = 50, $offset = 0) {
        $hasFollowupCol = $this->db->field_exists('followup_template_id', 'crm_bulk_mail_campaigns');
        $ftSelect = $hasFollowupCol ? ', c.followup_template_id, ft.name AS followup_template_name' : '';

        $this->db->select('q.*, c.subject AS campaign_subject, c.recipient_type, COALESCE(q.campaign_type, c.campaign_type) AS campaign_type, p.name AS product_name, p.sku AS product_sku, sa.name AS sender_mailbox_name' . $ftSelect)
            ->from('crm_bulk_mail_queue q')
            ->join('crm_bulk_mail_campaigns c', 'c.id = q.campaign_id', 'left')
            ->join('crm_products p', 'p.id = c.product_id', 'left')
            ->join('crm_smtp_accounts sa', 'sa.id = q.smtp_account_id', 'left');

        if ($hasFollowupCol) {
            $this->db->join('crm_notification_templates ft', 'ft.id = c.followup_template_id', 'left');
        }

        // Filter: Status
        if (!empty($params['status']) && $params['status'] !== 'all') {
            $this->db->where('q.status', $params['status']);
        }

        // Filter: Campaign Type
        if (!empty($params['campaign_type']) && $params['campaign_type'] !== 'all') {
            $this->db->where('COALESCE(q.campaign_type, c.campaign_type)', $params['campaign_type']);
        }

        // Filter: Sender Mailbox
        if (!empty($params['sender_email']) && $params['sender_email'] !== 'all') {
            $this->db->where('q.sender_email', $params['sender_email']);
        }

        // Filter: Date Range
        if (!empty($params['from_date'])) {
            $this->db->where('DATE(COALESCE(q.sent_at, q.created_at)) >=', $params['from_date']);
        }
        if (!empty($params['to_date'])) {
            $this->db->where('DATE(COALESCE(q.sent_at, q.created_at)) <=', $params['to_date']);
        }

        // Filter: Follow-Up Due
        if (!empty($params['followup_due']) && $params['followup_due'] !== 'all') {
            $today = date('Y-m-d');
            if ($params['followup_due'] === 'today') {
                $this->db->where('q.next_followup_date', $today);
            } elseif ($params['followup_due'] === 'overdue') {
                $this->db->where('q.next_followup_date <', $today);
                $this->db->where('q.next_followup_date IS NOT NULL', null, false);
            } elseif ($params['followup_due'] === 'upcoming') {
                $this->db->where('q.next_followup_date >', $today);
            } elseif ($params['followup_due'] === 'has_followup') {
                $this->db->where('q.next_followup_date IS NOT NULL', null, false);
            }
        }

        // Filter: Search keyword
        if (!empty($params['search'])) {
            $s = trim($params['search']);
            $this->db->group_start()
                ->like('q.recipient_email', $s)
                ->or_like('q.recipient_name', $s)
                ->or_like('q.anti_spam_hash', $s)
                ->or_like('q.sender_email', $s)
                ->or_like('c.subject', $s)
                ->group_end();
        }

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('q.id', 'DESC')->limit($limit, $offset);
        $rows = $this->db->get()->result_array();

        return [
            'total' => $total,
            'rows'  => $rows
        ];
    }

    /**
     * Centralized Background Queue Processor (Supports Web AJAX, CLI, and Server Cron)
     * Handles Hostinger SMTP pool rotation, daily quotas, personalization tokens, anti-spam hash, lead activities, and campaign completion.
     */
    public function execute_queue_batch($limit = 2) {
        $this->load->model(['Smtp_account_model', 'App_setting_model']);
        $this->load->library('email');
        $this->load->helper(['crm', 'url']);

        // Global Queue Pause Gate
        $isQueuePaused = (int)$this->App_setting_model->get_by_key('queue_is_paused');
        if ($isQueuePaused === 1) {
            $queuedCount = $this->db->where('status', 'queued')->count_all_results('crm_bulk_mail_queue');
            $pausedCount = $this->db->where('status', 'paused')->count_all_results('crm_bulk_mail_queue');
            $sync = $this->get_queue_sync_status();
            return array_merge([
                'status'    => 'paused',
                'message'   => 'Anti-ban queue is currently paused globally. Dispatches are temporarily halted.',
                'processed' => 0,
                'remaining' => $queuedCount,
                'paused'    => $pausedCount,
                'is_paused' => true
            ], $sync);
        }

        // Fetch up to $limit pending items — also pull forced_smtp_account_id and followup_template_id from campaign
        $extraCampSelect = $this->db->field_exists('followup_template_id', 'crm_bulk_mail_campaigns') ? ', c.followup_template_id as camp_followup_template_id' : '';
        $items = $this->db->select('q.*, c.subject, c.message, c.product_id, c.template_id, c.recipient_type, c.forced_smtp_account_id' . $extraCampSelect)
            ->from('crm_bulk_mail_queue q')
            ->join('crm_bulk_mail_campaigns c', 'c.id = q.campaign_id')
            ->where('q.status', 'queued')
            ->order_by('q.id', 'ASC')
            ->limit($limit)
            ->get()->result_array();

        if (empty($items)) {
            $queuedCount = $this->db->where('status', 'queued')->count_all_results('crm_bulk_mail_queue');
            $pausedCount = $this->db->where('status', 'paused')->count_all_results('crm_bulk_mail_queue');
            $sync = $this->get_queue_sync_status();
            return array_merge([
                'status'    => 'idle',
                'message'   => $pausedCount > 0 
                    ? "Queue has 0 active items, but {$pausedCount} email(s) are PAUSED in campaign(s). Click 'Resume' on the campaign to dispatch them."
                    : 'Queue is empty. No pending emails to dispatch.',
                'processed' => 0,
                'remaining' => $queuedCount,
                'paused'    => $pausedCount
            ], $sync);
        }

        $processed = 0;
        $details = [];

        foreach ($items as $item) {
            // SENDER SELECTION: Use campaign's forced mailbox ONLY (Strict Sender Lock - No Auto-Switch)
            $currentSmtp = null;
            if (!empty($item['forced_smtp_account_id'])) {
                // Campaign was sent with a specific mailbox — use ONLY that mailbox
                $currentSmtp = $this->Smtp_account_model->get_by_id((int)$item['forced_smtp_account_id']);
                if (!$currentSmtp || $currentSmtp['status'] === 'disabled') {
                    $mboxEmail = $currentSmtp['sender_email'] ?? ('Account #' . $item['forced_smtp_account_id']);
                    return [
                        'status'    => 'paused',
                        'message'   => "Selected sender mailbox ({$mboxEmail}) is disabled. Queue is held safely without auto-switching to another mailbox.",
                        'processed' => $processed,
                        'remaining' => $this->db->where('status', 'queued')->count_all_results('crm_bulk_mail_queue'),
                        'details'   => $details
                    ];
                }
                $rem = max(0, (int)$currentSmtp['daily_limit'] - (int)$currentSmtp['sent_today']);
                if ($rem <= 0) {
                    return [
                        'status'    => 'quota_exhausted',
                        'message'   => 'Selected sender mailbox (' . $currentSmtp['sender_email'] . ') has reached its daily limit (' . $currentSmtp['daily_limit'] . ' emails). Queue will pause and will NOT auto-switch to another mailbox.',
                        'processed' => $processed,
                        'remaining' => $this->db->where('status', 'queued')->count_all_results('crm_bulk_mail_queue'),
                        'details'   => $details
                    ];
                }
            } else {
                // Fallback only if campaign has no mailbox attached
                $currentSmtp = $this->Smtp_account_model->get_next_available_account();
                $poolStatus  = $this->Smtp_account_model->get_pool_status();
                if ($poolStatus['total_accounts'] > 0 && !$currentSmtp) {
                    return [
                        'status'    => 'quota_exhausted',
                        'message'   => 'All Hostinger SMTP mailboxes have reached their daily sending limits. Queued emails will resume automatically.',
                        'processed' => $processed,
                        'remaining' => $this->db->where('status', 'queued')->count_all_results('crm_bulk_mail_queue'),
                        'details'   => $details
                    ];
                }
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
                    $productPrice = function_exists('format_inr') ? format_inr($prod['price']) : ('₹' . number_format($prod['price'], 2));
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

            $anti_spam_hash = $this->generate_anti_spam_hash();
            $next_followup_date = !empty($item['next_followup_date']) ? $item['next_followup_date'] : null;

            $leadFullName = '';
            if ($leadData) {
                $leadFullName = trim(($leadData['first_name'] ?? '') . ' ' . ($leadData['last_name'] ?? ''));
                if (!$leadFullName) $leadFullName = $leadData['company_name'] ?? '';
            }
            $recipientName = $item['recipient_name'] ?: ($custData['customer_name'] ?? ($leadFullName ?: 'Customer'));

            $replacements = [
                '{{customer_name}}'       => $recipientName,
                '{{lead_name}}'           => $recipientName,
                '{{name}}'                => $recipientName,
                '{{first_name}}'          => $leadData['first_name'] ?? ($recipientName ?: 'Customer'),
                '{{last_name}}'           => $leadData['last_name'] ?? '',
                '{{company_name}}'        => $custData['customer_org_name'] ?? ($leadData['company_name'] ?? 'Company'),
                '{{email}}'               => $item['recipient_email'],
                '{{phone}}'               => $custData['phone'] ?? ($leadData['corporate_phone'] ?? ($leadData['company_phone'] ?? '')),
                '{{product_name}}'        => $productName,
                '{{product_price}}'       => $productPrice,
                '{{login_url}}'           => base_url('auth/login'),
                '{{login_email}}'         => $item['recipient_email'],
                '{{temporary_password}}'  => 'Zazu@' . rand(1000, 9999),
                '{{sender_name}}'         => $fromName,
                '{{sender_phone}}'        => '',
                '{{current_date}}'        => date('d M Y')
            ];

            $rawSubject = $item['subject'];
            $rawMessage = $item['message'];

            // If a follow-up template is attached, and this item is a follow-up (or has follow-up template ID):
            $followupTplId = !empty($item['followup_template_id']) ? (int)$item['followup_template_id'] : (!empty($item['camp_followup_template_id']) ? (int)$item['camp_followup_template_id'] : 0);

            if ($followupTplId > 0 && (strpos($item['campaign_type'] ?? '', 'followup') !== false || !empty($item['next_followup_date']))) {
                $fTpl = $this->db->get_where('crm_notification_templates', ['id' => $followupTplId])->row_array();
                if ($fTpl && !empty($fTpl['subject']) && !empty($fTpl['body'])) {
                    $rawSubject = $fTpl['subject'];
                    $rawMessage = $fTpl['body'];
                }
            }

            $personalizedSubject = str_replace(array_keys($replacements), array_values($replacements), $rawSubject);
            $personalizedMessage = str_replace(array_keys($replacements), array_values($replacements), $rawMessage);

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
            $this->update_queue_item($item['id'], [
                'sender_email'       => $fromEmail,
                'smtp_account_id'   => $currentSmtp ? $currentSmtp['id'] : null,
                'anti_spam_hash'     => $anti_spam_hash,
                'status'             => $queueStatus,
                'sent_at'            => date('Y-m-d H:i:s'),
                'next_followup_date' => $next_followup_date
            ]);

            // Update campaign status
            $this->check_and_update_campaign($item['campaign_id']);

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

                $followupNotePart = !empty($next_followup_date) ? " Next follow-up on {$next_followup_date}." : "";
                $activityNote = $sendOk 
                    ? "Queued {$typeTitle} Dispatched Successfully via {$fromEmail} [Ref: #{$anti_spam_hash}]. Status: Send Success ({$nowFormatted}).{$followupNotePart}"
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

        if ($processed > 0) {
            $this->App_setting_model->set('queue_last_dispatched_at', (string)time());
        }

        $sync = $this->get_queue_sync_status();

        return array_merge([
            'status'    => 'success',
            'message'   => "Successfully dispatched {$processed} queued email(s).",
            'processed' => $processed,
            'remaining' => $remaining,
            'details'   => $details
        ], $sync);
    }

    /**
     * Get real-time queue timing & counts synced with the server background clock
     */
    public function get_queue_sync_status() {
        $this->load->model('App_setting_model');
        $now = time();
        $interval = 60; // 1-minute cycle

        // Cron runs every minute at :00 seconds, so seconds remaining in the current minute is exact:
        $currentSecond = (int)date('s', $now);
        $secondsRemaining = (60 - $currentSecond) % 60;
        if ($secondsRemaining === 0) {
            $secondsRemaining = 60;
        }

        $lastDispatched = (int)$this->App_setting_model->get_by_key('queue_last_dispatched_at');
        if ($lastDispatched <= 0) {
            $lastDispatched = $now;
        }

        $isPaused = (int)$this->App_setting_model->get_by_key('queue_is_paused') === 1;
        $queuedCount = $this->db->where('status', 'queued')->count_all_results('crm_bulk_mail_queue');
        $pausedCount = $this->db->where('status', 'paused')->count_all_results('crm_bulk_mail_queue');
        $sentCount   = $this->db->where('status', 'sent')->count_all_results('crm_bulk_mail_queue');
        $failedCount = $this->db->where('status', 'failed')->count_all_results('crm_bulk_mail_queue');

        return [
            'server_time'        => $now,
            'last_dispatched_at' => $lastDispatched,
            'interval_seconds'   => $interval,
            'seconds_remaining'  => $secondsRemaining,
            'is_paused'          => $isPaused,
            'queued'             => $queuedCount,
            'paused'             => $pausedCount,
            'sent'               => $sentCount,
            'failed'             => $failedCount
        ];
    }
}
