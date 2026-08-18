<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_package_split_model extends MY_Model {
    protected $table = 'product_package_splits';

    public function get_splits_by_product($product_id) {
        return $this->db->select('pps.*, pt.name AS tier_name, pt.slug AS tier_slug, pt.badge_color, pt.icon')
                        ->from('product_package_splits pps')
                        ->join('package_tiers pt', 'pt.id = pps.package_tier_id')
                        ->where(['pps.product_id' => $product_id, 'pps.is_deleted' => 0])
                        ->order_by('pt.sort_order', 'ASC')
                        ->get()
                        ->result_array();
    }

    public function get_splits_by_product_ids($product_ids) {
        if (empty($product_ids)) return [];
        if (!is_array($product_ids)) {
            $product_ids = explode(',', $product_ids);
        }
        $product_ids = array_filter(array_map('intval', $product_ids));
        if (empty($product_ids)) return [];

        return $this->db->select('pps.*, pt.name AS tier_name, pt.slug AS tier_slug, pt.badge_color, pt.icon, p.name AS product_name, p.sku AS product_sku')
                        ->from('product_package_splits pps')
                        ->join('package_tiers pt', 'pt.id = pps.package_tier_id')
                        ->join('products p', 'p.id = pps.product_id')
                        ->where_in('pps.product_id', $product_ids)
                        ->where('pps.is_deleted', 0)
                        ->order_by('pps.product_id', 'ASC')
                        ->order_by('pt.sort_order', 'ASC')
                        ->get()
                        ->result_array();
    }

    public function get_split_details($id) {
        return $this->db->select('pps.*, pt.name AS tier_name, pt.slug AS tier_slug, pt.badge_color, pt.icon, p.name AS product_name, p.sku AS product_sku')
                        ->from('product_package_splits pps')
                        ->join('package_tiers pt', 'pt.id = pps.package_tier_id')
                        ->join('products p', 'p.id = pps.product_id')
                        ->where(['pps.id' => (int)$id, 'pps.is_deleted' => 0])
                        ->get()
                        ->row_array();
    }
}
