<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_model extends MY_Model {
    protected $table = 'notifications';

    public function get_user_notifications($user_id, $limit = 20) {
        return $this->db->where(['user_id' => $user_id, 'is_deleted' => 0])
                        ->order_by('id', 'desc')
                        ->limit($limit)
                        ->get($this->table)->result_array();
    }

    public function unread_count($user_id) {
        return $this->db->where(['user_id' => $user_id, 'is_read' => 0, 'is_deleted' => 0])
                        ->count_all_results($this->table);
    }

    public function mark_read($id, $user_id) {
        return $this->db->where(['id' => $id, 'user_id' => $user_id])
                        ->update($this->table, ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
    }

    public function mark_all_read($user_id) {
        return $this->db->where(['user_id' => $user_id, 'is_read' => 0])
                        ->update($this->table, ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
    }
}
