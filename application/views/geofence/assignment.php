<section class="content-header">
    <h1>Zone Assignment</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('geofence') ?>">Geofence</a></li><li class="active">Assignment</li></ol>
</section>
<section class="content">
<div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title">Assign Zones to Staff</h3></div>
    <div class="box-body">
        <p class="text-muted">Zone assignment feature — assigns specific geofence zones to specific staff members.</p>
        <table class="table table-bordered">
            <thead><tr><th>Zone Name</th><th>Type</th><th>Auto Check-in</th></tr></thead>
            <tbody>
            <?php foreach($zones as $z): ?>
            <tr><td><?= esc_html($z['name']) ?></td><td><?= esc_html($z['zone_type']) ?></td><td><?= $z['auto_checkin']?'Yes':'No' ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</section>
