<section class="content-header">
    <h1><?= esc_html($customer['name']) ?> <small>Customer Detail</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>">Home</a></li><li><a href="<?= base_url('customers') ?>">Customers</a></li><li class="active"><?= esc_html($customer['name']) ?></li></ol>
</section>
<section class="content">
<div class="row">
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">Info</h3></div>
            <div class="box-body">
                <dl class="dl-horizontal">
                    <dt>Phone</dt><dd><?= esc_html($customer['phone']) ?></dd>
                    <dt>Email</dt><dd><?= esc_html($customer['email']??'-') ?></dd>
                    <dt>City</dt><dd><?= esc_html($customer['city']??'-') ?></dd>
                    <dt>State</dt><dd><?= esc_html($customer['state']??'-') ?></dd>
                    <dt>GST</dt><dd><?= esc_html($customer['gst_number']??'-') ?></dd>
                    <dt>Assigned</dt><dd><?= esc_html($customer['assigned_name']??'Unassigned') ?></dd>
                    <dt>Status</dt><dd><?= status_badge($customer['status']) ?></dd>
                </dl>
                <?php if($customer['notes']): ?>
                <hr><p class="text-muted"><?= nl2br(esc_html($customer['notes'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contacts -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Contact Persons</h3>
                <button class="btn btn-xs btn-success pull-right" id="btn-add-contact" data-customer="<?= $customer['id'] ?>"><i class="fa fa-plus"></i></button>
            </div>
            <div class="box-body" id="contacts-list">
                <?php foreach($contacts as $c): ?>
                <div class="contact-card border-bottom mb-2 pb-2">
                    <strong><?= esc_html($c['name']) ?></strong><?php if($c['is_primary']): ?> <span class="label label-success">Primary</span><?php endif; ?>
                    <br><small class="text-muted"><?= esc_html($c['designation']??'') ?></small>
                    <br><i class="fa fa-phone"></i> <?= esc_html($c['phone']??'-') ?>
                </div>
                <?php endforeach; ?>
                <?php if(!$contacts): ?><p class="text-muted">No contacts added.</p><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Recent Orders -->
        <div class="box box-warning">
            <div class="box-header with-border"><h3 class="box-title">Recent Orders</h3><a href="<?= base_url('orders') ?>" class="btn btn-xs btn-primary pull-right">All Orders</a></div>
            <div class="box-body">
                <table class="table table-condensed">
                    <thead><tr><th>Order #</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody id="customer-orders-body"><tr><td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i></td></tr></tbody>
                </table>
            </div>
        </div>
        <!-- Recent Leads -->
        <div class="box box-success">
            <div class="box-header with-border"><h3 class="box-title">Leads</h3><a href="<?= base_url('leads') ?>" class="btn btn-xs btn-primary pull-right">All Leads</a></div>
            <div class="box-body">
                <table class="table table-condensed">
                    <thead><tr><th>Title</th><th>Stage</th><th>Value</th><th>Date</th></tr></thead>
                    <tbody id="customer-leads-body"><tr><td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i></td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</section>

<script>
var cid = <?= (int)$customer['id'] ?>;
// Load orders
$.getJSON(BASE_URL+'orders/datatable?customer_id='+cid+'&length=5&start=0&draw=1', function(res) {
    var html = '';
    $.each(res.data||[], function(i,r) {
        html += '<tr><td>'+CRM.esc(r[1])+'</td><td>'+CRM.esc(r[4]||'-')+'</td><td>'+CRM.esc(r[2]||'-')+'</td><td>'+CRM.esc(r[7]||'-')+'</td></tr>';
    });
    $('#customer-orders-body').html(html||'<tr><td colspan="4" class="text-center text-muted">No orders</td></tr>');
});
// Load leads
$.getJSON(BASE_URL+'leads/datatable?customer_id='+cid+'&length=5&start=0&draw=1', function(res) {
    var html = '';
    $.each(res.data||[], function(i,r) {
        html += '<tr><td>'+CRM.esc(r[1])+'</td><td>'+CRM.esc(r[3]||'-')+'</td><td>'+CRM.esc(r[6]||'-')+'</td><td>'+CRM.esc(r[9]||'-')+'</td></tr>';
    });
    $('#customer-leads-body').html(html||'<tr><td colspan="4" class="text-center text-muted">No leads</td></tr>');
});
</script>
