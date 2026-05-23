<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lead_activity_model extends MY_Model {
    protected $table = 'lead_activities';

    public function get_by_lead($lead_id) {
        return $this->db->select('la.*, u.name AS user_name')
            ->from('lead_activities la')
            ->join('users u', 'u.id = la.user_id', 'left')
            ->where(['la.lead_id' => $lead_id, 'la.is_deleted' => 0])
            ->order_by('la.occurred_at', 'desc')
            ->get()->result_array();
    }
}
