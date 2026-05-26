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
            'active'   => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-green-100 text-green-700">Active</span>',
            'inactive' => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-amber-100 text-amber-700">Inactive</span>',
            'deleted'  => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-red-100 text-red-700">Deleted</span>',
        ];
        return $map[$s] ?? '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600">' . esc_html($s) . '</span>';
    }
}

if (!function_exists('lead_status_badge')) {
    function lead_status_badge($s) {
        $map = [
            'new'         => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600">New</span>',
            'contacted'   => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-cyan-100 text-cyan-700">Contacted</span>',
            'qualified'   => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-blue-100 text-blue-700">Qualified</span>',
            'proposal'    => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-amber-100 text-amber-700">Proposal</span>',
            'negotiation' => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-orange-100 text-orange-700">Negotiation</span>',
            'won'         => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-green-100 text-green-700">Won</span>',
            'lost'        => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-red-100 text-red-700">Lost</span>',
        ];
        return $map[$s] ?? '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600">' . esc_html($s) . '</span>';
    }
}

if (!function_exists('order_status_badge')) {
    function order_status_badge($s) {
        $map = [
            'draft'            => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600">Draft</span>',
            'pending_approval' => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-amber-100 text-amber-700">Pending Approval</span>',
            'approved'         => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-green-100 text-green-700">Approved</span>',
            'dispatched'       => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-blue-100 text-blue-700">Dispatched</span>',
            'delivered'        => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-emerald-100 text-emerald-700">Delivered</span>',
            'cancelled'        => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-red-100 text-red-700">Cancelled</span>',
        ];
        return $map[$s] ?? '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600">' . esc_html($s) . '</span>';
    }
}

if (!function_exists('visit_status_badge')) {
    function visit_status_badge($s) {
        $map = [
            'planned'     => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-cyan-100 text-cyan-700">Planned</span>',
            'completed'   => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-green-100 text-green-700">Completed</span>',
            'missed'      => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-red-100 text-red-700">Missed</span>',
            'rescheduled' => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-amber-100 text-amber-700">Rescheduled</span>',
        ];
        return $map[$s] ?? '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600">' . esc_html($s) . '</span>';
    }
}

if (!function_exists('att_status_badge')) {
    function att_status_badge($s) {
        $map = [
            'present'   => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-green-100 text-green-700">Present</span>',
            'absent'    => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-red-100 text-red-700">Absent</span>',
            'half_day'  => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-amber-100 text-amber-700">Half Day</span>',
            'on_leave'  => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-blue-100 text-blue-700">On Leave</span>',
            'holiday'   => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-purple-100 text-purple-700">Holiday</span>',
            'week_off'  => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-500">Week Off</span>',
        ];
        return $map[$s] ?? '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600">' . esc_html($s) . '</span>';
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
        $mod = rtrim($resource, 's'); // 'customers'→'customer', 'leads'→'lead', etc.
        $b = '<div class="flex items-center gap-1">';
        if (!empty($opts['view']))
            $b .= '<a href="'.base_url($resource.'/detail/'.$id).'" class="inline-flex items-center justify-center w-7 h-7 bg-cyan-100 text-cyan-700 rounded-lg hover:bg-cyan-200 transition-colors" title="View"><i class="fa fa-eye" style="font-size:11px"></i></a>';
        if (!empty($opts['edit']) && $status !== 'deleted')
            $b .= '<button class="inline-flex items-center justify-center w-7 h-7 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors btn-edit-'.$mod.'" data-id="'.$id.'" title="Edit"><i class="fa fa-pencil" style="font-size:11px"></i></button>';
        if ($status === 'active')
            $b .= '<button class="inline-flex items-center justify-center w-7 h-7 bg-amber-100 text-amber-700 rounded-lg hover:bg-amber-200 transition-colors btn-'.$mod.'-status" data-id="'.$id.'" data-action="deactivate" title="Deactivate"><i class="fa fa-ban" style="font-size:11px"></i></button>'
                . '<button class="inline-flex items-center justify-center w-7 h-7 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors btn-'.$mod.'-status" data-id="'.$id.'" data-action="delete" title="Delete"><i class="fa fa-trash" style="font-size:11px"></i></button>';
        if ($status === 'inactive')
            $b .= '<button class="inline-flex items-center justify-center w-7 h-7 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors btn-'.$mod.'-status" data-id="'.$id.'" data-action="activate" title="Activate"><i class="fa fa-check" style="font-size:11px"></i></button>'
                . '<button class="inline-flex items-center justify-center w-7 h-7 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors btn-'.$mod.'-status" data-id="'.$id.'" data-action="delete" title="Delete"><i class="fa fa-trash" style="font-size:11px"></i></button>';
        if ($status === 'deleted')
            $b .= '<button class="inline-flex items-center justify-center w-7 h-7 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors btn-'.$mod.'-status" data-id="'.$id.'" data-action="restore" title="Restore"><i class="fa fa-undo" style="font-size:11px"></i></button>';
        return $b . '</div>';
    }
}

if (!function_exists('crm_btn')) {
    function crm_btn($class, $title, $icon, $attrs = '') {
        return '<button class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors '.$class.'" title="'.$title.'" '.$attrs.'><i class="fa fa-'.$icon.'" style="font-size:11px"></i></button>';
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

if (!function_exists('has_module_access')) {
    function has_module_access($module) {
        $CI =& get_instance();
        $user = $CI->session->userdata('crm_user');
        if (!$user) return false;

        $role = $user['role'] ?? null;
        if (!$role) return false;

        $row = $CI->db->where('role', $role)->get('role_permissions')->row_array();
        if ($row && !empty($row['module'])) {
            $allowed = json_decode($row['module'], true);
            return is_array($allowed) && in_array($module, $allowed);
        }
        return false;
    }
}
