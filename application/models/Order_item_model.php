<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_item_model extends MY_Model {
    protected $table = 'order_items';

    public function get_by_order($order_id) {
        return $this->db->select('oi.*, p.name AS product_name, p.sku, p.unit')
            ->from('order_items oi')
            ->join('products p', 'p.id = oi.product_id', 'left')
            ->where(['oi.order_id' => $order_id, 'oi.is_deleted' => 0])
            ->get()->result_array();
    }

    public function replace_items($order_id, array $items) {
        $this->db->where(['order_id' => $order_id])->update($this->table, [
            'status' => 'deleted', 'is_deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s'),
        ]);
        foreach ($items as $item) {
            $item['order_id']   = $order_id;
            $item['status']     = 'active';
            $item['is_deleted'] = 0;
            $item['created_at'] = date('Y-m-d H:i:s');
            $item['updated_at'] = date('Y-m-d H:i:s');
            $this->db->insert($this->table, $item);
        }
    }

    public function order_totals($order_id) {
        $items = $this->get_by_order($order_id);
        $total = 0;
        foreach ($items as $i) $total += $i['line_total'];
        return $total;
    }
}
