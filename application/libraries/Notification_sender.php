<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_sender {
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    public function send($user_id, $type, $title, $body, $data = []) {
        $this->CI->db->insert('notifications', [
            'user_id'    => $user_id,
            'notif_type' => $type,
            'title'      => $title,
            'body'       => $body,
            'data'       => json_encode($data),
            'is_read'    => 0,
            'status'     => 'active',
            'is_deleted' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->CI->db->insert_id();
    }

    public function send_to_role($role, $type, $title, $body, $data = []) {
        $users = $this->CI->db->where(['role' => $role, 'status' => 'active', 'is_deleted' => 0])
                              ->get('users')->result_array();
        foreach ($users as $u) {
            $this->send($u['id'], $type, $title, $body, $data);
        }
    }

    public function send_to_managers($type, $title, $body, $data = []) {
        $this->send_to_role('manager', $type, $title, $body, $data);
        $this->send_to_role('admin', $type, $title, $body, $data);
    }

    public function mark_read($id, $user_id) {
        return $this->CI->db->where(['id' => $id, 'user_id' => $user_id])
                            ->update('notifications', ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
    }

    public function unread_count($user_id) {
        return $this->CI->db->where(['user_id' => $user_id, 'is_read' => 0, 'is_deleted' => 0])->count_all_results('notifications');
    }
}
