<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(['Visit_log_model','Lead_model','Order_model','Attendance_model','Leave_request_model','User_model']);
    }

    public function visits() {
        $staff = $this->User_model->get_field_staff();
        $this->load_view('reports/visits', ['page_title'=>'Visit Reports','staff'=>$staff,'page_js'=>'reports']);
    }

    public function visits_data() {
        $from = $this->input->get('from') ?: date('Y-m-01');
        $to   = $this->input->get('to')   ?: date('Y-m-t');
        $uid  = (int)$this->input->get('user_id') ?: null;
        $this->db->select('u.name AS staff_name, COUNT(vl.id) AS total_visits, COUNT(DISTINCT vl.customer_id) AS unique_customers, AVG(TIMESTAMPDIFF(MINUTE,vl.check_in_at,vl.check_out_at)) AS avg_duration')
            ->from('visit_logs vl')->join('users u','u.id=vl.user_id','left')
            ->where('vl.is_deleted',0)->where('DATE(vl.check_in_at)>=',$from)->where('DATE(vl.check_in_at)<=',$to);
        if ($uid) $this->db->where('vl.user_id',$uid);
        $rows = $this->db->group_by('vl.user_id')->order_by('total_visits','desc')->get()->result_array();
        $this->json_success($rows);
    }

    public function lead_conversion() {
        $staff = $this->User_model->get_field_staff();
        $this->load_view('reports/lead_conversion', ['page_title'=>'Lead Conversion','staff'=>$staff,'page_js'=>'reports']);
    }

    public function lead_data() {
        $from = $this->input->get('from') ?: date('Y-m-01');
        $to   = $this->input->get('to')   ?: date('Y-m-t');
        $uid  = (int)$this->input->get('user_id') ?: null;
        $data = $this->Lead_model->conversion_stats($from, $to, $uid);
        $this->json_success($data);
    }

    public function orders_report() {
        $staff = $this->User_model->get_field_staff();
        $this->load_view('reports/orders', ['page_title'=>'Order Reports','staff'=>$staff,'page_js'=>'reports']);
    }

    public function orders_data() {
        $from = $this->input->get('from') ?: date('Y-m-01');
        $to   = $this->input->get('to')   ?: date('Y-m-t');
        $uid  = (int)$this->input->get('user_id') ?: null;
        $this->db->select('u.name AS staff_name, COUNT(o.id) AS total_orders, SUM(o.final_amount) AS total_revenue, o.order_status')
            ->from('orders o')->join('users u','u.id=o.created_by','left')
            ->where(['o.is_deleted'=>0,'o.status'=>'active'])
            ->where('DATE(o.created_at)>=',$from)->where('DATE(o.created_at)<=',$to);
        if ($uid) $this->db->where('o.created_by',$uid);
        $rows = $this->db->group_by('o.created_by,o.order_status')->order_by('total_revenue','desc')->get()->result_array();
        $this->json_success($rows);
    }

    public function staff_sales() {
        $staff = $this->User_model->get_field_staff();
        $this->load_view('reports/staff_sales', ['page_title'=>'Staff Sales','staff'=>$staff,'page_js'=>'reports']);
    }

    public function staff_data() {
        $from = $this->input->get('from') ?: date('Y-m-01');
        $to   = $this->input->get('to')   ?: date('Y-m-t');
        $uid  = (int)$this->input->get('user_id') ?: null;
        $this->db->select('u.name, COUNT(DISTINCT o.id) AS orders, SUM(o.final_amount) AS revenue, COUNT(DISTINCT vl.id) AS visits, COUNT(DISTINCT l.id) AS leads')
            ->from('users u')
            ->join('orders o','o.created_by=u.id AND o.is_deleted=0 AND DATE(o.created_at)>="'.$from.'" AND DATE(o.created_at)<="'.$to.'"','left')
            ->join('visit_logs vl','vl.user_id=u.id AND vl.is_deleted=0 AND DATE(vl.check_in_at)>="'.$from.'" AND DATE(vl.check_in_at)<="'.$to.'"','left')
            ->join('leads l','l.assigned_to=u.id AND l.is_deleted=0','left')
            ->where(['u.role'=>'field_staff','u.is_deleted'=>0]);
        if ($uid) $this->db->where('u.id', $uid);
        $this->db->group_by('u.id')->order_by('revenue','desc');
        $rows = $this->db->get()->result_array();
        $this->json_success($rows);
    }

    public function attendance_report() {
        $staff = $this->User_model->get_field_staff();
        $this->load_view('reports/attendance', ['page_title'=>'Attendance Report','staff'=>$staff,'page_js'=>'reports']);
    }

    public function attendance_data() {
        $from = $this->input->get('from') ?: date('Y-m-01');
        $to   = $this->input->get('to')   ?: date('Y-m-t');
        $uid  = (int)$this->input->get('user_id') ?: null;
        $this->db->select('u.name AS staff_name, a.attendance_status, COUNT(*) AS cnt')
            ->from('attendance a')->join('users u','u.id=a.user_id','left')
            ->where('a.is_deleted',0)->where('a.date>=',$from)->where('a.date<=',$to);
        if ($uid) $this->db->where('a.user_id',$uid);
        $rows = $this->db->group_by('a.user_id,a.attendance_status')->get()->result_array();
        $this->json_success($rows);
    }

    public function leave_utilisation() {
        $this->load_view('reports/leave_utilisation', ['page_title'=>'Leave Utilisation','page_js'=>'reports']);
    }

    public function leave_data() {
        $year = (int)($this->input->get('year') ?: date('Y'));
        $this->db->select('u.name AS staff_name, lt.name AS leave_type, lb.total_days, lb.used_days, lb.pending_days')
            ->from('leave_balances lb')->join('users u','u.id=lb.user_id','left')->join('leave_types lt','lt.id=lb.leave_type_id','left')
            ->where(['lb.year'=>$year,'lb.is_deleted'=>0]);
        $rows = $this->db->order_by('u.name')->get()->result_array();
        $this->json_success($rows);
    }

    public function punctuality() {
        $staff = $this->User_model->get_field_staff();
        $this->load_view('reports/punctuality', ['page_title'=>'Punctuality','page_js'=>'reports','staff'=>$staff]);
    }

    public function punctuality_data() {
        $from = $this->input->get('from') ?: date('Y-m-01');
        $to   = $this->input->get('to')   ?: date('Y-m-t');
        $uid  = (int)$this->input->get('user_id') ?: null;
        $this->db->select('u.name AS staff_name, COUNT(*) AS total_days, SUM(CASE WHEN a.attendance_status="present" THEN 1 ELSE 0 END) AS present_days, AVG(a.working_hours) AS avg_hours')
            ->from('attendance a')->join('users u','u.id=a.user_id','left')
            ->where('a.is_deleted',0)->where('a.date>=',$from)->where('a.date<=',$to);
        if ($uid) $this->db->where('a.user_id', $uid);
        $this->db->group_by('a.user_id')->order_by('present_days','desc');
        $rows = $this->db->get()->result_array();
        $this->json_success($rows);
    }

    public function coverage() {
        $this->load_view('reports/coverage', ['page_title'=>'Coverage Map','page_js'=>'reports']);
    }

    public function coverage_data() {
        $from = $this->input->get('from') ?: date('Y-m-01');
        $to   = $this->input->get('to')   ?: date('Y-m-t');
        $this->db->select('c.name, c.latitude, c.longitude, COUNT(vl.id) AS visit_count')
            ->from('customers c')
            ->join('visit_logs vl','vl.customer_id=c.id AND vl.is_deleted=0 AND DATE(vl.check_in_at)>="'.$from.'" AND DATE(vl.check_in_at)<="'.$to.'"','left')
            ->where(['c.is_deleted'=>0,'c.status'=>'active'])->where('c.latitude IS NOT NULL')
            ->group_by('c.id');
        $rows = $this->db->get()->result_array();
        $this->json_success($rows);
    }

    public function export($type) {
        $this->json_error('Export not implemented.', 501);
    }
}
