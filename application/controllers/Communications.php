<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Communications extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model('Bulk_mail_model');
    }

    public function index() {
        redirect('communications/bulk_mail');
    }

    public function bulk_mail() {
        $this->load->model('Notification_template_model');
        $templates = $this->db->where('is_deleted', 0)->get('notification_templates')->result_array();
        
        $stats = $this->Bulk_mail_model->get_stats();
        $recent_campaigns = $this->Bulk_mail_model->get_recent_campaigns(5);

        $this->load_view('communications/bulk_mail', [
            'page_title' => 'Bulk Mail',
            'templates' => $templates,
            'stats' => $stats,
            'recent_campaigns' => $recent_campaigns,
            'page_js' => 'communications' // Will contain select2/wysiwyg init
        ]);
    }

    public function process_bulk_mail() {
        $recipient_type = $this->input->post('recipient_type');
        $subject = trim($this->input->post('subject'));
        $message = trim($this->input->post('message'));

        if (!$subject || !$message) {
            $this->session->set_flashdata('error', 'Subject and Message are required.');
            redirect('communications/bulk_mail');
        }

        $recipients = [];
        
        if ($recipient_type === 'Customers') {
            $recipients = $this->db->select('name, email')->where('is_deleted', 0)->where('email !=', '')->get('customers')->result_array();
        } elseif ($recipient_type === 'Active Customers') {
            $recipients = $this->db->select('name, email')->where('is_deleted', 0)->where('status', 'active')->where('email !=', '')->get('customers')->result_array();
        } elseif ($recipient_type === 'All Leads') {
            $recipients = $this->db->select('name, email')->where('is_deleted', 0)->where('email !=', '')->get('leads')->result_array();
        } elseif ($recipient_type === 'Contact Book') {
            $recipients = $this->db->select('name, email')->where('email !=', '')->get('contact_book')->result_array();
        }

        if (empty($recipients)) {
            $this->session->set_flashdata('error', 'No valid recipients found with email addresses for the selected group.');
            redirect('communications/bulk_mail');
        }

        $campaign_data = [
            'subject' => $subject,
            'message' => $message,
            'recipient_type' => $recipient_type,
            'total_recipients' => count($recipients),
            'status' => 'pending'
        ];

        $campaign_id = $this->Bulk_mail_model->create_campaign($campaign_data);
        $this->Bulk_mail_model->add_to_queue($campaign_id, $recipients);

        $this->session->set_flashdata('success', 'Bulk mail campaign created! ' . count($recipients) . ' emails queued for sending to ' . esc_html($recipient_type) . '.');
        redirect('communications/bulk_mail');
    }
}
