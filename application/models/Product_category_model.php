<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_category_model extends MY_Model {
    protected $table = 'product_categories';

    public function get_tree() {
        $all = $this->get_all([], 'name ASC');
        $tree = [];
        foreach ($all as $cat) {
            if (!$cat['parent_id']) {
                $cat['children'] = [];
                $tree[$cat['id']] = $cat;
            }
        }
        foreach ($all as $cat) {
            if ($cat['parent_id'] && isset($tree[$cat['parent_id']])) {
                $tree[$cat['parent_id']]['children'][] = $cat;
            }
        }
        return array_values($tree);
    }

    public function datatable($params, $status_filter = null) {
        $this->db->select('c.id, c.name, p.name AS parent_name, c.status, c.created_at')
            ->from('product_categories c')
            ->join('product_categories p', 'p.id = c.parent_id', 'left');

        if ($status_filter === 'deleted')      $this->db->where('c.is_deleted', 1);
        elseif ($status_filter)                $this->db->where(['c.status' => $status_filter, 'c.is_deleted' => 0]);
        else                                   $this->db->where('c.is_deleted', 0);

        $search = $params['search']['value'] ?? '';
        if ($search) $this->db->like('c.name', $search);

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('c.name')->limit($params['length'], $params['start']);
        return [$this->db->get()->result_array(), $total];
    }
}
