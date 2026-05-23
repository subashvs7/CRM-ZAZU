<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Selfie extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(['Attendance_model','App_setting_model','User_model']);
    }

    public function log() {
        $this->load_view('selfie/log', ['page_title'=>'Selfie Log','page_js'=>'attendance','sf'=>'']);
    }

    public function log_datatable() {
        $params = $this->input->get();
        $this->db->select('a.id, u.name AS user_name, a.date, a.punch_in_selfie, a.face_verified, a.face_confidence_score, a.punch_in_at')
            ->from('attendance a')
            ->join('users u','u.id=a.user_id','left')
            ->where(['a.is_deleted'=>0])->where('a.punch_in_selfie IS NOT NULL');
        $total = $this->db->count_all_results('',false);
        $this->db->order_by('a.date','desc')->limit($params['length'],$params['start']);
        $rows = $this->db->get()->result_array();
        $data = [];
        foreach ($rows as $r) {
            $img = $r['punch_in_selfie'] ? '<a href="'.base_url('uploads/selfies/'.basename($r['punch_in_selfie'])).'" target="_blank"><img src="'.base_url('uploads/selfies/'.basename($r['punch_in_selfie'])).'" style="height:40px;border-radius:4px"></a>' : '-';
            $verified = $r['face_verified'] ? '<span class="label label-success">Verified</span>' : '<span class="label label-danger">Mismatch</span>';
            $data[] = [$r['id'],esc_html($r['user_name']),$r['date'],$r['punch_in_at']??'-',$img,$verified,$r['face_confidence_score']??'-'];
        }
        $this->json_list($data,$total,$total);
    }

    public function mismatches() {
        $this->require_role(['admin','manager']);
        $this->load_view('selfie/mismatches', ['page_title'=>'Face Mismatches','page_js'=>'attendance']);
    }

    public function mismatches_datatable() {
        $params = $this->input->get();
        $this->db->select('a.id, u.name AS user_name, a.date, a.punch_in_selfie, a.face_confidence_score, a.punch_in_at')
            ->from('attendance a')
            ->join('users u','u.id=a.user_id','left')
            ->where(['a.is_deleted'=>0,'a.face_verified'=>0])->where('a.punch_in_selfie IS NOT NULL');
        $total = $this->db->count_all_results('',false);
        $this->db->order_by('a.date','desc')->limit($params['length'],$params['start']);
        $rows = $this->db->get()->result_array();
        $data = [];
        foreach ($rows as $r) {
            $acts = '<button class="btn btn-xs btn-success btn-override-selfie" data-id="'.$r['id'].'"><i class="fa fa-check"></i> Override</button>';
            $data[] = [$r['id'],esc_html($r['user_name']),$r['date'],$r['punch_in_at']??'-',$r['face_confidence_score']??'-',$acts];
        }
        $this->json_list($data,$total,$total);
    }

    public function override() {
        $this->require_role(['admin','manager']);
        $id = (int)$this->input->post('id');
        $this->Attendance_model->update($id, ['face_verified'=>1]);
        $this->json_success([],'Selfie verification overridden.');
    }

    public function settings() {
        $this->require_role('admin');
        $settings = ['face_match_threshold'=>$this->App_setting_model->get_by_key('face_match_threshold')];
        $this->load_view('selfie/settings', ['page_title'=>'Selfie Settings','settings'=>$settings]);
    }

    public function save_settings() {
        $this->require_role('admin');
        $this->App_setting_model->set('face_match_threshold', $this->input->post('face_match_threshold'));
        $this->json_success([],'Settings saved.');
    }
}
