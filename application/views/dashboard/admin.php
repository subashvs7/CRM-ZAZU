<!-- Content Header -->
<section class="content-header">
    <h1>Dashboard <small>Overview</small></h1>
    <ol class="breadcrumb"><li class="active"><i class="fa fa-dashboard"></i> Dashboard</li></ol>
</section>
<section class="content">

<!-- KPI Row -->
<div class="row" id="kpi-row">
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-aqua">
            <div class="inner"><h3 id="kpi-customers">-</h3><p>Total Customers</p></div>
            <div class="icon"><i class="fa fa-building-o"></i></div>
            <a href="<?= base_url('customers') ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner"><h3 id="kpi-leads">-</h3><p>Active Leads</p></div>
            <div class="icon"><i class="fa fa-filter"></i></div>
            <a href="<?= base_url('leads') ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner"><h3 id="kpi-orders">-</h3><p>Total Orders</p></div>
            <div class="icon"><i class="fa fa-shopping-cart"></i></div>
            <a href="<?= base_url('orders') ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
            <div class="inner"><h3 id="kpi-pending">-</h3><p>Pending Approvals</p></div>
            <div class="icon"><i class="fa fa-clock-o"></i></div>
            <a href="<?= base_url('orders/approval') ?>" class="small-box-footer">Review <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Monthly Revenue Chart -->
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bar-chart"></i> Monthly Revenue</h3>
            </div>
            <div class="box-body">
                <div id="revenue-chart" style="height:280px"></div>
            </div>
        </div>
    </div>
    <!-- Lead Pipeline -->
    <div class="col-md-4">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-filter"></i> Lead Pipeline</h3>
            </div>
            <div class="box-body" id="pipeline-summary"></div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top Staff -->
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-trophy"></i> Top Field Staff (This Month)</h3>
            </div>
            <div class="box-body">
                <table class="table table-striped" id="top-staff-table">
                    <thead><tr><th>#</th><th>Staff</th><th>Visits</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Visits Today -->
    <div class="col-md-6">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-map-signs"></i> Today's Activity</h3>
                <a href="<?= base_url('tracking/live') ?>" class="btn btn-xs btn-primary pull-right"><i class="fa fa-map-marker"></i> Live Map</a>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-6 text-center">
                        <h3 class="text-primary" id="visits-today">-</h3>
                        <p>Visits Today</p>
                    </div>
                    <div class="col-xs-6 text-center">
                        <h3 class="text-success" id="visits-month">-</h3>
                        <p>Visits This Month</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</section>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
$(function() {
    $.getJSON(BASE_URL + 'dashboard/kpi_data', function(res) {
        var d = res.data;
        $('#kpi-customers').text(d.total_customers);
        $('#kpi-leads').text(d.total_leads);
        $('#kpi-orders').text(d.total_orders);
        $('#kpi-pending').text(d.pending_orders);
        $('#visits-today').text(d.visits_today);
        $('#visits-month').text(d.visits_month);

        // Revenue chart
        var days = [], amounts = [];
        $.each(d.monthly_orders || [], function(i, r) {
            days.push(r.d); amounts.push((r.total/100).toFixed(2));
        });
        var chart = new ApexCharts(document.getElementById('revenue-chart'), {
            chart: { type: 'area', height: 280, toolbar: { show: false } },
            series: [{ name: 'Revenue (₹)', data: amounts }],
            xaxis: { categories: days },
            yaxis: { labels: { formatter: v => '₹' + parseFloat(v).toLocaleString('en-IN') } },
            colors: ['#3c8dbc'], stroke: { curve: 'smooth' },
            fill: { type: 'gradient' }
        });
        chart.render();

        // Pipeline summary
        var stages = {new:'default',contacted:'info',qualified:'primary',proposal:'warning',negotiation:'warning',won:'success',lost:'danger'};
        var html = '';
        $.each(d.lead_pipeline || [], function(i, r) {
            var cls = stages[r.lead_status] || 'default';
            html += '<div class="clearfix"><span class="label label-'+cls+' pull-left">'+r.lead_status+'</span>' +
                '<strong class="pull-right">'+r.cnt+'</strong></div><div class="progress xs"><div class="progress-bar progress-bar-'+cls+'" style="width:'+Math.min(100,(r.cnt/d.total_leads*100)).toFixed(0)+'%"></div></div>';
        });
        $('#pipeline-summary').html(html || '<p class="text-muted">No data</p>');

        // Top staff
        var tbody = '';
        $.each(d.top_staff || [], function(i, r) {
            tbody += '<tr><td>'+(i+1)+'</td><td>'+CRM.esc(r.name)+'</td><td><strong>'+r.visit_count+'</strong></td></tr>';
        });
        $('#top-staff-table tbody').html(tbody || '<tr><td colspan="3" class="text-center">No data</td></tr>');
    });
});
</script>
