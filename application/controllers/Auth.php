<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('Crm_auth');
    }

    public function index()    { redirect('auth/login'); }

    public function login() {
        if ($this->crm_auth->check()) redirect('dashboard');
        $this->load->view('auth/login', [
            'title'      => 'Login — Field CRM',
            'csrf_name'  => $this->security->get_csrf_token_name(),
            'csrf_hash'  => $this->security->get_csrf_hash(),
        ]);
    }

    public function do_login() {
        if (!$this->input->is_ajax_request()) show_404();

        $email    = trim($this->input->post('email'));
        $password = $this->input->post('password');

        if (!$email || !$password) $this->json_error('Email and password are required.');

        $result = $this->crm_auth->login($email, $password);
        if (!$result['success']) $this->json_error($result['message']);

        $role = $result['user']['role'];
        $redirect = base_url('dashboard');
        $this->json_success(['redirect' => $redirect], 'Welcome back, ' . $result['user']['name'] . '!');
    }

    public function logout() {
        $this->crm_auth->logout();
        redirect('auth/login');
    }
}
