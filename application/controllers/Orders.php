<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(['Order_model','Order_item_model','Customer_model','Product_model','Lead_model','User_model']);
        $this->load->library('Notification_sender');
    }

    public function index() {
        $customers = $this->Customer_model->get_active();
        $products  = $this->Product_model->get_active_with_category();
        $this->load_view('orders/index', ['page_title'=>'Orders','page_js'=>'orders','customers'=>$customers,'products'=>$products,'sf'=>'']);
    }

    // Returns active product list for order form (accessible to all logged-in roles)
    public function products_list() {
        $products = $this->Product_model->get_active_with_category();
        $list = [];
        foreach ($products as $p) {
            $list[] = ['id'=>$p['id'],'name'=>$p['name'],'sku'=>$p['sku'],'price'=>$p['price'],'unit'=>$p['unit'],'min_price'=>$p['min_price']];
        }
        $this->json_success($list);
    }

    public function approval() {
        $this->require_role(['admin','manager']);
        $this->load_view('orders/approval', ['page_title'=>'Pending Approval','page_js'=>'orders']);
    }

    public function datatable() {
        $params = $this->input->get();
        $sf     = $this->input->get('status_filter');
        [$rows, $total] = $this->Order_model->datatable($params, $sf, $this->get_user_id(), $this->get_role());
        $data = [];
        foreach ($rows as $r) {
            $actions = '<div class="btn-group">';
            $actions .= '<a href="'.base_url('orders/detail/'.$r['id']).'" class="btn btn-xs btn-info"><i class="fa fa-eye"></i></a> ';
            if ($r['status']!=='deleted') $actions .= '<button class="btn btn-xs btn-primary btn-edit-order" data-id="'.$r['id'].'"><i class="fa fa-pencil"></i></button> ';
            if ($r['status']==='active')   $actions .= '<button class="btn btn-xs btn-danger btn-order-status" data-id="'.$r['id'].'" data-action="delete"><i class="fa fa-trash"></i></button>';
            if ($r['status']==='deleted')  $actions .= '<button class="btn btn-xs btn-success btn-order-status" data-id="'.$r['id'].'" data-action="restore"><i class="fa fa-undo"></i></button>';
            $actions .= '</div>';
            $data[] = [
                $r['id'], esc_html($r['order_number']), esc_html($r['customer_name']),
                order_status_badge($r['order_status']), format_inr($r['final_amount']),
                esc_html($r['created_by_name']),
                date('d M Y', strtotime($r['created_at'])),
                status_badge($r['status']),
                $actions,
            ];
        }
        $this->json_list($data, $total, $total);
    }

    public function approval_datatable() {
        $this->require_role(['admin','manager']);
        $params = $this->input->get();
        [$rows, $total] = $this->Order_model->pending_approval_datatable($params);
        $data = [];
        foreach ($rows as $r) {
            $acts = '<a href="'.base_url('orders/detail/'.$r['id']).'" class="btn btn-xs btn-info"><i class="fa fa-eye"></i></a> ';
            $acts .= '<button class="btn btn-xs btn-success btn-approve-order" data-id="'.$r['id'].'"><i class="fa fa-check"></i> Approve</button> ';
            $acts .= '<button class="btn btn-xs btn-danger btn-reject-order" data-id="'.$r['id'].'"><i class="fa fa-times"></i> Reject</button>';
            $data[] = [$r['id'], esc_html($r['order_number']), esc_html($r['customer_name']), format_inr($r['final_amount']), esc_html($r['created_by_name']), date('d M Y H:i', strtotime($r['created_at'])), $acts];
        }
        $this->json_list($data, $total, $total);
    }

    public function save() {
        $id  = (int) $this->input->post('id');
        $cid = (int) $this->input->post('customer_id');
        if (!$cid) $this->json_error('Customer required.', 400, ['customer_id'=>'Select a customer.']);

        $items_json = $this->input->post('items');
        $items = json_decode($items_json, true);
        if (empty($items)) $this->json_error('Add at least one product.', 400, ['items'=>'No items.']);

        $total = 0; $rows = [];
        foreach ($items as $item) {
            $product  = $this->Product_model->get_by_id((int)$item['product_id']);
            if (!$product) continue;
            $qty      = max(1, (int)$item['qty']);
            $price    = (int)$item['unit_price'] ?: $product['price'];
            $disc     = min(100, max(0, (float)($item['discount_pct']??0)));
            $line     = (int) round($qty * $price * (1 - $disc/100));
            $total   += $line;
            $rows[]   = ['product_id'=>$product['id'],'qty'=>$qty,'unit_price'=>$price,'discount_pct'=>$disc,'line_total'=>$line];
        }

        $discount = inr_to_paise((float)$this->input->post('discount_amount'));
        $final    = max(0, $total - $discount);

        $data = [
            'customer_id'     => $cid,
            'lead_id'         => (int)$this->input->post('lead_id') ?: null,
            'created_by'      => $this->get_user_id(),
            'order_status'    => $this->input->post('submit_for_approval') ? 'pending_approval' : 'draft',
            'total_amount'    => $total,
            'discount_amount' => $discount,
            'final_amount'    => $final,
            'notes'           => $this->input->post('notes'),
            'delivery_date'   => $this->input->post('delivery_date') ?: null,
        ];

        if ($id) {
            $this->Order_model->update($id, $data);
            $this->Order_item_model->replace_items($id, $rows);
            if ($data['order_status']==='pending_approval') {
                $this->notification_sender->send_to_managers('order_approval', 'Order Pending Approval', 'Order needs your review', ['order_id'=>$id]);
            }
            $this->json_success([], 'Order updated.');
        } else {
            $data['order_number'] = $this->Order_model->generate_number();
            $new = $this->Order_model->insert($data);
            $this->Order_item_model->replace_items($new, $rows);
            if ($data['order_status']==='pending_approval') {
                $this->notification_sender->send_to_managers('order_approval','Order Pending Approval','Order #'.$data['order_number'].' needs review',['order_id'=>$new]);
            }
            $this->json_success(['id'=>$new], 'Order created.');
        }
    }

    public function get($id) {
        $order = $this->Order_model->get_with_details($id);
        if (!$order) $this->json_error('Not found.', 404);
        $items = $this->Order_item_model->get_by_order($id);
        $this->json_success(['order'=>$order,'items'=>$items]);
    }

    public function detail($id) {
        $order = $this->Order_model->get_with_details($id);
        if (!$order) show_404();
        $items = $this->Order_item_model->get_by_order($id);
        $this->load_view('orders/_detail', ['page_title'=>'Order '.$order['order_number'],'order'=>$order,'items'=>$items]);
    }

    public function approve() {
        $this->require_role(['admin','manager']);
        $id = (int)$this->input->post('id');
        $this->Order_model->update($id, ['order_status'=>'approved','approved_by'=>$this->get_user_id(),'approved_at'=>date('Y-m-d H:i:s')]);
        $order = $this->Order_model->get_with_details($id);
        $this->notification_sender->send($order['created_by'], 'order_approved', 'Order Approved', 'Your order '.$order['order_number'].' has been approved.', ['order_id'=>$id]);
        $this->json_success([], 'Order approved.');
    }

    public function reject() {
        $this->require_role(['admin','manager']);
        $id     = (int)$this->input->post('id');
        $reason = $this->input->post('reason');
        $this->Order_model->update($id, ['order_status'=>'cancelled','notes'=>$this->db->get_where('orders',['id'=>$id])->row_array()['notes']."\n[Rejected: $reason]"]);
        $order = $this->Order_model->get_with_details($id);
        $this->notification_sender->send($order['created_by'], 'order_rejected', 'Order Rejected', 'Your order '.$order['order_number'].' was rejected: '.$reason, ['order_id'=>$id]);
        $this->json_success([], 'Order rejected.');
    }

    public function update_status() {
        $id = (int)$this->input->post('id'); $action = $this->input->post('action');
        switch($action){case'delete':$this->Order_model->soft_delete($id);break;case'restore':$this->Order_model->restore($id);break;default:$this->json_error('Invalid.');}
        $this->json_success([],'Status updated.');
    }

    public function download_pdf($id) {
        $order = $this->Order_model->get_with_details($id);
        if (!$order) show_404();
        $items = $this->Order_item_model->get_by_order($id);
        $this->load_view('orders/_pdf', ['order'=>$order,'items'=>$items], false);
    }
}
