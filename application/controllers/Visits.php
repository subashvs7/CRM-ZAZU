<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Visits extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(['Visit_plan_model','Visit_log_model','Customer_model','User_model']);
    }

    public function index() {
        $customers = $this->Customer_model->get_active();
        $staff     = $this->is_manager() ? $this->User_model->get_field_staff() : [];
        $this->load_view('visits/index', ['page_title'=>'Visit Plans','page_js'=>'visits','customers'=>$customers,'staff'=>$staff,'sf'=>'']);
    }

    public function history() {
        $this->load_view('visits/history', ['page_title'=>'Visit History','page_js'=>'visits','sf'=>'']);
    }

    public function planned_vs_actual() {
        $this->load_view('visits/planned_vs_actual', ['page_title'=>'Planned vs Actual','page_js'=>'visits']);
    }

    public function checkin() {
        $plan_id   = (int)$this->input->get('plan_id');
        $plan      = $plan_id ? $this->Visit_plan_model->get_with_details($plan_id) : null;
        $customers = $this->Customer_model->get_active();
        // Pass any existing open check-in so view can show a "checkout" prompt
        $open_visit = $this->Visit_log_model->get_open_visit($this->get_user_id());
        $this->load_view('visits/checkin', [
            'page_title' => 'Check In',
            'plan'       => $plan,
            'customers'  => $customers,
            'open_visit' => $open_visit,
        ]);
    }

    public function do_checkin() {
        $cid     = (int)$this->input->post('customer_id');
        $lat     = $this->input->post('latitude');
        $lng     = $this->input->post('longitude');
        $plan_id = (int)$this->input->post('visit_plan_id') ?: null;

        if (!$cid) {
            $this->json_error('Please select a customer.', 400, ['customer_id' => 'Customer is required.']);
        }

        // Check for an existing open check-in
        $open = $this->Visit_log_model->get_open_visit($this->get_user_id());
        if ($open) {
            $cust = $this->Customer_model->get_by_id($open['customer_id']);
            $this->json_error(
                'You are already checked in at <strong>' . esc_html($cust['name'] ?? 'a customer') . '</strong>. '
                . 'Please check out first.',
                400,
                ['open_visit_id' => $open['id']]
            );
        }

        $data = [
            'visit_plan_id'    => $plan_id,
            'user_id'          => $this->get_user_id(),
            'customer_id'      => $cid,
            'check_in_at'      => date('Y-m-d H:i:s'),
            'check_in_lat'     => $lat ?: null,
            'check_in_lng'     => $lng ?: null,
            'check_in_address' => $this->input->post('notes'),
            'notes'            => $this->input->post('notes'),
        ];
        $id = $this->Visit_log_model->insert($data);

        if ($plan_id) {
            $this->Visit_plan_model->update($plan_id, ['visit_status' => 'completed']);
        }

        $this->json_success(['id' => $id], 'Checked in successfully at ' . date('H:i'));
    }

    public function do_checkout($id) {
        $log = $this->Visit_log_model->get_by_id($id);
        if (!$log || $log['user_id'] != $this->get_user_id()) $this->json_error('Not found.', 404);
        if ($log['check_out_at']) $this->json_error('Already checked out.');

        $data = [
            'check_out_at'      => date('Y-m-d H:i:s'),
            'check_out_lat'     => $this->input->post('latitude') ?: null,
            'check_out_lng'     => $this->input->post('longitude') ?: null,
            'check_out_address' => $this->input->post('address'),
            'notes'             => $this->input->post('notes') ?: $log['notes'],
        ];
        $this->Visit_log_model->update($id, $data);
        $this->json_success([], 'Checked out successfully.');
    }

    public function datatable() {
        $params = $this->input->get(); $sf = $this->input->get('status_filter');
        [$rows, $total] = $this->Visit_plan_model->datatable($params, $sf, $this->get_user_id(), $this->get_role());
        $data = [];
        foreach ($rows as $r) {
            $acts = '<div class="btn-group"><button class="btn btn-xs btn-primary btn-edit-visit" data-id="'.$r['id'].'"><i class="fa fa-pencil"></i></button> ';
            if ($r['status']==='active') $acts .= '<button class="btn btn-xs btn-danger btn-visit-status" data-id="'.$r['id'].'" data-action="delete"><i class="fa fa-trash"></i></button>';
            $acts .= '</div>';
            $data[] = [$r['id'], $r['planned_date'], $r['planned_time']??'-', esc_html($r['customer_name']), esc_html($r['user_name']), visit_status_badge($r['visit_status']), esc_html(substr($r['purpose']??'',0,50)), status_badge($r['status']), $acts];
        }
        $this->json_list($data, $total, $total);
    }

    public function save() {
        $id  = (int)$this->input->post('id');
        $cid = (int)$this->input->post('customer_id');
        $uid = (int)$this->input->post('user_id') ?: $this->get_user_id();
        $dt  = $this->input->post('planned_date');
        if (!$cid || !$dt) $this->json_error('Validation failed.',400,['customer_id'=>'Required.','planned_date'=>'Required.']);
        $data = ['user_id'=>$uid,'customer_id'=>$cid,'planned_date'=>$dt,'planned_time'=>$this->input->post('planned_time'),'purpose'=>$this->input->post('purpose'),'lead_id'=>(int)$this->input->post('lead_id')?:null,'created_by'=>$this->get_user_id()];
        if ($id) { $this->Visit_plan_model->update($id,$data); $this->json_success([],'Visit updated.'); }
        else     { $new=$this->Visit_plan_model->insert($data); $this->json_success(['id'=>$new],'Visit planned.'); }
    }

    public function detail($id) {
        $plan = $this->Visit_plan_model->get_with_details($id);
        if (!$plan) show_404();
        $this->load_view('visits/_detail', ['page_title'=>'Visit Detail','plan'=>$plan]);
    }

    public function update_status() {
        $id=(int)$this->input->post('id');$action=$this->input->post('action');
        switch($action){case'activate':$this->Visit_plan_model->activate($id);break;case'delete':$this->Visit_plan_model->soft_delete($id);break;case'restore':$this->Visit_plan_model->restore($id);break;}
        $this->json_success([],'Status updated.');
    }

    public function calendar_data() {
        $data = $this->Visit_plan_model->calendar_data($this->get_user_id(),$this->get_role(),$this->input->get('start'),$this->input->get('end'));
        $events = [];
        foreach ($data as $d) {
            $color = ['planned'=>'#3c8dbc','completed'=>'#00a65a','missed'=>'#dd4b39','rescheduled'=>'#f39c12'][$d['visit_status']] ?? '#777';
            $events[] = ['id'=>$d['id'],'title'=>$d['customer_name'].' ('.$d['user_name'].')','start'=>$d['planned_date'].($d['planned_time']?' T'.$d['planned_time']:''),'color'=>$color];
        }
        echo json_encode($events); exit;
    }
}
