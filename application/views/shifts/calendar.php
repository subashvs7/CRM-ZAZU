<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-calendar text-indigo-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Shift Calendar</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('shifts') ?>" class="hover:text-blue-600 transition-colors">Shifts</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Calendar</span>
            </nav>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
            <i class="fa fa-calendar text-indigo-600 text-sm"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-gray-800">Shift Calendar</h3>
            <p class="text-xs text-gray-400 mt-0.5">Monthly shift schedule view</p>
        </div>
    </div>
    <div class="p-4">
        <div id="shift-calendar"></div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script>
var cal = new FullCalendar.Calendar(document.getElementById('shift-calendar'), {
    initialView: 'dayGridMonth',
    events: { url: BASE_URL+'shifts/calendar_data' },
    eventClick: function(info){ CRM.toast('info', info.event.title); }
});
cal.render();
</script>
