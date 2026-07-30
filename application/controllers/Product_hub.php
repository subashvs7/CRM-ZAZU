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
        $categories = $this->Product_category_model->get_active();
        
        $this->load_view('product_hub/products', [
            'page_title' => 'Products Catalogue',
            'page_js' => 'product_hub',
            'categories' => $categories
        ]);
    }

    public function products_dt() {
        $params = $this->input->get();
        [$rows, $total] = $this->Product_model->datatable($params);
        $data = [];
        foreach ($rows as $r) {
            $acts = '<div class="flex items-center gap-1">';
            $acts .= '<button class="bg-blue-100 text-blue-700 hover:bg-blue-200 px-2 py-1 rounded-lg transition-colors btn-edit-product" data-id="'.$r['id'].'" title="Edit"><i class="fa fa-pencil"></i></button>';
            $acts .= '<button class="bg-red-100 text-red-700 hover:bg-red-200 px-2 py-1 rounded-lg transition-colors btn-product-status" data-id="'.$r['id'].'" data-action="delete" title="Delete"><i class="fa fa-trash"></i></button>';
            $acts .= '</div>';
            
            $data[] = [
                $r['id'],
                esc_html($r['sku']),
                esc_html($r['name']),
                esc_html($r['category_name'] ?? '-'),
                '₹' . number_format($r['price'] / 100, 2),
                '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Available</span>',
                $acts
            ];
        }
        $this->json_list($data, $total, $total);
    }

    public function save_product() {
        $id   = (int) $this->input->post('id');
        $name = trim($this->input->post('name'));
        $sku  = trim($this->input->post('sku'));
        if (!$name) $this->json_error('Validation failed.', 400, ['name'=>'Name required.']);
        if (!$sku)  $this->json_error('Validation failed.', 400, ['sku'=>'SKU required.']);

        $price     = inr_to_paise((float)$this->input->post('price'));
        $min_price = inr_to_paise((float)$this->input->post('min_price'));
        $data = [
            'name'=>$name,'sku'=>$sku,'description'=>$this->input->post('description'),
            'unit'=>$this->input->post('unit')?:'pcs','price'=>$price,'min_price'=>$min_price,
            'stock'=>(int)$this->input->post('stock'),'category_id'=>(int)$this->input->post('category_id')?:null,
        ];

        if ($id) { $this->Product_model->update($id, $data); $this->json_success([], 'Product updated.'); }
        else     { $new = $this->Product_model->insert($data); $this->json_success(['id'=>$new], 'Product created.'); }
    }

    public function get_product($id) {
        $p = $this->Product_model->get_by_id($id);
        if (!$p) $this->json_error('Product not found.', 404);
        $p['price'] = $p['price'] / 100;
        $p['min_price'] = $p['min_price'] / 100;
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
}
