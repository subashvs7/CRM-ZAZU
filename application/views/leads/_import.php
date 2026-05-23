<section class="content-header">
    <h1>Import Leads <small>CSV upload</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('leads') ?>">Leads</a></li><li class="active">Import</li></ol>
</section>
<section class="content">
<div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-upload"></i> Import Leads via CSV</h3></div>
    <div class="box-body">
        <div class="alert alert-info"><i class="fa fa-info-circle"></i> CSV columns: <strong>title, customer_phone, source, lead_status, expected_value, expected_close_date</strong></div>
        <form id="import-form" method="POST" action="<?= base_url('leads/import_process') ?>" enctype="multipart/form-data">
            <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
            <div class="form-group"><label>Select CSV File</label><input type="file" name="csv_file" accept=".csv" required></div>
            <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Import</button>
        </form>
    </div>
</div>
</section>
<script>
$('#import-form').on('submit', function(e) {
    e.preventDefault();
    CRM.submit_form($(this), function(res) {
        CRM.toast('success', 'Import complete: ' + (res.data.imported || 0) + ' leads imported.');
        setTimeout(function() { window.location = BASE_URL + 'leads'; }, 1500);
    });
});
</script>
