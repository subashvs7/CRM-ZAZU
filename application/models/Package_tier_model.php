<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Package_tier_model extends MY_Model {
    protected $table = 'package_tiers';

    public function get_all_active() {
        return $this->db->where(['status' => 'active', 'is_deleted' => 0])
                        ->order_by('sort_order', 'ASC')
                        ->get($this->table)
                        ->result_array();
    }

    public function datatable($params, $status_filter = null) {
        $this->db->from($this->table);
        if ($status_filter === 'deleted')       $this->db->where('is_deleted', 1);
        elseif ($status_filter === 'active')    $this->db->where(['status' => 'active',   'is_deleted' => 0]);
        elseif ($status_filter === 'inactive')  $this->db->where(['status' => 'inactive', 'is_deleted' => 0]);
        else                                    $this->db->where('is_deleted', 0);

        $search = $params['search']['value'] ?? '';
        if ($search) {
            $this->db->group_start()
                     ->like('name', $search)
                     ->or_like('slug', $search)
                     ->or_like('description', $search)
                     ->group_end();
        }
        $total = $this->db->count_all_results('', false);
        $this->db->order_by('sort_order', 'ASC');
        if (isset($params['length']) && $params['length'] != -1) {
            $this->db->limit($params['length'], $params['start'] ?? 0);
        }
        return [$this->db->get()->result_array(), $total];
    }
}
