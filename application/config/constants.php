<?php
defined('BASEPATH') OR exit('No direct script access allowed');

define('UPLOAD_PATH',    FCPATH . 'uploads/');
define('SELFIE_PATH',    FCPATH . 'uploads/selfies/');
define('PRODUCT_PATH',   FCPATH . 'uploads/products/');
define('ATTACH_PATH',    FCPATH . 'uploads/attachments/');
define('GPS_LIVE_PATH',  FCPATH . 'writable/gps_live/');

define('ROLES',          ['admin','manager','field_staff']);
define('LEAD_STATUSES',  ['new','contacted','qualified','proposal','negotiation','won','lost']);
define('ORDER_STATUSES', ['draft','pending_approval','approved','dispatched','delivered','cancelled']);
define('VISIT_STATUSES', ['planned','completed','missed','rescheduled']);
define('ATT_STATUSES',   ['present','absent','half_day','on_leave','holiday','week_off']);

define('PAISE',          100); // multiply INR by 100 to get paise
