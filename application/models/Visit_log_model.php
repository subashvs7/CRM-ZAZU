<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Visit_log_model extends MY_Model {
    protected $table = 'visit_logs';

    public function get_with_details($id) {
        return $this->db->select('vl.*, c.name AS customer_name, u.name AS user_name')
            ->from('visit_logs vl')
            ->join('customers c', 'c.id = vl.customer_id', 'left')
            ->join('users u', 'u.id = vl.user_id', 'left')
            ->where(['vl.id' => $id, 'vl.is_deleted' => 0])
            ->get()->row_array();
    }

    public function get_open_visit($user_id, $customer_id = null) {
        $q = $this->db->where(['vl.user_id' => $user_id, 'vl.is_deleted' => 0])
            ->where('vl.check_in_at IS NOT NULL')->where('vl.check_out_at IS NULL');
        if ($customer_id) $q->where('vl.customer_id', $customer_id);
        return $this->db->from('visit_logs vl')->get()->row_array();
    }

    public function datatable($params, $status_filter = null, $user_id = null, $role = null) {
        $this->db->select('vl.id, vl.check_in_at, vl.check_out_at, c.name AS customer_name, u.name AS user_name, vl.distance_from_customer, vl.is_auto_checkin, vl.status')
            ->from('visit_logs vl')
            ->join('customers c', 'c.id = vl.customer_id', 'left')
            ->join('users u', 'u.id = vl.user_id', 'left');

        if ($status_filter === 'deleted')      $this->db->where('vl.is_deleted', 1);
        elseif ($status_filter)                $this->db->where(['vl.status' => $status_filter, 'vl.is_deleted' => 0]);
        else                                   $this->db->where('vl.is_deleted', 0);

        if ($role === 'field_staff') $this->db->where('vl.user_id', $user_id);

        $search = $params['search']['value'] ?? '';
        if ($search) {
            $this->db->group_start()
                ->like('c.name', $search)->or_like('u.name', $search)
                ->group_end();
        }

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('vl.id', 'desc')->limit($params['length'], $params['start']);
        return [$this->db->get()->result_array(), $total];
    }

    public function planned_vs_actual($from, $to, $user_id = null) {
        $plans = $this->db->select('vp.planned_date, vp.user_id, u.name AS user_name, COUNT(*) AS planned')
            ->from('visit_plans vp')
            ->join('users u', 'u.id = vp.user_id', 'left')
            ->where(['vp.is_deleted' => 0])->where('vp.planned_date >=', $from)->where('vp.planned_date <=', $to);
        if ($user_id) $plans->where('vp.user_id', $user_id);
        $plans = $plans->group_by('vp.planned_date, vp.user_id')->get()->result_array();

        $actual = $this->db->select('DATE(vl.check_in_at) AS visit_date, vl.user_id, COUNT(*) AS actual')
            ->from('visit_logs vl')
            ->where(['vl.is_deleted' => 0])->where('DATE(vl.check_in_at) >=', $from)->where('DATE(vl.check_in_at) <=', $to);
        if ($user_id) $actual->where('vl.user_id', $user_id);
        $actual = $actual->group_by('DATE(vl.check_in_at), vl.user_id')->get()->result_array();

        return ['plans' => $plans, 'actual' => $actual];
    }
}
