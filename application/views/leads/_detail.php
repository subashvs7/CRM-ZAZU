<section class="content-header">
    <h1><?= esc_html($lead['title']) ?> <small><?= lead_status_badge($lead['lead_status']) ?></small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>">Home</a></li><li><a href="<?= base_url('leads') ?>">Leads</a></li><li class="active"><?= esc_html($lead['title']) ?></li></ol>
</section>
<section class="content">
<div class="row">
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">Lead Info</h3></div>
            <div class="box-body">
                <dl class="dl-horizontal">
                    <dt>Customer</dt><dd><?= esc_html($lead['customer_name']) ?></dd>
                    <dt>Phone</dt><dd><?= esc_html($lead['customer_phone']??'-') ?></dd>
                    <dt>Stage</dt><dd><?= lead_status_badge($lead['lead_status']) ?></dd>
                    <dt>Source</dt><dd><?= esc_html(ucfirst($lead['source'])) ?></dd>
                    <dt>Assigned</dt><dd><?= esc_html($lead['assigned_name']??'-') ?></dd>
                    <dt>Value</dt><dd><?= $lead['expected_value'] ? format_inr($lead['expected_value']) : '-' ?></dd>
                    <dt>Close Date</dt><dd><?= $lead['expected_close_date'] ?? '-' ?></dd>
                    <dt>Record Status</dt><dd><?= status_badge($lead['status']) ?></dd>
                </dl>
                <?php if($lead['description']): ?>
                <hr><p><?= nl2br(esc_html($lead['description'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="box box-warning">
            <div class="box-header with-border"><h3 class="box-title">Update Stage</h3></div>
            <div class="box-body">
                <form id="activity-form" method="POST" action="<?= base_url('leads/add_activity') ?>">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
                    <div class="form-group"><label>Activity Type</label>
                        <select name="activity_type" class="form-control">
                            <option value="call">Call</option><option value="email">Email</option><option value="visit">Visit</option><option value="note">Note</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Move Stage To</label>
                        <select name="stage" class="form-control">
                            <option value="">-- No Change --</option>
                            <option value="new">New</option><option value="contacted">Contacted</option><option value="qualified">Qualified</option><option value="proposal">Proposal</option><option value="negotiation">Negotiation</option><option value="won">Won</option><option value="lost">Lost</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Notes</label><textarea name="notes" class="form-control" rows="3"></textarea></div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-plus"></i> Log Activity</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="box box-info">
            <div class="box-header with-border"><h3 class="box-title">Activity Timeline</h3></div>
            <div class="box-body" id="activity-timeline">
                <?php foreach ($activities as $a): ?>
                <div class="timeline-item" style="border-left:3px solid #3c8dbc;padding:8px 12px;margin-bottom:12px">
                    <span class="label label-info"><?= esc_html(ucfirst($a['activity_type'])) ?></span>
                    <strong class="ml-1"><?= esc_html($a['user_name']) ?></strong>
                    <small class="text-muted ml-2"><?= time_ago($a['occurred_at']) ?></small>
                    <p class="mb-0 mt-1"><?= nl2br(esc_html($a['notes']??'')) ?></p>
                </div>
                <?php endforeach; ?>
                <?php if (!$activities): ?><p class="text-muted">No activities yet.</p><?php endif; ?>
            </div>
        </div>
    </div>
</div>
</section>
<script>
$('#activity-form').on('submit', function(e) {
    e.preventDefault();
    CRM.submit_form($(this), function() {
        setTimeout(function() { location.reload(); }, 800);
    });
});
</script>
