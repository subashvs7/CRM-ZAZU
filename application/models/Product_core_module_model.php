<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_core_module_model extends MY_Model {
    protected $table = 'product_core_modules';

    public function get_by_product($product_id) {
        return $this->db->where(['product_id' => $product_id, 'is_deleted' => 0, 'status' => 'active'])
                        ->order_by('sort_order', 'ASC')
                        ->get($this->table)
                        ->result_array();
    }
}
