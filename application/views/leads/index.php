<section class="content-header">
    <h1>Leads <small>Pipeline management</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>">Home</a></li><li class="active">Leads</li></ol>
</section>
<section class="content">
<div style="margin-bottom:10px">
    <a href="<?= base_url('leads/pipeline') ?>" class="btn btn-info btn-sm"><i class="fa fa-columns"></i> Pipeline View</a>
    <button class="btn btn-success btn-sm" id="btn-add-lead"><i class="fa fa-plus"></i> Add Lead</button>
    <a href="<?= base_url('leads/import') ?>" class="btn btn-default btn-sm"><i class="fa fa-upload"></i> Import</a>
</div>
<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>
<div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-filter"></i> Leads</h3></div>
    <div class="box-body">
        <table id="leads-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Title</th><th>Customer</th><th>Stage</th><th>Source</th><th>Assigned</th><th>Value</th><th>Close Date</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="lead-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Lead</h4></div>
            <div class="modal-body">
                <form id="lead-form" method="POST" action="<?= base_url('leads/save') ?>">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="lead-id" value="0">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group"><label>Title *</label><input type="text" name="title" class="form-control" required></div>
                            <div class="form-group"><label>Customer *</label>
                                <select name="customer_id" class="form-control select2" required>
                                    <option value="">-- Select Customer --</option>
                                    <?php foreach($customers as $c): ?><option value="<?= $c['id'] ?>"><?= esc_html($c['name']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group"><label>Source</label>
                                <select name="source" class="form-control">
                                    <option value="field">Field</option><option value="call">Call</option><option value="referral">Referral</option><option value="online">Online</option><option value="walk_in">Walk In</option>
                                </select>
                            </div>
                            <div class="form-group"><label>Stage</label>
                                <select name="lead_status" class="form-control">
                                    <option value="new">New</option><option value="contacted">Contacted</option><option value="qualified">Qualified</option><option value="proposal">Proposal</option><option value="negotiation">Negotiation</option><option value="won">Won</option><option value="lost">Lost</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <?php if($is_manager): ?>
                            <div class="form-group"><label>Assign To</label>
                                <select name="assigned_to" class="form-control select2">
                                    <option value="">-- Self --</option>
                                    <?php foreach($staff as $s): ?><option value="<?= $s['id'] ?>"><?= esc_html($s['name']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            <div class="form-group"><label>Expected Value (₹)</label><input type="number" step="0.01" name="expected_value" class="form-control"></div>
                            <div class="form-group"><label>Expected Close Date</label><input type="text" name="expected_close_date" class="form-control datepicker"></div>
                            <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="4"></textarea></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btn-save-lead"><i class="fa fa-save"></i> Save</button>
            </div>
        </div>
    </div>
</div>
</section>
