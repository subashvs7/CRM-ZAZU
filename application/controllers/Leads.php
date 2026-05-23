<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leads extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(['Lead_model','Lead_activity_model','Customer_model','User_model']);
    }

    public function index() {
        $customers = $this->Customer_model->get_active();
        $staff     = $this->is_manager() ? $this->User_model->get_field_staff() : [];
        $this->load_view('leads/index', ['page_title'=>'Leads','page_js'=>'leads','customers'=>$customers,'staff'=>$staff,'sf'=>'']);
    }

    public function pipeline() {
        $data = $this->Lead_model->pipeline_data($this->get_user_id(), $this->get_role());
        $this->load_view('leads/pipeline', ['page_title'=>'Lead Pipeline','page_js'=>'leads','pipeline'=>$data]);
    }

    public function datatable() {
        $params = $this->input->get();
        $sf     = $this->input->get('status_filter');
        [$rows, $total] = $this->Lead_model->datatable($params, $sf, $this->get_user_id(), $this->get_role());
        $data = [];
        foreach ($rows as $r) {
            $actions = '<div class="btn-group">';
            $actions .= '<a href="'.base_url('leads/detail/'.$r['id']).'" class="btn btn-xs btn-info"><i class="fa fa-eye"></i></a> ';
            if ($r['status']!=='deleted') $actions .= '<button class="btn btn-xs btn-primary btn-edit-lead" data-id="'.$r['id'].'"><i class="fa fa-pencil"></i></button> ';
            if ($r['status']==='active')   $actions .= '<button class="btn btn-xs btn-warning btn-lead-status" data-id="'.$r['id'].'" data-action="deactivate"><i class="fa fa-ban"></i></button> <button class="btn btn-xs btn-danger btn-lead-status" data-id="'.$r['id'].'" data-action="delete"><i class="fa fa-trash"></i></button>';
            if ($r['status']==='inactive') $actions .= '<button class="btn btn-xs btn-success btn-lead-status" data-id="'.$r['id'].'" data-action="activate"><i class="fa fa-check"></i></button>';
            if ($r['status']==='deleted')  $actions .= '<button class="btn btn-xs btn-success btn-lead-status" data-id="'.$r['id'].'" data-action="restore"><i class="fa fa-undo"></i></button>';
            $actions .= '</div>';
            $data[] = [
                $r['id'], esc_html($r['title']), esc_html($r['customer_name']),
                lead_status_badge($r['lead_status']), esc_html($r['source']),
                esc_html($r['assigned_name']??'-'),
                $r['expected_value'] ? format_inr($r['expected_value']) : '-',
                $r['expected_close_date'] ?? '-',
                status_badge($r['status']),
                date('d M Y', strtotime($r['created_at'])),
                $actions,
            ];
        }
        $this->json_list($data, $total, $total);
    }

    public function save() {
        $id    = (int) $this->input->post('id');
        $title = trim($this->input->post('title'));
        $cid   = (int) $this->input->post('customer_id');
        $errors = [];
        if (!$title) $errors['title']       = 'Title required.';
        if (!$cid)   $errors['customer_id'] = 'Customer required.';
        if ($errors) $this->json_error('Validation failed.', 400, $errors);

        $data = [
            'title'               => $title,
            'customer_id'         => $cid,
            'description'         => $this->input->post('description'),
            'source'              => $this->input->post('source'),
            'lead_status'         => $this->input->post('lead_status') ?: 'new',
            'assigned_to'         => (int)$this->input->post('assigned_to') ?: $this->get_user_id(),
            'expected_value'      => $this->input->post('expected_value') ? inr_to_paise((float)$this->input->post('expected_value')) : null,
            'expected_close_date' => $this->input->post('expected_close_date') ?: null,
        ];
        if ($id) { $this->Lead_model->update($id, $data); $this->json_success([], 'Lead updated.'); }
        else     { $new = $this->Lead_model->insert($data); $this->json_success(['id'=>$new], 'Lead created.'); }
    }

    public function get($id) {
        $lead = $this->Lead_model->get_with_details($id);
        if (!$lead) $this->json_error('Not found.', 404);
        $this->json_success($lead);
    }

    public function detail($id) {
        $lead       = $this->Lead_model->get_with_details($id);
        if (!$lead) show_404();
        $activities = $this->Lead_activity_model->get_by_lead($id);
        $this->load_view('leads/_detail', ['page_title'=>$lead['title'],'lead'=>$lead,'activities'=>$activities]);
    }

    public function update_status() {
        $id = (int)$this->input->post('id'); $action = $this->input->post('action');
        switch($action){case'activate':$this->Lead_model->activate($id);break;case'deactivate':$this->Lead_model->deactivate($id);break;case'delete':$this->Lead_model->soft_delete($id);break;case'restore':$this->Lead_model->restore($id);break;default:$this->json_error('Invalid.');}
        $this->json_success([],'Status updated.');
    }

    public function add_activity() {
        $lead_id = (int)$this->input->post('lead_id');
        if (!$lead_id) $this->json_error('Lead ID required.');
        $type = $this->input->post('activity_type');
        $notes = $this->input->post('notes');
        $stage = $this->input->post('stage');

        $data = [
            'lead_id'       => $lead_id,
            'user_id'       => $this->get_user_id(),
            'activity_type' => $type,
            'notes'         => $notes,
            'occurred_at'   => date('Y-m-d H:i:s'),
        ];
        $this->Lead_activity_model->insert($data);

        if ($stage) $this->Lead_model->update($lead_id, ['lead_status' => $stage]);
        $this->json_success([], 'Activity logged.');
    }

    public function activities($lead_id) {
        $acts = $this->Lead_activity_model->get_by_lead($lead_id);
        $this->json_success($acts);
    }

    public function import() {
        $this->load_view('leads/_import', ['page_title'=>'Import Leads']);
    }

    public function import_process() {
        $this->json_error('Import not configured.', 501);
    }
}
