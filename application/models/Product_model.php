<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends MY_Model {
    protected $table = 'products';

    public function get_with_category($id) {
        return $this->db->select('p.*, pc.name AS category_name')
            ->from('products p')
            ->join('product_categories pc', 'pc.id = p.category_id', 'left')
            ->where(['p.id' => $id, 'p.is_deleted' => 0])
            ->get()->row_array();
    }

    public function get_active_with_category() {
        return $this->db->select('p.*, pc.name AS category_name')
            ->from('products p')
            ->join('product_categories pc', 'pc.id = p.category_id', 'left')
            ->where(['p.status' => 'active', 'p.is_deleted' => 0])
            ->order_by('p.name')->get()->result_array();
    }

    public function datatable($params, $status_filter = null) {
        $this->db->select('p.id, p.name, p.sku, pc.name AS category_name, p.unit, p.price, p.stock, p.status, p.created_at')
            ->from('products p')
            ->join('product_categories pc', 'pc.id = p.category_id', 'left');

        if ($status_filter === 'deleted')      $this->db->where('p.is_deleted', 1);
        elseif ($status_filter === 'active')   $this->db->where(['p.status' => 'active',   'p.is_deleted' => 0]);
        elseif ($status_filter === 'inactive') $this->db->where(['p.status' => 'inactive', 'p.is_deleted' => 0]);
        else                                   $this->db->where('p.is_deleted', 0);

        $search = $params['search']['value'] ?? '';
        if ($search) {
            $this->db->group_start()
                ->like('p.name', $search)->or_like('p.sku', $search)->or_like('pc.name', $search)
                ->group_end();
        }

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('p.name')->limit($params['length'], $params['start']);
        return [$this->db->get()->result_array(), $total];
    }
}
