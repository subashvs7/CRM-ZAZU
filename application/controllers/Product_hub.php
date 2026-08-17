<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_hub extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model('Product_model');
        $this->load->model('Product_asset_model');
    }

    public function index() {
        redirect('product_hub/assets');
    }

    public function assets() {
        $products = $this->Product_model->get_active_with_category();
        $this->load_view('product_hub/assets', [
            'page_title' => 'Assets & Demos',
            'page_js' => 'product_hub',
            'products' => $products
        ]);
    }

    public function datatable() {
        $params = $this->input->get();
        [$rows, $total] = $this->Product_asset_model->datatable($params);
        $data = [];
        foreach ($rows as $r) {
            $actions = '<a href="'.base_url('product_hub/asset_details/'.$r['id']).'" class="text-blue-600 hover:text-blue-800"><i class="fa fa-info-circle"></i> Details</a>';
            
            $file_link = esc_html($r['file_link']);
            if (filter_var($file_link, FILTER_VALIDATE_URL)) {
                $link_html = '<a href="'.$file_link.'" target="_blank" class="text-blue-600 hover:underline"><i class="fa fa-external-link"></i> View</a>';
            } else {
                $link_html = '<a href="'.base_url($file_link).'" target="_blank" class="text-blue-600 hover:underline"><i class="fa fa-download"></i> Download</a>';
            }

            $raw_url = filter_var($file_link, FILTER_VALIDATE_URL) ? $file_link : base_url($file_link);
            
            $data[] = [
                $r['id'],
                esc_html($r['title']),
                esc_html($r['asset_type']),
                esc_html($r['product_name']),
                $link_html,
                date('d M Y', strtotime($r['created_at'])),
                $actions,
                $raw_url
            ];
        }
        $this->json_list($data, $total, $total);
    }

    public function upload() {
        redirect('product_hub/assets');
    }

    public function process_upload() {
        $id = (int)$this->input->post('id');
        $product_id = (int)$this->input->post('product_id');
        $asset_type = $this->input->post('asset_type');
        $title = trim($this->input->post('title'));
        $description = trim($this->input->post('description'));
        $link_url = trim($this->input->post('link_url'));

        if (!$title || !$asset_type) {
            if ($this->input->is_ajax_request()) $this->json_error('Title and Asset Type are required.', 400);
            $this->session->set_flashdata('error', 'Title and Asset Type are required.');
            redirect('product_hub/upload');
        }

        $file_link = '';
        $update_file = false;

        if (!empty($_FILES['asset_file']['name'])) {
            $config['upload_path']   = FCPATH . 'uploads/assets/';
            $config['allowed_types'] = '*';
            $config['max_size']      = 50000; // 50MB max
            $config['encrypt_name']  = TRUE;

            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0777, TRUE);
            }

            $this->load->library('upload');
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('asset_file')) {
                if ($this->input->is_ajax_request()) $this->json_error($this->upload->display_errors('',''), 400);
                $this->session->set_flashdata('error', $this->upload->display_errors('',''));
                redirect('product_hub/upload');
            } else {
                $upload_data = $this->upload->data();
                $file_link = 'uploads/assets/' . $upload_data['file_name'];
                $update_file = true;
            }
        } else if ($link_url) {
            $file_link = $link_url;
            $update_file = true;
        } else if (!$id) {
            if ($this->input->is_ajax_request()) $this->json_error('You must provide either a file or a link.', 400);
            $this->session->set_flashdata('error', 'You must provide either a file or a link.');
            redirect('product_hub/upload');
        }

        $data = [
            'product_id'  => $product_id ?: null,
            'asset_type'  => $asset_type,
            'title'       => $title,
            'description' => $description,
            'updated_at'  => date('Y-m-d H:i:s')
        ];

        if ($update_file) {
            $data['file_link'] = $file_link;
        }

        if ($id) {
            $this->Product_asset_model->update($id, $data);
            if ($this->input->is_ajax_request()) $this->json_success([], 'Asset updated successfully.');
        } else {
            $data['created_by'] = $this->get_user_id();
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->Product_asset_model->insert($data);
            if ($this->input->is_ajax_request()) $this->json_success([], 'Asset uploaded successfully.');
        }
        
        $this->session->set_flashdata('success', 'Asset saved successfully.');
        redirect('product_hub/assets');
    }

    public function get_asset($id) {
        $asset = $this->Product_asset_model->get_by_id($id);
        if (!$asset) $this->json_error('Asset not found.', 404);
        $this->json_success($asset);
    }

    public function delete_asset() {
        $id = (int)$this->input->post('id');
        $this->Product_asset_model->soft_delete($id);
        $this->json_success([], 'Asset deleted.');
    }

    public function get_asset_counts() {
        $this->json_success($this->Product_asset_model->get_counts());
    }

    public function asset_details($id) {
        $asset = $this->Product_asset_model->get_with_product($id);
        if (!$asset) show_404();

        $this->load_view('product_hub/asset_details', [
            'page_title' => 'Asset Details',
            'asset' => $asset
        ]);
    }
    public function products() {
        $this->load->model('Product_category_model');
        $this->load->model('Package_tier_model');
        $categories = $this->Product_category_model->get_active();
        $package_tiers = $this->Package_tier_model->get_all_active();
        $all_products = $this->Product_model->get_active_with_category();
        
        $this->load_view('product_hub/products', [
            'page_title' => 'Products & Package Splits Catalogue',
            'page_js' => 'product_hub',
            'categories' => $categories,
            'package_tiers' => $package_tiers,
            'all_products' => $all_products
        ]);
    }

    public function products_dt() {
        $params = $this->input->get();
        [$rows, $total] = $this->Product_model->datatable($params);
        $data = [];
        foreach ($rows as $r) {
            $logo_html = '';
            if (!empty($r['logo'])) {
                $logo_html = '<img src="'.base_url($r['logo']).'" class="w-12 h-12 rounded-xl object-contain bg-white border border-gray-200 p-1 shadow-sm flex-shrink-0" alt="Product Image">';
            } elseif (!empty($r['image'])) {
                $logo_html = '<img src="'.base_url($r['image']).'" class="w-12 h-12 rounded-xl object-contain bg-white border border-gray-200 p-1 shadow-sm flex-shrink-0" alt="Product Image">';
            } else {
                $logo_html = '<div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-base border border-blue-100 shadow-sm flex-shrink-0"><i class="fa fa-cube"></i></div>';
            }

            $name_cell = '<div class="flex items-center gap-3.5">' . $logo_html . '<div class="min-w-0"><div class="font-extrabold text-gray-900 text-sm truncate">' . esc_html($r['name']) . '</div>' . (!empty($r['subtitle']) ? '<div class="text-xs text-gray-400 font-normal truncate max-w-sm mt-0.5">'.esc_html($r['subtitle']).'</div>' : '') . '</div></div>';

            $acts = '<div class="flex items-center gap-1.5">';
            $acts .= '<button class="bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 px-3 py-1 rounded-lg text-xs font-bold transition-colors btn-manage-splits flex items-center gap-1.5" data-id="'.$r['id'].'" data-name="'.esc_html($r['name']).'" data-sku="'.esc_html($r['sku']).'" title="Configure Package Tiers & Splits"><i class="fa fa-tags text-[11px]"></i> Splits</button>';
            $acts .= '<button class="bg-blue-100 text-blue-700 hover:bg-blue-200 px-2.5 py-1 rounded-lg transition-colors btn-edit-product" data-id="'.$r['id'].'" title="Edit Product"><i class="fa fa-pencil"></i></button>';
            $acts .= '<button class="bg-red-100 text-red-700 hover:bg-red-200 px-2.5 py-1 rounded-lg transition-colors btn-product-status" data-id="'.$r['id'].'" data-action="delete" title="Delete Product"><i class="fa fa-trash"></i></button>';
            $acts .= '</div>';
            
            $data[] = [
                $r['id'],
                esc_html($r['sku']),
                $name_cell,
                esc_html($r['category_name'] ?? '-'),
                '₹' . number_format($r['price'] / 100, 2),
                '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Available</span>',
                $acts
            ];
        }
        $this->json_list($data, $total, $total);
    }

    public function save_product() {
        $id          = (int) $this->input->post('id');
        $name        = trim($this->input->post('name'));
        $sku         = trim($this->input->post('sku'));
        $subtitle    = trim($this->input->post('subtitle'));
        $theme_color = trim($this->input->post('theme_color')) ?: 'blue';
        $website_url = trim($this->input->post('website_url')) ?: 'www.zazutech.in';
        $why_points  = trim($this->input->post('why_points'));

        if (!$name) $this->json_error('Validation failed.', 400, ['name'=>'Name required.']);
        if (!$sku)  $this->json_error('Validation failed.', 400, ['sku'=>'SKU required.']);

        $price     = inr_to_paise((float)$this->input->post('price'));
        $min_price = inr_to_paise((float)$this->input->post('min_price'));
        
        $data = [
            'name'        => $name,
            'sku'         => $sku,
            'subtitle'    => $subtitle,
            'description' => $this->input->post('description'),
            'unit'        => $this->input->post('unit') ?: 'pcs',
            'price'       => $price,
            'min_price'   => $min_price,
            'stock'       => (int)$this->input->post('stock'),
            'category_id' => (int)$this->input->post('category_id') ?: null,
            'theme_color' => $theme_color,
            'website_url' => $website_url,
            'why_points'  => $why_points
        ];

        // Handle Product Logo Upload
        if (!empty($_FILES['logo']['name'])) {
            $upload_path = FCPATH . 'uploads/products/';
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            $config['upload_path']   = $upload_path;
            $config['allowed_types'] = 'jpg|jpeg|png|webp|svg';
            $config['max_size']      = 10240; // 10MB
            $config['encrypt_name']  = TRUE;

            $this->load->library('upload');
            $this->upload->initialize($config);

            if ($this->upload->do_upload('logo')) {
                $upload_data = $this->upload->data();
                $data['logo']  = 'uploads/products/' . $upload_data['file_name'];
                $data['image'] = $data['logo'];
            }
        }

        if ($id) {
            $this->Product_model->update($id, $data);
            $this->json_success([], 'Product updated.');
        } else {
            $new = $this->Product_model->insert($data);
            $this->json_success(['id'=>$new], 'Product created.');
        }
    }

    public function get_product($id) {
        $p = $this->Product_model->get_by_id($id);
        if (!$p) $this->json_error('Product not found.', 404);
        $p['price'] = $p['price'] / 100;
        $p['min_price'] = $p['min_price'] / 100;
        $p['logo_url'] = !empty($p['logo']) ? base_url($p['logo']) : (!empty($p['image']) ? base_url($p['image']) : '');
        $this->json_success($p);
    }

    public function product_status() {
        $id = (int)$this->input->post('id');
        $action = $this->input->post('action');
        if ($action === 'delete') {
            $this->Product_model->soft_delete($id);
            $this->json_success([], 'Product deleted.');
        }
        $this->json_error('Invalid action.', 400);
    }

    public function get_active_products_ajax() {
        $products = $this->Product_model->get_active_with_category();
        $list = [];
        foreach ($products as $p) {
            $logo_url = !empty($p['logo']) ? base_url($p['logo']) : (!empty($p['image']) ? base_url($p['image']) : '');
            $list[] = [
                'id'       => $p['id'],
                'name'     => $p['name'],
                'sku'      => $p['sku'],
                'subtitle' => $p['subtitle'] ?? '',
                'logo_url' => $logo_url
            ];
        }
        $this->json_success($list);
    }

    // ========================================================
    // MASTER: PACKAGE TIERS (Bronze, Silver, Gold, Platinum, etc.)
    // ========================================================

    public function package_tiers() {
        $this->load_view('product_hub/package_tiers', [
            'page_title' => 'Master Package Tiers',
            'page_js' => 'product_hub'
        ]);
    }

    public function package_tiers_dt() {
        $this->load->model('Package_tier_model');
        $params = $this->input->get();
        [$rows, $total] = $this->Package_tier_model->datatable($params);
        $data = [];
        foreach ($rows as $r) {
            $color = $r['badge_color'] ?: 'gray';
            $badge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-'.$color.'-50 text-'.$color.'-700 border border-'.$color.'-200"><i class="fa '.($r['icon']?:'fa-cube').'"></i> '.esc_html($r['name']).'</span>';
            
            $status_badge = $r['status'] === 'active' 
                ? '<span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>'
                : '<span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>';

            $acts = '<div class="flex items-center gap-1">';
            $acts .= '<button class="bg-blue-100 text-blue-700 hover:bg-blue-200 px-2 py-1 rounded-lg transition-colors btn-edit-tier" data-id="'.$r['id'].'" title="Edit Tier"><i class="fa fa-pencil"></i></button>';
            if ($r['status'] === 'active') {
                $acts .= '<button class="bg-amber-100 text-amber-700 hover:bg-amber-200 px-2 py-1 rounded-lg transition-colors btn-tier-status" data-id="'.$r['id'].'" data-action="deactivate" title="Deactivate"><i class="fa fa-ban"></i></button>';
            } else {
                $acts .= '<button class="bg-green-100 text-green-700 hover:bg-green-200 px-2 py-1 rounded-lg transition-colors btn-tier-status" data-id="'.$r['id'].'" data-action="activate" title="Activate"><i class="fa fa-check"></i></button>';
            }
            $acts .= '<button class="bg-red-100 text-red-700 hover:bg-red-200 px-2 py-1 rounded-lg transition-colors btn-tier-status" data-id="'.$r['id'].'" data-action="delete" title="Delete"><i class="fa fa-trash"></i></button>';
            $acts .= '</div>';

            $data[] = [
                $r['sort_order'],
                $badge,
                esc_html($r['slug']),
                esc_html($r['description'] ?? '-'),
                $status_badge,
                $acts
            ];
        }
        $this->json_list($data, $total, $total);
    }

    public function get_package_tier($id) {
        $this->load->model('Package_tier_model');
        $tier = $this->Package_tier_model->get_by_id((int)$id);
        if (!$tier) $this->json_error('Package tier not found.', 404);
        $this->json_success($tier);
    }

    public function save_package_tier() {
        $this->load->model('Package_tier_model');
        $id          = (int)$this->input->post('id');
        $name        = trim($this->input->post('name'));
        $slug        = trim($this->input->post('slug')) ?: strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        $badge_color = trim($this->input->post('badge_color')) ?: 'blue';
        $icon        = trim($this->input->post('icon')) ?: 'fa-cube';
        $description = trim($this->input->post('description'));
        $sort_order  = (int)$this->input->post('sort_order');
        $status      = $this->input->post('status') === 'inactive' ? 'inactive' : 'active';

        if (!$name) $this->json_error('Tier Name is required.', 400);

        $data = [
            'name'        => $name,
            'slug'        => $slug,
            'badge_color' => $badge_color,
            'icon'        => $icon,
            'description' => $description,
            'sort_order'  => $sort_order,
            'status'      => $status
        ];

        if ($id > 0) {
            $this->Package_tier_model->update($id, $data);
            $this->json_success([], 'Package Tier updated.');
        } else {
            $new_id = $this->Package_tier_model->insert($data);
            $this->json_success(['id' => $new_id], 'Package Tier created.');
        }
    }

    public function package_tier_status() {
        $this->load->model('Package_tier_model');
        $id     = (int)$this->input->post('id');
        $action = $this->input->post('action');
        if ($action === 'activate') {
            $this->Package_tier_model->activate($id);
            $this->json_success([], 'Package Tier activated.');
        } elseif ($action === 'deactivate') {
            $this->Package_tier_model->deactivate($id);
            $this->json_success([], 'Package Tier deactivated.');
        } elseif ($action === 'delete') {
            $this->Package_tier_model->soft_delete($id);
            $this->json_success([], 'Package Tier deleted.');
        }
        $this->json_error('Invalid action.', 400);
    }

    public function get_active_tiers() {
        $this->load->model('Package_tier_model');
        $tiers = $this->Package_tier_model->get_all_active();
        $this->json_success($tiers);
    }

    // ========================================================
    // PRODUCT PACKAGE SPLIT AJAX CRUD
    // ========================================================

    public function get_package_splits($product_id) {
        $this->load->model('Product_package_split_model');
        $splits = $this->Product_package_split_model->get_splits_by_product((int)$product_id);
        
        foreach ($splits as &$s) {
            $s['price_formatted'] = '₹' . number_format($s['price'] / 100, 2);
            $s['min_price_formatted'] = '₹' . number_format($s['min_price'] / 100, 2);
            $s['monthly_price_formatted'] = !empty($s['monthly_price']) ? '₹' . number_format($s['monthly_price'] / 100, 0) : '₹' . number_format($s['price'] / 1200, 0);
            $s['yearly_price_formatted'] = !empty($s['yearly_price']) ? '₹' . number_format($s['yearly_price'] / 100, 0) : '₹' . number_format($s['price'] / 100, 0);
            $s['implementation_fee_formatted'] = !empty($s['implementation_fee']) ? '₹' . number_format($s['implementation_fee'] / 100, 0) : '₹0';
            $s['raw_price'] = $s['price'] / 100;
            $s['raw_min_price'] = $s['min_price'] / 100;
            $s['raw_monthly_price'] = $s['monthly_price'] / 100;
            $s['raw_yearly_price'] = $s['yearly_price'] / 100;
            $s['raw_implementation_fee'] = $s['implementation_fee'] / 100;
            $s['features_array'] = !empty($s['features_included']) ? array_filter(array_map('trim', explode("\n", $s['features_included']))) : [];
        }
        $this->json_success($splits);
    }

    public function get_single_package_split($id) {
        $this->load->model('Product_package_split_model');
        $split = $this->Product_package_split_model->get_split_details((int)$id);
        if (!$split) $this->json_error('Package split not found.', 404);

        $split['price'] = $split['price'] / 100;
        $split['min_price'] = $split['min_price'] / 100;
        $split['monthly_price'] = $split['monthly_price'] / 100;
        $split['yearly_price'] = $split['yearly_price'] / 100;
        $split['implementation_fee'] = $split['implementation_fee'] / 100;
        $this->json_success($split);
    }

    public function save_package_split() {
        $this->load->model('Product_package_split_model');
        
        $id                 = (int)$this->input->post('id');
        $product_id         = (int)$this->input->post('product_id');
        $package_tier_id    = (int)$this->input->post('package_tier_id');
        $tier_subtitle      = trim($this->input->post('tier_subtitle'));
        $monthly_price      = inr_to_paise((float)$this->input->post('monthly_price'));
        $yearly_price       = inr_to_paise((float)$this->input->post('yearly_price'));
        $discount_label     = trim($this->input->post('discount_label')) ?: 'Save 17%';
        $price              = $yearly_price ?: inr_to_paise((float)$this->input->post('price'));
        $min_price          = inr_to_paise((float)$this->input->post('min_price'));
        $billing_cycle      = $this->input->post('billing_cycle') ?: 'annual';
        $amc_percentage     = (float)$this->input->post('amc_percentage') ?: 18.00;
        $implementation_fee = inr_to_paise((float)$this->input->post('implementation_fee'));
        $user_limit         = trim($this->input->post('user_limit')) ?: 'Unlimited';
        $storage_limit      = trim($this->input->post('storage_limit')) ?: '10 GB';
        $support_type       = trim($this->input->post('support_type')) ?: 'Standard Support';
        $reports_type       = trim($this->input->post('reports_type')) ?: 'Basic Reports';
        $features           = trim($this->input->post('features_included'));
        $is_popular         = (int)$this->input->post('is_popular') ? 1 : 0;

        if (!$product_id)      $this->json_error('Product is required.', 400);
        if (!$package_tier_id) $this->json_error('Package Tier is required.', 400);

        $data = [
            'product_id'         => $product_id,
            'package_tier_id'    => $package_tier_id,
            'tier_subtitle'      => $tier_subtitle,
            'monthly_price'      => $monthly_price,
            'yearly_price'       => $yearly_price,
            'discount_label'     => $discount_label,
            'price'              => $price,
            'min_price'          => $min_price,
            'billing_cycle'      => $billing_cycle,
            'amc_percentage'     => $amc_percentage,
            'implementation_fee' => $implementation_fee,
            'user_limit'         => $user_limit,
            'storage_limit'      => $storage_limit,
            'support_type'       => $support_type,
            'reports_type'       => $reports_type,
            'features_included'  => $features,
            'is_popular'         => $is_popular,
            'status'             => 'active'
        ];

        if ($id > 0) {
            $this->Product_package_split_model->update($id, $data);
            $this->json_success([], 'Package split updated successfully.');
        } else {
            $new_id = $this->Product_package_split_model->insert($data);
            $this->json_success(['id' => $new_id], 'Package split created successfully.');
        }
    }

    public function delete_package_split() {
        $this->load->model('Product_package_split_model');
        $id = (int)$this->input->post('id');
        if (!$id) $this->json_error('Invalid ID.', 400);

        $this->Product_package_split_model->soft_delete($id);
        $this->json_success([], 'Package split removed.');
    }

    // ========================================================
    // COMPLETE PRICING MATRIX BROCHURE ENDPOINTS (Like Classwall / Peroman)
    // ========================================================

    public function get_pricing_matrix_data($product_id) {
        $this->load->model(['Product_package_split_model', 'Product_addon_model', 'Product_core_module_model']);
        
        $product = $this->Product_model->get_by_id((int)$product_id);
        if (!$product) $this->json_error('Product not found.', 404);

        $product['logo_url'] = !empty($product['logo']) ? base_url($product['logo']) : (!empty($product['image']) ? base_url($product['image']) : '');

        // Splits / Tiers
        $splits = $this->Product_package_split_model->get_splits_by_product((int)$product_id);
        foreach ($splits as &$s) {
            $s['monthly_price_formatted'] = !empty($s['monthly_price']) ? '₹' . number_format($s['monthly_price'] / 100, 0) : '₹' . number_format($s['price'] / 1200, 0);
            $s['yearly_price_formatted'] = !empty($s['yearly_price']) ? '₹' . number_format($s['yearly_price'] / 100, 0) : '₹' . number_format($s['price'] / 100, 0);
            $s['implementation_fee_formatted'] = !empty($s['implementation_fee']) ? '₹' . number_format($s['implementation_fee'] / 100, 0) : '₹0';
            $s['features_array'] = !empty($s['features_included']) ? array_filter(array_map('trim', explode("\n", $s['features_included']))) : [];
        }

        // Core Modules
        $core_modules = $this->Product_core_module_model->get_by_product((int)$product_id);

        // Addons
        $addons = $this->Product_addon_model->get_by_product((int)$product_id);
        foreach ($addons as &$a) {
            $a['monthly_price_formatted'] = '₹' . number_format($a['monthly_price'] / 100, 0);
        }

        // Why Points
        $why_points = [];
        if (!empty($product['why_points'])) {
            $lines = array_filter(array_map('trim', explode("\n", $product['why_points'])));
            foreach ($lines as $line) {
                $parts = explode(':', $line, 2);
                $why_points[] = [
                    'title' => $parts[0] ?? '',
                    'desc'  => $parts[1] ?? ''
                ];
            }
        }

        $this->json_success([
            'product'      => $product,
            'splits'       => $splits,
            'core_modules' => $core_modules,
            'addons'       => $addons,
            'why_points'   => $why_points
        ]);
    }
}
