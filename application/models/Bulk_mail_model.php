<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bulk_mail_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function create_campaign($data) {
        $this->db->insert('bulk_mail_campaigns', $data);
        return $this->db->insert_id();
    }

    public function add_to_queue($campaign_id, $recipients) {
        if (empty($recipients)) return false;

        $batch_data = [];
        foreach ($recipients as $recipient) {
            $batch_data[] = [
                'campaign_id' => $campaign_id,
                'recipient_email' => $recipient['email'],
                'recipient_name' => isset($recipient['name']) ? $recipient['name'] : '',
                'status' => 'queued'
            ];
        }

        return $this->db->insert_batch('bulk_mail_queue', $batch_data);
    }

    public function get_stats() {
        $stats = [
            'total' => 0,
            'sent' => 0,
            'queued' => 0,
            'failed' => 0
        ];

        // Total campaigns isn't as useful as total queue items, let's get total queue items
        $this->db->select('status, count(id) as count');
        $this->db->from('bulk_mail_queue');
        $this->db->group_by('status');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            if ($row->status == 'sent') $stats['sent'] = $row->count;
            if ($row->status == 'queued') $stats['queued'] = $row->count;
            if ($row->status == 'failed') $stats['failed'] = $row->count;
        }
        $stats['total'] = $stats['sent'] + $stats['queued'] + $stats['failed'];

        return $stats;
    }

    public function get_recent_campaigns($limit = 5) {
        $this->db->select('*');
        $this->db->from('bulk_mail_campaigns');
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    public function get_live_activity($limit = 10) {
        $this->db->select('q.*, c.subject');
        $this->db->from('bulk_mail_queue q');
        $this->db->join('bulk_mail_campaigns c', 'c.id = q.campaign_id', 'left');
        $this->db->order_by('q.id', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    public function get_pending_queue($limit = 50) {
        $this->db->select('q.*, c.subject, c.message');
        $this->db->from('bulk_mail_queue q');
        $this->db->join('bulk_mail_campaigns c', 'c.id = q.campaign_id');
        $this->db->where('q.status', 'queued');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    public function update_queue_item($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('bulk_mail_queue', $data);
    }

    public function update_campaign_status($id, $status) {
        $this->db->where('id', $id);
        return $this->db->update('bulk_mail_campaigns', ['status' => $status]);
    }
    
    public function check_and_update_campaign($campaign_id) {
        // Check if there are any remaining queued items for this campaign
        $this->db->where('campaign_id', $campaign_id);
        $this->db->where('status', 'queued');
        $count = $this->db->count_all_results('bulk_mail_queue');
        
        if ($count == 0) {
            $this->update_campaign_status($campaign_id, 'completed');
        } else {
            $this->update_campaign_status($campaign_id, 'processing');
        }
    }
}
