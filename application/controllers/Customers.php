<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customers extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(['Customer_model','Contact_person_model','User_model']);
    }

    public function index() {
        $staff = $this->is_admin() ? $this->User_model->get_staff_list('field_staff') : [];
        $this->load_view('customers/index', ['page_title'=>'Customers','page_js'=>'customers','staff'=>$staff,'sf'=>'']);
    }

    public function datatable() {
        $params = $this->input->get();
        $sf     = $this->input->get('status_filter');
        [$rows, $total] = $this->Customer_model->datatable($params, $sf, $this->get_user_id(), $this->get_role());
        $data = [];
        foreach ($rows as $r) {
            $actions = crm_action_btns($r['id'], 'customers', $r['status'], ['view'=>true,'edit'=>true]);
            $data[] = [$r['id'], esc_html($r['name']), esc_html($r['phone']), esc_html($r['email']??'-'), esc_html($r['city']??'-'), esc_html($r['state']??'-'), esc_html($r['assigned_name']??'-'), status_badge($r['status']), date('d M Y', strtotime($r['created_at'])), $actions];
        }
        $this->json_list($data, $total, $total);
    }

    public function save() {
        $id    = (int) $this->input->post('id');
        $name  = trim($this->input->post('name'));
        $phone = trim($this->input->post('phone'));
        $errors = [];
        if (!$name)  $errors['name']  = 'Name is required.';
        if (!$phone) $errors['phone'] = 'Phone is required.';
        if ($errors) $this->json_error('Validation failed.', 400, $errors);

        $data = [
            'name'        => $name,
            'phone'       => $phone,
            'email'       => $this->input->post('email'),
            'address'     => $this->input->post('address'),
            'city'        => $this->input->post('city'),
            'state'       => $this->input->post('state'),
            'pincode'     => $this->input->post('pincode'),
            'gst_number'  => $this->input->post('gst_number'),
            'notes'       => $this->input->post('notes'),
            'latitude'    => $this->input->post('latitude') ?: null,
            'longitude'   => $this->input->post('longitude') ?: null,
        ];
        if ($this->is_manager()) {
            $data['assigned_to'] = (int)$this->input->post('assigned_to') ?: null;
        } else if (!$id) {
            $data['assigned_to'] = $this->get_user_id();
        }
        if ($id) { $this->Customer_model->update($id, $data); $this->json_success([], 'Customer updated.'); }
        else     { $new = $this->Customer_model->insert($data); $this->json_success(['id'=>$new], 'Customer created.'); }
    }

    public function get($id) {
        $c = $this->Customer_model->get_with_staff($id);
        if (!$c) $this->json_error('Not found.', 404);
        $this->json_success($c);
    }

    public function detail($id) {
        $customer = $this->Customer_model->get_with_staff($id);
        if (!$customer) show_404();
        $contacts = $this->Contact_person_model->get_by_customer($id);
        $this->load_view('customers/_detail', ['page_title'=>$customer['name'],'page_js'=>'customers','customer'=>$customer,'contacts'=>$contacts]);
    }

    public function update_status() {
        $id = (int)$this->input->post('id'); $action = $this->input->post('action');
        switch($action){case'activate':$this->Customer_model->activate($id);break;case'deactivate':$this->Customer_model->deactivate($id);break;case'delete':$this->Customer_model->soft_delete($id);break;case'restore':$this->Customer_model->restore($id);break;default:$this->json_error('Invalid.');}
        $this->json_success([],'Status updated.');
    }

    public function save_contact() {
        $id  = (int)$this->input->post('id');
        $cid = (int)$this->input->post('customer_id');
        if (!$cid) $this->json_error('Customer ID required.');
        $data = ['customer_id'=>$cid,'name'=>$this->input->post('name'),'designation'=>$this->input->post('designation'),'phone'=>$this->input->post('phone'),'email'=>$this->input->post('email'),'is_primary'=>(int)$this->input->post('is_primary')];
        if (!$data['name']) $this->json_error('Validation failed.',400,['name'=>'Name required.']);
        if ($id) { $this->Contact_person_model->update($id,$data); $this->json_success([],'Contact updated.'); }
        else     { $this->Contact_person_model->insert($data); $this->json_success([],'Contact added.'); }
    }

    public function get_contacts($customer_id) {
        $contacts = $this->Contact_person_model->get_by_customer($customer_id);
        $this->json_success($contacts);
    }

    public function contact_status() {
        $id=(int)$this->input->post('id'); $action=$this->input->post('action');
        switch($action){case'activate':$this->Contact_person_model->activate($id);break;case'delete':$this->Contact_person_model->soft_delete($id);break;}
        $this->json_success([],'Done.');
    }

    public function map_data() {
        $data = $this->Customer_model->get_map_data($this->get_user_id(), $this->get_role());
        $this->json_success($data);
    }
}
