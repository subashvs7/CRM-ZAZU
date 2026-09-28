<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Geo_alert_model extends MY_Model {
    protected $table = 'crm_geo_alerts';

    public function datatable($params, $status_filter = null) {
        $this->db->select('ga.id, gz.name AS zone_name, u.name AS user_name, ga.alert_type, ga.triggered_at, ga.resolved_at, ga.status')
            ->from('crm_geo_alerts ga')
            ->join('crm_geofence_zones gz', 'gz.id = ga.geofence_zone_id', 'left')
            ->join('crm_user u', 'u.id = ga.user_id', 'left');

        if ($status_filter === 'deleted') $this->db->where('ga.is_deleted', 1);
        else                              $this->db->where('ga.is_deleted', 0);

        $search = $params['search']['value'] ?? '';
        if ($search) {
            $this->db->group_start()
                ->like('gz.name', $search)->or_like('u.name', $search)->or_like('ga.alert_type', $search)
                ->group_end();
        }

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('ga.id', 'desc');
        if (isset($params['length']) && $params['length'] != -1) {
            $this->db->limit($params['length'], $params['start'] ?? 0);
        }
        return [$this->db->get()->result_array(), $total];
    }

    public function resolve($id, $resolver_id) {
        return $this->update($id, ['resolved_at' => date('Y-m-d H:i:s'), 'resolved_by' => $resolver_id]);
    }
}
