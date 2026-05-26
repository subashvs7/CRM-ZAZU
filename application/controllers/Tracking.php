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
        $result = [];
        foreach ($staff as $s) {
            $p = $pos[$s['id']] ?? null;
            $result[] = [
                'user_id'   => $s['id'],
                'name'      => $s['name'],
                'lat'       => $p['lat']    ?? null,
                'lng'       => $p['lng']    ?? null,
                'battery'   => $p['battery']?? null,
                'ts'        => $p['ts']     ?? null,
                'online'    => $p ? (time() - ($p['ts']??0)) < 300 : false,
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
        $this->json_success($data);
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
