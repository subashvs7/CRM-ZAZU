<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(['User_model','Team_model','Product_model','Product_category_model','Notification_template_model','App_setting_model','Smtp_account_model']);
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
            $data[] = [$r['id'], esc_html($r['name']), esc_html($r['sku']), esc_html($r['category_name']??'-'), esc_html($r['unit']), format_inr($r['price']), status_badge($r['status']), $acts];
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
        $settings  = $this->App_setting_model->get_all_as_array();
        $smtp_pool = $this->Smtp_account_model->get_pool_status();
        $this->load_view('admin/settings', [
            'page_title' => 'Settings & SMTP Mail Pool',
            'page_js'    => 'admin',
            'settings'   => $settings,
            'smtp_pool'  => $smtp_pool
        ]);
    }

    public function save_settings() {
        if (!$this->is_admin()) {
            $this->json_error('Access denied. Administrator privileges required.', 403);
        }
        $post = $this->input->post();
        unset($post[$this->security->get_csrf_token_name()]);
        $this->App_setting_model->set_bulk($post);
        $this->json_success([], 'Settings saved.');
    }

    /**
     * AJAX: Get all Hostinger SMTP Accounts in Pool
     */
    public function smtp_accounts_ajax() {
        $pool = $this->Smtp_account_model->get_pool_status();
        $this->json_success($pool);
    }

    /**
     * AJAX: Save or Create Hostinger SMTP Account
     */
    public function save_smtp_account() {
        $id = (int)$this->input->post('id') ?: null;
        $name = trim($this->input->post('name'));
        $sender_email = trim($this->input->post('sender_email'));

        if (!$name || !$sender_email) {
            $this->json_error('Account Name and Sender Email are required.');
        }

        if (!filter_var($sender_email, FILTER_VALIDATE_EMAIL)) {
            $this->json_error('Please provide a valid sender email address.');
        }

        $data = [
            'name'         => $name,
            'sender_email' => $sender_email,
            'sender_name'  => trim($this->input->post('sender_name')) ?: $name,
            'smtp_host'    => trim($this->input->post('smtp_host')) ?: 'smtp.hostinger.com',
            'smtp_port'    => (int)$this->input->post('smtp_port') ?: 465,
            'smtp_crypto'  => strtolower(trim($this->input->post('smtp_crypto'))) ?: 'ssl',
            'smtp_user'    => trim($this->input->post('smtp_user')),
            'smtp_pass'    => trim($this->input->post('smtp_pass')),
            'daily_limit'  => (int)$this->input->post('daily_limit') ?: 100,
            'status'       => $this->input->post('status') === 'disabled' ? 'disabled' : 'active'
        ];

        $res = $this->Smtp_account_model->save_account($data, $id);
        if ($res['success']) {
            $this->json_success(['id' => $res['id']], $res['message']);
        } else {
            $this->json_error($res['message']);
        }
    }

    /**
     * AJAX: Delete Hostinger SMTP Account
     */
    public function delete_smtp_account() {
        $id = (int)$this->input->post('id');
        if (!$id) {
            $this->json_error('Invalid account ID.');
        }

        $this->Smtp_account_model->delete_account($id);
        $this->json_success([], 'Hostinger SMTP account removed from pool.');
    }

    /**
     * AJAX: Reset today's sent counter manually
     */
    public function reset_smtp_counter() {
        $id = (int)$this->input->post('id');
        if (!$id) {
            $this->json_error('Invalid account ID.');
        }

        $this->Smtp_account_model->reset_sent_today($id);
        $this->json_success([], 'Daily sent count reset to 0 for this account.');
    }

    /**
     * AJAX: Test SMTP Connection to Hostinger Server
     */
    public function test_smtp_connection() {
        $id = (int)$this->input->post('id');
        if ($id) {
            $account = $this->Smtp_account_model->get_by_id($id);
            if (!$account) {
                $this->json_error('SMTP Account not found.', 404);
            }
        } else {
            // Live test before saving
            $account = [
                'smtp_host'   => trim($this->input->post('smtp_host')) ?: 'smtp.hostinger.com',
                'smtp_port'   => (int)$this->input->post('smtp_port') ?: 465,
                'smtp_crypto' => strtolower(trim($this->input->post('smtp_crypto'))) ?: 'ssl'
            ];
        }

        $result = $this->Smtp_account_model->test_connection($account);
        if ($result['success']) {
            $this->json_success($result, $result['message']);
        } else {
            $this->json_error($result['message']);
        }
    }

    /**
     * AJAX: Send real test email template directly to user's inbox
     */
    public function send_test_smtp_email() {
        $to_email = trim($this->input->post('to_email'));
        $id       = (int)$this->input->post('id');

        if (!$to_email || !filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
            $this->json_error('Please enter a valid recipient email address.');
        }

        if ($id) {
            $account = $this->Smtp_account_model->get_by_id($id);
            if (!$account) {
                $this->json_error('SMTP Account not found.', 404);
            }
        } else {
            $account = [
                'name'         => trim($this->input->post('name')) ?: 'Hostinger Mail',
                'sender_email' => trim($this->input->post('sender_email')),
                'sender_name'  => trim($this->input->post('sender_name')) ?: 'CRM Mailer',
                'smtp_host'    => trim($this->input->post('smtp_host')) ?: 'smtp.hostinger.com',
                'smtp_port'    => (int)$this->input->post('smtp_port') ?: 465,
                'smtp_crypto'  => strtolower(trim($this->input->post('smtp_crypto'))) ?: 'ssl',
                'smtp_user'    => trim($this->input->post('smtp_user')),
                'smtp_pass'    => trim($this->input->post('smtp_pass')),
            ];
        }

        if (empty($account['smtp_user']) || empty($account['smtp_pass'])) {
            $this->json_error('SMTP Username and Password are required to send an email.');
        }

        $this->load->library('email');
        $smtpConfig = [
            'protocol'    => 'smtp',
            'smtp_host'   => $account['smtp_host'] ?: 'smtp.hostinger.com',
            'smtp_port'   => (int)($account['smtp_port'] ?: 465),
            'smtp_user'   => $account['smtp_user'],
            'smtp_pass'   => $account['smtp_pass'],
            'smtp_crypto' => strtolower($account['smtp_crypto'] ?: 'ssl'),
            'mailtype'    => 'html',
            'charset'     => 'utf-8',
            'newline'     => "\r\n",
            'crlf'        => "\r\n",
            'smtp_timeout'=> 10
        ];

        $this->email->initialize($smtpConfig);
        $this->email->clear(true);
        $this->email->from($account['sender_email'], $account['sender_name']);
        $this->email->to($to_email);
        $this->email->subject('✅ Hostinger SMTP Test - ZAZU CRM Mailer Verification');

        $body = '
        <div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; max-width: 600px; margin: 20px auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 16px; background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <div style="text-align: center; margin-bottom: 24px;">
                <div style="display: inline-block; width: 48px; height: 48px; line-height: 48px; background: #eff6ff; border-radius: 12px; margin-bottom: 8px;">
                    <span style="font-size: 24px;">🚀</span>
                </div>
                <h2 style="color: #1e293b; margin: 0; font-size: 20px; font-weight: 700;">ZAZU Field CRM</h2>
                <p style="color: #64748b; font-size: 13px; margin: 4px 0 0 0;">Hostinger Multi-SMTP Mail Pool Verification</p>
            </div>
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px; margin-bottom: 24px; text-align: center;">
                <div style="font-size: 16px; font-weight: 700; color: #166534; margin-bottom: 4px;">🎉 Connection &amp; Delivery Successful!</div>
                <p style="color: #15803d; font-size: 13px; margin: 0; line-height: 1.5;">This email confirms that your Hostinger SMTP mailbox is properly configured, authenticated, and ready to dispatch marketing &amp; sales campaigns.</p>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin-bottom: 12px;">Configuration Details</div>
                <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                    <tr><td style="padding: 6px 0; color: #64748b; width: 40%;">Account Label:</td><td style="padding: 6px 0; font-weight: 600; color: #0f172a;">' . htmlspecialchars($account['name'], ENT_QUOTES, 'UTF-8') . '</td></tr>
                    <tr><td style="padding: 6px 0; color: #64748b;">Sender Email:</td><td style="padding: 6px 0; font-weight: 600; color: #0f172a;">' . htmlspecialchars($account['sender_email'], ENT_QUOTES, 'UTF-8') . '</td></tr>
                    <tr><td style="padding: 6px 0; color: #64748b;">Display Name:</td><td style="padding: 6px 0; font-weight: 600; color: #0f172a;">' . htmlspecialchars($account['sender_name'], ENT_QUOTES, 'UTF-8') . '</td></tr>
                    <tr><td style="padding: 6px 0; color: #64748b;">Server &amp; Port:</td><td style="padding: 6px 0; font-family: monospace; font-weight: 600; color: #2563eb;">' . htmlspecialchars($account['smtp_host'] . ':' . $account['smtp_port'], ENT_QUOTES, 'UTF-8') . ' (' . strtoupper($account['smtp_crypto']) . ')</td></tr>
                    <tr><td style="padding: 6px 0; color: #64748b;">Dispatched At:</td><td style="padding: 6px 0; color: #475569;">' . date('d M Y, h:i:s A') . '</td></tr>
                </table>
            </div>
            <div style="font-size: 11px; color: #94a3b8; text-align: center; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                Sent automatically by ZAZU CRM Mail Engine &bull; Hostinger Multi-SMTP Pool
            </div>
        </div>';

        $this->email->message($body);

        if (@$this->email->send()) {
            if ($id) {
                $this->Smtp_account_model->increment_sent_count($id);
            }
            $this->json_success([], 'Test email sent successfully to ' . esc_html($to_email) . '! Please check your Inbox / Spam folder.');
        } else {
            $debug = $this->email->print_debugger(['headers']);
            $cleanDebug = strip_tags($debug);
            $this->json_error('Failed to send email. Hostinger returned: ' . $cleanDebug);
        }
    }

    /**
     * AJAX: Get SMTP pool status with live remaining counts & alerts
     */
    public function smtp_pool_status_ajax() {
        $status = $this->Smtp_account_model->get_pool_status();
        $this->json_success($status);
    }

    // ── ROLE PERMISSIONS ──────────────────────────────────────────────
    public function role_permissions() {
        $this->load_view('admin/role_permissions', ['page_title'=>'Role Permissions','page_js'=>'admin']);
    }

    public function fetch_role_permissions() {
        $all_modules = ['dashboard', 'customers', 'leads', 'orders', 'visits', 'tracking/live', 'geofence', 'attendance', 'shifts', 'leave', 'selfie/log', 'reports', 'admin'];
        $rows = $this->db->get('role_permissions')->result_array();

        if (empty($rows)) {
            // Auto-seed default permissions into DB if table is empty
            $this->db->insert('role_permissions', ['role' => 'admin', 'module' => json_encode($all_modules)]);
            $this->db->insert('role_permissions', ['role' => 'manager', 'module' => json_encode($all_modules)]);
            $this->db->insert('role_permissions', ['role' => 'field_staff', 'module' => json_encode(['dashboard', 'customers', 'leads', 'orders', 'visits', 'attendance', 'leave'])]);
            $rows = $this->db->get('role_permissions')->result_array();
        }

        $perms = [
            'admin' => $all_modules,
            'manager' => $all_modules,
            'field_staff' => ['dashboard', 'customers', 'leads', 'orders', 'visits', 'attendance', 'leave']
        ];
        foreach ($rows as $r) {
            $modules = json_decode($r['module'], true);
            if (is_array($modules)) {
                $perms[$r['role']] = $modules;
            }
        }
        $this->json_success($perms);
    }

    public function save_role_permissions() {
        $role = $this->input->post('role');
        $modules = $this->input->post('modules') ?: [];
        if (!in_array($role, ['admin', 'manager', 'field_staff'])) {
            $this->json_error('Invalid role.');
        }

        // Ensure admin always retains admin module access
        if ($role === 'admin' && !in_array('admin', $modules)) {
            $modules[] = 'admin';
        }

        $json_modules = json_encode(array_values($modules));

        $exists = $this->db->where('role', $role)->count_all_results('role_permissions');
        if ($exists) {
            $status = $this->db->where('role', $role)->update('role_permissions', ['module' => $json_modules]);
        } else {
            $status = $this->db->insert('role_permissions', [
                'role' => $role,
                'module' => $json_modules
            ]);
        }

        if (!$status) {
            $this->json_error('Failed to save role permissions.');
        }

        $this->json_success([], 'Role permissions saved.');
    }

    // ── TRANSFER STAFF ────────────────────────────────────────────────
    public function transfer_staff() {
        if (!$this->is_admin()) {
            $this->session->set_flashdata('error', 'Access denied.');
            redirect('dashboard');
        }
        
        $staff = $this->User_model->get_field_staff();

        $this->load_view('admin/transfer_staff', [
            'page_title' => 'Transfer Staff',
            'page_js' => 'admin',
            'staff' => $staff
        ]);
    }
    
    public function transfer_staff_dt() {
        if (!$this->is_admin()) {
            $this->json_error('Access denied.', 403);
        }
        $this->load->model('Customer_model');
        $params = $this->input->get();
        
        // Adjust column index for ordering since we added a checkbox column at index 0
        if (isset($params['order'][0]['column'])) {
            $col_idx = (int)$params['order'][0]['column'];
            $params['order'][0]['column'] = $col_idx > 0 ? $col_idx - 1 : 0;
        }

        $sf = ''; // all non-deleted
        [$rows, $total] = $this->Customer_model->datatable($params, $sf, null, null);
        
        $data = [];
        foreach ($rows as $r) {
            $checkbox = '<input type="checkbox" class="customer-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" value="'.$r['id'].'">';
            $city = !empty($r['city']) ? esc_html($r['city']) : '-';
            
            $data[] = [
                $checkbox,
                $r['id'],
                esc_html($r['name']),
                esc_html($r['phone']),
                $city,
                esc_html($r['assigned_name'] ?? 'Unassigned')
            ];
        }
        $this->json_list($data, $total, $total);
    }

    public function process_transfer_staff() {
        if (!$this->is_admin()) {
            $this->json_error('Access denied.', 403);
        }
        
        $customer_ids = $this->input->post('customer_ids');
        $staff_id = (int) $this->input->post('staff_id');
        
        if (empty($customer_ids) || !is_array($customer_ids) || !$staff_id) {
            $this->json_error('Please select customers and a field staff.');
        }
        
        $this->db->where_in('id', $customer_ids)->update('customers', ['assigned_to' => $staff_id]);
        
        $this->json_success([], count($customer_ids) . ' customer(s) transferred successfully.');
    }
}
