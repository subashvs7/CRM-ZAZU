<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->require_role('admin');
        $this->load->model(['User_model','Team_model','Product_model','Product_category_model','Notification_template_model','App_setting_model']);
        $this->load->library('Crm_auth');
    }

    private function _btn($cls, $icon, $title, $extra = '') {
        return '<button class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors '.$cls.'" title="'.$title.'" '.$extra.'><i class="fa fa-'.$icon.'" style="font-size:11px"></i></button>';
    }

    // ── USERS ──────────────────────────────────────────────────────────
    public function users() {
        $teams = $this->Team_model->get_active();
        $this->load_view('admin/users', ['page_title'=>'Users','page_js'=>'admin','teams'=>$teams,'sf'=>'']);
    }

    public function users_datatable() {
        $params = $this->input->get();
        $sf     = $this->input->get('status_filter');
        [$rows, $total] = $this->User_model->datatable($params, $sf);
        $data = [];
        foreach ($rows as $r) {
            $acts = '<div class="flex items-center gap-1">';
            $acts .= $this->_btn('bg-blue-100 text-blue-700 hover:bg-blue-200 btn-edit-user', 'pencil', 'Edit', 'data-id="'.$r['id'].'"');
            if ($r['status']==='active')   $acts .= $this->_btn('bg-amber-100 text-amber-700 hover:bg-amber-200 btn-user-status','ban','Deactivate','data-id="'.$r['id'].'" data-action="deactivate"') . $this->_btn('bg-red-100 text-red-700 hover:bg-red-200 btn-user-status','trash','Delete','data-id="'.$r['id'].'" data-action="delete"');
            if ($r['status']==='inactive') $acts .= $this->_btn('bg-green-100 text-green-700 hover:bg-green-200 btn-user-status','check','Activate','data-id="'.$r['id'].'" data-action="activate"') . $this->_btn('bg-red-100 text-red-700 hover:bg-red-200 btn-user-status','trash','Delete','data-id="'.$r['id'].'" data-action="delete"');
            if ($r['status']==='deleted')  $acts .= $this->_btn('bg-green-100 text-green-700 hover:bg-green-200 btn-user-status','undo','Restore','data-id="'.$r['id'].'" data-action="restore"');
            $acts .= '</div>';
            $roleColor = $r['role']==='admin' ? 'bg-red-100 text-red-700' : ($r['role']==='manager' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700');
            $data[] = [
                $r['id'], esc_html($r['name']), esc_html($r['email']),
                '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg '.$roleColor.'">'.esc_html(str_replace('_',' ',$r['role'])).'</span>',
                esc_html($r['team_name'] ?? '-'), status_badge($r['status']),
                $r['last_login_at'] ? date('d M Y H:i', strtotime($r['last_login_at'])) : 'Never',
                $acts,
            ];
        }
        $this->json_list($data, $total, $total);
    }

    public function save_user() {
        $id    = (int) $this->input->post('id');
        $name  = trim($this->input->post('name'));
        $email = trim($this->input->post('email'));
        $role  = $this->input->post('role');
        $phone = trim($this->input->post('phone'));
        $team  = (int) $this->input->post('team_id') ?: null;

        $errors = [];
        if (!$name)  $errors['name']  = 'Name is required.';
        if (!$email) $errors['email'] = 'Email is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email.';
        if (!in_array($role, ['admin','manager','field_staff'])) $errors['role'] = 'Invalid role.';
        if ($errors) $this->json_error('Validation failed.', 400, $errors);

        if ($id) {
            $check = $this->db->where('email', $email)->where('id !=', $id)->get('users')->row_array();
        } else {
            $check = $this->db->where('email', $email)->get('users')->row_array();
        }
        if ($check) $this->json_error('Email already exists.', 400, ['email'=>'This email is already registered.']);

        $data = ['name'=>$name,'email'=>$email,'role'=>$role,'phone'=>$phone,'team_id'=>$team];
        $pw   = $this->input->post('password');
        if ($pw) $data['password'] = $this->crm_auth->hash_password($pw);

        if ($id) {
            $this->User_model->update($id, $data);
            $perms = $this->input->post('permissions') ?: [];
            $this->User_model->set_permissions($id, $perms);
            $this->json_success([], 'User updated.');
        } else {
            if (!$pw) $this->json_error('Password required for new user.', 400, ['password'=>'Password is required.']);
            $data['password'] = $this->crm_auth->hash_password($pw);
            $uid = $this->User_model->insert($data);
            $perms = $this->input->post('permissions') ?: [];
            $this->User_model->set_permissions($uid, $perms);
            $this->json_success(['id'=>$uid], 'User created.');
        }
    }

    public function fetch_user($id) {
        $user = $this->User_model->get_with_team($id);
        if (!$user) $this->json_error('User not found.', 404);
        $user['permissions'] = $this->User_model->get_permissions($id);
        unset($user['password']);
        $this->json_success($user);
    }

    public function users_credentials() {
        $users = $this->db->select('id, name, email, role, status, phone, team_id, last_login_at')
            ->where('is_deleted', 0)
            ->order_by('role, name')
            ->get('users')->result_array();
        $this->json_success($users);
    }

    public function reset_password() {
        $id = (int) $this->input->post('id');
        $pw = $this->input->post('password');
        if (!$id || strlen($pw) < 6) $this->json_error('Password must be at least 6 characters.');
        $hash = $this->crm_auth->hash_password($pw);
        $this->User_model->update($id, ['password' => $hash]);
        $this->json_success([], 'Password reset successfully.');
    }

    public function update_user_status() {
        $id     = (int) $this->input->post('id');
        $action = $this->input->post('action');
        switch ($action) {
            case 'activate':   $this->User_model->activate($id);    break;
            case 'deactivate': $this->User_model->deactivate($id);  break;
            case 'delete':     $this->User_model->soft_delete($id); break;
            case 'restore':    $this->User_model->restore($id);     break;
            default:           $this->json_error('Invalid action.');
        }
        $this->json_success([], 'Status updated.');
    }

    // ── TEAMS ──────────────────────────────────────────────────────────
    public function teams() {
        $managers = $this->User_model->get_staff_list('manager');
        $this->load_view('admin/teams', ['page_title'=>'Teams','page_js'=>'admin','managers'=>$managers,'sf'=>'']);
    }

    public function teams_datatable() {
        $params = $this->input->get();
        $sf     = $this->input->get('status_filter');
        [$rows, $total] = $this->Team_model->datatable($params, $sf);
        $data = [];
        foreach ($rows as $r) {
            $cnt  = $this->Team_model->get_member_count($r['id']);
            $acts = '<div class="flex items-center gap-1">';
            $acts .= $this->_btn('bg-blue-100 text-blue-700 hover:bg-blue-200 btn-edit-team', 'pencil', 'Edit', 'data-id="'.$r['id'].'"');
            if ($r['status']==='active')   $acts .= $this->_btn('bg-amber-100 text-amber-700 hover:bg-amber-200 btn-team-status','ban','Deactivate','data-id="'.$r['id'].'" data-action="deactivate"') . $this->_btn('bg-red-100 text-red-700 hover:bg-red-200 btn-team-status','trash','Delete','data-id="'.$r['id'].'" data-action="delete"');
            if ($r['status']==='inactive') $acts .= $this->_btn('bg-green-100 text-green-700 hover:bg-green-200 btn-team-status','check','Activate','data-id="'.$r['id'].'" data-action="activate"');
            if ($r['status']==='deleted')  $acts .= $this->_btn('bg-green-100 text-green-700 hover:bg-green-200 btn-team-status','undo','Restore','data-id="'.$r['id'].'" data-action="restore"');
            $acts .= '</div>';
            $data[] = [$r['id'], esc_html($r['name']), esc_html($r['manager_name']??'-'), esc_html($r['territory']??'-'), $cnt, status_badge($r['status']), $acts];
        }
        $this->json_list($data, $total, $total);
    }

    public function fetch_team($id) {
        $team = $this->Team_model->get_by_id($id);
        if (!$team) $this->json_error('Team not found.', 404);
        $this->json_success($team);
    }

    public function save_team() {
        $id   = (int) $this->input->post('id');
        $name = trim($this->input->post('name'));
        if (!$name) $this->json_error('Validation failed.', 400, ['name'=>'Name is required.']);
        $data = ['name'=>$name,'manager_id'=>(int)$this->input->post('manager_id')?:null,'territory'=>$this->input->post('territory'),'description'=>$this->input->post('description')];
        if ($id) { $this->Team_model->update($id, $data); $this->json_success([], 'Team updated.'); }
        else     { $new = $this->Team_model->insert($data); $this->json_success(['id'=>$new], 'Team created.'); }
    }

    public function update_team_status() {
        $id = (int)$this->input->post('id'); $action = $this->input->post('action');
        switch($action){case'activate':$this->Team_model->activate($id);break;case'deactivate':$this->Team_model->deactivate($id);break;case'delete':$this->Team_model->soft_delete($id);break;case'restore':$this->Team_model->restore($id);break;}
        $this->json_success([],'Status updated.');
    }

    // ── PRODUCTS ───────────────────────────────────────────────────────
    public function products() {
        $cats = $this->Product_category_model->get_active();
        $this->load_view('admin/products', ['page_title'=>'Products','page_js'=>'admin','categories'=>$cats,'sf'=>'']);
    }

    public function products_datatable() {
        $params = $this->input->get(); $sf = $this->input->get('status_filter');
        [$rows, $total] = $this->Product_model->datatable($params, $sf);
        $data = [];
        foreach ($rows as $r) {
            $acts = '<div class="flex items-center gap-1">';
            $acts .= $this->_btn('bg-blue-100 text-blue-700 hover:bg-blue-200 btn-edit-product', 'pencil', 'Edit', 'data-id="'.$r['id'].'"');
            if ($r['status']==='active')   $acts .= $this->_btn('bg-amber-100 text-amber-700 hover:bg-amber-200 btn-product-status','ban','Deactivate','data-id="'.$r['id'].'" data-action="deactivate"') . $this->_btn('bg-red-100 text-red-700 hover:bg-red-200 btn-product-status','trash','Delete','data-id="'.$r['id'].'" data-action="delete"');
            if ($r['status']==='inactive') $acts .= $this->_btn('bg-green-100 text-green-700 hover:bg-green-200 btn-product-status','check','Activate','data-id="'.$r['id'].'" data-action="activate"');
            if ($r['status']==='deleted')  $acts .= $this->_btn('bg-green-100 text-green-700 hover:bg-green-200 btn-product-status','undo','Restore','data-id="'.$r['id'].'" data-action="restore"');
            $acts .= '</div>';
            $data[] = [$r['id'], esc_html($r['name']), esc_html($r['sku']), esc_html($r['category_name']??'-'), esc_html($r['unit']), format_inr($r['price']), $r['stock'], status_badge($r['status']), $acts];
        }
        $this->json_list($data, $total, $total);
    }

    public function fetch_product($id) {
        $p = $this->Product_model->get_by_id($id);
        if (!$p) $this->json_error('Product not found.', 404);
        $p['price']     = paise_to_inr($p['price']);
        $p['min_price'] = paise_to_inr($p['min_price']);
        $this->json_success($p);
    }

    public function save_product() {
        $id   = (int) $this->input->post('id');
        $name = trim($this->input->post('name'));
        $sku  = trim($this->input->post('sku'));
        if (!$name) $this->json_error('Validation failed.', 400, ['name'=>'Name required.']);
        if (!$sku)  $this->json_error('Validation failed.', 400, ['sku'=>'SKU required.']);

        $price     = inr_to_paise((float)$this->input->post('price'));
        $min_price = inr_to_paise((float)$this->input->post('min_price'));
        $data = [
            'name'=>$name,'sku'=>$sku,'description'=>$this->input->post('description'),
            'unit'=>$this->input->post('unit')?:'pcs','price'=>$price,'min_price'=>$min_price,
            'stock'=>(int)$this->input->post('stock'),'category_id'=>(int)$this->input->post('category_id')?:null,
        ];

        if ($id) { $this->Product_model->update($id, $data); $this->json_success([], 'Product updated.'); }
        else     { $new = $this->Product_model->insert($data); $this->json_success(['id'=>$new], 'Product created.'); }
    }

    public function update_product_status() {
        $id = (int)$this->input->post('id'); $action = $this->input->post('action');
        switch($action){case'activate':$this->Product_model->activate($id);break;case'deactivate':$this->Product_model->deactivate($id);break;case'delete':$this->Product_model->soft_delete($id);break;case'restore':$this->Product_model->restore($id);break;}
        $this->json_success([],'Status updated.');
    }

    public function categories_datatable() {
        $params = $this->input->get();
        [$rows, $total] = $this->Product_category_model->datatable($params);
        $data = [];
        foreach ($rows as $r) {
            $acts = '<div class="flex items-center gap-1">' . $this->_btn('bg-blue-100 text-blue-700 hover:bg-blue-200 btn-edit-cat','pencil','Edit','data-id="'.$r['id'].'"') . '</div>';
            $data[] = [$r['id'], esc_html($r['name']), esc_html($r['parent_name']??'-'), status_badge($r['status']), $acts];
        }
        $this->json_list($data, $total, $total);
    }

    public function fetch_category($id) {
        $c = $this->Product_category_model->get_by_id($id);
        if (!$c) $this->json_error('Category not found.', 404);
        $this->json_success($c);
    }

    public function save_category() {
        $id   = (int) $this->input->post('id');
        $name = trim($this->input->post('name'));
        if (!$name) $this->json_error('Validation failed.', 400, ['name'=>'Name required.']);
        $data = ['name'=>$name,'parent_id'=>(int)$this->input->post('parent_id')?:null];
        if ($id) { $this->Product_category_model->update($id, $data); $this->json_success([], 'Category updated.'); }
        else     { $new = $this->Product_category_model->insert($data); $this->json_success(['id'=>$new], 'Category created.'); }
    }

    // ── NOTIFICATION TEMPLATES ─────────────────────────────────────────
    public function notif_templates() {
        $this->load_view('admin/notif_templates', ['page_title'=>'Notification Templates','page_js'=>'admin']);
    }

    public function templates_datatable() {
        $params = $this->input->get();
        [$rows, $total] = $this->Notification_template_model->datatable($params);
        $data = [];
        foreach ($rows as $r) {
            $acts = '<div class="flex items-center gap-1">' . $this->_btn('bg-blue-100 text-blue-700 hover:bg-blue-200 btn-edit-tpl','pencil','Edit','data-id="'.$r['id'].'"') . '</div>';
            $data[] = [$r['id'], esc_html($r['name']), esc_html($r['channel']), esc_html(substr($r['body'],0,60)).'...', status_badge($r['status']), $acts];
        }
        $this->json_list($data, $total, $total);
    }

    public function fetch_template($id) {
        $t = $this->Notification_template_model->get_by_id($id);
        if (!$t) $this->json_error('Template not found.', 404);
        $this->json_success($t);
    }

    public function save_template() {
        $id = (int)$this->input->post('id');
        $data = ['name'=>$this->input->post('name'),'channel'=>$this->input->post('channel'),'subject'=>$this->input->post('subject'),'body'=>$this->input->post('body')];
        if ($id) { $this->Notification_template_model->update($id,$data); $this->json_success([],'Saved.'); }
        else     { $this->Notification_template_model->insert($data); $this->json_success([],'Saved.'); }
    }

    // ── SETTINGS ──────────────────────────────────────────────────────
    public function settings() {
        $settings = $this->App_setting_model->get_all_as_array();
        $this->load_view('admin/settings', ['page_title'=>'Settings','page_js'=>'admin','settings'=>$settings]);
    }

    public function save_settings() {
        $post = $this->input->post();
        unset($post[$this->security->get_csrf_token_name()]);
        $this->App_setting_model->set_bulk($post);
        $this->json_success([], 'Settings saved.');
    }
}
