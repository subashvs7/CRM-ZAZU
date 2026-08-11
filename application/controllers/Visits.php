<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Visits extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(['Visit_plan_model','Visit_log_model','Customer_model','User_model']);
    }

    public function index() {
        $customers = $this->Customer_model->get_active($this->is_manager() ? [] : ['assigned_to' => $this->get_user_id()]);
        $staff     = $this->is_manager() ? $this->User_model->get_field_staff() : [];
        $this->load_view('visits/index', ['page_title'=>'Visit Plans','page_js'=>'visits','customers'=>$customers,'staff'=>$staff,'sf'=>'']);
    }

    public function history() {
        $this->load_view('visits/history', ['page_title'=>'Visit History','page_js'=>'visits','sf'=>'']);
    }

    public function planned_vs_actual() {
        $this->load_view('visits/planned_vs_actual', ['page_title'=>'Planned vs Actual','page_js'=>'visits']);
    }

    public function checkin() {
        $plan_id   = (int)$this->input->get('plan_id');
        $plan      = $plan_id ? $this->Visit_plan_model->get_with_details($plan_id) : null;
        $customers = $this->Customer_model->get_active($this->is_manager() ? [] : ['assigned_to' => $this->get_user_id()]);
        // Pass any existing open check-in so view can show a "checkout" prompt
        $open_visit = $this->Visit_log_model->get_open_visit($this->get_user_id());
        $this->load_view('visits/checkin', [
            'page_title' => 'Check In',
            'plan'       => $plan,
            'customers'  => $customers,
            'open_visit' => $open_visit,
        ]);
    }

    public function do_checkin() {
        $cid     = (int)$this->input->post('customer_id');
        $plan_id = (int)$this->input->post('visit_plan_id') ?: null;
        $lat     = $this->input->post('latitude');
        $lng     = $this->input->post('longitude');

        if ($this->is_admin() && $this->input->post('check_in_location')) {
            $loc = explode(',', $this->input->post('check_in_location'));
            if (count($loc) == 2) {
                $lat = trim($loc[0]);
                $lng = trim($loc[1]);
            }
        }
        $notes   = $this->input->post('notes');

        if (!$cid) {
            $this->json_error('Customer is required.', 400);
        }

        // Check for an existing open check-in
        $open = $this->Visit_log_model->get_open_visit($this->get_user_id());
        if ($open) {
            $this->json_error('You are already checked in elsewhere. Please check out first.', 400, ['open_log_id' => $open['id']]);
        }

        $distance = null;
        if ($lat && $lng) {
            $customer = $this->Customer_model->get_by_id($cid);
            if ($customer && $customer['latitude'] && $customer['longitude']) {
                $theta = $lng - $customer['longitude'];
                $dist = sin(deg2rad($lat)) * sin(deg2rad($customer['latitude'])) +  cos(deg2rad($lat)) * cos(deg2rad($customer['latitude'])) * cos(deg2rad($theta));
                $dist = acos($dist);
                $dist = rad2deg($dist);
                $distance = round($dist * 60 * 1.1515 * 1.609344 * 1000); // meters
            }
        }

        $now = date('Y-m-d H:i:s');
        if ($this->is_admin() && $this->input->post('check_in_at')) {
            $parsed = strtotime($this->input->post('check_in_at'));
            if ($parsed) $now = date('Y-m-d H:i:s', $parsed);
        }
        $data = [
            'visit_plan_id'    => $plan_id,
            'user_id'          => $this->get_user_id(),
            'customer_id'      => $cid,
            'check_in_at'      => $now,
            'check_in_lat'     => $lat ?: null,
            'check_in_lng'     => $lng ?: null,
            'distance_from_customer' => $distance,
            'notes'            => $notes,
        ];
        
        $id = $this->Visit_log_model->insert($data);

        $this->json_success(['id' => $id], 'Checked in successfully.');
    }

    public function do_checkout($id) {
        $log = $this->Visit_log_model->get_by_id($id);
        if (!$log || $log['user_id'] != $this->get_user_id()) $this->json_error('Not found.', 404);
        if ($log['check_out_at']) $this->json_error('Already checked out.');

        $lat = $this->input->post('latitude');
        $lng = $this->input->post('longitude');
        
        if ($this->is_admin() && $this->input->post('check_out_location')) {
            $loc = explode(',', $this->input->post('check_out_location'));
            if (count($loc) == 2) {
                $lat = trim($loc[0]);
                $lng = trim($loc[1]);
            }
        }
        $notes = $this->input->post('notes');
        $outcome = $this->input->post('visit_outcome');

        if (!$outcome) $this->json_error('Please select a Status.', 400);

        $now = date('Y-m-d H:i:s');
        if ($this->is_admin() && $this->input->post('check_out_at')) {
            $parsed = strtotime($this->input->post('check_out_at'));
            if ($parsed) $now = date('Y-m-d H:i:s', $parsed);
        }

        $data = [
            'check_out_at'       => $now,
            'check_out_lat'      => $lat ?: null,
            'check_out_lng'      => $lng ?: null,
            'visit_outcome'      => $outcome,
            'related_follow_ups' => json_encode($this->input->post('related_follow_ups') ?: []),
            'notes'              => $notes ?: $log['notes'],
        ];
        $this->Visit_log_model->update($id, $data);
        
        // Complete the plan
        if ($log['visit_plan_id']) {
            $this->Visit_plan_model->update($log['visit_plan_id'], ['visit_status' => 'completed']);
        }
        
        // Shift customer to followup
        $this->Customer_model->update($log['customer_id'], ['customer_type' => 'followup']);

        $this->json_success([], 'Checked out successfully. Customer moved to Follow-ups.');
    }

    public function datatable() {
        $params = $this->input->get(); $sf = $this->input->get('status_filter');
        [$rows, $total] = $this->Visit_plan_model->datatable($params, $sf, $this->get_user_id(), $this->get_role());
        $data = [];
        foreach ($rows as $r) {
            $acts = '<div class="flex items-center justify-center gap-1.5">';
            if ($r['visit_status'] === 'planned' || $r['visit_status'] === 'rescheduled') {
                if ($r['open_visit_log_id']) {
                    $acts .= '<button class="px-2.5 py-1 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-xs font-bold btn-checkout" data-id="'.$r['id'].'" data-log-id="'.$r['open_visit_log_id'].'" data-customer="'.$r['customer_id'].'">Check Out</button>';
                } else {
                    $acts .= '<button class="px-2.5 py-1 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-xs font-bold btn-checkin" data-id="'.$r['id'].'" data-customer="'.$r['customer_id'].'">Check In</button>';
                }
            } else if ($r['visit_status'] === 'completed') {
                $acts .= '<button class="px-2.5 py-1 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition-colors text-xs font-bold btn-replan" data-id="'.$r['id'].'" data-date="'.$r['planned_date'].'" data-time="'.$r['planned_time'].'">Re-plan</button>';
            }
            $acts .= '</div>';
            $data[] = [
                'DT_RowClass' => ($r['customer_type'] === 'primary' ? 'bg-blue-50' : ($r['customer_type'] === 'followup' ? 'bg-yellow-50' : '')),
                0 => $r['id'], 
                1 => esc_html($r['customer_name']), 
                2 => esc_html($r['user_name']), 
                3 => date('d M Y', strtotime($r['planned_date'])) . ($r['planned_time'] ? '<br><span class="text-[10px] text-gray-500">'.date('h:i A', strtotime($r['planned_time'])).'</span>' : ''), 
                4 => esc_html(substr($r['purpose']??'',0,50)), 
                5 => visit_status_badge($r['visit_status']), 
                6 => $acts
            ];
        }
        $this->json_list($data, $total, $total);
    }

    public function history_datatable() {
        $params = $this->input->get(); $sf = $this->input->get('status_filter');
        [$rows, $total] = $this->Visit_log_model->datatable($params, $sf, $this->get_user_id(), $this->get_role());
        $data = [];
        foreach ($rows as $r) {
            $checkin = date('d M Y h:i A', strtotime($r['check_in_at']));
            if ($r['check_in_lat'] && $r['check_in_lng']) {
                $checkin .= '<br><span class="text-[10px] text-gray-400"><i class="fa fa-map-marker text-red-500"></i> ' . $r['check_in_lat'] . ', ' . $r['check_in_lng'] . '</span>';
            }

            $checkout = '-';
            if ($r['check_out_at']) {
                $checkout = date('d M Y h:i A', strtotime($r['check_out_at']));
                if ($r['check_out_lat'] && $r['check_out_lng']) {
                    $checkout .= '<br><span class="text-[10px] text-gray-400"><i class="fa fa-map-marker text-red-500"></i> ' . $r['check_out_lat'] . ', ' . $r['check_out_lng'] . '</span>';
                }
            }

            $outcome = $r['visit_outcome'];
            $outcome_badge = '<span class="px-2 py-0.5 border border-gray-200 text-gray-500 bg-gray-50 text-[11px] rounded-md font-medium">Pending</span>';
            if ($outcome === 'Met Successfully') {
                $outcome_badge = '<span class="px-2 py-0.5 border border-green-200 text-green-600 bg-green-50 text-[11px] rounded-md font-medium">Met Successfully</span>';
            } elseif ($outcome === 'Refused' || $outcome === 'Canceled') {
                $outcome_badge = '<span class="px-2 py-0.5 border border-red-200 text-red-600 bg-red-50 text-[11px] rounded-md font-medium">'.$outcome.'</span>';
            } elseif ($outcome === 'Reschedule' || $outcome === 'Not Available') {
                $outcome_badge = '<span class="px-2 py-0.5 border border-yellow-200 text-yellow-700 bg-yellow-50 text-[11px] rounded-md font-medium">'.$outcome.'</span>';
            } elseif ($outcome) {
                $outcome_badge = '<span class="px-2 py-0.5 border border-blue-200 text-blue-600 bg-blue-50 text-[11px] rounded-md font-medium">'.$outcome.'</span>';
            }

            $data[] = [
                $r['id'], 
                $checkin, 
                $checkout, 
                esc_html($r['customer_name']), 
                esc_html($r['user_name']), 
                $r['distance_from_customer'] ? $r['distance_from_customer'].'m' : '-', 
                $r['is_auto_checkin'] ? '<span class="text-green-600">Yes</span>' : '<span class="text-gray-400">No</span>', 
                $outcome_badge
            ];
        }
        $this->json_list($data, $total, $total);
    }

    public function save() {
        $id  = (int)$this->input->post('id');
        
        if ($this->input->post('action') === 'replan') {
            $dt = $this->input->post('planned_date');
            if (!$dt) $this->json_error('Validation failed.', 400, ['planned_date'=>'Required.']);
            $this->Visit_plan_model->update($id, [
                'planned_date' => $dt,
                'planned_time' => $this->input->post('planned_time'),
                'visit_status' => 'planned'
            ]);
            $this->json_success([], 'Visit re-planned.');
        }

        $cid = (int)$this->input->post('customer_id');
        $uid = (int)$this->input->post('user_id') ?: $this->get_user_id();
        $dt  = $this->input->post('planned_date');
        if (!$cid || !$dt) $this->json_error('Validation failed.',400,['customer_id'=>'Required.','planned_date'=>'Required.']);
        $data = ['user_id'=>$uid,'customer_id'=>$cid,'planned_date'=>$dt,'planned_time'=>$this->input->post('planned_time'),'purpose'=>$this->input->post('purpose'),'lead_id'=>(int)$this->input->post('lead_id')?:null,'created_by'=>$this->get_user_id()];
        if ($id) { $this->Visit_plan_model->update($id,$data); $this->json_success([],'Visit updated.'); }
        else     { $new=$this->Visit_plan_model->insert($data); $this->json_success(['id'=>$new],'Visit planned.'); }
    }

    public function get($id) {
        $plan = $this->Visit_plan_model->get_with_details($id);
        if (!$plan) $this->json_error('Not found.', 404);
        $this->json_success($plan);
    }

    public function detail($id) {
        $plan = $this->Visit_plan_model->get_with_details($id);
        if (!$plan) show_404();
        $this->load_view('visits/_detail', ['page_title'=>'Visit Detail','plan'=>$plan]);
    }

    public function update_status() {
        $id=(int)$this->input->post('id');$action=$this->input->post('action');
        switch($action){case'activate':$this->Visit_plan_model->activate($id);break;case'delete':$this->Visit_plan_model->soft_delete($id);break;case'restore':$this->Visit_plan_model->restore($id);break;}
        $this->json_success([],'Status updated.');
    }

    public function calendar_data() {
        $data = $this->Visit_plan_model->calendar_data($this->get_user_id(),$this->get_role(),$this->input->get('start'),$this->input->get('end'));
        $events = [];
        foreach ($data as $d) {
            $color = ['planned'=>'#3c8dbc','completed'=>'#00a65a','missed'=>'#dd4b39','rescheduled'=>'#f39c12'][$d['visit_status']] ?? '#777';
            $events[] = ['id'=>$d['id'],'title'=>$d['customer_name'].' ('.$d['user_name'].')','start'=>$d['planned_date'].($d['planned_time']?' T'.$d['planned_time']:''),'color'=>$color];
        }
        echo json_encode($events); exit;
    }
}
