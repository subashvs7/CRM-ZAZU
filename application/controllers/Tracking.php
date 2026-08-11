<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tracking extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(['Gps_track_model','User_model']);
    }

    public function live() {
        $staff = $this->User_model->get_field_staff();
        $this->load_view('tracking/live', ['page_title'=>'Live Tracking','page_js'=>'tracking','staff'=>$staff]);
    }

    public function live_data() {
        $staff = $this->User_model->get_field_staff();
        $uids  = array_column($staff, 'id');
        $pos   = $this->Gps_track_model->get_live_positions($uids);

        // Today's visit counts per user
        $today = date('Y-m-d');
        $visit_rows = $this->db
            ->select('vl.user_id, COUNT(*) AS visit_count, MAX(c.name) AS last_customer')
            ->from('visit_logs vl')
            ->join('customers c', 'c.id = vl.customer_id', 'left')
            ->where(['vl.is_deleted' => 0])
            ->where('DATE(vl.check_in_at)', $today)
            ->where_in('vl.user_id', $uids ?: [0])
            ->group_by('vl.user_id')
            ->get()->result_array();
        $visit_map = [];
        foreach ($visit_rows as $vr) {
            $visit_map[$vr['user_id']] = $vr;
        }

        // Speed from last two GPS pings
        $result = [];
        foreach ($staff as $s) {
            $p = $pos[$s['id']] ?? null;
            $vm = $visit_map[$s['id']] ?? null;
            $result[] = [
                'user_id'       => $s['id'],
                'name'          => $s['name'],
                'phone'         => $s['phone'] ?? null,
                'lat'           => $p['lat']     ?? null,
                'lng'           => $p['lng']     ?? null,
                'battery'       => $p['battery'] ?? null,
                'accuracy'      => $p['accuracy'] ?? null,
                'ts'            => $p['ts']      ?? null,
                'online'        => $p ? (time() - ($p['ts'] ?? 0)) < 300 : false,
                'visits_today'  => $vm ? (int)$vm['visit_count'] : 0,
                'last_customer' => $vm ? $vm['last_customer'] : null,
            ];
        }
        $this->json_success($result);
    }

    public function trail($user_id) {
        $user = $this->User_model->get_by_id($user_id);
        if (!$user) show_404();
        $this->load_view('tracking/trail', ['page_title'=>'GPS Trail — '.$user['name'],'user'=>$user,'page_js'=>'tracking']);
    }

    public function trail_data() {
        $uid  = (int)$this->input->get('user_id');
        $date = $this->input->get('date') ?: date('Y-m-d');
        $data = $this->Gps_track_model->get_trail($uid, $date);

        // Fetch customer visits for this user on this date
        $visits = $this->db->select('vl.id, vl.customer_id, vl.check_in_at, vl.check_out_at, vl.check_in_lat, vl.check_in_lng, vl.check_out_lat, vl.check_out_lng, vl.notes, c.name AS customer_name, c.latitude AS customer_lat, c.longitude AS customer_lng')
            ->from('visit_logs vl')
            ->join('customers c', 'c.id = vl.customer_id', 'left')
            ->where(['vl.user_id' => $uid, 'vl.is_deleted' => 0])
            ->where('DATE(vl.check_in_at)', $date)
            ->order_by('vl.check_in_at', 'asc')
            ->get()->result_array(); 

        // Fetch customer IDs that are relevant to this user on this specific date:
        // 1. Created on this date
        $c_created = $this->db->select('id')
            ->from('customers')
            ->where('DATE(created_at)', $date)
            ->where('assigned_to', $uid)
            ->where('is_deleted', 0)
            ->get()->result_array();

        // 2. Planned for this date for this user
        $c_planned = $this->db->select('customer_id AS id')
            ->from('visit_plans')
            ->where('planned_date', $date)
            ->where('user_id', $uid)
            ->where('is_deleted', 0)
            ->get()->result_array();

        // 3. Visited on this date by this user (from visit_logs)
        $c_visited = $this->db->select('customer_id AS id')
            ->from('visit_logs')
            ->where('user_id', $uid)
            ->where('is_deleted', 0)
            ->where('DATE(check_in_at)', $date)
            ->get()->result_array();

        $cust_ids = array_unique(array_filter(array_merge(
            array_column($c_created, 'id'),
            array_column($c_planned, 'id'),
            array_column($c_visited, 'id')
        )));

        $day_customers = [];
        $day_visits = [];
        if (!empty($cust_ids)) {
            $day_customers = $this->db->select('c.id, c.name, c.phone, c.city, c.latitude, c.longitude, u.name AS assigned_staff')
                ->from('customers c')
                ->join('users u', 'u.id = c.assigned_to', 'left')
                ->where_in('c.id', $cust_ids)
                ->where('c.is_deleted', 0)
                ->get()->result_array();

            $day_visits = $this->db->select('vl.customer_id, vl.check_in_at, vl.check_out_at, vl.notes, u.name AS visited_by_staff')
                ->from('visit_logs vl')
                ->join('users u', 'u.id = vl.user_id', 'left')
                ->where_in('vl.customer_id', $cust_ids)
                ->where('DATE(vl.check_in_at)', $date)
                ->where('vl.is_deleted', 0)
                ->order_by('vl.check_in_at', 'asc')
                ->get()->result_array();
        }

        $visit_map = [];
        foreach ($day_visits as $dv) {
            $visit_map[$dv['customer_id']][] = $dv;
        }

        foreach ($day_customers as &$c) {
            $c['visits'] = $visit_map[$c['id']] ?? [];
        }

        $this->json_success([
            'trail'         => $data,
            'visits'        => $visits,
            'day_customers' => $day_customers
        ]);
    }

    public function ping_status() {
        $this->require_role(['admin','manager']);
        $this->load_view('tracking/ping_status', ['page_title'=>'Ping Status','page_js'=>'tracking']);
    }

    public function ping_datatable() {
        $params = $this->input->get();
        [$rows, $total] = $this->Gps_track_model->ping_datatable($params);
        $data = [];
        foreach ($rows as $r) {
            $data[] = [$r['id'],esc_html($r['user_name']),$r['latitude'].','.$r['longitude'],$r['accuracy']??'-',$r['speed']??'-',$r['battery_level']?$r['battery_level'].'%':'-',date('d M Y H:i:s',strtotime($r['recorded_at']))];
        }
        $this->json_list($data,$total,$total);
    }
}
