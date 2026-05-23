<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leave_balance_model extends MY_Model {
    protected $table = 'leave_balances';

    public function get_user_balances($user_id, $year = null) {
        $year = $year ?? date('Y');
        return $this->db->select('lb.*, lt.name AS leave_type_name, lt.paid')
            ->from('leave_balances lb')
            ->join('leave_types lt', 'lt.id = lb.leave_type_id', 'left')
            ->where(['lb.user_id' => $user_id, 'lb.year' => $year, 'lb.is_deleted' => 0])
            ->get()->result_array();
    }

    public function get_balance($user_id, $leave_type_id, $year = null) {
        $year = $year ?? date('Y');
        return $this->db->where(['user_id' => $user_id, 'leave_type_id' => $leave_type_id, 'year' => $year, 'is_deleted' => 0])
                        ->get($this->table)->row_array();
    }

    public function deduct($user_id, $leave_type_id, $days, $year = null) {
        $year = $year ?? date('Y');
        return $this->db->where(['user_id' => $user_id, 'leave_type_id' => $leave_type_id, 'year' => $year])
                        ->set('used_days', 'used_days + ' . (int)$days, false)
                        ->set('pending_days', 'pending_days - ' . (int)$days, false)
                        ->update($this->table);
    }

    public function add_pending($user_id, $leave_type_id, $days, $year = null) {
        $year = $year ?? date('Y');
        return $this->db->where(['user_id' => $user_id, 'leave_type_id' => $leave_type_id, 'year' => $year])
                        ->set('pending_days', 'pending_days + ' . (int)$days, false)
                        ->update($this->table);
    }

    public function revert_pending($user_id, $leave_type_id, $days, $year = null) {
        $year = $year ?? date('Y');
        return $this->db->where(['user_id' => $user_id, 'leave_type_id' => $leave_type_id, 'year' => $year])
                        ->set('pending_days', 'GREATEST(0, pending_days - ' . (int)$days . ')', false)
                        ->update($this->table);
    }
}
