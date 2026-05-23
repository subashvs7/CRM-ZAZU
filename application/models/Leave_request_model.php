<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leave_request_model extends MY_Model {
    protected $table = 'leave_requests';

    public function get_with_details($id) {
        return $this->db->select('lr.*, u.name AS user_name, lt.name AS leave_type_name, a.name AS approved_by_name')
            ->from('leave_requests lr')
            ->join('users u', 'u.id = lr.user_id', 'left')
            ->join('leave_types lt', 'lt.id = lr.leave_type_id', 'left')
            ->join('users a', 'a.id = lr.approved_by', 'left')
            ->where(['lr.id' => $id, 'lr.is_deleted' => 0])
            ->get()->row_array();
    }

    public function datatable($params, $status_filter = null, $user_id = null, $role = null) {
        $this->db->select('lr.id, lr.user_id, u.name AS user_name, lt.name AS leave_type_name, lr.from_date, lr.to_date, lr.days, lr.leave_status, lr.created_at, lr.status')
            ->from('leave_requests lr')
            ->join('users u', 'u.id = lr.user_id', 'left')
            ->join('leave_types lt', 'lt.id = lr.leave_type_id', 'left');

        if ($status_filter === 'deleted') $this->db->where('lr.is_deleted', 1);
        else                              $this->db->where('lr.is_deleted', 0);

        if ($role === 'field_staff') $this->db->where('lr.user_id', $user_id);

        $search = $params['search']['value'] ?? '';
        if ($search) $this->db->like('u.name', $search);

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('lr.id', 'desc')->limit($params['length'], $params['start']);
        return [$this->db->get()->result_array(), $total];
    }

    public function approval_datatable($params) {
        $this->db->select('lr.id, u.name AS user_name, lt.name AS leave_type_name, lr.from_date, lr.to_date, lr.days, lr.reason, lr.created_at')
            ->from('leave_requests lr')
            ->join('users u', 'u.id = lr.user_id', 'left')
            ->join('leave_types lt', 'lt.id = lr.leave_type_id', 'left')
            ->where(['lr.leave_status' => 'pending', 'lr.is_deleted' => 0]);

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('lr.id', 'asc')->limit($params['length'], $params['start']);
        return [$this->db->get()->result_array(), $total];
    }
}
