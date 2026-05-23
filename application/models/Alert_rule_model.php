<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Alert_rule_model extends MY_Model {
    protected $table = 'alert_rules';

    public function get_by_zone($zone_id) {
        return $this->db->where(['geofence_zone_id' => $zone_id, 'is_deleted' => 0, 'status' => 'active'])
                        ->get($this->table)->result_array();
    }

    public function datatable($params) {
        $this->db->select('ar.id, gz.name AS zone_name, ar.event_type, ar.notify_roles, ar.cooldown_minutes, ar.status, ar.created_at')
            ->from('alert_rules ar')
            ->join('geofence_zones gz', 'gz.id = ar.geofence_zone_id', 'left')
            ->where('ar.is_deleted', 0);

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('ar.id', 'desc')->limit($params['length'], $params['start']);
        return [$this->db->get()->result_array(), $total];
    }
}
