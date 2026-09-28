<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bulk_mail_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Fetch active templates, optionally filtered by product and/or category.
     */
    public function get_templates_by_product($product_id = null, $category = null) {
        $this->db->select('t.*, p.name AS product_name, p.sku AS product_sku')
            ->from('notification_templates t')
            ->join('products p', 'p.id = t.product_id', 'left')
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
     * Get single template by ID
     */
    public function get_template_by_id($id) {
        return $this->db->select('t.*, p.name AS product_name, p.price AS product_price, p.website_url AS product_website')
            ->from('notification_templates t')
            ->join('products p', 'p.id = t.product_id', 'left')
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
            $this->db->where('id', $id)->update('notification_templates', $data);
            return $id;
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['is_deleted'] = 0;
            $this->db->insert('notification_templates', $data);
            return $this->db->insert_id();
        }
    }

    /**
     * Soft delete template
     */
    public function delete_template($id) {
        return $this->db->where('id', (int)$id)->update('notification_templates', [
            'is_deleted' => 1,
            'status'     => 'deleted',
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Query audience recipients dynamically based on group and filters
     * Supports single or multi-audience selection with automatic email deduplication
     */
    public function get_recipients($recipient_types, $product_id = null, $lead_status = null, $limit = 2000) {
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

        // 1. Process Leads
        if ($hasLeads) {
            $this->db->select("l.id AS lead_id, l.email, 
                TRIM(CONCAT(COALESCE(l.first_name, ''), ' ', COALESCE(l.last_name, ''))) AS full_name,
                l.first_name, l.last_name, l.company_name, l.title, l.corporate_phone AS phone,
                l.product_id, p.name AS product_name, p.price AS product_price, l.lead_status")
                ->from('leads l')
                ->join('products p', 'p.id = l.product_id', 'left')
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

            $this->db->order_by('l.id', 'DESC');
            if ($limit > 0) $this->db->limit($limit);

            $rows = $this->db->get()->result_array();
            foreach ($rows as $r) {
                $email = strtolower(trim($r['email']));
                if (!isset($seenEmails[$email])) {
                    $seenEmails[$email] = true;
                    $name = !empty($r['full_name']) ? $r['full_name'] : ($r['title'] ?: ($r['company_name'] ?: 'Lead #' . $r['lead_id']));
                    $recipients[] = [
                        'key'           => 'lead_' . $r['lead_id'],
                        'type'          => 'lead',
                        'source_type'   => 'Lead',
                        'lead_id'       => (int)$r['lead_id'],
                        'customer_id'   => null,
                        'email'         => trim($r['email']),
                        'name'          => $name,
                        'first_name'    => $r['first_name'] ?: $name,
                        'last_name'     => $r['last_name'] ?? '',
                        'company'       => $r['company_name'] ?? '',
                        'phone'         => $r['phone'] ?? '',
                        'product_id'    => $r['product_id'],
                        'product_name'  => $r['product_name'] ?? '',
                        'product_price' => $r['product_price'] ? format_inr($r['product_price']) : '',
                        'status'        => $r['lead_status'] ?? 'new'
                    ];
                }
            }
        }

        // 2. Process Customers
        if ($hasCustomers) {
            $this->db->select("c.id AS customer_id, c.email, c.customer_name, c.customer_org_name, c.phone, c.status")
                ->from('customers c')
                ->where('c.is_deleted', 0)
                ->where('c.email IS NOT NULL', null, false)
                ->where('TRIM(c.email) !=', '');

            if ($activeCustOnly) {
                $this->db->where('c.status', 'active');
            }

            $this->db->order_by('c.id', 'DESC');
            if ($limit > 0) $this->db->limit($limit);

            $rows = $this->db->get()->result_array();
            foreach ($rows as $r) {
                $email = strtolower(trim($r['email']));
                if (!isset($seenEmails[$email])) {
                    $seenEmails[$email] = true;
                    $name = !empty($r['customer_name']) ? $r['customer_name'] : ($r['customer_org_name'] ?: 'Customer #' . $r['customer_id']);
                    $recipients[] = [
                        'key'           => 'customer_' . $r['customer_id'],
                        'type'          => 'customer',
                        'source_type'   => 'Customer',
                        'lead_id'       => null,
                        'customer_id'   => (int)$r['customer_id'],
                        'email'         => trim($r['email']),
                        'name'          => $name,
                        'first_name'    => $name,
                        'last_name'     => '',
                        'company'       => $r['customer_org_name'] ?? '',
                        'phone'         => $r['phone'] ?? '',
                        'product_id'    => null,
                        'product_name'  => '',
                        'product_price' => '',
                        'status'        => $r['status'] ?? 'active'
                    ];
                }
            }
        }

        // 3. Process Contact Book
        if ($hasContactBook) {
            $this->db->select("cb.id AS contact_id, cb.email, cb.name, cb.company_name, cb.phone, cb.job_title")
                ->from('contact_book cb')
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
                    $recipients[] = [
                        'key'           => 'contact_' . $r['contact_id'],
                        'type'          => 'contact_book',
                        'source_type'   => 'Contact Book',
                        'lead_id'       => null,
                        'customer_id'   => null,
                        'contact_id'    => (int)$r['contact_id'],
                        'email'         => trim($r['email']),
                        'name'          => $r['name'],
                        'first_name'    => $r['name'],
                        'last_name'     => '',
                        'company'       => $r['company_name'] ?? '',
                        'phone'         => $r['phone'] ?? '',
                        'product_id'    => null,
                        'product_name'  => '',
                        'product_price' => '',
                        'status'        => 'active'
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
        $this->db->insert('bulk_mail_campaigns', $data);
        return $this->db->insert_id();
    }

    public function add_to_queue($campaign_id, $recipients) {
        if (empty($recipients)) return false;

        $batch_data = [];
        foreach ($recipients as $r) {
            $batch_data[] = [
                'campaign_id'     => $campaign_id,
                'recipient_email' => $r['email'],
                'recipient_name'  => $r['name'] ?? '',
                'lead_id'         => $r['lead_id'] ?? null,
                'customer_id'     => $r['customer_id'] ?? null,
                'status'          => 'queued',
                'created_at'      => date('Y-m-d H:i:s')
            ];
        }

        // Chunk in blocks of 200 for safe SQL batch insert
        $chunks = array_chunk($batch_data, 200);
        foreach ($chunks as $chunk) {
            $this->db->insert_batch('bulk_mail_queue', $chunk);
        }
        return true;
    }

    /**
     * Get aggregate statistics across campaigns
     */
    public function get_stats() {
        $stats = [
            'total'     => 0,
            'sent'      => 0,
            'queued'    => 0,
            'failed'    => 0,
            'campaigns' => 0
        ];

        $this->db->select('status, count(id) as count');
        $this->db->from('bulk_mail_queue');
        $this->db->group_by('status');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            if ($row->status == 'sent')   $stats['sent']   = (int)$row->count;
            if ($row->status == 'queued') $stats['queued'] = (int)$row->count;
            if ($row->status == 'failed') $stats['failed'] = (int)$row->count;
        }
        $stats['total'] = $stats['sent'] + $stats['queued'] + $stats['failed'];

        $stats['campaigns'] = (int)$this->db->count_all('bulk_mail_campaigns');
        return $stats;
    }

    /**
     * Get list of recent campaigns with rich metadata
     */
    public function get_recent_campaigns($limit = 10) {
        $this->db->select('c.*, p.name AS product_name, t.name AS template_name,
            COUNT(q.id) AS queue_total,
            SUM(CASE WHEN q.status = "sent" THEN 1 ELSE 0 END) AS count_sent,
            SUM(CASE WHEN q.status = "failed" THEN 1 ELSE 0 END) AS count_failed,
            SUM(CASE WHEN q.status = "queued" THEN 1 ELSE 0 END) AS count_queued')
            ->from('bulk_mail_campaigns c')
            ->join('products p', 'p.id = c.product_id', 'left')
            ->join('notification_templates t', 't.id = c.template_id', 'left')
            ->join('bulk_mail_queue q', 'q.campaign_id = c.id', 'left')
            ->group_by('c.id')
            ->order_by('c.created_at', 'DESC');

        if ($limit > 0) $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    /**
     * Get detailed campaign information and recipient queue
     */
    public function get_campaign_detail($campaign_id) {
        $campaign = $this->db->select('c.*, p.name AS product_name, t.name AS template_name')
            ->from('bulk_mail_campaigns c')
            ->join('products p', 'p.id = c.product_id', 'left')
            ->join('notification_templates t', 't.id = c.template_id', 'left')
            ->where('c.id', (int)$campaign_id)
            ->get()->row_array();

        if (!$campaign) return null;

        $items = $this->db->select('q.*, l.title AS lead_title, cust.customer_name')
            ->from('bulk_mail_queue q')
            ->join('leads l', 'l.id = q.lead_id', 'left')
            ->join('customers cust', 'cust.id = q.customer_id', 'left')
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
            ->from('bulk_mail_queue q')
            ->join('bulk_mail_campaigns c', 'c.id = q.campaign_id')
            ->where('q.status', 'queued')
            ->limit($limit);
        return $this->db->get()->result_array();
    }

    public function update_queue_item($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('bulk_mail_queue', $data);
    }

    public function update_campaign_status($id, $status) {
        $this->db->where('id', $id);
        return $this->db->update('bulk_mail_campaigns', ['status' => $status]);
    }

    public function check_and_update_campaign($campaign_id) {
        $this->db->where('campaign_id', $campaign_id);
        $this->db->where('status', 'queued');
        $count = $this->db->count_all_results('bulk_mail_queue');

        if ($count == 0) {
            $this->update_campaign_status($campaign_id, 'completed');
        } else {
            $this->update_campaign_status($campaign_id, 'processing');
        }
    }
}
