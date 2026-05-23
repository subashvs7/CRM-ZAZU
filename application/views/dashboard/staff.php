<section class="content-header">
    <h1>My Dashboard <small>Field Staff</small></h1>
    <ol class="breadcrumb"><li class="active"><i class="fa fa-dashboard"></i> Dashboard</li></ol>
</section>
<section class="content">
<div class="row">
    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-aqua">
            <div class="inner"><h3 id="kpi-visits-today">-</h3><p>Visits Today</p></div>
            <div class="icon"><i class="fa fa-map-signs"></i></div>
            <a href="<?= base_url('visits/history') ?>" class="small-box-footer">History <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner"><h3 id="kpi-visits-month">-</h3><p>Visits This Month</p></div>
            <div class="icon"><i class="fa fa-calendar"></i></div>
            <a href="<?= base_url('visits') ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner"><h3 id="kpi-my-leads">-</h3><p>My Leads</p></div>
            <div class="icon"><i class="fa fa-filter"></i></div>
            <a href="<?= base_url('leads') ?>" class="small-box-footer">View <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-red">
            <div class="inner"><h3 id="kpi-my-orders">-</h3><p>My Orders</p></div>
            <div class="icon"><i class="fa fa-shopping-cart"></i></div>
            <a href="<?= base_url('orders') ?>" class="small-box-footer">View <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-clock-o"></i> Today's Attendance</h3>
                <a href="<?= base_url('attendance/punch') ?>" class="btn btn-xs btn-success pull-right"><i class="fa fa-sign-in"></i> Punch</a>
            </div>
            <div class="box-body text-center" id="attendance-box">
                <div class="text-muted"><i class="fa fa-spinner fa-spin"></i> Loading...</div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list"></i> Today's Planned Visits</h3>
                <a href="<?= base_url('visits') ?>" class="btn btn-xs btn-primary pull-right"><i class="fa fa-plus"></i> Plan Visit</a>
            </div>
            <div class="box-body">
                <table class="table table-condensed" id="todays-plans">
                    <thead><tr><th>Time</th><th>Customer</th><th>Action</th></tr></thead>
                    <tbody><tr><td colspan="3" class="text-center text-muted"><i class="fa fa-spinner fa-spin"></i></td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</section>

<script>
$(function() {
    $.getJSON(BASE_URL + 'dashboard/kpi_data', function(res) {
        var d = res.data;
        $('#kpi-visits-today').text(d.my_visits_today);
        $('#kpi-visits-month').text(d.my_visits_month);
        $('#kpi-my-leads').text(d.my_leads);
        $('#kpi-my-orders').text(d.my_orders);

        var attHtml = '';
        if (d.today_attendance === 'not_punched') {
            attHtml = '<div class="text-warning"><i class="fa fa-exclamation-circle fa-3x"></i><p class="mt-2">Not Punched In</p>' +
                '<a href="'+BASE_URL+'attendance/punch" class="btn btn-success btn-sm mt-1"><i class="fa fa-sign-in"></i> Punch In Now</a></div>';
        } else {
            attHtml = '<div class="text-success"><i class="fa fa-check-circle fa-3x"></i>' +
                '<p class="mt-2">'+CRM.att_badge(d.today_attendance)+'</p>' +
                '<small class="text-muted">In: '+CRM.esc(d.punch_in_at||'')+'</small></div>';
        }
        $('#attendance-box').html(attHtml);

        var tbody = '';
        $.each(d.todays_plans || [], function(i, p) {
            tbody += '<tr><td>'+(p.planned_time||'-')+'</td><td>'+CRM.esc(p.customer_name)+'</td>' +
                '<td><a href="'+BASE_URL+'visits/checkin?plan_id='+p.id+'" class="btn btn-xs btn-success"><i class="fa fa-check"></i> Check In</a></td></tr>';
        });
        if (!tbody) tbody = '<tr><td colspan="3" class="text-center text-muted">No visits planned for today</td></tr>';
        $('#todays-plans tbody').html(tbody);
    });
});
</script>
