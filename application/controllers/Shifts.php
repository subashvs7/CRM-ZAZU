<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shifts extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(['Shift_model','Shift_assignment_model','User_model']);
    }

    public function index() {
        $this->load_view('shifts/index', ['page_title'=>'Shifts','page_js'=>'attendance','sf'=>'']);
    }

    public function datatable() {
        $params = $this->input->get(); $sf = $this->input->get('status_filter');
        [$rows, $total] = $this->Shift_model->datatable($params, $sf);
        $data = [];
        foreach ($rows as $r) {
            $acts = '<button class="btn btn-xs btn-primary btn-edit-shift" data-id="'.$r['id'].'"><i class="fa fa-pencil"></i></button> ';
            if ($r['status']==='active')   $acts .= '<button class="btn btn-xs btn-warning btn-shift-status" data-id="'.$r['id'].'" data-action="deactivate"><i class="fa fa-ban"></i></button>';
            if ($r['status']==='inactive') $acts .= '<button class="btn btn-xs btn-success btn-shift-status" data-id="'.$r['id'].'" data-action="activate"><i class="fa fa-check"></i></button>';
            $data[] = [$r['id'],esc_html($r['name']),$r['start_time'],$r['end_time'],$r['grace_minutes'].'m',$r['full_day_hours'].'h',status_badge($r['status']),'<div class="btn-group">'.$acts.'</div>'];
        }
        $this->json_list($data,$total,$total);
    }

    public function save() {
        $id   = (int)$this->input->post('id');
        $name = trim($this->input->post('name'));
        if (!$name) $this->json_error('Validation failed.',400,['name'=>'Name required.']);
        $data = ['name'=>$name,'start_time'=>$this->input->post('start_time'),'end_time'=>$this->input->post('end_time'),'grace_minutes'=>(int)$this->input->post('grace_minutes'),'half_day_hours'=>(float)$this->input->post('half_day_hours'),'full_day_hours'=>(float)$this->input->post('full_day_hours')];
        if ($id) { $this->Shift_model->update($id,$data); $this->json_success([],'Shift updated.'); }
        else     { $new=$this->Shift_model->insert($data); $this->json_success(['id'=>$new],'Shift created.'); }
    }

    public function get($id) {
        $shift = $this->Shift_model->get_by_id($id);
        if (!$shift) $this->json_error('Not found.',404);
        $this->json_success($shift);
    }

    public function update_status() {
        $id=(int)$this->input->post('id');$action=$this->input->post('action');
        switch($action){case'activate':$this->Shift_model->activate($id);break;case'deactivate':$this->Shift_model->deactivate($id);break;case'delete':$this->Shift_model->soft_delete($id);break;}
        $this->json_success([],'Status updated.');
    }

    public function assignment() {
        $staff  = $this->User_model->get_field_staff();
        $shifts = $this->Shift_model->get_active();
        $this->load_view('shifts/assignment', ['page_title'=>'Shift Assignment','staff'=>$staff,'shifts'=>$shifts,'page_js'=>'attendance']);
    }

    public function save_assignment() {
        $uid  = (int)$this->input->post('user_id');
        $sid  = (int)$this->input->post('shift_id');
        $from = $this->input->post('effective_from');
        if (!$uid || !$sid || !$from) $this->json_error('All fields required.');
        $this->db->where(['user_id'=>$uid,'is_deleted'=>0])->where('effective_to IS NULL')->update('shift_assignments',['effective_to'=>date('Y-m-d',strtotime($from.' -1 day')),'updated_at'=>date('Y-m-d H:i:s')]);
        $this->Shift_assignment_model->insert(['user_id'=>$uid,'shift_id'=>$sid,'effective_from'=>$from,'effective_to'=>null]);
        $this->json_success([],'Shift assigned.');
    }

    public function calendar() {
        $this->load_view('shifts/calendar', ['page_title'=>'Shift Calendar','page_js'=>'attendance']);
    }

    public function calendar_data() {
        $assignments = $this->Shift_assignment_model->get_with_details();
        $events = [];
        foreach ($assignments as $a) {
            $events[] = ['title'=>$a['user_name'].' — '.$a['shift_name'],'start'=>$a['effective_from'],'end'=>$a['effective_to']??null,'color'=>'#3c8dbc'];
        }
        echo json_encode($events); exit;
    }
}
