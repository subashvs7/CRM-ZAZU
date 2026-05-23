<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact_person_model extends MY_Model {
    protected $table = 'contact_persons';

    public function get_by_customer($customer_id) {
        return $this->db->where(['customer_id' => $customer_id, 'is_deleted' => 0])
                        ->order_by('is_primary DESC, name ASC')->get($this->table)->result_array();
    }

    public function set_primary($id, $customer_id) {
        $this->db->where(['customer_id' => $customer_id])->update($this->table, ['is_primary' => 0]);
        return $this->db->where('id', $id)->update($this->table, ['is_primary' => 1]);
    }
}
