<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Allow only CLI execution
        if (!is_cli()) {
            show_error('CLI execution only.', 403);
        }
        $this->load->model('Bulk_mail_model');
        $this->load->library('email');
        
        // Optional: Load your email configuration here if it's not autoloaded or in config/email.php
        // $config = array(
        //     'protocol' => 'smtp',
        //     'smtp_host' => 'your_host',
        //     'smtp_port' => 465,
        //     'smtp_user' => 'your_user',
        //     'smtp_pass' => 'your_pass',
        //     'mailtype'  => 'html',
        //     'charset'   => 'iso-8859-1'
        // );
        // $this->email->initialize($config);
    }

    public function process_queue($limit = 50) {
        echo "Starting to process email queue... (limit: $limit)\n";
        
        $pending_items = $this->Bulk_mail_model->get_pending_queue($limit);
        
        if (empty($pending_items)) {
            echo "Queue is empty. Nothing to do.\n";
            return;
        }

        echo "Found " . count($pending_items) . " items to process.\n";

        $processed_campaigns = [];

        foreach ($pending_items as $item) {
            echo "Processing queue item ID: " . $item['id'] . " to " . $item['recipient_email'] . "\n";
            
            // Personalize the message
            $message = $item['message'];
            if (!empty($item['recipient_name'])) {
                $message = str_replace('{{customer_name}}', $item['recipient_name'], $message);
            } else {
                $message = str_replace('{{customer_name}}', 'Customer', $message);
            }

            $this->email->clear();
            $this->email->from('noreply@crm-zazu.local', 'CRM-Zazu'); // Change this to your from address
            $this->email->to($item['recipient_email']);
            $this->email->subject($item['subject']);
            $this->email->message($message);

            // Attempt to send email
            if ($this->email->send()) {
                $this->Bulk_mail_model->update_queue_item($item['id'], [
                    'status' => 'sent',
                    'sent_at' => date('Y-m-d H:i:s')
                ]);
                echo "Success.\n";
            } else {
                $error = $this->email->print_debugger(['headers']);
                $this->Bulk_mail_model->update_queue_item($item['id'], [
                    'status' => 'failed',
                    'error_message' => $error
                ]);
                echo "Failed.\n";
            }
            
            if (!in_array($item['campaign_id'], $processed_campaigns)) {
                $processed_campaigns[] = $item['campaign_id'];
            }
        }
        
        // Update statuses of processed campaigns
        foreach ($processed_campaigns as $campaign_id) {
            $this->Bulk_mail_model->check_and_update_campaign($campaign_id);
            echo "Updated status for campaign ID: $campaign_id\n";
        }
        
        echo "Finished processing batch.\n";
    }
}
