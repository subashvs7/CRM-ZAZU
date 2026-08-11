<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact_book_model extends MY_Model {
    protected $table = 'contact_book';

    public function datatable($params, $user_id = null) {
        $this->db->select("cb.id, cb.name, cb.phone, cb.email, cb.company_name, cb.customer_id, IF(c.customer_name != '', CONCAT(c.customer_name, ' (', c.customer_org_name, ')'), c.customer_org_name) AS linked_customer, cb.created_at")
            ->from('contact_book cb')
            ->join('customers c', 'c.id = cb.customer_id', 'left')
            ->where('cb.is_deleted', 0);

        if ($user_id) {
            $this->db->where('cb.user_id', $user_id);
        }

        $search = $params['search']['value'] ?? '';
        if ($search) {
            $this->db->group_start()
                ->like('cb.name', $search)
                ->or_like('cb.phone', $search)
                ->or_like('cb.email', $search)
                ->or_like('cb.company_name', $search)
                ->or_like('c.customer_org_name', $search)
                ->group_end();
        }

        $total = $this->db->count_all_results('', false);
        
        $order_cols = ['cb.id', 'cb.name', 'cb.phone', 'cb.email', 'cb.company_name', 'cb.created_at'];
        $oi = $params['order'][0]['column'] ?? 0;
        $od = $params['order'][0]['dir'] ?? 'desc';
        $this->db->order_by($order_cols[$oi] ?? 'cb.id', $od);
        
        if (isset($params['length']) && $params['length'] != -1) {
            $this->db->limit($params['length'], $params['start'] ?? 0);
        }
        return [$this->db->get()->result_array(), $total];
    }
}
