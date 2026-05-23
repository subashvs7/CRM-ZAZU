<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Gps_track_model','Geofence_zone_model','Visit_log_model','User_model']);
        $this->load->library('Notification_sender');
    }

    private function authenticate_token() {
        $token = $this->input->get_request_header('Authorization', TRUE);
        if (!$token) $token = $this->input->post('api_token');
        if (!$token) return null;
        $token = str_replace('Bearer ', '', $token);
        return $this->db->where(['fcm_token'=>$token,'is_deleted'=>0,'status'=>'active'])->get('users')->row_array();
    }

    public function get_token() {
        $email = $this->input->post('email');
        $pass  = $this->input->post('password');
        $user  = $this->db->where(['email'=>$email,'is_deleted'=>0,'status'=>'active'])->get('users')->row_array();
        if (!$user || !password_verify($pass, $user['password'])) $this->json_error('Invalid credentials.', 401);
        $token = bin2hex(random_bytes(32));
        $this->db->where('id',$user['id'])->update('users',['fcm_token'=>$token,'updated_at'=>date('Y-m-d H:i:s')]);
        $this->json_success(['token'=>$token,'user_id'=>$user['id'],'name'=>$user['name'],'role'=>$user['role']]);
    }

    public function gps_ping() {
        // CSRF excluded for mobile API
        $user = $this->authenticate_token();
        if (!$user) $this->json_error('Unauthorized.', 401);

        $lat     = $this->input->post('lat') ?: $this->input->post('latitude');
        $lng     = $this->input->post('lng') ?: $this->input->post('longitude');
        $acc     = $this->input->post('accuracy');
        $speed   = $this->input->post('speed');
        $battery = $this->input->post('battery');

        if (!$lat || !$lng) $this->json_error('Coordinates required.', 400);

        $track_data = [
            'user_id'     => $user['id'],
            'latitude'    => $lat,
            'longitude'   => $lng,
            'accuracy'    => $acc ?: null,
            'speed'       => $speed ?: null,
            'battery_level' => $battery ? (int)$battery : null,
            'recorded_at' => date('Y-m-d H:i:s'),
            'status'      => 'active',
            'is_deleted'  => 0,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('gps_tracks', $track_data);

        // Save live file
        $this->Gps_track_model->save_live_file($user['id'], $lat, $lng, $acc, $battery);

        // Check geofence auto-checkin
        $zones = $this->Geofence_zone_model->check_point_in_zones($lat, $lng);
        foreach ($zones as $zone) {
            if ($zone['auto_checkin']) {
                $open = $this->Visit_log_model->get_open_visit($user['id']);
                if (!$open && $zone['customer_id']) {
                    $this->db->insert('visit_logs', [
                        'user_id'=>$user['id'],'customer_id'=>$zone['customer_id'],
                        'check_in_at'=>date('Y-m-d H:i:s'),'check_in_lat'=>$lat,'check_in_lng'=>$lng,
                        'is_auto_checkin'=>1,'geofence_zone_id'=>$zone['id'],
                        'status'=>'active','is_deleted'=>0,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s'),
                    ]);
                }
            }
            if ($zone['alert_on_enter']) {
                $this->db->insert('geo_alerts', [
                    'geofence_zone_id'=>$zone['id'],'user_id'=>$user['id'],
                    'alert_type'=>'enter','triggered_at'=>date('Y-m-d H:i:s'),
                    'latitude'=>$lat,'longitude'=>$lng,
                    'status'=>'active','is_deleted'=>0,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s'),
                ]);
            }
        }

        $interval = (int)get_setting('gps_ping_interval', 30);
        $this->json_success(['next_ping'=>$interval]);
    }

    public function live_positions() {
        $user = $this->authenticate_token();
        if (!$user) $this->json_error('Unauthorized.', 401);

        $staff = $this->User_model->get_field_staff();
        $uids  = array_column($staff, 'id');
        $pos   = $this->Gps_track_model->get_live_positions($uids);
        $this->json_success($pos);
    }

    public function trail() {
        $user = $this->authenticate_token();
        if (!$user) $this->json_error('Unauthorized.', 401);

        $uid  = (int)$this->input->get('user_id') ?: $user['id'];
        $date = $this->input->get('date') ?: date('Y-m-d');
        $data = $this->Gps_track_model->get_trail($uid, $date);
        $this->json_success($data);
    }
}
