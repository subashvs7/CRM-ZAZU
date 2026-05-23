<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shift_assignment_model extends MY_Model {
    protected $table = 'shift_assignments';

    public function get_current_shift($user_id, $date = null) {
        $date = $date ?? date('Y-m-d');
        return $this->db->select('sa.*, s.name AS shift_name, s.start_time, s.end_time, s.grace_minutes, s.half_day_hours, s.full_day_hours')
            ->from('shift_assignments sa')
            ->join('shifts s', 's.id = sa.shift_id', 'left')
            ->where(['sa.user_id' => $user_id, 'sa.is_deleted' => 0, 'sa.status' => 'active'])
            ->where('sa.effective_from <=', $date)
            ->where('(sa.effective_to IS NULL OR sa.effective_to >= "' . $date . '")')
            ->order_by('sa.effective_from', 'desc')
            ->limit(1)->get()->row_array();
    }

    public function get_with_details($user_id = null) {
        $q = $this->db->select('sa.*, u.name AS user_name, s.name AS shift_name, s.start_time, s.end_time')
            ->from('shift_assignments sa')
            ->join('users u', 'u.id = sa.user_id', 'left')
            ->join('shifts s', 's.id = sa.shift_id', 'left')
            ->where('sa.is_deleted', 0);
        if ($user_id) $q->where('sa.user_id', $user_id);
        return $q->order_by('sa.effective_from', 'desc')->get()->result_array();
    }
}
