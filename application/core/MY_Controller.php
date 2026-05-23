<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
    protected $session_key = 'crm_user';

    public function __construct() {
        parent::__construct();
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'form', 'security', 'crm']);
    }

    protected function get_user()    { return $this->session->userdata($this->session_key); }
    protected function get_user_id() { $u = $this->get_user(); return $u['id']    ?? null; }
    protected function get_role()    { $u = $this->get_user(); return $u['role']  ?? null; }
    protected function is_admin()    { return $this->get_role() === 'admin'; }
    protected function is_manager()  { return in_array($this->get_role(), ['admin', 'manager']); }

    protected function require_login() {
        if (!$this->get_user()) {
            if ($this->input->is_ajax_request()) $this->json_error('Session expired.', 401);
            else redirect('auth/login');
        }
    }

    protected function require_role($roles) {
        if (!in_array($this->get_role(), (array) $roles)) {
            if ($this->input->is_ajax_request()) $this->json_error('Access denied.', 403);
            else show_error('Access denied.', 403);
        }
    }

    protected function has_permission($perm) {
        $u = $this->get_user();
        return in_array($perm, $u['permissions'] ?? []);
    }

    // Discard any PHP notices/warnings that leaked into the output buffer before JSON
    private function _clean_output_buffer() {
        if (ob_get_level()) ob_clean();
    }

    protected function json_success($data = [], $message = 'Success') {
        $this->_clean_output_buffer();
        $this->_send_csrf_header();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => $message, 'data' => $data]);
        exit;
    }

    protected function json_error($message = 'Error', $code = 400, $errors = []) {
        $this->_clean_output_buffer();
        http_response_code($code);
        $this->_send_csrf_header();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $message, 'errors' => $errors]);
        exit;
    }

    protected function json_list($data, $total, $filtered) {
        $this->_clean_output_buffer();
        $this->_send_csrf_header();
        header('Content-Type: application/json');
        echo json_encode([
            'draw'            => intval($this->input->get('draw')),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
        exit;
    }

    private function _send_csrf_header() {
        $token = $this->security->get_csrf_hash();
        header('X-CSRF-Token: ' . $token);
    }

    protected function load_view($view, $data = [], $with_layout = true) {
        // Inject common template variables so views don't call $this-> (which is CI_Loader in views)
        $common = [
            'csrf_name'       => $this->security->get_csrf_token_name(),
            'csrf_hash'       => $this->security->get_csrf_hash(),
            'current_user'    => $this->get_user() ?? [],
            'current_role'    => $this->get_role() ?? '',
            'current_user_id' => $this->get_user_id() ?? 0,
            'is_admin'        => $this->is_admin(),
            'is_manager'      => $this->is_manager(),
            'sf'              => $data['sf'] ?? '',
        ];
        $data = array_merge($common, $data);

        if ($with_layout) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view($view, $data);
            $this->load->view('templates/footer', $data);
        } else {
            $this->load->view($view, $data);
        }
    }
}
