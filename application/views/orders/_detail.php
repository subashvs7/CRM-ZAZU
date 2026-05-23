<section class="content-header">
    <h1>Order <?= esc_html($order['order_number']) ?> <small><?= order_status_badge($order['order_status']) ?></small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('orders') ?>">Orders</a></li><li class="active"><?= esc_html($order['order_number']) ?></li></ol>
</section>
<section class="content">
<div class="row">
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">Order Info</h3></div>
            <div class="box-body">
                <dl class="dl-horizontal">
                    <dt>Customer</dt><dd><?= esc_html($order['customer_name']) ?></dd>
                    <dt>Phone</dt><dd><?= esc_html($order['customer_phone']??'-') ?></dd>
                    <dt>Created By</dt><dd><?= esc_html($order['created_by_name']) ?></dd>
                    <dt>Status</dt><dd><?= order_status_badge($order['order_status']) ?></dd>
                    <dt>Total</dt><dd><?= format_inr($order['total_amount']) ?></dd>
                    <dt>Discount</dt><dd><?= format_inr($order['discount_amount']) ?></dd>
                    <dt>Final</dt><dd><strong><?= format_inr($order['final_amount']) ?></strong></dd>
                    <?php if($order['approved_by']): ?>
                    <dt>Approved By</dt><dd><?= esc_html($order['approved_by_name']) ?></dd>
                    <dt>Approved At</dt><dd><?= $order['approved_at'] ?></dd>
                    <?php endif; ?>
                    <dt>Delivery Date</dt><dd><?= $order['delivery_date']??'-' ?></dd>
                </dl>
                <a href="<?= base_url('orders/pdf/'.$order['id']) ?>" class="btn btn-default btn-block"><i class="fa fa-download"></i> Download PDF</a>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="box box-info">
            <div class="box-header with-border"><h3 class="box-title">Order Items</h3></div>
            <div class="box-body">
                <table class="table table-bordered">
                    <thead><tr><th>Product</th><th>SKU</th><th>Unit</th><th>Qty</th><th>Unit Price</th><th>Discount</th><th>Line Total</th></tr></thead>
                    <tbody>
                    <?php foreach($items as $item): ?>
                    <tr>
                        <td><?= esc_html($item['product_name']) ?></td>
                        <td><?= esc_html($item['sku']) ?></td>
                        <td><?= esc_html($item['unit']) ?></td>
                        <td><?= $item['qty'] ?></td>
                        <td><?= format_inr($item['unit_price']) ?></td>
                        <td><?= $item['discount_pct'] ?>%</td>
                        <td><?= format_inr($item['line_total']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><td colspan="6" class="text-right"><strong>Subtotal:</strong></td><td><?= format_inr($order['total_amount']) ?></td></tr>
                        <tr><td colspan="6" class="text-right">Discount:</td><td>-<?= format_inr($order['discount_amount']) ?></td></tr>
                        <tr><td colspan="6" class="text-right"><strong>Final:</strong></td><td><strong><?= format_inr($order['final_amount']) ?></strong></td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
</section>
