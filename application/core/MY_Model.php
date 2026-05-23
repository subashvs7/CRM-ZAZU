<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Model extends CI_Model {
    protected $table        = '';
    protected $primary_key  = 'id';

    public function __construct() { parent::__construct(); }

    public function get_all($where = [], $order = 'id DESC', $limit = null, $offset = null) {
        $where = array_merge(['is_deleted' => 0], $where);
        $this->db->where($where)->order_by($order);
        if ($limit) $this->db->limit($limit, $offset);
        return $this->db->get($this->table)->result_array();
    }

    public function get_by_id($id) {
        return $this->db->where(['id' => $id, 'is_deleted' => 0])->get($this->table)->row_array();
    }

    public function get_active($extra = []) {
        return $this->db->where(array_merge(['status' => 'active', 'is_deleted' => 0], $extra))->get($this->table)->result_array();
    }

    public function get_inactive($extra = []) {
        return $this->db->where(array_merge(['status' => 'inactive', 'is_deleted' => 0], $extra))->get($this->table)->result_array();
    }

    public function get_deleted($extra = []) {
        return $this->db->where(array_merge(['is_deleted' => 1], $extra))->get($this->table)->result_array();
    }

    public function get_with_deleted($where = []) {
        if ($where) $this->db->where($where);
        return $this->db->get($this->table)->result_array();
    }

    public function count_all($where = []) {
        $where = array_merge(['is_deleted' => 0], $where);
        return $this->db->where($where)->count_all_results($this->table);
    }

    public function insert($data) {
        $data['status']     = $data['status']     ?? 'active';
        $data['is_deleted'] = 0;
        $data['deleted_at'] = null;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->where($this->primary_key, $id)->update($this->table, $data);
    }

    public function activate($id)    { return $this->update($id, ['status' => 'active',   'is_deleted' => 0]); }
    public function deactivate($id)  { return $this->update($id, ['status' => 'inactive']); }
    public function soft_delete($id) { return $this->update($id, ['status' => 'deleted',  'is_deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s')]); }
    public function restore($id)     { return $this->update($id, ['status' => 'active',   'is_deleted' => 0, 'deleted_at' => null]); }

    public function datatable_query($status_filter = null) {
        $this->db->from($this->table);
        if ($status_filter === 'deleted')       $this->db->where('is_deleted', 1);
        elseif ($status_filter === 'active')    $this->db->where(['status' => 'active',   'is_deleted' => 0]);
        elseif ($status_filter === 'inactive')  $this->db->where(['status' => 'inactive', 'is_deleted' => 0]);
        else                                    $this->db->where('is_deleted', 0);
        return $this->db;
    }
}
