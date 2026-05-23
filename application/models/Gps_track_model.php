<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gps_track_model extends MY_Model {
    protected $table = 'gps_tracks';

    public function get_trail($user_id, $date) {
        return $this->db->where(['user_id' => $user_id, 'is_deleted' => 0])
            ->where('DATE(recorded_at)', $date)
            ->order_by('recorded_at', 'asc')
            ->get($this->table)->result_array();
    }

    public function get_last_ping($user_id) {
        return $this->db->where(['user_id' => $user_id, 'is_deleted' => 0])
            ->order_by('recorded_at', 'desc')
            ->limit(1)->get($this->table)->row_array();
    }

    public function ping_datatable($params) {
        $this->db->select('gt.id, u.name AS user_name, gt.latitude, gt.longitude, gt.accuracy, gt.speed, gt.battery_level, gt.recorded_at')
            ->from('gps_tracks gt')
            ->join('users u', 'u.id = gt.user_id', 'left')
            ->where('gt.is_deleted', 0);

        $search = $params['search']['value'] ?? '';
        if ($search) $this->db->like('u.name', $search);

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('gt.id', 'desc')->limit($params['length'], $params['start']);
        return [$this->db->get()->result_array(), $total];
    }

    public function save_live_file($user_id, $lat, $lng, $accuracy = null, $battery = null) {
        $path = GPS_LIVE_PATH . $user_id . '.json';
        $data = [
            'user_id'   => $user_id,
            'lat'       => $lat,
            'lng'       => $lng,
            'accuracy'  => $accuracy,
            'battery'   => $battery,
            'ts'        => time(),
        ];
        file_put_contents($path, json_encode($data));
    }

    public function get_live_positions($user_ids = []) {
        $positions = [];
        foreach ($user_ids as $uid) {
            $path = GPS_LIVE_PATH . $uid . '.json';
            if (file_exists($path)) {
                $data = json_decode(file_get_contents($path), true);
                if ($data && (time() - $data['ts']) < 300) {
                    $positions[$uid] = $data;
                }
            }
        }
        return $positions;
    }
}
