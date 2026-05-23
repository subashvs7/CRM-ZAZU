<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_model extends MY_Model {
    protected $table = 'customers';

    public function get_with_staff($id) {
        return $this->db->select('c.*, u.name AS assigned_name')
            ->from('customers c')
            ->join('users u', 'u.id = c.assigned_to', 'left')
            ->where(['c.id' => $id, 'c.is_deleted' => 0])
            ->get()->row_array();
    }

    public function datatable($params, $status_filter = null, $user_id = null, $role = null) {
        $this->db->select('c.id, c.name, c.phone, c.email, c.city, c.state, u.name AS assigned_name, c.status, c.created_at')
            ->from('customers c')
            ->join('users u', 'u.id = c.assigned_to', 'left');

        if ($status_filter === 'deleted')      $this->db->where('c.is_deleted', 1);
        elseif ($status_filter === 'active')   $this->db->where(['c.status' => 'active',   'c.is_deleted' => 0]);
        elseif ($status_filter === 'inactive') $this->db->where(['c.status' => 'inactive', 'c.is_deleted' => 0]);
        else                                   $this->db->where('c.is_deleted', 0);

        if ($role === 'field_staff') $this->db->where('c.assigned_to', $user_id);

        $search = $params['search']['value'] ?? '';
        if ($search) {
            $this->db->group_start()
                ->like('c.name', $search)->or_like('c.phone', $search)->or_like('c.email', $search)->or_like('c.city', $search)
                ->group_end();
        }

        $total = $this->db->count_all_results('', false);

        $order_cols = ['c.id','c.name','c.phone','c.email','c.city','u.name','c.status','c.created_at'];
        $oi = $params['order'][0]['column'] ?? 0;
        $od = $params['order'][0]['dir']    ?? 'desc';
        $this->db->order_by($order_cols[$oi] ?? 'c.id', $od);
        $this->db->limit($params['length'], $params['start']);
        return [$this->db->get()->result_array(), $total];
    }

    public function get_map_data($user_id = null, $role = null) {
        $this->db->select('c.id, c.name, c.phone, c.city, c.latitude, c.longitude, c.status')
            ->from('customers c')
            ->where(['c.is_deleted' => 0, 'c.status' => 'active'])
            ->where('c.latitude IS NOT NULL')
            ->where('c.longitude IS NOT NULL');
        if ($role === 'field_staff') $this->db->where('c.assigned_to', $user_id);
        return $this->db->get()->result_array();
    }
}
