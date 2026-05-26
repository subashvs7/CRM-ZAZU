<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Geofence extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(['Geofence_zone_model','Geo_alert_model','Alert_rule_model','Customer_model']);
    }

    public function index() {
        $customers = $this->Customer_model->get_active();
        $this->load_view('geofence/index', ['page_title'=>'Geofence','page_js'=>'geofence','customers'=>$customers,'sf'=>'']);
    }

    public function datatable() {
        $params = $this->input->get(); $sf = $this->input->get('status_filter');
        [$rows, $total] = $this->Geofence_zone_model->datatable($params, $sf);
        $data = [];
        foreach ($rows as $r) {
            $acts = '<button class="btn btn-xs btn-primary btn-edit-zone" data-id="'.$r['id'].'"><i class="fa fa-pencil"></i></button> ';
            if ($r['status']==='active') $acts .= '<button class="btn btn-xs btn-danger btn-zone-status" data-id="'.$r['id'].'" data-action="deactivate"><i class="fa fa-ban"></i></button>';
            $data[] = [$r['id'],esc_html($r['name']),esc_html($r['zone_type']),$r['radius_meters']?$r['radius_meters'].'m':'-',$r['auto_checkin']?'Yes':'No',$r['alert_on_enter']?'Yes':'No',$r['alert_on_exit']?'Yes':'No',status_badge($r['status']),'<div class="btn-group">'.$acts.'</div>'];
        }
        $this->json_list($data,$total,$total);
    }

    public function save() {
        $id   = (int)$this->input->post('id');
        $name = trim($this->input->post('name'));
        if (!$name) $this->json_error('Name required.',400,['name'=>'Name required.']);
        $data = ['name'=>$name,'zone_type'=>$this->input->post('zone_type'),'center_lat'=>$this->input->post('center_lat')?:(null),'center_lng'=>$this->input->post('center_lng')?:(null),'radius_meters'=>(int)$this->input->post('radius_meters')?:null,'customer_id'=>(int)$this->input->post('customer_id')?:null,'auto_checkin'=>(int)$this->input->post('auto_checkin'),'alert_on_enter'=>(int)$this->input->post('alert_on_enter'),'alert_on_exit'=>(int)$this->input->post('alert_on_exit')];
        if ($id) { $this->Geofence_zone_model->update($id,$data); $this->json_success([],'Zone updated.'); }
        else     { $new=$this->Geofence_zone_model->insert($data); $this->json_success(['id'=>$new],'Zone created.'); }
    }

    public function get($id) {
        $zone = $this->Geofence_zone_model->get_with_customer($id);
        if (!$zone) $this->json_error('Not found.',404);
        $this->json_success($zone);
    }

    public function update_status() {
        $id=(int)$this->input->post('id');$action=$this->input->post('action');
        switch($action){case'activate':$this->Geofence_zone_model->activate($id);break;case'deactivate':$this->Geofence_zone_model->deactivate($id);break;case'delete':$this->Geofence_zone_model->soft_delete($id);break;case'restore':$this->Geofence_zone_model->restore($id);break;}
        $this->json_success([],'Status updated.');
    }

    public function assignment() {
        $zones = $this->Geofence_zone_model->get_active_zones();
        $this->load_view('geofence/assignment', ['page_title'=>'Zone Assignment','zones'=>$zones,'page_js'=>'geofence']);
    }

    public function save_assignment() { $this->json_success([],'Assignment saved.'); }

    public function auto_checkins() {
        $this->load_view('geofence/auto_checkins', ['page_title'=>'Auto Check-ins','page_js'=>'geofence','sf'=>'']);
    }

    public function checkins_datatable() {
        $params = $this->input->get();
        $this->db->select('vl.id, u.name AS user_name, c.name AS customer_name, vl.check_in_at, gz.name AS zone_name')
            ->from('visit_logs vl')
            ->join('users u','u.id=vl.user_id','left')->join('customers c','c.id=vl.customer_id','left')
            ->join('geofence_zones gz','gz.id=vl.geofence_zone_id','left')
            ->where(['vl.is_auto_checkin'=>1,'vl.is_deleted'=>0]);
        $total = $this->db->count_all_results('',false);
        $this->db->order_by('vl.id','desc')->limit($params['length'],$params['start']);
        $rows = $this->db->get()->result_array();
        $data = [];
        foreach ($rows as $r) {
            $data[] = [$r['id'],esc_html($r['user_name']),esc_html($r['customer_name']),esc_html($r['zone_name']??'-'),date('d M Y H:i',strtotime($r['check_in_at']))];
        }
        $this->json_list($data,$total,$total);
    }

    public function violations() {
        $this->load_view('geofence/violations', ['page_title'=>'Geo Violations','page_js'=>'geofence','sf'=>'']);
    }

    public function violations_datatable() {
        $params = $this->input->get();
        [$rows, $total] = $this->Geo_alert_model->datatable($params);
        $data = [];
        foreach ($rows as $r) {
            $acts = !$r['resolved_at'] ? '<button class="btn btn-xs btn-success btn-resolve-alert" data-id="'.$r['id'].'"><i class="fa fa-check"></i> Resolve</button>' : '<span class="text-success"><i class="fa fa-check"></i> Resolved</span>';
            $data[] = [$r['id'],esc_html($r['zone_name']??'-'),esc_html($r['user_name']),esc_html($r['alert_type']),date('d M Y H:i',strtotime($r['triggered_at'])),$r['resolved_at']?date('d M Y H:i',strtotime($r['resolved_at'])):'-',$acts];
        }
        $this->json_list($data,$total,$total);
    }

    public function resolve_alert() {
        $id = (int)$this->input->post('id');
        $this->Geo_alert_model->resolve($id,$this->get_user_id());
        $this->json_success([],'Alert resolved.');
    }

    public function alert_rules() {
        $zones = $this->Geofence_zone_model->get_active_zones();
        $this->load_view('geofence/alert_rules', ['page_title'=>'Alert Rules','zones'=>$zones,'page_js'=>'geofence']);
    }

    public function rules_datatable() {
        $params = $this->input->get();
        [$rows, $total] = $this->Alert_rule_model->datatable($params);
        $data = [];
        foreach ($rows as $r) {
            $acts = '<button class="btn btn-xs btn-danger btn-rule-status" data-id="'.$r['id'].'" data-action="delete"><i class="fa fa-trash"></i></button>';
            $data[] = [$r['id'],esc_html($r['zone_name']??'All'),esc_html($r['event_type']),esc_html($r['notify_roles']),$r['cooldown_minutes'].'m',status_badge($r['status']),$acts];
        }
        $this->json_list($data,$total,$total);
    }

    public function save_rule() {
        $id   = (int)$this->input->post('id');
        $data = ['geofence_zone_id'=>(int)$this->input->post('geofence_zone_id')?:null,'event_type'=>$this->input->post('event_type'),'notify_roles'=>json_encode($this->input->post('notify_roles')?:[]),'cooldown_minutes'=>(int)$this->input->post('cooldown_minutes')];
        if ($id) { $this->Alert_rule_model->update($id,$data); $this->json_success([],'Rule updated.'); }
        else     { $this->Alert_rule_model->insert($data); $this->json_success([],'Rule created.'); }
    }

    public function update_rule_status() {
        $id=(int)$this->input->post('id');$action=$this->input->post('action');
        switch($action){case'activate':$this->Alert_rule_model->activate($id);break;case'deactivate':$this->Alert_rule_model->deactivate($id);break;case'delete':$this->Alert_rule_model->soft_delete($id);break;}
        $this->json_success([],'Status updated.');
    }
}
