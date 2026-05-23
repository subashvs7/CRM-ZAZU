<section class="content-header">
    <h1>Shift Calendar</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('shifts') ?>">Shifts</a></li><li class="active">Calendar</li></ol>
</section>
<section class="content">
<div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-calendar"></i> Shift Calendar</h3></div>
    <div class="box-body">
        <div id="shift-calendar"></div>
    </div>
</div>
</section>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script>
var cal = new FullCalendar.Calendar(document.getElementById('shift-calendar'), {
    initialView:'dayGridMonth',
    events:{url:BASE_URL+'shifts/calendar_data'},
    eventClick:function(info){ CRM.toast('info',info.event.title); }
});
cal.render();
</script>
