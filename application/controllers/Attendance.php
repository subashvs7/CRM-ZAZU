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
            if ($this->is_manager()) {
                $in  = $r['punch_in_at']  ? date('H:i', strtotime($r['punch_in_at']))  : '';
                $out = $r['punch_out_at'] ? date('H:i', strtotime($r['punch_out_at'])) : '';
                $acts = '<button class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold bg-amber-100 text-amber-700 rounded-lg hover:bg-amber-200 transition-colors btn-regularize"'
                      . ' data-id="'.$r['id'].'" data-date="'.$r['date'].'" data-in="'.$in.'" data-out="'.$out.'">'
                      . '<i class="fa fa-edit" style="font-size:11px"></i> Regularize</button>';
            }
            $face = $r['face_verified']
                ? '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-green-100 text-green-700">Verified</span>'
                : '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-500">No</span>';
            $reg = $r['is_regularized']
                ? '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-amber-100 text-amber-700">Yes</span>'
                : '';
            $data[] = [
                $r['id'], $r['date'], esc_html($r['user_name']),
                $r['punch_in_at'] ? date('H:i',strtotime($r['punch_in_at'])) : '-',
                $r['punch_out_at'] ? date('H:i',strtotime($r['punch_out_at'])) : '-',
                att_status_badge($r['attendance_status']),
                $r['working_hours'].'h',
                $face, $reg, $acts,
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
            $data[] = [$r['id'],esc_html($r['user_name']),$r['date'],$r['punch_in_at']??'-',$r['punch_out_at']??'-',att_status_badge($r['attendance_status']),'<button class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors btn-approve-corr" data-id="'.$r['id'].'"><i class="fa fa-check" style="font-size:11px"></i> Approve</button>'];
        }
        $this->json_list($data,$total,$total);
    }

    public function request_correction() {
        $id        = (int)$this->input->post('id');
        $reason    = $this->input->post('reason');
        $corr_date = $this->input->post('corrected_date');
        $corr_in   = $this->input->post('corrected_in');
        $corr_out  = $this->input->post('corrected_out');

        if (!$id || !$reason) $this->json_error('Attendance ID and reason are required.');

        $record = $this->Attendance_model->get_by_id($id);
        if (!$record) $this->json_error('Attendance record not found.', 404);

        $upd = ['regularized_reason' => $reason];

        if ($this->is_manager()) {
            $base_date = ($corr_date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $corr_date)) ? $corr_date : $record['date'];
            if ($corr_in)  $upd['punch_in_at']  = $base_date . ' ' . $corr_in  . ':00';
            if ($corr_out) $upd['punch_out_at'] = $base_date . ' ' . $corr_out . ':00';
            $upd['is_regularized'] = 1;
            $upd['regularized_by'] = $this->get_user_id();
            $msg = 'Attendance regularized successfully.';
        } else {
            $msg = 'Correction requested. Awaiting manager review.';
        }

        $this->Attendance_model->update($id, $upd);
        $this->json_success([], $msg);
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
