<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shift_model extends MY_Model {
    protected $table = 'shifts';

    public function datatable($params, $status_filter = null) {
        $this->db->from($this->table);
        if ($status_filter === 'deleted')      $this->db->where('is_deleted', 1);
        elseif ($status_filter)                $this->db->where(['status' => $status_filter, 'is_deleted' => 0]);
        else                                   $this->db->where('is_deleted', 0);

        $search = $params['search']['value'] ?? '';
        if ($search) $this->db->like('name', $search);

        $total = $this->db->count_all_results('', false);
        // do NOT pass table name to get() — from() was already called above
        $this->db->order_by('name')->limit($params['length'], $params['start']);
        return [$this->db->get()->result_array(), $total];
    }
}
