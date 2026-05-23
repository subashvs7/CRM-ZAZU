<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * GPS Web Ping Controller
 * Receives location from logged-in field staff browser (no API token needed).
 * Called every N minutes by the auto-tracker JS in footer.php.
 */
class Gps extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login(); // any role — field_staff, manager, admin
        $this->load->model(['Gps_track_model', 'Geofence_zone_model', 'Visit_log_model']);
    }

    /**
     * POST gps/ping
     * Body: lat, lng, accuracy, battery
     * Returns: { status, next_ping_seconds }
     */
    public function ping() {
        $lat      = $this->input->post('lat');
        $lng      = $this->input->post('lng');
        $accuracy = $this->input->post('accuracy');
        $battery  = $this->input->post('battery');

        if (!$lat || !$lng) {
            $this->json_error('Coordinates required.', 400);
        }

        $uid = $this->get_user_id();

        // 1. Save to gps_tracks table
        $this->db->insert('gps_tracks', [
            'user_id'      => $uid,
            'latitude'     => $lat,
            'longitude'    => $lng,
            'accuracy'     => $accuracy ?: null,
            'speed'        => null,
            'battery_level'=> ($battery !== null && $battery !== '') ? (int)$battery : null,
            'recorded_at'  => date('Y-m-d H:i:s'),
            'status'       => 'active',
            'is_deleted'   => 0,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        // 2. Update live file (used by tracking/live_data)
        $this->Gps_track_model->save_live_file($uid, $lat, $lng, $accuracy, $battery);

        // 3. Check geofence zones for auto check-in
        $zones = $this->Geofence_zone_model->check_point_in_zones($lat, $lng);
        foreach ($zones as $zone) {
            if ($zone['auto_checkin'] && $zone['customer_id']) {
                $open = $this->Visit_log_model->get_open_visit($uid);
                if (!$open) {
                    $this->db->insert('visit_logs', [
                        'user_id'         => $uid,
                        'customer_id'     => $zone['customer_id'],
                        'check_in_at'     => date('Y-m-d H:i:s'),
                        'check_in_lat'    => $lat,
                        'check_in_lng'    => $lng,
                        'is_auto_checkin' => 1,
                        'geofence_zone_id'=> $zone['id'],
                        'status'          => 'active',
                        'is_deleted'      => 0,
                        'created_at'      => date('Y-m-d H:i:s'),
                        'updated_at'      => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        // Return next ping interval (seconds) from settings
        $interval = (int) get_setting('gps_ping_interval', 600);
        $this->json_success([
            'next_ping_seconds' => $interval,
            'recorded_at'       => date('Y-m-d H:i:s'),
        ], 'Location recorded.');
    }

    /**
     * GET gps/status — current user's last known position
     */
    public function status() {
        $last = $this->Gps_track_model->get_last_ping($this->get_user_id());
        $this->json_success($last ?: []);
    }
}
