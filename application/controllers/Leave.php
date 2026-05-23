<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leave extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(['Leave_request_model','Leave_balance_model','Leave_type_model','User_model']);
        $this->load->library('Notification_sender');
    }

    public function index() {
        $types    = $this->Leave_type_model->get_active();
        $balances = $this->Leave_balance_model->get_user_balances($this->get_user_id());
        $this->load_view('leave/index', ['page_title'=>'My Leave','page_js'=>'leave','types'=>$types,'balances'=>$balances,'sf'=>'']);
    }

    public function apply() {
        $types = $this->Leave_type_model->get_active();
        $this->load_view('leave/apply', ['page_title'=>'Apply for Leave','types'=>$types]);
    }

    public function save() {
        $uid  = $this->get_user_id();
        $tid  = (int)$this->input->post('leave_type_id');
        $from = $this->input->post('from_date');
        $to   = $this->input->post('to_date');
        $reason = $this->input->post('reason');
        $errors = [];
        if (!$tid)    $errors['leave_type_id'] = 'Leave type required.';
        if (!$from)   $errors['from_date']      = 'Start date required.';
        if (!$to)     $errors['to_date']         = 'End date required.';
        if (!$reason) $errors['reason']          = 'Reason required.';
        if ($errors)  $this->json_error('Validation failed.',400,$errors);

        $days    = working_days_between($from, $to);
        if ($days < 1) $this->json_error('Validation failed.', 400, ['from_date' => 'No working days in selected range.']);

        $balance = $this->Leave_balance_model->get_balance($uid, $tid);
        if (!$balance) {
            // Auto-initialise balance for users who don't have one yet
            $leave_type = $this->Leave_type_model->get_by_id($tid);
            if (!$leave_type) $this->json_error('Invalid leave type.');
            $this->Leave_balance_model->insert([
                'user_id'       => $uid,
                'leave_type_id' => $tid,
                'year'          => (int)date('Y'),
                'total_days'    => $leave_type['days_allowed_per_year'],
                'used_days'     => 0,
                'pending_days'  => 0,
            ]);
            $balance = $this->Leave_balance_model->get_balance($uid, $tid);
        }
        $available = $balance['total_days'] - $balance['used_days'] - $balance['pending_days'];
        if ($days > $available) $this->json_error("Insufficient balance. Available: {$available} days, Requested: {$days} days.");

        $data = ['user_id'=>$uid,'leave_type_id'=>$tid,'from_date'=>$from,'to_date'=>$to,'days'=>$days,'reason'=>$reason,'leave_status'=>'pending'];
        $id   = $this->Leave_request_model->insert($data);
        $this->Leave_balance_model->add_pending($uid, $tid, $days);
        $this->notification_sender->send_to_managers('leave_applied','Leave Request','New leave request from '.$this->get_user()['name'],['request_id'=>$id]);
        $this->json_success(['id'=>$id], 'Leave request submitted ('.$days.' days).');
    }

    public function datatable() {
        $params = $this->input->get(); $sf = $this->input->get('status_filter');
        [$rows, $total] = $this->Leave_request_model->datatable($params, $sf, $this->get_user_id(), $this->get_role());
        $data = [];
        foreach ($rows as $r) {
            $acts = '';
            if ($r['leave_status']==='pending' && ($r['user_id'] ?? 0)==$this->get_user_id()) {
                $acts .= '<button class="btn btn-xs btn-warning btn-cancel-leave" data-id="'.$r['id'].'"><i class="fa fa-times"></i> Cancel</button>';
            }
            $badge = ['pending'=>'warning','approved'=>'success','rejected'=>'danger','cancelled'=>'default'][$r['leave_status']] ?? 'default';
            $data[] = [$r['id'],esc_html($r['user_name']),esc_html($r['leave_type_name']),$r['from_date'],$r['to_date'],$r['days'],'<span class="label label-'.$badge.'">'.ucfirst($r['leave_status']).'</span>',date('d M Y',strtotime($r['created_at'])),$acts];
        }
        $this->json_list($data,$total,$total);
    }

    public function approval() {
        $this->require_role(['admin','manager']);
        $this->load_view('leave/approval', ['page_title'=>'Leave Approval','page_js'=>'leave']);
    }

    public function approval_datatable() {
        $this->require_role(['admin','manager']);
        $params = $this->input->get();
        [$rows, $total] = $this->Leave_request_model->approval_datatable($params);
        $data = [];
        foreach ($rows as $r) {
            $acts = '<button class="btn btn-xs btn-success btn-approve-leave" data-id="'.$r['id'].'"><i class="fa fa-check"></i> Approve</button> <button class="btn btn-xs btn-danger btn-reject-leave" data-id="'.$r['id'].'"><i class="fa fa-times"></i> Reject</button>';
            $data[] = [$r['id'],esc_html($r['user_name']),esc_html($r['leave_type_name']),$r['from_date'],$r['to_date'],$r['days'],esc_html(substr($r['reason'],0,60)),date('d M Y',strtotime($r['created_at'])),$acts];
        }
        $this->json_list($data,$total,$total);
    }

    public function approve() {
        $this->require_role(['admin','manager']);
        $id = (int)$this->input->post('id');
        $req = $this->Leave_request_model->get_with_details($id);
        if (!$req) $this->json_error('Not found.',404);
        $this->Leave_request_model->update($id,['leave_status'=>'approved','approved_by'=>$this->get_user_id(),'approval_notes'=>$this->input->post('notes')]);
        $this->Leave_balance_model->deduct($req['user_id'],$req['leave_type_id'],$req['days']);
        $this->notification_sender->send($req['user_id'],'leave_approved','Leave Approved','Your leave request has been approved.',['request_id'=>$id]);
        $this->json_success([],'Leave approved.');
    }

    public function reject() {
        $this->require_role(['admin','manager']);
        $id = (int)$this->input->post('id');
        $req = $this->Leave_request_model->get_with_details($id);
        if (!$req) $this->json_error('Not found.',404);
        $this->Leave_request_model->update($id,['leave_status'=>'rejected','approved_by'=>$this->get_user_id(),'approval_notes'=>$this->input->post('notes')]);
        $this->Leave_balance_model->revert_pending($req['user_id'],$req['leave_type_id'],$req['days']);
        $this->notification_sender->send($req['user_id'],'leave_rejected','Leave Rejected','Your leave request was rejected.',['request_id'=>$id]);
        $this->json_success([],'Leave rejected.');
    }

    public function cancel() {
        $id  = (int)$this->input->post('id');
        $req = $this->Leave_request_model->get_with_details($id);
        if (!$req || $req['user_id'] != $this->get_user_id()) $this->json_error('Not found.',404);
        if ($req['leave_status'] !== 'pending') $this->json_error('Cannot cancel a non-pending request.');
        $this->Leave_request_model->update($id,['leave_status'=>'cancelled']);
        $this->Leave_balance_model->revert_pending($req['user_id'],$req['leave_type_id'],$req['days']);
        $this->json_success([],'Leave cancelled.');
    }

    public function balances() {
        $users = $this->is_manager() ? $this->User_model->get_field_staff() : [$this->get_user()];
        $types = $this->Leave_type_model->get_active();
        $this->load_view('leave/balances', ['page_title'=>'Leave Balances','users'=>$users,'types'=>$types,'page_js'=>'leave']);
    }

    public function balances_data() {
        $uid  = $this->is_manager() ? (int)$this->input->get('user_id') ?: $this->get_user_id() : $this->get_user_id();
        $year = (int)($this->input->get('year') ?: date('Y'));
        $data = $this->Leave_balance_model->get_user_balances($uid, $year);
        $this->json_success($data);
    }

    public function update_status() {
        $id=(int)$this->input->post('id');$action=$this->input->post('action');
        if($action==='delete') $this->Leave_request_model->soft_delete($id);
        $this->json_success([],'Done.');
    }
}
