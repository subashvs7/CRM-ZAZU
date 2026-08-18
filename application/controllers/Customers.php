<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customers extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(['Customer_model','Contact_person_model','User_model','Product_model']);
    }

    public function index($type = '') {
        $staff = $this->is_admin() ? $this->User_model->get_staff_list('field_staff') : [];
        $products = $this->Product_model->get_active_with_category();
        $title = 'Customers';
        if ($type === 'primary') $title = 'Primary Customers';
        elseif ($type === 'followup') $title = 'Follow-ups Customers';
        
        $this->load_view('customers/index', ['page_title'=>$title,'page_js'=>'customers','staff'=>$staff,'products'=>$products,'sf'=>'','customer_type'=>$type]);
    }

    private function _format_logo_url($path) {
        if (empty($path)) return '';
        if (preg_match('/^https?:\/\/localhost[^\/]*\/[^\/]+\/(.+)$/i', $path, $m)) {
            $path = $m[1];
        }
        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }
        return base_url(ltrim($path, '/'));
    }

    public function datatable() {
        $params = $this->input->get();
        $sf     = $this->input->get('status_filter');
        [$rows, $total] = $this->Customer_model->datatable($params, $sf, $this->get_user_id(), $this->get_role());
        
        $products = $this->Product_model->get_active_with_category();
        $prod_map = [];
        foreach ($products as $p) {
            $prod_map[$p['id']] = $p['name'];
        }

        $this->load->model('Product_package_split_model');
        $all_splits = $this->db->select('pps.id, pps.product_id, pt.name AS tier_name, pt.badge_color, pt.icon, pps.monthly_price')
                               ->from('product_package_splits pps')
                               ->join('package_tiers pt', 'pt.id = pps.package_tier_id')
                               ->where('pps.is_deleted', 0)
                               ->get()->result_array();
        $split_map = [];
        foreach ($all_splits as $sp) {
            $split_map[$sp['id']] = $sp;
        }

        $data = [];
        foreach ($rows as $r) {
            $combined_name = $r['customer_name'] ? esc_html($r['customer_name'] . ' (' . $r['customer_org_name'] . ')') : esc_html($r['customer_org_name']);
            $actions = crm_action_btns($r['id'], 'customers', $r['status'], ['view'=>true,'edit'=>true]);
            if ($r['status'] !== 'deleted') {
                $actions = str_replace('</div>', '<button class="inline-flex items-center justify-center w-7 h-7 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-colors btn-plan-visit" data-id="'.$r['id'].'" data-name="'.$combined_name.'" title="Plan Visit"><i class="fa fa-calendar-plus-o" style="font-size:11px"></i></button></div>', $actions);
            }
            // Format contact: phone & email
            $phone = esc_html($r['phone']);
            $email = !empty($r['email']) ? esc_html($r['email']) : '-';
            $contact = '<div class="font-medium text-gray-900">' . $phone . '</div>';
            if ($email !== '-') {
                $contact .= '<div class="text-xs text-gray-500">' . $email . '</div>';
            } else {
                $contact .= '<div class="text-xs text-gray-400">-</div>';
            }

            // Format location: city & state
            $city = !empty($r['city']) ? esc_html($r['city']) : '-';
            $state = !empty($r['state']) ? esc_html($r['state']) : '';
            $location = '<div class="font-medium text-gray-900">' . $city . '</div>';
            if ($state !== '') {
                $location .= '<div class="text-xs text-gray-500">' . $state . '</div>';
            }

            // Format products as small badges with interactive popup trigger
            $prod_badges = [];
            if (!empty($r['product_ids'])) {
                $pids = explode(',', $r['product_ids']);
                foreach ($pids as $pid) {
                    if (isset($prod_map[$pid])) {
                        $prod_badges[] = '<button type="button" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all shadow-xs cursor-pointer btn-view-product-popup" data-product-id="'.$pid.'" data-customer-id="'.$r['id'].'" title="Click to view product details & package splits"><i class="fa fa-cube text-[10px]"></i> ' . esc_html($prod_map[$pid]) . '</button>';
                    }
                }
            }
            if (!empty($r['package_split_ids'])) {
                $sids = explode(',', $r['package_split_ids']);
                foreach ($sids as $sid) {
                    if (isset($split_map[$sid])) {
                        $sp = $split_map[$sid];
                        $badge_c = $sp['badge_color'] ?: 'indigo';
                        $prod_badges[] = '<button type="button" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-bold bg-'.$badge_c.'-50 text-'.$badge_c.'-700 border border-'.$badge_c.'-200 hover:bg-'.$badge_c.'-600 hover:text-white transition-all shadow-xs cursor-pointer btn-view-product-popup" data-product-id="'.$sp['product_id'].'" data-customer-id="'.$r['id'].'" title="Click to view package split details"><i class="fa '.($sp['icon']?:'fa-tag').' text-[10px]"></i> ' . esc_html($sp['tier_name']) . '</button>';
                    }
                }
            }
            $products_html = !empty($prod_badges) ? '<div class="flex flex-wrap gap-1.5 items-center">' . implode('', $prod_badges) . '</div>' : '<span class="text-gray-400">-</span>';

            // Format notes: truncate and set tooltip if > 50 chars
            $notes = !empty($r['notes']) ? trim($r['notes']) : '';
            $notes_html = '<span class="text-gray-400">-</span>';
            if ($notes !== '') {
                if (mb_strlen($notes) > 50) {
                    $truncated = esc_html(mb_substr($notes, 0, 50));
                    $full = esc_html($notes);
                    $notes_html = '<span class="cursor-pointer text-blue-600 hover:text-blue-800 hover:underline btn-view-notes" data-notes="' . htmlspecialchars($full, ENT_QUOTES, 'UTF-8') . '">' . $truncated . '... <span class="text-[10px] text-blue-500 font-semibold ml-1">[read more]</span></span>';
                } else {
                    $notes_html = esc_html($notes);
                }
            }

            $data[] = [
                $r['id'],
                esc_html($r['customer_name'] ?? '-'),
                esc_html($r['customer_org_name'] ?? '-'),
                $contact,
                $location,
                esc_html($r['assigned_name'] ?? '-'),
                $products_html,
                $notes_html,
                status_badge($r['status']),
                date('d M Y', strtotime($r['created_at'])),
                $actions
            ];
        }
        $this->json_list($data, $total, $total);
    }

    public function save() {
        $id                = (int) $this->input->post('id');
        $customer_name     = trim($this->input->post('customer_name'));
        $customer_org_name = trim($this->input->post('customer_org_name'));
        $phone             = trim($this->input->post('phone'));
        $errors = [];
        if (!$customer_name)      $errors['customer_name']     = 'Customer Name is required.';
        if (!$customer_org_name)  $errors['customer_org_name'] = 'Company Name is required.';
        if (!$phone)              $errors['phone']             = 'Phone is required.';
        if ($errors) $this->json_error('Validation failed.', 400, $errors);

        $product_ids = $this->input->post('product_ids');
        if (is_array($product_ids)) {
            $product_ids_str = !empty($product_ids) ? implode(',', array_filter(array_map('intval', $product_ids))) : null;
        } else {
            $product_ids_str = (!empty($product_ids) && (int)$product_ids > 0) ? (string) (int)$product_ids : null;
        }

        $package_split_ids = $this->input->post('package_split_ids');
        if (is_array($package_split_ids)) {
            $package_split_ids_str = !empty($package_split_ids) ? implode(',', array_filter(array_map('intval', $package_split_ids))) : null;
        } else {
            $package_split_ids_str = (!empty($package_split_ids) && (int)$package_split_ids > 0) ? (string) (int)$package_split_ids : null;
        }

        $data = [
            'customer_name'     => $customer_name,
            'customer_org_name' => $customer_org_name,
            'phone'             => $phone,
            'email'             => $this->input->post('email'),
            'address'           => $this->input->post('address'),
            'city'              => $this->input->post('city'),
            'state'             => $this->input->post('state'),
            'pincode'           => $this->input->post('pincode'),
            'gst_number'        => $this->input->post('gst_number'),
            'notes'             => $this->input->post('notes'),
            'product_ids'       => $product_ids_str,
            'package_split_ids' => $package_split_ids_str,
            'latitude'          => $this->input->post('latitude') ?: null,
            'longitude'         => $this->input->post('longitude') ?: null,
        ];
        if ($this->is_manager()) {
            $data['assigned_to'] = (int)$this->input->post('assigned_to') ?: null;
        } else if (!$id) {
            $data['assigned_to'] = $this->get_user_id();
        }
        if ($id) { $this->Customer_model->update($id, $data); $this->json_success([], 'Customer updated.'); }
        else     { $new = $this->Customer_model->insert($data); $this->json_success(['id'=>$new], 'Customer created.'); }
    }

    public function get_splits_by_products_ajax() {
        $product_ids = $this->input->get_post('product_ids');
        if (empty($product_ids)) {
            $this->json_success([]);
        }
        $this->load->model('Product_package_split_model');
        $splits = $this->Product_package_split_model->get_splits_by_product_ids($product_ids);
        
        $formatted = [];
        foreach ($splits as $s) {
            $price_txt = $s['monthly_price'] > 0 ? ' (₹' . number_format($s['monthly_price'] / 100) . '/mo)' : '';
            $formatted[] = [
                'id'           => $s['id'],
                'product_id'   => $s['product_id'],
                'product_name' => $s['product_name'],
                'product_sku'  => $s['product_sku'],
                'tier_name'    => $s['tier_name'],
                'tier_subtitle'=> $s['tier_subtitle'] ?? '',
                'display_name' => $s['product_name'] . ' - ' . $s['tier_name'] . $price_txt,
                'monthly_price'=> '₹' . number_format($s['monthly_price'] / 100, 2),
                'yearly_price' => '₹' . number_format($s['yearly_price'] / 100, 2),
                'badge_color'  => $s['badge_color'] ?: 'indigo',
                'icon'         => $s['icon'] ?: 'fa-tag'
            ];
        }
        $this->json_success($formatted);
    }

    public function get($id) {
        $c = $this->Customer_model->get_with_staff($id);
        if (!$c) $this->json_error('Not found.', 404);
        $this->json_success($c);
    }

    public function get_details($id) {
        $customer = $this->Customer_model->get_with_staff($id);
        if (!$customer) $this->json_error('Not found.', 404);
        
        $contacts = $this->Contact_person_model->get_by_customer($id);
        
        // Resolve products
        $this->load->model('Product_model');
        $products = $this->Product_model->get_active_with_category();
        $prod_map = [];
        foreach ($products as $p) {
            $prod_map[$p['id']] = $p['name'];
        }
        $resolved_products = [];
        $products_data = [];
        if (!empty($customer['product_ids'])) {
            $pids = explode(',', $customer['product_ids']);
            foreach ($pids as $pid) {
                if (isset($prod_map[$pid])) {
                    $resolved_products[] = $prod_map[$pid];
                    $products_data[] = ['id' => $pid, 'name' => $prod_map[$pid]];
                }
            }
        }
        $customer['products'] = $resolved_products;
        $customer['products_data'] = $products_data;

        // Resolve package tier splits
        $this->load->model('Product_package_split_model');
        $resolved_splits = [];
        $splits_data = [];
        if (!empty($customer['package_split_ids'])) {
            $splits = $this->Product_package_split_model->get_splits_by_product_ids($customer['product_ids']);
            $sids = explode(',', $customer['package_split_ids']);
            foreach ($splits as $sp) {
                if (in_array($sp['id'], $sids)) {
                    $resolved_splits[] = $sp['product_name'] . ' — ' . $sp['tier_name'] . ' (₹' . number_format($sp['monthly_price']/100, 2) . '/mo)';
                    $splits_data[] = [
                        'id' => $sp['id'],
                        'product_id' => $sp['product_id'],
                        'tier_name' => $sp['tier_name'],
                        'badge_color' => $sp['badge_color'] ?: 'indigo',
                        'icon' => $sp['icon'] ?: 'fa-tag'
                    ];
                }
            }
        }
        $customer['package_splits'] = $resolved_splits;
        $customer['splits_data'] = $splits_data;
        
        // Fetch Visit Plans
        $visits = $this->db->select('vp.id, vp.planned_date, vp.planned_time, vp.visit_status, vp.purpose, u.name AS user_name')
            ->from('visit_plans vp')
            ->join('users u', 'u.id = vp.user_id', 'left')
            ->where(['vp.customer_id' => $id, 'vp.is_deleted' => 0])
            ->order_by('vp.planned_date', 'desc')
            ->get()->result_array();
            
        // Fetch Visit Logs
        $logs = $this->db->select('vl.id, vl.check_in_at, vl.check_out_at, u.name AS user_name, vl.visit_outcome, vl.notes')
            ->from('visit_logs vl')
            ->join('users u', 'u.id = vl.user_id', 'left')
            ->where(['vl.customer_id' => $id, 'vl.is_deleted' => 0])
            ->order_by('vl.check_in_at', 'desc')
            ->get()->result_array();
            
        // Fetch Contact Book entries linked to this customer
        $cb_contacts = $this->db->select('id, name, phone, email, job_title, company_name')
            ->from('contact_book')
            ->where(['customer_id' => $id, 'is_deleted' => 0])
            ->order_by('name', 'asc')
            ->get()->result_array();
            
        $this->json_success([
            'customer'    => $customer,
            'contacts'    => $contacts,
            'cb_contacts' => $cb_contacts,
            'visit_plans' => $visits,
            'visit_logs'  => $logs
        ]);
    }

    public function get_product_splits_modal_data() {
        $product_id  = (int) $this->input->get_post('product_id');
        $split_id    = (int) $this->input->get_post('split_id');
        $customer_id = (int) $this->input->get_post('customer_id');

        if (!$product_id) {
            $this->json_error('Product ID is required.', 400);
        }

        $this->load->model(['Product_model', 'Product_package_split_model', 'Product_core_module_model']);
        $product = $this->Product_model->get_with_category($product_id);
        if (!$product) {
            $this->json_error('Product not found.', 404);
        }

        $raw_logo = !empty($product['logo']) ? $product['logo'] : (!empty($product['image']) ? $product['image'] : '');
        $product['logo_url'] = $this->_format_logo_url($raw_logo);
        $product['price_formatted'] = '₹' . number_format($product['price'] / 100, 2);

        // Get customer's selected splits if customer_id is provided
        $customer_split_ids = [];
        $customer_info = null;
        if ($customer_id > 0) {
            $cust = $this->Customer_model->get_by_id($customer_id);
            if ($cust) {
                $customer_info = [
                    'id' => $cust['id'],
                    'name' => $cust['customer_name'] ?: $cust['customer_org_name'],
                    'org_name' => $cust['customer_org_name']
                ];
                if (!empty($cust['package_split_ids'])) {
                    $customer_split_ids = array_map('intval', explode(',', $cust['package_split_ids']));
                }
            }
        }

        // Splits / Tiers for this product
        $all_splits = $this->Product_package_split_model->get_splits_by_product($product_id);
        
        // Filter to specific requested split_id or customer's assigned split if specified
        $splits = [];
        foreach ($all_splits as $s) {
            $is_sel = in_array((int)$s['id'], $customer_split_ids);
            $s['is_customer_selected'] = $is_sel;
            $s['monthly_price_formatted'] = !empty($s['monthly_price']) ? '₹' . number_format($s['monthly_price'] / 100, 0) : '₹' . number_format($s['price'] / 1200, 0);
            $s['yearly_price_formatted'] = !empty($s['yearly_price']) ? '₹' . number_format($s['yearly_price'] / 100, 0) : '₹' . number_format($s['price'] / 100, 0);
            $s['implementation_fee_formatted'] = !empty($s['implementation_fee']) ? '₹' . number_format($s['implementation_fee'] / 100, 0) : '₹0';
            $s['features_array'] = !empty($s['features_included']) ? array_filter(array_map('trim', explode("\n", $s['features_included']))) : [];
            
            if ($split_id > 0) {
                if ((int)$s['id'] === $split_id) {
                    $splits[] = $s;
                }
            } elseif (!empty($customer_split_ids)) {
                if ($is_sel) {
                    $splits[] = $s;
                }
            } else {
                $splits[] = $s;
            }
        }

        // If split_id was specified or customer has assigned split, only that card is returned
        // If none matched filter but all_splits exist, fallback to the first split or all_splits
        if (empty($splits) && !empty($all_splits)) {
            $first = $all_splits[0];
            $first['is_customer_selected'] = false;
            $first['monthly_price_formatted'] = !empty($first['monthly_price']) ? '₹' . number_format($first['monthly_price'] / 100, 0) : '₹' . number_format($first['price'] / 1200, 0);
            $first['yearly_price_formatted'] = !empty($first['yearly_price']) ? '₹' . number_format($first['yearly_price'] / 100, 0) : '₹' . number_format($first['price'] / 100, 0);
            $first['implementation_fee_formatted'] = !empty($first['implementation_fee']) ? '₹' . number_format($first['implementation_fee'] / 100, 0) : '₹0';
            $first['features_array'] = !empty($first['features_included']) ? array_filter(array_map('trim', explode("\n", $first['features_included']))) : [];
            $splits = [$first];
        }

        // Core modules if available
        $core_modules = $this->Product_core_module_model->get_by_product($product_id);

        $this->json_success([
            'product'       => $product,
            'splits'        => $splits,
            'core_modules'  => $core_modules,
            'customer_info' => $customer_info
        ]);
    }

    public function detail($id) {
        $customer = $this->Customer_model->get_with_staff($id);
        if (!$customer) show_404();
        $contacts = $this->Contact_person_model->get_by_customer($id);
        $this->load_view('customers/_detail', ['page_title'=>$customer['name'],'page_js'=>'customers','customer'=>$customer,'contacts'=>$contacts]);
    }

    public function update_status() {
        $id = (int)$this->input->post('id'); $action = $this->input->post('action');
        switch($action){case'activate':$this->Customer_model->activate($id);break;case'deactivate':$this->Customer_model->deactivate($id);break;case'delete':$this->Customer_model->soft_delete($id);break;case'restore':$this->Customer_model->restore($id);break;default:$this->json_error('Invalid.');}
        $this->json_success([],'Status updated.');
    }

    public function save_contact() {
        $id  = (int)$this->input->post('id');
        $cid = (int)$this->input->post('customer_id');
        if (!$cid) $this->json_error('Customer ID required.');
        $data = ['customer_id'=>$cid,'name'=>$this->input->post('name'),'designation'=>$this->input->post('designation'),'phone'=>$this->input->post('phone'),'email'=>$this->input->post('email'),'is_primary'=>(int)$this->input->post('is_primary')];
        if (!$data['name']) $this->json_error('Validation failed.',400,['name'=>'Name required.']);
        if ($id) { $this->Contact_person_model->update($id,$data); $this->json_success([],'Contact updated.'); }
        else     { $this->Contact_person_model->insert($data); $this->json_success([],'Contact added.'); }
    }

    public function get_contacts($customer_id) {
        $contacts = $this->Contact_person_model->get_by_customer($customer_id);
        $this->json_success($contacts);
    }

    public function contact_status() {
        $id=(int)$this->input->post('id'); $action=$this->input->post('action');
        switch($action){case'activate':$this->Contact_person_model->activate($id);break;case'delete':$this->Contact_person_model->soft_delete($id);break;}
        $this->json_success([],'Done.');
    }

    public function map_data() {
        $data = $this->Customer_model->get_map_data($this->get_user_id(), $this->get_role());
        $this->json_success($data);
    }
}
