<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Seeder extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // CLI only
        if (!$this->input->is_cli_request()) show_404();
        $this->load->library('Crm_auth');
        $this->load->helper('crm');
    }

    public function run() {
        $this->_seed_users();
        $this->_seed_teams();
        $this->_seed_shifts();
        $this->_seed_leave_types();
        $this->_seed_holidays();
        $this->_seed_product_categories();
        $this->_seed_products();
        $this->_seed_customers();
        $this->_seed_leads();
        echo "\n✅ Seeding complete!\n";
    }

    private function _seed_users() {
        $now  = date('Y-m-d H:i:s');
        $hash = password_hash('password123', PASSWORD_BCRYPT);
        $users = [
            ['name'=>'Admin User',  'email'=>'admin@fieldcrm.com',   'role'=>'admin',       'phone'=>'9000000001'],
            ['name'=>'Manager One', 'email'=>'manager1@fieldcrm.com','role'=>'manager',     'phone'=>'9000000002'],
            ['name'=>'Manager Two', 'email'=>'manager2@fieldcrm.com','role'=>'manager',     'phone'=>'9000000003'],
            ['name'=>'Staff Alpha', 'email'=>'staff1@fieldcrm.com',  'role'=>'field_staff', 'phone'=>'9000000004'],
            ['name'=>'Staff Beta',  'email'=>'staff2@fieldcrm.com',  'role'=>'field_staff', 'phone'=>'9000000005'],
            ['name'=>'Staff Gamma', 'email'=>'staff3@fieldcrm.com',  'role'=>'field_staff', 'phone'=>'9000000006'],
            ['name'=>'Staff Delta', 'email'=>'staff4@fieldcrm.com',  'role'=>'field_staff', 'phone'=>'9000000007'],
            ['name'=>'Staff Epsilon','email'=>'staff5@fieldcrm.com', 'role'=>'field_staff', 'phone'=>'9000000008'],
            ['name'=>'Staff Zeta',  'email'=>'staff6@fieldcrm.com',  'role'=>'field_staff', 'phone'=>'9000000009'],
        ];
        foreach ($users as $u) {
            $exists = $this->db->where('email',$u['email'])->count_all_results('users');
            if ($exists) continue;
            $this->db->insert('users', array_merge($u, ['password'=>$hash,'status'=>'active','is_deleted'=>0,'created_at'=>$now,'updated_at'=>$now]));
            $uid = $this->db->insert_id();
            if ($u['role'] === 'admin') {
                $perms = ['leads.manage','orders.approve','attendance.correct','reports.view'];
                foreach ($perms as $p) $this->db->insert('user_permissions',['user_id'=>$uid,'permission'=>$p]);
            }
        }
        echo "✅ Users seeded\n";
    }

    private function _seed_teams() {
        $now = date('Y-m-d H:i:s');
        $mgr1 = $this->db->where('email','manager1@fieldcrm.com')->get('users')->row_array();
        $mgr2 = $this->db->where('email','manager2@fieldcrm.com')->get('users')->row_array();
        $teams = [
            ['name'=>'North Team', 'manager_id'=>$mgr1['id']??null,'territory'=>'Delhi NCR'],
            ['name'=>'South Team', 'manager_id'=>$mgr2['id']??null,'territory'=>'Mumbai'],
            ['name'=>'East Team',  'manager_id'=>null,'territory'=>'Kolkata'],
        ];
        foreach ($teams as $t) {
            $this->db->insert('teams', array_merge($t, ['status'=>'active','is_deleted'=>0,'created_at'=>$now,'updated_at'=>$now]));
        }
        echo "✅ Teams seeded\n";
    }

    private function _seed_shifts() {
        $now = date('Y-m-d H:i:s');
        $shifts = [
            ['name'=>'Morning Shift','start_time'=>'09:00:00','end_time'=>'18:00:00','grace_minutes'=>15,'half_day_hours'=>4,'full_day_hours'=>8],
            ['name'=>'Afternoon Shift','start_time'=>'13:00:00','end_time'=>'22:00:00','grace_minutes'=>15,'half_day_hours'=>4,'full_day_hours'=>8],
            ['name'=>'General Shift','start_time'=>'10:00:00','end_time'=>'19:00:00','grace_minutes'=>20,'half_day_hours'=>4,'full_day_hours'=>8],
        ];
        foreach ($shifts as $s) {
            $this->db->insert('shifts', array_merge($s, ['status'=>'active','is_deleted'=>0,'created_at'=>$now,'updated_at'=>$now]));
        }
        echo "✅ Shifts seeded\n";
    }

    private function _seed_leave_types() {
        $now = date('Y-m-d H:i:s');
        $types = [
            ['name'=>'Earned Leave',  'days_allowed_per_year'=>15,'carry_forward'=>1,'paid'=>1],
            ['name'=>'Sick Leave',    'days_allowed_per_year'=>12,'carry_forward'=>0,'paid'=>1],
            ['name'=>'Casual Leave',  'days_allowed_per_year'=>8, 'carry_forward'=>0,'paid'=>1],
        ];
        foreach ($types as $t) {
            $this->db->insert('leave_types', array_merge($t, ['status'=>'active','is_deleted'=>0,'created_at'=>$now,'updated_at'=>$now]));
            $tid = $this->db->insert_id();
            // Seed balances for ALL users (admin/manager/field_staff)
            $users = $this->db->where('is_deleted', 0)->get('users')->result_array();
            foreach ($users as $u) {
                $exists = $this->db->where(['user_id'=>$u['id'],'leave_type_id'=>$tid,'year'=>date('Y')])->count_all_results('leave_balances');
                if (!$exists) {
                    $this->db->insert('leave_balances',['user_id'=>$u['id'],'leave_type_id'=>$tid,'year'=>date('Y'),'total_days'=>$t['days_allowed_per_year'],'used_days'=>0,'pending_days'=>0,'status'=>'active','is_deleted'=>0,'created_at'=>$now,'updated_at'=>$now]);
                }
            }
        }
        echo "✅ Leave types seeded\n";
    }

    private function _seed_holidays() {
        $now = date('Y-m-d H:i:s'); $year = date('Y');
        $holidays = [
            ['name'=>'Republic Day',     'date'=>$year.'-01-26','holiday_type'=>'national'],
            ['name'=>'Holi',             'date'=>$year.'-03-25','holiday_type'=>'national'],
            ['name'=>'Good Friday',      'date'=>$year.'-04-18','holiday_type'=>'national'],
            ['name'=>'Eid ul-Fitr',      'date'=>$year.'-04-20','holiday_type'=>'national'],
            ['name'=>'Independence Day', 'date'=>$year.'-08-15','holiday_type'=>'national'],
            ['name'=>'Gandhi Jayanti',   'date'=>$year.'-10-02','holiday_type'=>'national'],
            ['name'=>'Dussehra',         'date'=>$year.'-10-02','holiday_type'=>'national'],
            ['name'=>'Diwali',           'date'=>$year.'-10-20','holiday_type'=>'national'],
            ['name'=>'Christmas',        'date'=>$year.'-12-25','holiday_type'=>'national'],
            ['name'=>'New Year',         'date'=>$year.'-12-31','holiday_type'=>'national'],
        ];
        foreach ($holidays as $h) {
            $this->db->insert('holidays', array_merge($h, ['status'=>'active','is_deleted'=>0,'created_at'=>$now,'updated_at'=>$now]));
        }
        echo "✅ Holidays seeded\n";
    }

    private function _seed_product_categories() {
        $now = date('Y-m-d H:i:s');
        $roots = ['Electronics','FMCG','Pharmaceuticals','Industrial','Textiles'];
        foreach ($roots as $r) {
            $this->db->insert('product_categories',['name'=>$r,'parent_id'=>null,'status'=>'active','is_deleted'=>0,'created_at'=>$now,'updated_at'=>$now]);
            $pid = $this->db->insert_id();
            $subs = [$r.' - Type A',$r.' - Type B'];
            foreach ($subs as $s) $this->db->insert('product_categories',['name'=>$s,'parent_id'=>$pid,'status'=>'active','is_deleted'=>0,'created_at'=>$now,'updated_at'=>$now]);
        }
        echo "✅ Product categories seeded\n";
    }

    private function _seed_products() {
        $now = date('Y-m-d H:i:s');
        $products = [];
        for ($i=1; $i<=20; $i++) {
            $products[] = ['name'=>'Product '.$i,'sku'=>'SKU-'.str_pad($i,4,'0',STR_PAD_LEFT),'unit'=>'pcs','price'=>rand(10000,500000),'min_price'=>rand(5000,9000),'stock'=>rand(0,500),'status'=>$i<=18?'active':'inactive'];
        }
        foreach ($products as $p) {
            $this->db->insert('products', array_merge($p, ['is_deleted'=>0,'created_at'=>$now,'updated_at'=>$now]));
        }
        echo "✅ Products seeded\n";
    }

    private function _seed_customers() {
        $now   = date('Y-m-d H:i:s');
        $staff = $this->db->where(['role'=>'field_staff','is_deleted'=>0])->get('users')->result_array();
        $cities = ['Mumbai','Delhi','Pune','Bengaluru','Chennai','Hyderabad','Kolkata','Ahmedabad','Jaipur','Lucknow'];
        for ($i=1; $i<=30; $i++) {
            $status = $i <= 25 ? 'active' : ($i <= 28 ? 'inactive' : 'deleted');
            $assigned = $staff ? $staff[array_rand($staff)]['id'] : null;
            $this->db->insert('customers', [
                'name'=>'Customer '.$i,'phone'=>'98765'.str_pad($i,5,'0',STR_PAD_LEFT),
                'email'=>'customer'.$i.'@example.com','city'=>$cities[array_rand($cities)],
                'state'=>'Maharashtra','assigned_to'=>$assigned,
                'latitude'=>round(19.0 + (rand(0,100)/100), 6),'longitude'=>round(72.8 + (rand(0,100)/100), 6),
                'status'=>$status,'is_deleted'=>$status==='deleted'?1:0,
                'deleted_at'=>$status==='deleted'?$now:null,'created_at'=>$now,'updated_at'=>$now,
            ]);
        }
        echo "✅ Customers seeded\n";
    }

    private function _seed_leads() {
        $now       = date('Y-m-d H:i:s');
        $customers = $this->db->where(['is_deleted'=>0,'status'=>'active'])->get('customers')->result_array();
        $staff     = $this->db->where(['role'=>'field_staff','is_deleted'=>0])->get('users')->result_array();
        $stages    = ['new','contacted','qualified','proposal','negotiation','won','lost'];
        $sources   = ['field','call','referral','online','walk_in'];
        for ($i=1; $i<=50; $i++) {
            if (!$customers || !$staff) break;
            $cust    = $customers[array_rand($customers)];
            $assignee= $staff[array_rand($staff)];
            $this->db->insert('leads', [
                'customer_id'=>$cust['id'],'title'=>'Lead for '.$cust['name'].' #'.$i,
                'source'=>$sources[array_rand($sources)],'lead_status'=>$stages[array_rand($stages)],
                'assigned_to'=>$assignee['id'],'expected_value'=>rand(10000,1000000)*100,
                'status'=>'active','is_deleted'=>0,'created_at'=>$now,'updated_at'=>$now,
            ]);
        }
        echo "✅ Leads seeded\n";
    }
}
