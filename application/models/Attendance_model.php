<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance_model extends MY_Model {
    protected $table = 'attendance';

    public function get_by_user_date($user_id, $date) {
        return $this->db->where(['user_id' => $user_id, 'date' => $date, 'is_deleted' => 0])
                        ->get($this->table)->row_array();
    }

    public function punch_in($user_id, $data) {
        $existing = $this->get_by_user_date($user_id, date('Y-m-d'));
        $data['date']       = date('Y-m-d');
        $data['punch_in_at']= date('Y-m-d H:i:s');
        $data['status']     = 'active';
        $data['is_deleted'] = 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($existing) {
            unset($data['created_at']);
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('id', $existing['id'])->update($this->table, $data);
            return $existing['id'];
        }
        $data['attendance_status'] = 'present';
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function punch_out($id, $data) {
        $att = $this->get_by_id($id);
        if (!$att) return false;
        $data['punch_out_at']   = date('Y-m-d H:i:s');
        $punch_in               = strtotime($att['punch_in_at']);
        $punch_out              = time();
        $hours                  = round(($punch_out - $punch_in) / 3600, 2);
        $data['working_hours']  = $hours;
        $data['attendance_status'] = $hours >= 4 ? ($hours >= 8 ? 'present' : 'half_day') : 'half_day';
        return $this->update($id, $data);
    }

    public function datatable($params, $status_filter = null, $user_id = null, $role = null) {
        $this->db->select('a.id, a.date, u.name AS user_name, a.punch_in_at, a.punch_out_at, a.attendance_status, a.working_hours, a.face_verified, a.is_regularized, a.status')
            ->from('attendance a')
            ->join('users u', 'u.id = a.user_id', 'left');

        if ($status_filter === 'deleted') $this->db->where('a.is_deleted', 1);
        else                              $this->db->where('a.is_deleted', 0);

        if ($role === 'field_staff') $this->db->where('a.user_id', $user_id);

        $search = $params['search']['value'] ?? '';
        if ($search) $this->db->like('u.name', $search);

        $total = $this->db->count_all_results('', false);
        $this->db->order_by('a.date', 'desc')->limit($params['length'], $params['start']);
        return [$this->db->get()->result_array(), $total];
    }

    public function monthly_data($user_id, $year, $month) {
        return $this->db->where(['user_id' => $user_id, 'is_deleted' => 0])
            ->where('YEAR(date)', $year)->where('MONTH(date)', $month)
            ->order_by('date', 'asc')->get($this->table)->result_array();
    }

    public function get_stats($user_id, $from, $to) {
        return $this->db->select('attendance_status, COUNT(*) AS cnt')
            ->from($this->table)
            ->where(['user_id' => $user_id, 'is_deleted' => 0])
            ->where('date >=', $from)->where('date <=', $to)
            ->group_by('attendance_status')->get()->result_array();
    }
}
