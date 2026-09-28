<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lead_model extends MY_Model {
    protected $table = 'crm_leads';

    public function get_with_details($id) {
        return $this->db->select("l.*, 
            COALESCE(NULLIF(IF(c.customer_name != '', CONCAT(c.customer_name, ' (', c.customer_org_name, ')'), c.customer_org_name), ''), l.company_name, '') AS customer_name, 
            COALESCE(c.phone, l.corporate_phone, '') AS customer_phone, 
            COALESCE(u.name, l.account_owner, '') AS assigned_name,
            p.name AS product_name,
            p.sku AS product_sku,
            p.price AS product_catalog_price")
            ->from('crm_leads l')
            ->join('crm_customers c', 'c.id = l.customer_id', 'left')
            ->join('crm_user u', 'u.id = l.assigned_to', 'left')
            ->join('crm_products p', 'p.id = l.product_id', 'left')
            ->where(['l.id' => $id, 'l.is_deleted' => 0])
            ->get()->row_array();
    }

    public function datatable($params, $status_filter = null, $user_id = null, $role = null) {
        $this->db->select("l.*, 
            COALESCE(NULLIF(IF(c.customer_name != '', CONCAT(c.customer_name, ' (', c.customer_org_name, ')'), c.customer_org_name), ''), l.company_name, '') AS customer_name, 
            c.phone AS customer_phone, 
            COALESCE(u.name, l.account_owner, '') AS assigned_name,
            p.name AS product_name,
            p.sku AS product_sku")
            ->from('crm_leads l')
            ->join('crm_customers c', 'c.id = l.customer_id', 'left')
            ->join('crm_user u', 'u.id = l.assigned_to', 'left')
            ->join('crm_products p', 'p.id = l.product_id', 'left');

        if ($status_filter === 'deleted')      $this->db->where('l.is_deleted', 1);
        elseif ($status_filter === 'active')   $this->db->where(['l.status' => 'active',   'l.is_deleted' => 0]);
        elseif ($status_filter === 'inactive') $this->db->where(['l.status' => 'inactive', 'l.is_deleted' => 0]);
        else                                   $this->db->where('l.is_deleted', 0);

        if ($role === 'field_staff' && $user_id) {
            $this->db->where('l.assigned_to', $user_id);
        }

        if (!empty($params['customer_id'])) {
            $this->db->where('l.customer_id', (int)$params['customer_id']);
        }

        if (!empty($params['product_id'])) {
            if ($params['product_id'] === 'unassigned') {
                $this->db->group_start()
                    ->where('l.product_id IS NULL', null, false)
                    ->or_where('l.product_id', 0)
                    ->group_end();
            } else {
                $this->db->where('l.product_id', (int)$params['product_id']);
            }
        }

        $search = trim($params['search']['value'] ?? '');
        if ($search) {
            $this->db->group_start()
                ->like('l.title', $search)
                ->or_like('l.first_name', $search)
                ->or_like('l.last_name', $search)
                ->or_like('l.company_name', $search)
                ->or_like('l.email', $search)
                ->or_like('l.corporate_phone', $search)
                ->or_like('l.account_owner', $search)
                ->or_like('l.industry', $search)
                ->or_like('l.city', $search)
                ->or_like('l.country', $search)
                ->or_like('c.customer_name', $search)
                ->or_like('c.customer_org_name', $search)
                ->or_like('l.lead_status', $search)
                ->or_like('p.name', $search)
                ->or_like('p.sku', $search)
                ->group_end();
        }

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('l.id', 'desc');
        if (isset($params['length']) && $params['length'] != -1) {
            $this->db->limit($params['length'], $params['start'] ?? 0);
        }
        return [$this->db->get()->result_array(), $total];
    }

    public function pipeline_data($user_id = null, $role = null) {
        $this->db->select("l.*, 
            COALESCE(NULLIF(IF(c.customer_name != '', CONCAT(c.customer_name, ' (', c.customer_org_name, ')'), c.customer_org_name), ''), l.company_name, '') AS customer_name, 
            COALESCE(u.name, l.account_owner, '') AS assigned_name")
            ->from('crm_leads l')
            ->join('crm_customers c', 'c.id = l.customer_id', 'left')
            ->join('crm_user u', 'u.id = l.assigned_to', 'left')
            ->where('l.is_deleted', 0)
            ->where('l.status', 'active');

        if ($role === 'field_staff' && $user_id) {
            $this->db->where('l.assigned_to', $user_id);
        }

        $this->db->order_by('l.id', 'desc');
        $rows = $this->db->get()->result_array();

        $pipeline = [
            'new'         => [],
            'contacted'   => [],
            'qualified'   => [],
            'proposal'    => [],
            'negotiation' => [],
            'won'         => [],
            'lost'        => []
        ];
  
        foreach ($rows as $row) {
            $status = strtolower($row['lead_status']);
            if (!isset($pipeline[$status])) {
                $pipeline[$status] = [];
            }
            $pipeline[$status][] = $row;
        }

        return $pipeline;
    }

    public function update_stage($id, $stage, $user_id) {
        $this->update($id, ['lead_status' => $stage]);
        $this->db->insert('crm_lead_activities', [
            'lead_id'       => $id,
            'user_id'       => $user_id,
            'activity_type' => 'status_change',
            'notes'         => 'Stage changed to: ' . $stage,
            'occurred_at'   => date('Y-m-d H:i:s'),
            'status'        => 'active',
            'is_deleted'    => 0,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function conversion_stats($from = null, $to = null, $user_id = null) {
        $this->db->select('lead_status, COUNT(*) AS cnt, SUM(expected_value) AS total_value')
            ->from('crm_leads')
            ->where('is_deleted', 0)
            ->where('status', 'active');
        if ($from) $this->db->where('created_at >=', $from);
        if ($to)   $this->db->where('created_at <=', $to . ' 23:59:59');
        if ($user_id) $this->db->where('assigned_to', $user_id);
        $this->db->group_by('lead_status');
        return $this->db->get()->result_array();
    }

    /**
     * Apollo.io Style Lists Summary:
     * Returns products / campaign lists with real-time leads count, customers count, creator, and last activity
     */
    public function get_product_lists_summary() {
        $products = $this->db->select('p.*, pc.name AS category_name')
            ->from('crm_products p')
            ->join('crm_product_categories pc', 'pc.id = p.category_id', 'left')
            ->where('p.is_deleted', 0)
            ->order_by('p.name', 'ASC')
            ->get()->result_array();

        $lists = [];
        $totalAllLeads = 0;
        $totalAllCusts = 0;

        foreach ($products as $p) {
            $pId = (int)$p['id'];

            // Leads count for this product
            $leadsCount = $this->db->where(['product_id' => $pId, 'is_deleted' => 0])->count_all_results('crm_leads');

            // Customers count linked to this product (via product_ids or via converted leads)
            $custCount  = $this->db->query("SELECT COUNT(*) AS cnt FROM crm_customers 
                WHERE is_deleted = 0 AND (
                    FIND_IN_SET(?, REPLACE(COALESCE(product_ids, ''), ' ', '')) > 0 
                    OR id IN (SELECT customer_id FROM crm_leads WHERE product_id = ? AND customer_id IS NOT NULL AND is_deleted = 0)
                )", [$pId, $pId])->row()->cnt ?? 0;

            // Last activity timestamp
            $lastLead = $this->db->select('created_at, updated_at')
                ->where(['product_id' => $pId, 'is_deleted' => 0])
                ->order_by('id', 'DESC')->limit(1)->get('crm_leads')->row_array();

            $lastActivity = $lastLead ? ($lastLead['updated_at'] ?: $lastLead['created_at']) : ($p['updated_at'] ?: $p['created_at']);

            $lists[] = [
                'id'              => $pId,
                'name'            => $p['name'],
                'sku'             => $p['sku'],
                'category_name'   => $p['category_name'] ?: 'General',
                'description'     => $p['description'] ?? '',
                'leads_count'     => (int)$leadsCount,
                'customers_count' => (int)$custCount,
                'total_records'   => (int)$leadsCount + (int)$custCount,
                'type'            => 'People',
                'created_by'      => 'Super Admin',
                'created_at'      => $p['created_at'],
                'last_activity'   => $lastActivity,
                'status'          => $p['status']
            ];

            $totalAllLeads += $leadsCount;
            $totalAllCusts += $custCount;
        }

        // Check for unassigned leads (where product_id IS NULL or 0)
        $unassignedCount = $this->db->where('is_deleted', 0)
            ->group_start()
            ->where('product_id IS NULL', null, false)
            ->or_where('product_id', 0)
            ->group_end()
            ->count_all_results('crm_leads');

        if ($unassignedCount > 0) {
            $lastUnassigned = $this->db->select('created_at, updated_at')
                ->where('is_deleted', 0)
                ->group_start()
                ->where('product_id IS NULL', null, false)
                ->or_where('product_id', 0)
                ->group_end()
                ->order_by('id', 'DESC')->limit(1)->get('crm_leads')->row_array();

            $lists[] = [
                'id'              => 'unassigned',
                'name'            => 'Unassigned Ads Leads',
                'sku'             => 'GENERAL',
                'category_name'   => 'Uncategorized',
                'description'     => 'Leads imported without product association',
                'leads_count'     => (int)$unassignedCount,
                'customers_count' => 0,
                'total_records'   => (int)$unassignedCount,
                'type'            => 'People',
                'created_by'      => 'System',
                'created_at'      => $lastUnassigned ? $lastUnassigned['created_at'] : date('Y-m-d H:i:s'),
                'last_activity'   => $lastUnassigned ? ($lastUnassigned['updated_at'] ?: $lastUnassigned['created_at']) : date('Y-m-d H:i:s'),
                'status'          => 'active'
            ];
            $totalAllLeads += $unassignedCount;
        }

        return $lists;
    }

    /**
     * Synchronize product_id for leads based on product_demo column matching crm_products
     */
    public function sync_products_from_product_demo() {
        $products = $this->db->where(['status' => 'active', 'is_deleted' => 0])->get('crm_products')->result_array();
        if (empty($products)) return 0;

        $leads = $this->db->select('id, product_demo, product_id')
            ->where('is_deleted', 0)
            ->where('product_demo IS NOT NULL', null, false)
            ->where('product_demo !=', '')
            ->get('crm_leads')
            ->result_array();

        $updated = 0;
        foreach ($leads as $l) {
            $pDemo = trim($l['product_demo']);
            if ($pDemo === '') continue;

            $matchedId = null;
            $cleanDemo = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $pDemo));

            foreach ($products as $p) {
                $pName = trim($p['name']);
                $pSku  = trim($p['sku']);
                $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $pName));
                $cleanSku  = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $pSku));

                // Direct / exact matching
                if (($cleanDemo !== '' && ($cleanDemo === $cleanName || $cleanDemo === $cleanSku))
                    || stripos($pName, $pDemo) !== false
                    || stripos($pDemo, $pName) !== false
                    || ($cleanSku !== '' && (stripos($cleanDemo, $cleanSku) !== false || stripos($cleanSku, $cleanDemo) !== false))
                    || ($cleanName !== '' && (stripos($cleanDemo, $cleanName) !== false || stripos($cleanName, $cleanDemo) !== false))) {
                    $matchedId = (int)$p['id'];
                    break;
                }

                // Word level matching (e.g. "Class Wall", "Proman", "ERP", etc.)
                $words = preg_split('/[\s\-_,\.\(\)\/]+/', strtolower($pName), -1, PREG_SPLIT_NO_EMPTY);
                foreach ($words as $w) {
                    if (strlen($w) >= 3 && !in_array($w, ['the', 'and', 'for', 'software', 'management', 'system', 'application'])) {
                        if (stripos($pDemo, $w) !== false) {
                            $matchedId = (int)$p['id'];
                            break 2;
                        }
                    }
                }
            }

            if ($matchedId && (empty($l['product_id']) || (int)$l['product_id'] !== $matchedId)) {
                $this->db->where('id', $l['id'])->update('crm_leads', [
                    'product_id' => $matchedId,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $updated++;
            }
        }
        return $updated;
    }
}

