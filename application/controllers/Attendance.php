<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(['Attendance_model','Shift_assignment_model','User_model']);
    }

    public function index() {
        $this->load_view('attendance/index', ['page_title'=>'Attendance','page_js'=>'attendance','sf'=>'']);
    }

    public function punch() {
        $today = $this->Attendance_model->get_by_user_date($this->get_user_id(), date('Y-m-d'));
        $this->load_view('attendance/punch', ['page_title'=>'Punch','att'=>$today]);
    }

    public function do_punch() {
        $uid  = $this->get_user_id();
        $lat  = $this->input->post('latitude');
        $lng  = $this->input->post('longitude');
        $addr = $this->input->post('address');
        $type = $this->input->post('type'); // in or out

        $today = $this->Attendance_model->get_by_user_date($uid, date('Y-m-d'));

        if ($type === 'in') {
            if ($today && $today['punch_in_at']) $this->json_error('Already punched in today.');
            $data = ['user_id'=>$uid,'punch_in_lat'=>$lat,'punch_in_lng'=>$lng,'punch_in_address'=>$addr];
            $id   = $this->Attendance_model->punch_in($uid, $data);
            $this->json_success(['id'=>$id], 'Punched in at '.date('H:i'));
        } else {
            if (!$today || !$today['punch_in_at']) $this->json_error('Not punched in yet.');
            if ($today['punch_out_at']) $this->json_error('Already punched out today.');
            $data = ['punch_out_lat'=>$lat,'punch_out_lng'=>$lng,'punch_out_address'=>$addr];
            $this->Attendance_model->punch_out($today['id'], $data);
            $this->json_success([], 'Punched out at '.date('H:i'));
        }
    }

    public function monthly() {
        $this->load_view('attendance/monthly', ['page_title'=>'Monthly Attendance','page_js'=>'attendance']);
    }

    public function monthly_data() {
        $uid   = $this->is_manager() ? (int)$this->input->get('user_id') ?: $this->get_user_id() : $this->get_user_id();
        $year  = (int)($this->input->get('year')  ?: date('Y'));
        $month = (int)($this->input->get('month') ?: date('m'));
        $data  = $this->Attendance_model->monthly_data($uid, $year, $month);
        $this->json_success($data);
    }

    public function datatable() {
        $params = $this->input->get(); $sf = $this->input->get('status_filter');
        [$rows, $total] = $this->Attendance_model->datatable($params, $sf, $this->get_user_id(), $this->get_role());
        $data = [];
        foreach ($rows as $r) {
            $acts = '';
            if ($this->is_manager()) $acts = '<button class="btn btn-xs btn-warning btn-regularize" data-id="'.$r['id'].'"><i class="fa fa-edit"></i> Regularize</button>';
            $data[] = [
                $r['id'], $r['date'], esc_html($r['user_name']),
                $r['punch_in_at'] ? date('H:i',strtotime($r['punch_in_at'])) : '-',
                $r['punch_out_at'] ? date('H:i',strtotime($r['punch_out_at'])) : '-',
                att_status_badge($r['attendance_status']),
                $r['working_hours'].'h',
                $r['face_verified'] ? '<span class="label label-success">Verified</span>' : '<span class="label label-default">No</span>',
                $r['is_regularized'] ? '<span class="label label-warning">Yes</span>' : '',
                $acts,
            ];
        }
        $this->json_list($data, $total, $total);
    }

    public function corrections() {
        $this->require_role(['admin','manager']);
        $this->load_view('attendance/corrections', ['page_title'=>'Attendance Corrections','page_js'=>'attendance']);
    }

    public function corrections_datatable() {
        $this->require_role(['admin','manager']);
        $params = $this->input->get();
        $this->db->select('a.id, u.name AS user_name, a.date, a.punch_in_at, a.punch_out_at, a.attendance_status, a.regularized_reason')
            ->from('attendance a')
            ->join('users u','u.id=a.user_id','left')
            ->where(['a.is_regularized'=>0,'a.is_deleted'=>0]);
        $total = $this->db->count_all_results('',false);
        $this->db->order_by('a.date','desc')->limit($params['length'],$params['start']);
        $rows = $this->db->get()->result_array();
        $data = [];
        foreach ($rows as $r) {
            $data[] = [$r['id'],esc_html($r['user_name']),$r['date'],$r['punch_in_at']??'-',$r['punch_out_at']??'-',att_status_badge($r['attendance_status']),'<button class="btn btn-xs btn-success btn-approve-corr" data-id="'.$r['id'].'"><i class="fa fa-check"></i> Approve</button>'];
        }
        $this->json_list($data,$total,$total);
    }

    public function request_correction() {
        $id     = (int)$this->input->post('id');
        $reason = $this->input->post('reason');
        if (!$id || !$reason) $this->json_error('ID and reason required.');
        $this->Attendance_model->update($id, ['regularized_reason'=>$reason]);
        $this->json_success([],'Correction requested.');
    }

    public function approve_correction() {
        $this->require_role(['admin','manager']);
        $id = (int)$this->input->post('id');
        $this->Attendance_model->update($id, ['is_regularized'=>1,'regularized_by'=>$this->get_user_id()]);
        $this->json_success([],'Correction approved.');
    }

    public function update_status() {
        $id=(int)$this->input->post('id');$action=$this->input->post('action');
        if($action==='delete') $this->Attendance_model->soft_delete($id);
        $this->json_success([],'Done.');
    }
}
