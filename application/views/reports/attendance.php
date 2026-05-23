<section class="content-header"><h1>Attendance Report</h1><ol class="breadcrumb"><li>Reports</li><li class="active">Attendance</li></ol></section>
<section class="content">
<?php include(APPPATH.'views/reports/_filter_bar.php'); ?>
<div class="box box-info"><div class="box-header with-border"><h3 class="box-title">Attendance Summary</h3></div>
<div class="box-body"><table class="table table-bordered"><thead><tr><th>Staff</th><th>Status</th><th>Days</th></tr></thead><tbody id="att-report-body"><tr><td colspan="3" class="text-center text-muted">Apply filter</td></tr></tbody></table></div></div>
</section>
<script>function loadReport(){$.getJSON(BASE_URL+'reports/attendance_data',{from:$('#from-date').val(),to:$('#to-date').val(),user_id:$('#staff-filter').val()||''},function(res){var html='';$.each(res.data||[],function(i,r){html+='<tr><td>'+CRM.esc(r.staff_name)+'</td><td>'+CRM.esc(r.attendance_status)+'</td><td>'+r.cnt+'</td></tr>';});$('#att-report-body').html(html||'<tr><td colspan="3" class="text-center text-muted">No data</td></tr>');});}
$('#btn-filter').click(loadReport);</script>
