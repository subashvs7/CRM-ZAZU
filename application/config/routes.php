<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'auth';
$route['404_override']       = '';
$route['translate_uri_dashes'] = FALSE;

// Auth
$route['auth/login']    = 'Auth/login';
$route['auth/logout']   = 'Auth/logout';
$route['auth/do_login'] = 'Auth/do_login';

// Dashboard
$route['dashboard']          = 'Dashboard/index';
$route['dashboard/kpi_data'] = 'Dashboard/kpi_data';

// Leads
$route['leads']                   = 'Leads/index';
$route['leads/pipeline']          = 'Leads/pipeline';
$route['leads/datatable']         = 'Leads/datatable';
$route['leads/save']              = 'Leads/save';
$route['leads/get/(:num)']        = 'Leads/get/$1';
$route['leads/detail/(:num)']     = 'Leads/detail/$1';
$route['leads/status']            = 'Leads/update_status';
$route['leads/add_activity']      = 'Leads/add_activity';
$route['leads/activities/(:num)'] = 'Leads/activities/$1';
$route['leads/import']            = 'Leads/import';
$route['leads/import_process']    = 'Leads/import_process';

// Customers
$route['customers']                 = 'Customers/index';
$route['customers/datatable']       = 'Customers/datatable';
$route['customers/save']            = 'Customers/save';
$route['customers/get/(:num)']      = 'Customers/get/$1';
$route['customers/detail/(:num)']   = 'Customers/detail/$1';
$route['customers/status']          = 'Customers/update_status';
$route['customers/contacts/save']   = 'Customers/save_contact';
$route['customers/contacts/status'] = 'Customers/contact_status';
$route['customers/contacts/(:num)'] = 'Customers/get_contacts/$1';
$route['customers/map_data']        = 'Customers/map_data';

// Orders
$route['orders']               = 'Orders/index';
$route['orders/products_list'] = 'Orders/products_list';
$route['orders/approval']      = 'Orders/approval';
$route['orders/datatable']     = 'Orders/datatable';
$route['orders/approval_dt']   = 'Orders/approval_datatable';
$route['orders/save']          = 'Orders/save';
$route['orders/get/(:num)']    = 'Orders/get/$1';
$route['orders/detail/(:num)'] = 'Orders/detail/$1';
$route['orders/status']        = 'Orders/update_status';
$route['orders/approve']       = 'Orders/approve';
$route['orders/reject']        = 'Orders/reject';
$route['orders/pdf/(:num)']    = 'Orders/download_pdf/$1';

// Visits
$route['visits']                    = 'Visits/index';
$route['visits/history']            = 'Visits/history';
$route['visits/planned_actual']     = 'Visits/planned_vs_actual';
$route['visits/checkin']            = 'Visits/checkin';
$route['visits/do_checkin']         = 'Visits/do_checkin';
$route['visits/do_checkout/(:num)'] = 'Visits/do_checkout/$1';
$route['visits/calendar_data']      = 'Visits/calendar_data';
$route['visits/save']               = 'Visits/save';
$route['visits/datatable']          = 'Visits/datatable';
$route['visits/get/(:num)']         = 'Visits/get/$1';
$route['visits/detail/(:num)']      = 'Visits/detail/$1';
$route['visits/status']             = 'Visits/update_status';

// Geofence
$route['geofence']                 = 'Geofence/index';
$route['geofence/datatable']       = 'Geofence/datatable';
$route['geofence/save']            = 'Geofence/save';
$route['geofence/get/(:num)']      = 'Geofence/get/$1';
$route['geofence/status']          = 'Geofence/update_status';
$route['geofence/assignment']      = 'Geofence/assignment';
$route['geofence/save_assignment'] = 'Geofence/save_assignment';
$route['geofence/auto_checkins']   = 'Geofence/auto_checkins';
$route['geofence/checkins_dt']     = 'Geofence/checkins_datatable';
$route['geofence/violations']      = 'Geofence/violations';
$route['geofence/violations_dt']   = 'Geofence/violations_datatable';
$route['geofence/resolve_alert']   = 'Geofence/resolve_alert';
$route['geofence/alert_rules']     = 'Geofence/alert_rules';
$route['geofence/rules_dt']        = 'Geofence/rules_datatable';
$route['geofence/save_rule']       = 'Geofence/save_rule';
$route['geofence/rule_status']     = 'Geofence/update_rule_status';

// Tracking
$route['tracking/live']         = 'Tracking/live';
$route['tracking/live_data']    = 'Tracking/live_data';
$route['tracking/trail/(:num)'] = 'Tracking/trail/$1';
$route['tracking/trail_data']   = 'Tracking/trail_data';
$route['tracking/ping_status']  = 'Tracking/ping_status';
$route['tracking/ping_dt']      = 'Tracking/ping_datatable';

// Attendance
$route['attendance']                    = 'Attendance/index';
$route['attendance/punch']              = 'Attendance/punch';
$route['attendance/do_punch']           = 'Attendance/do_punch';
$route['attendance/monthly']            = 'Attendance/monthly';
$route['attendance/monthly_data']       = 'Attendance/monthly_data';
$route['attendance/datatable']          = 'Attendance/datatable';
$route['attendance/corrections']        = 'Attendance/corrections';
$route['attendance/corrections_dt']     = 'Attendance/corrections_datatable';
$route['attendance/request_correction'] = 'Attendance/request_correction';
$route['attendance/approve_correction'] = 'Attendance/approve_correction';
$route['attendance/status']             = 'Attendance/update_status';

// Shifts
$route['shifts']                = 'Shifts/index';
$route['shifts/datatable']      = 'Shifts/datatable';
$route['shifts/save']           = 'Shifts/save';
$route['shifts/get/(:num)']     = 'Shifts/get/$1';
$route['shifts/status']         = 'Shifts/update_status';
$route['shifts/assignment']     = 'Shifts/assignment';
$route['shifts/save_assignment']= 'Shifts/save_assignment';
$route['shifts/calendar']       = 'Shifts/calendar';
$route['shifts/calendar_data']  = 'Shifts/calendar_data';

// Leave
$route['leave']               = 'Leave/index';
$route['leave/apply']         = 'Leave/apply';
$route['leave/save']          = 'Leave/save';
$route['leave/datatable']     = 'Leave/datatable';
$route['leave/approval']      = 'Leave/approval';
$route['leave/approval_dt']   = 'Leave/approval_datatable';
$route['leave/approve']       = 'Leave/approve';
$route['leave/reject']        = 'Leave/reject';
$route['leave/cancel']        = 'Leave/cancel';
$route['leave/balances']      = 'Leave/balances';
$route['leave/balances_data'] = 'Leave/balances_data';
$route['leave/status']        = 'Leave/update_status';

// Selfie
$route['selfie/log']           = 'Selfie/log';
$route['selfie/log_dt']        = 'Selfie/log_datatable';
$route['selfie/mismatches']    = 'Selfie/mismatches';
$route['selfie/mismatches_dt'] = 'Selfie/mismatches_datatable';
$route['selfie/override']      = 'Selfie/override';
$route['selfie/settings']      = 'Selfie/settings';
$route['selfie/save_settings'] = 'Selfie/save_settings';

// Reports
$route['reports/visits']           = 'Reports/visits';
$route['reports/visits_data']      = 'Reports/visits_data';
$route['reports/lead_conversion']  = 'Reports/lead_conversion';
$route['reports/lead_data']        = 'Reports/lead_data';
$route['reports/orders']           = 'Reports/orders_report';
$route['reports/orders_data']      = 'Reports/orders_data';
$route['reports/staff_sales']      = 'Reports/staff_sales';
$route['reports/staff_data']       = 'Reports/staff_data';
$route['reports/attendance']       = 'Reports/attendance_report';
$route['reports/attendance_data']  = 'Reports/attendance_data';
$route['reports/leave_util']       = 'Reports/leave_utilisation';
$route['reports/leave_data']       = 'Reports/leave_data';
$route['reports/punctuality']      = 'Reports/punctuality';
$route['reports/punctuality_data'] = 'Reports/punctuality_data';
$route['reports/coverage']         = 'Reports/coverage';
$route['reports/coverage_data']    = 'Reports/coverage_data';
$route['reports/export/(:any)']    = 'Reports/export/$1';

// Admin
$route['admin/users']              = 'Admin/users';
$route['admin/users_dt']           = 'Admin/users_datatable';
$route['admin/save_user']          = 'Admin/save_user';
$route['admin/get_user/(:num)']    = 'Admin/fetch_user/$1';
$route['admin/get_team/(:num)']    = 'Admin/fetch_team/$1';
$route['admin/get_product/(:num)'] = 'Admin/fetch_product/$1';
$route['admin/get_category/(:num)']= 'Admin/fetch_category/$1';
$route['admin/get_template/(:num)']= 'Admin/fetch_template/$1';
$route['admin/user_status']        = 'Admin/update_user_status';
$route['admin/teams']              = 'Admin/teams';
$route['admin/teams_dt']           = 'Admin/teams_datatable';
$route['admin/save_team']          = 'Admin/save_team';
$route['admin/team_status']        = 'Admin/update_team_status';
$route['admin/products']           = 'Admin/products';
$route['admin/products_dt']        = 'Admin/products_datatable';
$route['admin/save_product']       = 'Admin/save_product';
$route['admin/product_status']     = 'Admin/update_product_status';
$route['admin/categories_dt']      = 'Admin/categories_datatable';
$route['admin/save_category']      = 'Admin/save_category';
$route['admin/notif_templates']    = 'Admin/notif_templates';
$route['admin/templates_dt']       = 'Admin/templates_datatable';
$route['admin/save_template']      = 'Admin/save_template';
$route['admin/settings']           = 'Admin/settings';
$route['admin/save_settings']      = 'Admin/save_settings';
$route['admin/users_credentials']  = 'Admin/users_credentials';
$route['admin/reset_password']     = 'Admin/reset_password';

// Notifications
$route['notifications']           = 'Notifications/index';
$route['notifications/mark_read'] = 'Notifications/mark_read';
$route['notifications/mark_all']  = 'Notifications/mark_all_read';
$route['notifications/count']     = 'Notifications/unread_count';
$route['notifications/delete']    = 'Notifications/soft_delete';

// API
$route['api/gps_ping']       = 'Api/gps_ping';
$route['api/get_token']      = 'Api/get_token';
$route['api/live_positions'] = 'Api/live_positions';
$route['api/trail']          = 'Api/trail';

// Web GPS ping (session auth — called by browser auto-tracker)
$route['gps/ping']   = 'Gps/ping';
$route['gps/status'] = 'Gps/status';

// Seeder (CLI only)
$route['seeder/(:any)'] = 'Seeder/$1';
