<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_model extends MY_Model {
    protected $table = 'crm_orders';

    public function get_with_details($id) {
        return $this->db->select("o.*, IF(c.customer_name != '', CONCAT(c.customer_name, ' (', c.customer_org_name, ')'), c.customer_org_name) AS customer_name, c.phone AS customer_phone, c.address AS customer_address, u.name AS created_by_name, a.name AS approved_by_name")
            ->from('crm_orders o')
            ->join('crm_customers c', 'c.id = o.customer_id', 'left')
            ->join('crm_user u', 'u.id = o.created_by', 'left')
            ->join('crm_user a', 'a.id = o.approved_by', 'left')
            ->where(['o.id' => $id, 'o.is_deleted' => 0])
            ->get()->row_array();
    }

    public function datatable($params, $status_filter = null, $user_id = null, $role = null) {
        $this->db->select("o.id, o.order_number, IF(c.customer_name != '', CONCAT(c.customer_name, ' (', c.customer_org_name, ')'), c.customer_org_name) AS customer_name, o.order_status, o.final_amount, u.name AS created_by_name, o.created_at, o.status")
            ->from('crm_orders o')
            ->join('crm_customers c', 'c.id = o.customer_id', 'left')
            ->join('crm_user u', 'u.id = o.created_by', 'left');

        if ($status_filter === 'deleted')      $this->db->where('o.is_deleted', 1);
        elseif ($status_filter === 'active')   $this->db->where(['o.status' => 'active',   'o.is_deleted' => 0]);
        elseif ($status_filter === 'inactive') $this->db->where(['o.status' => 'inactive', 'o.is_deleted' => 0]);
        else                                   $this->db->where('o.is_deleted', 0);

        if ($role === 'field_staff') $this->db->where('o.created_by', $user_id);

        if (!empty($params['customer_id'])) $this->db->where('o.customer_id', (int)$params['customer_id']);

        $search = $params['search']['value'] ?? '';
        if ($search) {
            $this->db->group_start()
                ->like('o.order_number', $search)->or_like('c.customer_name', $search)->or_like('c.customer_org_name', $search)
                ->group_end();
        }

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('o.id', 'desc');
        if (isset($params['length']) && $params['length'] != -1) {
            $this->db->limit($params['length'], $params['start'] ?? 0);
        }
        return [$this->db->get()->result_array(), $total];
    }

    public function pending_approval_datatable($params) {
        $this->db->select("o.id, o.order_number, IF(c.customer_name != '', CONCAT(c.customer_name, ' (', c.customer_org_name, ')'), c.customer_org_name) AS customer_name, o.final_amount, u.name AS created_by_name, o.created_at")
            ->from('crm_orders o')
            ->join('crm_customers c', 'c.id = o.customer_id', 'left')
            ->join('crm_user u', 'u.id = o.created_by', 'left')
            ->where(['o.order_status' => 'pending_approval', 'o.is_deleted' => 0]);

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('o.id', 'asc');
        if (isset($params['length']) && $params['length'] != -1) {
            $this->db->limit($params['length'], $params['start'] ?? 0);
        }
        return [$this->db->get()->result_array(), $total];
    }

    public function generate_number() {
        $prefix = get_setting('order_prefix', 'ORD');
        $count  = $this->db->count_all('crm_orders') + 1;
        return $prefix . '-' . date('Ym') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
