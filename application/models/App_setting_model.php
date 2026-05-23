<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_setting_model extends MY_Model {
    protected $table = 'app_settings';

    public function get_by_key($key) {
        $row = $this->db->where(['setting_key' => $key, 'is_deleted' => 0])->get($this->table)->row_array();
        return $row ? $row['setting_value'] : null;
    }

    public function get_by_group($group) {
        return $this->db->where(['setting_group' => $group, 'is_deleted' => 0])->get($this->table)->result_array();
    }

    public function set($key, $value) {
        $exists = $this->db->where(['setting_key' => $key])->count_all_results($this->table);
        if ($exists) {
            return $this->db->where('setting_key', $key)->update($this->table, ['setting_value' => $value, 'updated_at' => date('Y-m-d H:i:s')]);
        } else {
            return $this->db->insert($this->table, [
                'setting_key'   => $key,
                'setting_value' => $value,
                'setting_group' => 'general',
                'status'        => 'active',
                'is_deleted'    => 0,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function set_bulk(array $settings) {
        foreach ($settings as $k => $v) $this->set($k, $v);
    }

    public function get_all_as_array() {
        $rows = $this->get_all();
        $out  = [];
        foreach ($rows as $r) $out[$r['setting_key']] = $r['setting_value'];
        return $out;
    }
}
