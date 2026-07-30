<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Workspace extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model('Contact_book_model');
        $this->load->model('User_model');
    }

    public function index() {
        redirect('workspace/contact_book');
    }

    // ── CONTACT BOOK ──────────────────────────────────────────────────

    public function contact_book() {
        $this->load_view('workspace/contact_book', [
            'page_title' => 'Add New Contact',
            'page_js' => 'workspace'
        ]);
    }

    public function contacts_dt() {
        $params = $this->input->get();
        // Assuming shared directory for now, or user specific if not admin.
        // Let's make it shared for everyone to see.
        $user_id = null; 
        
        [$rows, $total] = $this->Contact_book_model->datatable($params, $user_id);
        
        $data = [];
        foreach ($rows as $r) {
            $name = esc_html($r['name']);
            $parts = explode(' ', $name);
            $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
            $name_html = '<div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-[10px]">'.$initials.'</div>
                            <span class="font-semibold text-gray-800 text-sm">'.$name.'</span>
                          </div>';

            $actions = '<div class="flex gap-2">
                            <a href="javascript:void(0)" class="text-blue-500 hover:text-blue-700 transition-colors btn-edit-contact" data-id="'.$r['id'].'"><i class="fa fa-pencil"></i></a>
                            <a href="javascript:void(0)" class="text-red-500 hover:text-red-700 transition-colors btn-delete-contact" data-id="'.$r['id'].'"><i class="fa fa-trash-o"></i></a>
                        </div>';

            $data[] = [
                $r['id'],
                $name_html,
                esc_html($r['company_name'] ?? '-'),
                esc_html($r['phone'] ?? '-'),
                esc_html($r['email'] ?? '-'),
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
            'name' => $name,
            'phone' => trim($this->input->post('phone')),
            'email' => trim($this->input->post('email')),
            'company_name' => trim($this->input->post('company_name')),
            'job_title' => trim($this->input->post('job_title')),
            'address' => trim($this->input->post('address')),
            'notes' => trim($this->input->post('notes')),
            'updated_at' => date('Y-m-d H:i:s')
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
        $this->json_success(['contact' => $contact]);
    }

    public function delete_contact($id) {
        $this->Contact_book_model->soft_delete($id);
        $this->json_success([], 'Contact deleted successfully.');
    }
}
