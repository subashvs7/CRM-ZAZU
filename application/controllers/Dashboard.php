<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(['User_model','Customer_model','Lead_model','Order_model','Visit_plan_model','Visit_log_model','Attendance_model']);
    }

    public function index() {
        $role = $this->get_role();
        $view = 'dashboard/' . ($role === 'admin' ? 'admin' : ($role === 'manager' ? 'manager' : 'staff'));
        $this->load_view($view, ['page_title' => 'Dashboard', 'page_js' => 'dashboard']);
    }

    public function kpi_data() {
        $this->require_login();
        $role    = $this->get_role();
        $uid     = $this->get_user_id();
        $today   = date('Y-m-d');
        $month_s = date('Y-m-01');
        $month_e = date('Y-m-t');

        $data = [];

        if (in_array($role, ['admin','manager'])) {
            $data['total_customers']  = $this->Customer_model->count_all(['status'=>'active']);
            $data['total_leads']      = $this->Lead_model->count_all(['status'=>'active']);
            $data['total_orders']     = $this->Order_model->count_all(['status'=>'active']);
            $data['pending_orders']   = $this->Order_model->count_all(['order_status'=>'pending_approval','is_deleted'=>0]);
            $data['active_staff']     = $this->User_model->count_all(['role'=>'field_staff','status'=>'active']);
            $data['visits_today']     = $this->db->where(['is_deleted'=>0])->where('DATE(check_in_at)',$today)->count_all_results('visit_logs');
            $data['visits_month']     = $this->db->where(['is_deleted'=>0])->where('check_in_at >=',$month_s)->where('check_in_at <=',$month_e.' 23:59:59')->count_all_results('visit_logs');

            $conv = $this->Lead_model->conversion_stats($month_s, $month_e);
            $data['lead_pipeline'] = $conv;

            $monthly_orders = $this->db->select('DATE(created_at) AS d, SUM(final_amount) AS total')
                ->from('orders')->where(['is_deleted'=>0,'status'=>'active'])
                ->where('created_at >=',$month_s)->where('created_at <=',$month_e.' 23:59:59')
                ->group_by('DATE(created_at)')->order_by('d','asc')->get()->result_array();
            $data['monthly_orders'] = $monthly_orders;

            $data['top_staff'] = $this->db->select('u.id, u.name, COUNT(DISTINCT vl.id) AS visit_count, COUNT(DISTINCT l.id) AS lead_count')
                ->from('visit_logs vl')
                ->join('users u','u.id = vl.user_id','left')
                ->join('leads l','l.assigned_to = u.id AND l.is_deleted = 0','left')
                ->where(['vl.is_deleted'=>0])->where('DATE(vl.check_in_at)>=',$month_s)->where('DATE(vl.check_in_at)<=',$month_e)
                ->group_by('u.id')->order_by('visit_count','desc')->limit(5)->get()->result_array();
        } else {
            $data['my_visits_today'] = $this->db->where(['user_id'=>$uid,'is_deleted'=>0])->where('DATE(check_in_at)',$today)->count_all_results('visit_logs');
            $data['my_visits_month'] = $this->db->where(['user_id'=>$uid,'is_deleted'=>0])->where('check_in_at >=',$month_s)->count_all_results('visit_logs');
            $data['my_leads']        = $this->Lead_model->count_all(['assigned_to'=>$uid,'status'=>'active']);
            $data['my_orders']       = $this->Order_model->count_all(['created_by'=>$uid,'status'=>'active']);

            $att = $this->Attendance_model->get_by_user_date($uid, $today);
            $data['today_attendance'] = $att ? $att['attendance_status'] : 'not_punched';
            $data['punch_in_at']      = ($att && $att['punch_in_at'])  ? date('H:i', strtotime($att['punch_in_at']))  : null;
            $data['punch_out_at']     = ($att && $att['punch_out_at']) ? date('H:i', strtotime($att['punch_out_at'])) : null;

            $plan = $this->db->select('vp.id, vp.planned_time, c.name AS customer_name')
                ->from('visit_plans vp')
                ->join('customers c','c.id=vp.customer_id','left')
                ->where(['vp.user_id'=>$uid,'vp.planned_date'=>$today,'vp.visit_status'=>'planned','vp.is_deleted'=>0])
                ->get()->result_array();
            $data['todays_plans'] = $plan;
        }

        $this->json_success($data);
    }
}
