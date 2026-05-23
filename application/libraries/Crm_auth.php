<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Crm_auth {
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    public function login($email, $password) {
        $user = $this->CI->db
            ->where(['email' => $email, 'is_deleted' => 0])
            ->get('users')->row_array();

        if (!$user) return ['success' => false, 'message' => 'Invalid credentials.'];
        if ($user['status'] === 'inactive') return ['success' => false, 'message' => 'Your account has been disabled.'];
        if ($user['status'] === 'deleted')  return ['success' => false, 'message' => 'Account not found.'];
        if (!password_verify($password, $user['password'])) return ['success' => false, 'message' => 'Invalid credentials.'];

        $perms = $this->CI->db->where('user_id', $user['id'])->get('user_permissions')->result_array();
        $user['permissions'] = array_column($perms, 'permission');
        unset($user['password']);

        $this->CI->session->set_userdata('crm_user', $user);
        $this->CI->db->where('id', $user['id'])->update('users', ['last_login_at' => date('Y-m-d H:i:s')]);

        return ['success' => true, 'user' => $user];
    }

    public function logout() {
        $this->CI->session->unset_userdata('crm_user');
        $this->CI->session->sess_destroy();
    }

    public function check() {
        return $this->CI->session->userdata('crm_user') !== null;
    }

    public function user() {
        return $this->CI->session->userdata('crm_user');
    }

    public function hash_password($plain) {
        return password_hash($plain, PASSWORD_BCRYPT);
    }
}
