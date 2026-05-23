<section class="content-header">
    <h1>Geofence Zones</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>">Home</a></li><li class="active">Geofence</li></ol>
</section>
<section class="content">
<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-circle-o"></i> Geofence Zones</h3>
        <div class="box-tools">
            <a href="<?= base_url('geofence/violations') ?>" class="btn btn-danger btn-sm"><i class="fa fa-exclamation-triangle"></i> Violations</a>
            <a href="<?= base_url('geofence/alert_rules') ?>" class="btn btn-warning btn-sm"><i class="fa fa-bell"></i> Alert Rules</a>
            <button class="btn btn-success btn-sm" id="btn-add-zone"><i class="fa fa-plus"></i> Add Zone</button>
        </div>
    </div>
    <div class="box-body">
        <table id="zones-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Name</th><th>Type</th><th>Radius</th><th>Auto Checkin</th><th>On Enter</th><th>On Exit</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="zone-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Geofence Zone</h4></div>
            <div class="modal-body">
                <form id="zone-form">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="zone-id" value="0">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group"><label>Name *</label><input type="text" name="name" class="form-control" required></div>
                            <div class="form-group"><label>Zone Type</label>
                                <select name="zone_type" class="form-control"><option value="customer">Customer</option><option value="office">Office</option><option value="restricted">Restricted</option><option value="territory">Territory</option></select>
                            </div>
                            <div class="form-group"><label>Customer</label>
                                <select name="customer_id" class="form-control select2">
                                    <option value="">-- None --</option>
                                    <?php foreach($customers as $c): ?><option value="<?= $c['id'] ?>"><?= esc_html($c['name']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Location Picker for zone center -->
                            <div class="form-group location-picker-group">
                                <label>Zone Center <small class="text-muted">(GPS coordinates)</small></label>
                                <div class="row">
                                    <div class="col-xs-6">
                                        <input type="text" name="center_lat" class="form-control" placeholder="Latitude">
                                    </div>
                                    <div class="col-xs-6">
                                        <input type="text" name="center_lng" class="form-control" placeholder="Longitude">
                                    </div>
                                </div>
                                <button type="button"
                                        class="btn btn-sm btn-primary btn-detect-location"
                                        style="margin-top:6px;width:100%"
                                        data-lat="[name='center_lat']"
                                        data-lng="[name='center_lng']"
                                        data-feedback="#geo-location-feedback"
                                        data-map="#geo-map-preview">
                                    <i class="fa fa-crosshairs"></i> Detect My Location
                                </button>
                                <div id="geo-location-feedback" style="margin-top:5px;font-size:12px"></div>
                                <div id="geo-map-preview" style="display:none;margin-top:8px;border-radius:4px;overflow:hidden"></div>
                            </div>
                            <div class="form-group"><label>Radius (meters)</label><input type="number" name="radius_meters" class="form-control" placeholder="e.g. 200"></div>
                            <div class="checkbox"><label><input type="checkbox" name="auto_checkin" value="1"> Auto Check-in</label></div>
                            <div class="checkbox"><label><input type="checkbox" name="alert_on_enter" value="1"> Alert on Enter</label></div>
                            <div class="checkbox"><label><input type="checkbox" name="alert_on_exit" value="1"> Alert on Exit</label></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btn-save-zone">Save</button>
            </div>
        </div>
    </div>
</div>
</section>
