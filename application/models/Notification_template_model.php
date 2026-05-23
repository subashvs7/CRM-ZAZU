<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_template_model extends MY_Model {
    protected $table = 'notification_templates';

    public function get_by_name($name) {
        return $this->db->where(['name' => $name, 'is_deleted' => 0])->get($this->table)->row_array();
    }

    public function datatable($params) {
        $this->db->from($this->table)->where('is_deleted', 0);
        $search = $params['search']['value'] ?? '';
        if ($search) $this->db->like('name', $search);
        $total = $this->db->count_all_results('', false);
        $this->db->order_by('name')->limit($params['length'], $params['start']);
        return [$this->db->get()->result_array(), $total];
    }

    public function render($name, array $vars = []) {
        $tpl = $this->get_by_name($name);
        if (!$tpl) return '';
        $body = $tpl['body'];
        foreach ($vars as $k => $v) $body = str_replace('{{' . $k . '}}', $v, $body);
        return $body;
    }
}
