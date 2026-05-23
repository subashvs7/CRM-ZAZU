<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model('Notification_model');
    }

    public function index() {
        $notifications = $this->Notification_model->get_user_notifications($this->get_user_id(), 50);
        $this->load_view('notifications/index', ['page_title'=>'Notifications','notifications'=>$notifications]);
    }

    public function unread_count() {
        $count = $this->Notification_model->unread_count($this->get_user_id());
        $items = $this->Notification_model->get_user_notifications($this->get_user_id(), 10);
        $this->json_success(['count'=>$count,'items'=>$items]);
    }

    public function mark_read() {
        $id = (int)$this->input->post('id');
        $this->Notification_model->mark_read($id, $this->get_user_id());
        $this->json_success([]);
    }

    public function mark_all_read() {
        $this->Notification_model->mark_all_read($this->get_user_id());
        $this->json_success([], 'All marked as read.');
    }

    public function soft_delete() {
        $id = (int)$this->input->post('id');
        $this->Notification_model->soft_delete($id);
        $this->json_success([]);
    }
}
