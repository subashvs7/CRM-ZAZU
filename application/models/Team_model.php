<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Team_model extends MY_Model {
    protected $table = 'teams';

    public function get_with_manager($id = null) {
        $this->db->select('t.*, u.name AS manager_name')
            ->from('teams t')
            ->join('users u', 'u.id = t.manager_id', 'left')
            ->where('t.is_deleted', 0);
        if ($id) { $this->db->where('t.id', $id); return $this->db->get()->row_array(); }
        return $this->db->order_by('t.name')->get()->result_array();
    }

    public function datatable($params, $status_filter = null) {
        $this->db->select('t.id, t.name, u.name AS manager_name, t.territory, t.status, t.created_at')
            ->from('teams t')
            ->join('users u', 'u.id = t.manager_id', 'left');

        if ($status_filter === 'deleted')      $this->db->where('t.is_deleted', 1);
        elseif ($status_filter === 'active')   $this->db->where(['t.status' => 'active',   't.is_deleted' => 0]);
        elseif ($status_filter === 'inactive') $this->db->where(['t.status' => 'inactive', 't.is_deleted' => 0]);
        else                                   $this->db->where('t.is_deleted', 0);

        $search = $params['search']['value'] ?? '';
        if ($search) $this->db->like('t.name', $search);

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('t.name')->limit($params['length'], $params['start']);
        return [$this->db->get()->result_array(), $total];
    }

    public function get_member_count($team_id) {
        return $this->db->where(['team_id' => $team_id, 'is_deleted' => 0])->count_all_results('users');
    }
}
