<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lead_model extends MY_Model {
    protected $table = 'leads';

    public function get_with_details($id) {
        return $this->db->select("l.*, 
            COALESCE(NULLIF(IF(c.customer_name != '', CONCAT(c.customer_name, ' (', c.customer_org_name, ')'), c.customer_org_name), ''), l.company_name, '') AS customer_name, 
            COALESCE(c.phone, l.corporate_phone, '') AS customer_phone, 
            COALESCE(u.name, l.account_owner, '') AS assigned_name,
            p.name AS product_name,
            p.sku AS product_sku,
            p.price AS product_catalog_price")
            ->from('leads l')
            ->join('customers c', 'c.id = l.customer_id', 'left')
            ->join('users u', 'u.id = l.assigned_to', 'left')
            ->join('products p', 'p.id = l.product_id', 'left')
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
            ->from('leads l')
            ->join('customers c', 'c.id = l.customer_id', 'left')
            ->join('users u', 'u.id = l.assigned_to', 'left')
            ->join('products p', 'p.id = l.product_id', 'left');

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
            ->from('leads l')
            ->join('customers c', 'c.id = l.customer_id', 'left')
            ->join('users u', 'u.id = l.assigned_to', 'left')
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
        $this->db->insert('lead_activities', [
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
            ->from('leads')
            ->where('is_deleted', 0)
            ->where('status', 'active');
        if ($from) $this->db->where('created_at >=', $from);
        if ($to)   $this->db->where('created_at <=', $to . ' 23:59:59');
        if ($user_id) $this->db->where('assigned_to', $user_id);
        $this->db->group_by('lead_status');
        return $this->db->get()->result_array();
    }
}
