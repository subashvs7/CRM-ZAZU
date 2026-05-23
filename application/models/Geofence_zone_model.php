<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Geofence_zone_model extends MY_Model {
    protected $table = 'geofence_zones';

    public function get_with_customer($id) {
        return $this->db->select('gz.*, c.name AS customer_name')
            ->from('geofence_zones gz')
            ->join('customers c', 'c.id = gz.customer_id', 'left')
            ->where(['gz.id' => $id, 'gz.is_deleted' => 0])
            ->get()->row_array();
    }

    public function get_active_zones() {
        return $this->db->select('gz.*, c.name AS customer_name')
            ->from('geofence_zones gz')
            ->join('customers c', 'c.id = gz.customer_id', 'left')
            ->where(['gz.status' => 'active', 'gz.is_deleted' => 0])
            ->get()->result_array();
    }

    public function datatable($params, $status_filter = null) {
        $this->db->select('gz.id, gz.name, gz.zone_type, gz.radius_meters, gz.auto_checkin, gz.alert_on_exit, gz.alert_on_enter, gz.status, gz.created_at')
            ->from('geofence_zones gz');

        if ($status_filter === 'deleted')      $this->db->where('gz.is_deleted', 1);
        elseif ($status_filter)                $this->db->where(['gz.status' => $status_filter, 'gz.is_deleted' => 0]);
        else                                   $this->db->where('gz.is_deleted', 0);

        $search = $params['search']['value'] ?? '';
        if ($search) $this->db->like('gz.name', $search);

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('gz.id', 'desc')->limit($params['length'], $params['start']);
        return [$this->db->get()->result_array(), $total];
    }

    public function check_point_in_zones($lat, $lng) {
        $zones = $this->get_active_zones();
        $matched = [];
        foreach ($zones as $z) {
            if ($z['center_lat'] && $z['center_lng'] && $z['radius_meters']) {
                if (is_point_in_circle($lat, $lng, $z['center_lat'], $z['center_lng'], $z['radius_meters'])) {
                    $matched[] = $z;
                }
            }
        }
        return $matched;
    }
}
