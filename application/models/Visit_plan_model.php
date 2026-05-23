<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Visit_plan_model extends MY_Model {
    protected $table = 'visit_plans';

    public function get_with_details($id) {
        return $this->db->select('vp.*, c.name AS customer_name, u.name AS user_name, cb.name AS created_by_name')
            ->from('visit_plans vp')
            ->join('customers c', 'c.id = vp.customer_id', 'left')
            ->join('users u', 'u.id = vp.user_id', 'left')
            ->join('users cb', 'cb.id = vp.created_by', 'left')
            ->where(['vp.id' => $id, 'vp.is_deleted' => 0])
            ->get()->row_array();
    }

    public function datatable($params, $status_filter = null, $user_id = null, $role = null) {
        $this->db->select('vp.id, vp.planned_date, vp.planned_time, c.name AS customer_name, u.name AS user_name, vp.visit_status, vp.purpose, vp.status, vp.created_at')
            ->from('visit_plans vp')
            ->join('customers c', 'c.id = vp.customer_id', 'left')
            ->join('users u', 'u.id = vp.user_id', 'left');

        if ($status_filter === 'deleted')      $this->db->where('vp.is_deleted', 1);
        elseif ($status_filter === 'active')   $this->db->where(['vp.status' => 'active',   'vp.is_deleted' => 0]);
        elseif ($status_filter === 'inactive') $this->db->where(['vp.status' => 'inactive', 'vp.is_deleted' => 0]);
        else                                   $this->db->where('vp.is_deleted', 0);

        if ($role === 'field_staff') $this->db->where('vp.user_id', $user_id);

        $search = $params['search']['value'] ?? '';
        if ($search) {
            $this->db->group_start()
                ->like('c.name', $search)->or_like('u.name', $search)->or_like('vp.visit_status', $search)
                ->group_end();
        }

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('vp.planned_date', 'desc')->limit($params['length'], $params['start']);
        return [$this->db->get()->result_array(), $total];
    }

    public function calendar_data($user_id = null, $role = null, $start = null, $end = null) {
        $this->db->select('vp.id, vp.planned_date, vp.planned_time, vp.visit_status, c.name AS customer_name, u.name AS user_name')
            ->from('visit_plans vp')
            ->join('customers c', 'c.id = vp.customer_id', 'left')
            ->join('users u', 'u.id = vp.user_id', 'left')
            ->where('vp.is_deleted', 0);
        if ($role === 'field_staff') $this->db->where('vp.user_id', $user_id);
        if ($start) $this->db->where('vp.planned_date >=', $start);
        if ($end)   $this->db->where('vp.planned_date <=', $end);
        return $this->db->get()->result_array();
    }
}
