<section class="content-header">
    <h1>Orders <small>Sales orders management</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>">Home</a></li><li class="active">Orders</li></ol>
</section>
<section class="content">
<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-shopping-cart"></i> Orders</h3>
        <div class="box-tools">
            <?php if($is_manager): ?>
            <a href="<?= base_url('orders/approval') ?>" class="btn btn-warning btn-sm"><i class="fa fa-clock-o"></i> Pending Approval</a>
            <?php endif; ?>
            <button class="btn btn-success btn-sm" id="btn-add-order"><i class="fa fa-plus"></i> New Order</button>
        </div>
    </div>
    <div class="box-body">
        <table id="orders-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Order #</th><th>Customer</th><th>Status</th><th>Amount</th><th>Created By</th><th>Date</th><th>Record</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="order-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">New Order</h4></div>
            <div class="modal-body">
                <form id="order-form" method="POST" action="<?= base_url('orders/save') ?>">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="order-id" value="0">
                    <input type="hidden" name="items" id="order-items-json" value="[]">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group"><label>Customer *</label>
                                <select name="customer_id" class="form-control select2" required>
                                    <option value="">-- Select Customer --</option>
                                    <?php foreach($customers as $c): ?><option value="<?= $c['id'] ?>"><?= esc_html($c['name']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group"><label>Delivery Date</label><input type="text" name="delivery_date" class="form-control datepicker"></div>
                        </div>
                    </div>
                    <h5>Order Items</h5>
                    <table class="table table-bordered table-condensed" id="order-items-table">
                        <thead><tr><th>Product</th><th>Qty</th><th>Unit Price (₹)</th><th>Disc %</th><th>Line Total</th><th></th></tr></thead>
                        <tbody id="order-items-body"></tbody>
                        <tfoot><tr><td colspan="6"><button type="button" class="btn btn-xs btn-info" id="btn-add-item"><i class="fa fa-plus"></i> Add Item</button></td></tr></tfoot>
                    </table>
                    <div class="row">
                        <div class="col-md-4 col-md-offset-8">
                            <table class="table table-condensed">
                                <tr><td>Subtotal:</td><td id="order-subtotal">₹0.00</td></tr>
                                <tr><td>Discount (₹):</td><td><input type="number" step="0.01" name="discount_amount" id="order-discount" class="form-control input-sm" value="0"></td></tr>
                                <tr><td><strong>Total:</strong></td><td><strong id="order-total">₹0.00</strong></td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="form-group"><label>Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button class="btn btn-warning" id="btn-save-draft"><i class="fa fa-save"></i> Save Draft</button>
                <button class="btn btn-primary" id="btn-submit-approval"><i class="fa fa-paper-plane"></i> Submit for Approval</button>
            </div>
        </div>
    </div>
</div>
</section>
<!-- Pass product list to JS before crm.orders.js loads -->
<script>
window.PRODUCT_LIST = <?= json_encode(array_map(function($p){
    return ['id'=>(int)$p['id'],'name'=>$p['name'],'sku'=>$p['sku'],'unit'=>$p['unit'],'price'=>(int)$p['price'],'min_price'=>(int)$p['min_price']];
}, $products ?? [])) ?>;
</script>
