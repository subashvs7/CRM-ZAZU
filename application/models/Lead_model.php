<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lead_model extends MY_Model {
    protected $table = 'leads';

    public function get_with_details($id) {
        return $this->db->select('l.*, c.name AS customer_name, c.phone AS customer_phone, u.name AS assigned_name')
            ->from('leads l')
            ->join('customers c', 'c.id = l.customer_id', 'left')
            ->join('users u', 'u.id = l.assigned_to', 'left')
            ->where(['l.id' => $id, 'l.is_deleted' => 0])
            ->get()->row_array();
    }

    public function datatable($params, $status_filter = null, $user_id = null, $role = null) {
        $this->db->select('l.id, l.title, c.name AS customer_name, l.lead_status, l.source, u.name AS assigned_name, l.expected_value, l.expected_close_date, l.status, l.created_at')
            ->from('leads l')
            ->join('customers c', 'c.id = l.customer_id', 'left')
            ->join('users u', 'u.id = l.assigned_to', 'left');

        if ($status_filter === 'deleted')      $this->db->where('l.is_deleted', 1);
        elseif ($status_filter === 'active')   $this->db->where(['l.status' => 'active',   'l.is_deleted' => 0]);
        elseif ($status_filter === 'inactive') $this->db->where(['l.status' => 'inactive', 'l.is_deleted' => 0]);
        else                                   $this->db->where('l.is_deleted', 0);

        if ($role === 'field_staff') $this->db->where('l.assigned_to', $user_id);

        if (!empty($params['customer_id'])) $this->db->where('l.customer_id', (int)$params['customer_id']);

        $search = $params['search']['value'] ?? '';
        if ($search) {
            $this->db->group_start()
                ->like('l.title', $search)->or_like('c.name', $search)->or_like('l.lead_status', $search)
                ->group_end();
        }

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('l.id', 'desc')->limit($params['length'], $params['start']);
        return [$this->db->get()->result_array(), $total];
    }

    public function pipeline_data($user_id = null, $role = null) {
        $stages = ['new','contacted','qualified','proposal','negotiation','won','lost'];
        $result = [];
        foreach ($stages as $stage) {
            $q = $this->db->select('l.id, l.title, l.expected_value, l.expected_close_date, c.name AS customer_name, u.name AS assigned_name')
                ->from('leads l')
                ->join('customers c', 'c.id = l.customer_id', 'left')
                ->join('users u', 'u.id = l.assigned_to', 'left')
                ->where(['l.lead_status' => $stage, 'l.is_deleted' => 0, 'l.status' => 'active']);
            if ($role === 'field_staff') $q->where('l.assigned_to', $user_id);
            $result[$stage] = $q->order_by('l.id', 'desc')->get()->result_array();
        }
        return $result;
    }

    public function update_stage($id, $stage, $user_id) {
        $this->update($id, ['lead_status' => $stage]);
        $this->db->insert('lead_activities', [
            'lead_id'       => $id, 'user_id' => $user_id,
            'activity_type' => 'status_change',
            'notes'         => 'Stage changed to: ' . $stage,
            'occurred_at'   => date('Y-m-d H:i:s'),
            'status'        => 'active', 'is_deleted' => 0,
            'created_at'    => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function conversion_stats($from = null, $to = null, $user_id = null) {
        $this->db->select('lead_status, COUNT(*) AS cnt, SUM(expected_value) AS total_value')
            ->from('leads')
            ->where('is_deleted', 0)->where('status', 'active');
        if ($from) $this->db->where('created_at >=', $from);
        if ($to)   $this->db->where('created_at <=', $to . ' 23:59:59');
        if ($user_id) $this->db->where('assigned_to', $user_id);
        $this->db->group_by('lead_status');
        return $this->db->get()->result_array();
    }
}
