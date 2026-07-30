<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact_book_model extends MY_Model {
    protected $table = 'contact_book';

    public function datatable($params, $user_id = null) {
        $this->db->select('id, name, phone, email, company_name, created_at')
            ->from('contact_book')
            ->where('is_deleted', 0);

        if ($user_id) {
            $this->db->where('user_id', $user_id);
        }

        $search = $params['search']['value'] ?? '';
        if ($search) {
            $this->db->group_start()
                ->like('name', $search)
                ->or_like('phone', $search)
                ->or_like('email', $search)
                ->or_like('company_name', $search)
                ->group_end();
        }

        $total = $this->db->count_all_results('', false);
        
        $order_cols = ['id', 'name', 'phone', 'email', 'company_name', 'created_at'];
        $oi = $params['order'][0]['column'] ?? 0;
        $od = $params['order'][0]['dir'] ?? 'desc';
        $this->db->order_by($order_cols[$oi] ?? 'id', $od);
        
        $this->db;
        if (isset($params['length']) && $params['length'] != -1) {
            $this->db->limit($params['length'], $params['start'] ?? 0);
        }
        return [$this->db->get()->result_array(), $total];
    }
}
