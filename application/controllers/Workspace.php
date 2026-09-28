<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Workspace extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model('Contact_book_model');
        $this->load->model('User_model');
        $this->load->model('Customer_model');
    }

    public function index() {
        redirect('workspace/contact_book');
    }

    // ── CONTACT BOOK ──────────────────────────────────────────────────

    public function contact_book() {
        $customers = $this->db->select("id, IF(customer_name != '', CONCAT(customer_name, ' (', customer_org_name, ')'), customer_org_name) AS display_name")
            ->from('crm_customers')->where(['status' => 'active', 'is_deleted' => 0, 'customer_type' => 'primary'])
            ->order_by('customer_org_name', 'asc')->get()->result_array();
        $this->load_view('workspace/contact_book', [
            'page_title' => 'Contact Book',
            'page_js'    => 'workspace',
            'customers'  => $customers,
        ]);
    }

    public function contacts_dt() {
        $params = $this->input->get();
        $user_id = null;
        
        [$rows, $total] = $this->Contact_book_model->datatable($params, $user_id);
        
        $data = [];
        foreach ($rows as $r) {
            $name = esc_html($r['name']);
            $parts = explode(' ', $name);
            $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
            $name_html = '<div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-[10px]">'.$initials.'</div>
                            <div>
                              <span class="font-semibold text-gray-800 text-sm">'.$name.'</span>
                              '.(!empty($r['linked_customer']) ? '<span class="block text-[10px] text-indigo-500 font-medium mt-0.5"><i class="fa fa-building-o mr-0.5"></i>'.esc_html($r['linked_customer']).'</span>' : '').'
                            </div>
                          </div>';

            $phone_html = $r['phone'] ? '<a href="tel:'.esc_html($r['phone']).'" class="flex items-center gap-1 text-blue-600 hover:underline"><i class="fa fa-phone text-[10px]"></i>'.esc_html($r['phone']).'</a>' : '<span class="text-gray-300">—</span>';

            $actions = '<div class="flex gap-2">
                            <a href="javascript:void(0)" class="w-7 h-7 flex items-center justify-center bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 btn-edit-contact" data-id="'.$r['id'].'" title="Edit"><i class="fa fa-pencil" style="font-size:11px"></i></a>
                            <a href="javascript:void(0)" class="w-7 h-7 flex items-center justify-center bg-red-50 text-red-500 rounded-lg hover:bg-red-100 btn-delete-contact" data-id="'.$r['id'].'" title="Delete"><i class="fa fa-trash-o" style="font-size:11px"></i></a>
                        </div>';

            $data[] = [
                $r['id'],
                $name_html,
                esc_html($r['company_name'] ?? '—'),
                $phone_html,
                esc_html($r['email'] ?? '—'),
                date('d M Y', strtotime($r['created_at'])),
                $actions
            ];
        }
        $this->json_list($data, $total, $total);
    }

    public function add_contact() {
        redirect('workspace/contact_book');
    }

    public function process_contact() {
        $name = trim($this->input->post('name'));
        if (!$name) {
            $this->json_error('Name is required.');
        }

        $id = $this->input->post('id');
        
        $data = [
            'name'         => $name,
            'phone'        => trim($this->input->post('phone')),
            'email'        => trim($this->input->post('email')),
            'company_name' => trim($this->input->post('company_name')),
            'job_title'    => trim($this->input->post('job_title')),
            'address'      => trim($this->input->post('address')),
            'notes'        => trim($this->input->post('notes')),
            'customer_id'  => (int)$this->input->post('customer_id') ?: null,
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        if ($id) {
            // Update
            $this->Contact_book_model->update($id, $data);
            $this->json_success([], 'Contact updated successfully.');
        } else {
            // Insert
            $data['user_id'] = $this->get_user_id();
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->Contact_book_model->insert($data);
            $this->json_success([], 'Contact added successfully.');
        }
    }

    public function get_contact($id) {
        $contact = $this->Contact_book_model->get_by_id($id);
        if (!$contact) {
            $this->json_error('Contact not found.');
        }
        // Get linked customer display name if any
        if (!empty($contact['customer_id'])) {
            $cust = $this->db->select("IF(customer_name != '', CONCAT(customer_name, ' (', customer_org_name, ')'), customer_org_name) AS display_name")
                ->from('crm_customers')->where('id', $contact['customer_id'])->get()->row_array();
            $contact['customer_display'] = $cust['display_name'] ?? '';
        } else {
            $contact['customer_display'] = '';
        }
        $this->json_success(['contact' => $contact]);
    }

    public function delete_contact($id) {
        $this->Contact_book_model->soft_delete($id);
        $this->json_success([], 'Contact deleted successfully.');
    }
}
