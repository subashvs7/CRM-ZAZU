<section class="content-header">
    <h1>Customers <small>Manage customer accounts</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>">Home</a></li><li class="active">Customers</li></ol>
</section>
<section class="content">
<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-building-o"></i> Customers</h3>
        <div class="box-tools">
            <button class="btn btn-success btn-sm" id="btn-add-customer"><i class="fa fa-plus"></i> Add Customer</button>
        </div>
    </div>
    <div class="box-body">
        <table id="customers-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Email</th><th>City</th><th>State</th><th>Assigned To</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="customer-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title" id="customer-modal-title">Customer</h4></div>
            <div class="modal-body">
                <form id="customer-form" method="POST" action="<?= base_url('customers/save') ?>">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="customer-id" value="0">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group"><label>Name *</label><input type="text" name="name" class="form-control" required></div>
                            <div class="form-group"><label>Phone *</label><input type="text" name="phone" class="form-control" required></div>
                            <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control"></div>
                            <div class="form-group"><label>GST Number</label><input type="text" name="gst_number" class="form-control"></div>
                            <?php if($is_manager): ?>
                            <div class="form-group"><label>Assigned To</label>
                                <select name="assigned_to" class="form-control select2">
                                    <option value="">-- Unassigned --</option>
                                    <?php foreach($staff as $s): ?><option value="<?= $s['id'] ?>"><?= esc_html($s['name']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group"><label>Address</label><textarea name="address" class="form-control" rows="3"></textarea></div>
                            <div class="form-group"><label>City</label><input type="text" name="city" class="form-control"></div>
                            <div class="form-group"><label>State</label><input type="text" name="state" class="form-control"></div>
                            <div class="form-group"><label>Pincode</label><input type="text" name="pincode" class="form-control"></div>
                            <!-- Location Picker -->
                            <div class="form-group location-picker-group">
                                <label>Location <small class="text-muted">(GPS coordinates)</small></label>
                                <div class="row">
                                    <div class="col-xs-6">
                                        <input type="text" name="latitude" id="cust-lat"
                                               class="form-control" placeholder="Latitude" step="any">
                                    </div>
                                    <div class="col-xs-6">
                                        <input type="text" name="longitude" id="cust-lng"
                                               class="form-control" placeholder="Longitude" step="any">
                                    </div>
                                </div>
                                <button type="button"
                                        class="btn btn-sm btn-primary btn-detect-location"
                                        style="margin-top:6px;width:100%"
                                        data-lat="[name='latitude']"
                                        data-lng="[name='longitude']"
                                        data-feedback="#cust-location-feedback"
                                        data-map="#cust-map-preview">
                                    <i class="fa fa-crosshairs"></i> Detect My Location
                                </button>
                                <div id="cust-location-feedback" style="margin-top:5px;font-size:12px"></div>
                                <div id="cust-map-preview" style="display:none;margin-top:8px;border-radius:4px;overflow:hidden"></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group"><label>Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btn-save-customer"><i class="fa fa-save"></i> Save</button>
            </div>
        </div>
    </div>
</div>
</section>
