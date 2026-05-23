<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('format_inr')) {
    function format_inr($paise) {
        return '₹' . number_format($paise / 100, 2);
    }
}

if (!function_exists('paise_to_inr')) {
    function paise_to_inr($paise) { return $paise / 100; }
}

if (!function_exists('inr_to_paise')) {
    function inr_to_paise($inr) { return (int) round($inr * 100); }
}

if (!function_exists('time_ago')) {
    function time_ago($datetime) {
        $diff = time() - strtotime($datetime);
        if ($diff < 60)    return $diff . 's ago';
        if ($diff < 3600)  return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        return floor($diff / 86400) . 'd ago';
    }
}

if (!function_exists('status_badge')) {
    function status_badge($s) {
        $map = [
            'active'   => '<span class="label label-success">Active</span>',
            'inactive' => '<span class="label label-warning">Inactive</span>',
            'deleted'  => '<span class="label label-danger">Deleted</span>',
        ];
        return $map[$s] ?? '<span class="label label-default">' . esc_html($s) . '</span>';
    }
}

if (!function_exists('lead_status_badge')) {
    function lead_status_badge($s) {
        $map = [
            'new'         => '<span class="label label-default">New</span>',
            'contacted'   => '<span class="label label-info">Contacted</span>',
            'qualified'   => '<span class="label label-primary">Qualified</span>',
            'proposal'    => '<span class="label label-warning">Proposal</span>',
            'negotiation' => '<span class="label label-warning" style="background:#e67e22">Negotiation</span>',
            'won'         => '<span class="label label-success">Won</span>',
            'lost'        => '<span class="label label-danger">Lost</span>',
        ];
        return $map[$s] ?? esc_html($s);
    }
}

if (!function_exists('order_status_badge')) {
    function order_status_badge($s) {
        $map = [
            'draft'            => '<span class="label label-default">Draft</span>',
            'pending_approval' => '<span class="label label-warning">Pending Approval</span>',
            'approved'         => '<span class="label label-success">Approved</span>',
            'dispatched'       => '<span class="label label-info">Dispatched</span>',
            'delivered'        => '<span class="label label-success" style="background:#1abc9c">Delivered</span>',
            'cancelled'        => '<span class="label label-danger">Cancelled</span>',
        ];
        return $map[$s] ?? esc_html($s);
    }
}

if (!function_exists('visit_status_badge')) {
    function visit_status_badge($s) {
        $map = [
            'planned'     => '<span class="label label-info">Planned</span>',
            'completed'   => '<span class="label label-success">Completed</span>',
            'missed'      => '<span class="label label-danger">Missed</span>',
            'rescheduled' => '<span class="label label-warning">Rescheduled</span>',
        ];
        return $map[$s] ?? esc_html($s);
    }
}

if (!function_exists('att_status_badge')) {
    function att_status_badge($s) {
        $map = [
            'present'   => '<span class="label label-success">Present</span>',
            'absent'    => '<span class="label label-danger">Absent</span>',
            'half_day'  => '<span class="label label-warning">Half Day</span>',
            'on_leave'  => '<span class="label label-info">On Leave</span>',
            'holiday'   => '<span class="label label-primary">Holiday</span>',
            'week_off'  => '<span class="label label-default">Week Off</span>',
        ];
        return $map[$s] ?? esc_html($s);
    }
}

if (!function_exists('esc_html')) {
    function esc_html($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
}

if (!function_exists('generate_order_number')) {
    function generate_order_number() {
        $CI =& get_instance();
        $prefix = $CI->db->where(['setting_key' => 'order_prefix', 'is_deleted' => 0])
                         ->get('app_settings')->row_array();
        $prefix = $prefix['setting_value'] ?? 'ORD';
        return $prefix . '-' . strtoupper(substr(uniqid(), -6)) . '-' . date('Ymd');
    }
}

if (!function_exists('haversine_distance')) {
    function haversine_distance($lat1, $lng1, $lat2, $lng2) {
        $R = 6371000;
        $phi1 = deg2rad($lat1); $phi2 = deg2rad($lat2);
        $dphi = deg2rad($lat2 - $lat1);
        $dlam = deg2rad($lng2 - $lng1);
        $a = sin($dphi / 2) ** 2 + cos($phi1) * cos($phi2) * sin($dlam / 2) ** 2;
        return (int) ($R * 2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}

if (!function_exists('is_point_in_circle')) {
    function is_point_in_circle($lat, $lng, $center_lat, $center_lng, $radius_m) {
        return haversine_distance($lat, $lng, $center_lat, $center_lng) <= $radius_m;
    }
}

if (!function_exists('get_setting')) {
    function get_setting($key, $default = '') {
        $CI =& get_instance();
        $row = $CI->db->where(['setting_key' => $key, 'is_deleted' => 0])
                      ->get('app_settings')->row_array();
        return $row ? $row['setting_value'] : $default;
    }
}

if (!function_exists('crm_action_btns')) {
    function crm_action_btns($id, $resource, $status, $opts = []) {
        $b = '<div class="btn-group">';
        if (!empty($opts['view']))  $b .= '<a href="'.base_url($resource.'/detail/'.$id).'" class="btn btn-xs btn-info"><i class="fa fa-eye"></i></a> ';
        if (!empty($opts['edit']) && $status !== 'deleted') $b .= '<button class="btn btn-xs btn-primary btn-edit" data-id="'.$id.'"><i class="fa fa-pencil"></i></button> ';
        if ($status === 'active')   $b .= '<button class="btn btn-xs btn-warning btn-deactivate" data-id="'.$id.'"><i class="fa fa-ban"></i></button> <button class="btn btn-xs btn-danger btn-delete" data-id="'.$id.'"><i class="fa fa-trash"></i></button>';
        if ($status === 'inactive') $b .= '<button class="btn btn-xs btn-success btn-activate" data-id="'.$id.'"><i class="fa fa-check"></i></button> <button class="btn btn-xs btn-danger btn-delete" data-id="'.$id.'"><i class="fa fa-trash"></i></button>';
        if ($status === 'deleted')  $b .= '<button class="btn btn-xs btn-success btn-restore" data-id="'.$id.'"><i class="fa fa-undo"></i></button>';
        return $b . '</div>';
    }
}

if (!function_exists('working_days_between')) {
    function working_days_between($start, $end) {
        $CI =& get_instance();
        $holidays = $CI->db->where(['is_deleted' => 0, 'status' => 'active'])
                           ->where('date >=', $start)->where('date <=', $end)
                           ->get('holidays')->result_array();
        $holiday_dates = array_column($holidays, 'date');
        $count = 0;
        $d = new DateTime($start);
        $e = new DateTime($end);
        while ($d <= $e) {
            $dow = (int) $d->format('N');
            $ds  = $d->format('Y-m-d');
            if ($dow < 7 && !in_array($ds, $holiday_dates)) $count++;
            $d->modify('+1 day');
        }
        return $count;
    }
}
