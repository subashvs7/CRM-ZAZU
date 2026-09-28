<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_addon_model extends MY_Model {
    protected $table = 'crm_product_addons';

    public function get_by_product($product_id) {
        return $this->db->where(['product_id' => $product_id, 'is_deleted' => 0, 'status' => 'active'])
                        ->order_by('sort_order', 'ASC')
                        ->get($this->table)
                        ->result_array();
    }
}
