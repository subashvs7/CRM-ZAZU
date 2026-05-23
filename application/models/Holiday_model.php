<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Holiday_model extends MY_Model {
    protected $table = 'holidays';

    public function get_by_year($year) {
        return $this->db->where(['is_deleted' => 0, 'status' => 'active'])
                        ->where('YEAR(date)', $year)
                        ->order_by('date', 'asc')
                        ->get($this->table)->result_array();
    }

    public function is_holiday($date) {
        return (bool) $this->db->where(['date' => $date, 'is_deleted' => 0, 'status' => 'active'])
                                ->count_all_results($this->table);
    }
}
