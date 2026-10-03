<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Bulk_mail_model');
    }

    /**
     * Universal Cron Endpoint for processing queue dispatches.
     * Can be executed via:
     * 1. CLI: php index.php cron process_queue
     * 2. Hostinger / cPanel Wget / Curl / Web Cron:
     *    curl -s "https://crm.zazutech.in/cron/process_queue"
     */
    public function process_queue($limit = 1) {
        // Allow limit override via CLI param or GET parameter (default 1 email for safe anti-ban pacing)
        $paramLimit = $this->input->get('limit') ? (int)$this->input->get('limit') : (int)$limit;
        if ($paramLimit < 1) $paramLimit = 1;
        if ($paramLimit > 10) $paramLimit = 10;

        $result = $this->Bulk_mail_model->execute_queue_batch($paramLimit);

        if (is_cli()) {
            echo "[" . date('Y-m-d H:i:s') . "] Status: " . $result['status'] 
               . " | Processed: " . $result['processed'] 
               . " | Remaining Queued: " . ($result['remaining'] ?? 0) 
               . (isset($result['paused']) ? " | Paused: " . $result['paused'] : "")
               . " | Message: " . $result['message'] . PHP_EOL;
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    public function index() {
        $this->process_queue();
    }
}
