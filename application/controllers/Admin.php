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
            $actions = '<div class="btn-group">';
            $actions .= '<button class="btn btn-xs btn-primary btn-edit-user" data-id="'.$r['id'].'"><i class="fa fa-pencil"></i></button> ';
            if ($r['status']==='active')   $actions .= '<button class="btn btn-xs btn-warning btn-user-status" data-id="'.$r['id'].'" data-action="deactivate"><i class="fa fa-ban"></i></button> <button class="btn btn-xs btn-danger btn-user-status" data-id="'.$r['id'].'" data-action="delete"><i class="fa fa-trash"></i></button>';
            if ($r['status']==='inactive') $actions .= '<button class="btn btn-xs btn-success btn-user-status" data-id="'.$r['id'].'" data-action="activate"><i class="fa fa-check"></i></button> <button class="btn btn-xs btn-danger btn-user-status" data-id="'.$r['id'].'" data-action="delete"><i class="fa fa-trash"></i></button>';
            if ($r['status']==='deleted')  $actions .= '<button class="btn btn-xs btn-success btn-user-status" data-id="'.$r['id'].'" data-action="restore"><i class="fa fa-undo"></i></button>';
            $actions .= '</div>';
            $data[] = [
                $r['id'], esc_html($r['name']), esc_html($r['email']),
                '<span class="label label-'.($r['role']==='admin'?'danger':($r['role']==='manager'?'warning':'primary')).'">'.esc_html(str_replace('_',' ',$r['role'])).'</span>',
                esc_html($r['team_name'] ?? '-'), status_badge($r['status']),
                $r['last_login_at'] ? date('d M Y H:i', strtotime($r['last_login_at'])) : 'Never',
                $actions,
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
            $acts = '<div class="btn-group">';
            $acts .= '<button class="btn btn-xs btn-primary btn-edit-team" data-id="'.$r['id'].'"><i class="fa fa-pencil"></i></button> ';
            if ($r['status']==='active')   $acts .= '<button class="btn btn-xs btn-warning btn-team-status" data-id="'.$r['id'].'" data-action="deactivate"><i class="fa fa-ban"></i></button> <button class="btn btn-xs btn-danger btn-team-status" data-id="'.$r['id'].'" data-action="delete"><i class="fa fa-trash"></i></button>';
            if ($r['status']==='inactive') $acts .= '<button class="btn btn-xs btn-success btn-team-status" data-id="'.$r['id'].'" data-action="activate"><i class="fa fa-check"></i></button>';
            if ($r['status']==='deleted')  $acts .= '<button class="btn btn-xs btn-success btn-team-status" data-id="'.$r['id'].'" data-action="restore"><i class="fa fa-undo"></i></button>';
            $acts .= '</div>';
            $data[] = [$r['id'], esc_html($r['name']), esc_html($r['manager_name']??'-'), esc_html($r['territory']??'-'), $cnt, status_badge($r['status']), $acts];
        }
        $this->json_list($data, $total, $total);
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
            $acts = '<div class="btn-group"><button class="btn btn-xs btn-primary btn-edit-product" data-id="'.$r['id'].'"><i class="fa fa-pencil"></i></button> ';
            if ($r['status']==='active')   $acts .= '<button class="btn btn-xs btn-warning btn-product-status" data-id="'.$r['id'].'" data-action="deactivate"><i class="fa fa-ban"></i></button> <button class="btn btn-xs btn-danger btn-product-status" data-id="'.$r['id'].'" data-action="delete"><i class="fa fa-trash"></i></button>';
            if ($r['status']==='inactive') $acts .= '<button class="btn btn-xs btn-success btn-product-status" data-id="'.$r['id'].'" data-action="activate"><i class="fa fa-check"></i></button>';
            if ($r['status']==='deleted')  $acts .= '<button class="btn btn-xs btn-success btn-product-status" data-id="'.$r['id'].'" data-action="restore"><i class="fa fa-undo"></i></button>';
            $acts .= '</div>';
            $data[] = [$r['id'], esc_html($r['name']), esc_html($r['sku']), esc_html($r['category_name']??'-'), esc_html($r['unit']), format_inr($r['price']), $r['stock'], status_badge($r['status']), $acts];
        }
        $this->json_list($data, $total, $total);
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
            $acts = '<button class="btn btn-xs btn-primary btn-edit-cat" data-id="'.$r['id'].'"><i class="fa fa-pencil"></i></button>';
            $data[] = [$r['id'], esc_html($r['name']), esc_html($r['parent_name']??'-'), status_badge($r['status']), $acts];
        }
        $this->json_list($data, $total, $total);
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
            $acts = '<button class="btn btn-xs btn-primary btn-edit-tpl" data-id="'.$r['id'].'"><i class="fa fa-pencil"></i></button>';
            $data[] = [$r['id'], esc_html($r['name']), esc_html($r['channel']), esc_html(substr($r['body'],0,60)).'...', status_badge($r['status']), $acts];
        }
        $this->json_list($data, $total, $total);
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
