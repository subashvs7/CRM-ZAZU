<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_model extends MY_Model {
    protected $table = 'orders';

    public function get_with_details($id) {
        return $this->db->select('o.*, c.name AS customer_name, c.phone AS customer_phone, c.address AS customer_address, u.name AS created_by_name, a.name AS approved_by_name')
            ->from('orders o')
            ->join('customers c', 'c.id = o.customer_id', 'left')
            ->join('users u', 'u.id = o.created_by', 'left')
            ->join('users a', 'a.id = o.approved_by', 'left')
            ->where(['o.id' => $id, 'o.is_deleted' => 0])
            ->get()->row_array();
    }

    public function datatable($params, $status_filter = null, $user_id = null, $role = null) {
        $this->db->select('o.id, o.order_number, c.name AS customer_name, o.order_status, o.final_amount, u.name AS created_by_name, o.created_at, o.status')
            ->from('orders o')
            ->join('customers c', 'c.id = o.customer_id', 'left')
            ->join('users u', 'u.id = o.created_by', 'left');

        if ($status_filter === 'deleted')      $this->db->where('o.is_deleted', 1);
        elseif ($status_filter === 'active')   $this->db->where(['o.status' => 'active',   'o.is_deleted' => 0]);
        elseif ($status_filter === 'inactive') $this->db->where(['o.status' => 'inactive', 'o.is_deleted' => 0]);
        else                                   $this->db->where('o.is_deleted', 0);

        if ($role === 'field_staff') $this->db->where('o.created_by', $user_id);

        $search = $params['search']['value'] ?? '';
        if ($search) {
            $this->db->group_start()
                ->like('o.order_number', $search)->or_like('c.name', $search)
                ->group_end();
        }

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('o.id', 'desc')->limit($params['length'], $params['start']);
        return [$this->db->get()->result_array(), $total];
    }

    public function pending_approval_datatable($params) {
        $this->db->select('o.id, o.order_number, c.name AS customer_name, o.final_amount, u.name AS created_by_name, o.created_at')
            ->from('orders o')
            ->join('customers c', 'c.id = o.customer_id', 'left')
            ->join('users u', 'u.id = o.created_by', 'left')
            ->where(['o.order_status' => 'pending_approval', 'o.is_deleted' => 0]);

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('o.id', 'asc')->limit($params['length'], $params['start']);
        return [$this->db->get()->result_array(), $total];
    }

    public function generate_number() {
        $prefix = get_setting('order_prefix', 'ORD');
        $count  = $this->db->count_all('orders') + 1;
        return $prefix . '-' . date('Ym') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
