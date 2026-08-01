<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-800">My Dashboard</h1>
        <p class="text-sm text-gray-400 mt-0.5"><?= date('l, d F Y') ?></p>
    </div>
    <a href="<?= base_url('attendance/punch') ?>"
       class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-xl hover:bg-green-700 transition-colors shadow-sm">
        <i class="fa fa-sign-in"></i> Punch In / Out
    </a>
</div>

<!-- KPI Row -->
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <a href="<?= base_url('visits/history') ?>"
       class="group relative bg-gradient-to-br from-cyan-500 to-cyan-700 text-white rounded-2xl p-5 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-0.5 overflow-hidden block">
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fa fa-map-signs text-white text-lg"></i>
                </div>
                <span class="text-cyan-200 text-xs font-medium">Today</span>
            </div>
            <p class="text-3xl font-extrabold tracking-tight" id="kpi-visits-today">—</p>
            <p class="text-cyan-100 text-sm mt-1 font-medium">Visits Today</p>
        </div>
        <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-white/5 rounded-full"></div>
    </a>

    <a href="<?= base_url('visits') ?>"
       class="group relative bg-gradient-to-br from-emerald-500 to-green-700 text-white rounded-2xl p-5 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-0.5 overflow-hidden block">
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fa fa-calendar text-white text-lg"></i>
                </div>
                <span class="text-green-200 text-xs font-medium">This month</span>
            </div>
            <p class="text-3xl font-extrabold tracking-tight" id="kpi-visits-month">—</p>
            <p class="text-green-100 text-sm mt-1 font-medium">Visits This Month</p>
        </div>
        <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-white/5 rounded-full"></div>
    </a>

    <a href="<?= base_url('leads') ?>"
       class="group relative bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-2xl p-5 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-0.5 overflow-hidden block">
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fa fa-filter text-white text-lg"></i>
                </div>
                <span class="text-amber-200 text-xs font-medium">Active</span>
            </div>
            <p class="text-3xl font-extrabold tracking-tight" id="kpi-my-leads">—</p>
            <p class="text-amber-100 text-sm mt-1 font-medium">My Leads</p>
        </div>
        <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-white/5 rounded-full"></div>
    </a>

    <a href="<?= base_url('orders') ?>"
       class="group relative bg-gradient-to-br from-violet-500 to-purple-700 text-white rounded-2xl p-5 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-0.5 overflow-hidden block">
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fa fa-shopping-cart text-white text-lg"></i>
                </div>
                <span class="text-purple-200 text-xs font-medium">All time</span>
            </div>
            <p class="text-3xl font-extrabold tracking-tight" id="kpi-my-orders">—</p>
            <p class="text-purple-100 text-sm mt-1 font-medium">My Orders</p>
        </div>
        <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-white/5 rounded-full"></div>
    </a>
</div>

<!-- Main Content Row -->
<div class="grid grid-cols-1 xl:grid-cols-5 gap-4">

    <!-- Today's Attendance Card -->
    <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fa fa-clock-o text-blue-600 text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Today's Attendance</h3>
                    <p class="text-xs text-gray-400"><?= date('D, d M Y') ?></p>
                </div>
            </div>
            <a href="<?= base_url('attendance') ?>" class="text-xs text-blue-600 hover:underline">View all</a>
        </div>
        <div class="p-6 text-center" id="attendance-box">
            <div class="text-gray-300">
                <i class="fa fa-spinner fa-spin fa-2x"></i>
                <p class="text-sm mt-3">Loading...</p>
            </div>
        </div>
    </div>

    <!-- Planned Visits -->
    <div class="xl:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fa fa-list text-purple-600 text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Planned Visits</h3>
                    <p class="text-xs text-gray-400">Your schedule</p>
                </div>
            </div>
            <a href="<?= base_url('visits') ?>"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fa fa-plus"></i> Plan Visit
            </a>
        </div>
        <div class="border-b border-gray-100 px-6 pt-2 flex items-center gap-6">
            <button class="dash-visit-tab active text-sm font-semibold text-blue-600 border-b-2 border-blue-600 pb-2" data-target="today">Today</button>
            <button class="dash-visit-tab text-sm font-semibold text-gray-500 hover:text-gray-700 pb-2 border-b-2 border-transparent" data-target="upcoming">Upcoming</button>
        </div>
        <div class="divide-y divide-gray-50 flex-1 overflow-y-auto" style="max-height: 400px;" id="todays-plans-container">
            <div class="p-8 text-center text-gray-300">
                <i class="fa fa-spinner fa-spin fa-2x"></i>
                <p class="text-sm mt-3">Loading plans...</p>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    $.getJSON(BASE_URL + 'dashboard/kpi_data', function(res) {
        var d = res.data;
        $('#kpi-visits-today').text(d.my_visits_today || 0);
        $('#kpi-visits-month').text(d.my_visits_month || 0);
        $('#kpi-my-leads').text(d.my_leads || 0);
        $('#kpi-my-orders').text(d.my_orders || 0);

        /* Attendance box */
        var attHtml = '';
        if (d.today_attendance === 'not_punched') {
            attHtml =
                '<div class="py-2">' +
                '<div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">' +
                '<i class="fa fa-exclamation-circle text-amber-500 text-3xl"></i></div>' +
                '<p class="text-gray-700 font-semibold text-base">Not Punched In</p>' +
                '<p class="text-gray-400 text-xs mt-1 mb-4">You have not recorded attendance yet</p>' +
                '<a href="'+BASE_URL+'attendance/punch" class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-xl hover:bg-green-700 transition-colors shadow-sm">' +
                '<i class="fa fa-sign-in"></i> Punch In Now</a></div>';
        } else {
            attHtml =
                '<div class="py-2">' +
                '<div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">' +
                '<i class="fa fa-check-circle text-green-500 text-3xl"></i></div>' +
                '<p class="text-gray-700 font-semibold text-base mb-2">Attendance Recorded</p>' +
                '<div class="inline-block mb-3">' + CRM.att_badge(d.today_attendance) + '</div>' +
                '<div class="grid grid-cols-2 gap-3 mt-3 text-left">' +
                '<div class="bg-green-50 rounded-xl p-3 text-center">' +
                '<p class="text-xs text-gray-400 mb-1">Punch In</p>' +
                '<p class="text-sm font-bold text-green-700">' + CRM.esc(d.punch_in_at || '—') + '</p>' +
                '</div>' +
                '<div class="bg-red-50 rounded-xl p-3 text-center">' +
                '<p class="text-xs text-gray-400 mb-1">Punch Out</p>' +
                '<p class="text-sm font-bold text-red-600">' + CRM.esc(d.punch_out_at || 'Not yet') + '</p>' +
                '</div></div></div>';
        }
        $('#attendance-box').html(attHtml);

        /* Planned visits */
        var allPlans = d.todays_plans || [];
        var todayStr = new Date().toISOString().split('T')[0];

        function renderPlans(tab) {
            var filtered = allPlans.filter(function(p) {
                if (tab === 'today') return p.planned_date === todayStr;
                return p.planned_date !== todayStr;
            });
            
            if (filtered.length === 0) {
                $('#todays-plans-container').html(
                    '<div class="p-10 text-center">' +
                    '<div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">' +
                    '<i class="fa fa-calendar-o text-gray-300 text-2xl"></i></div>' +
                    '<p class="text-sm font-medium text-gray-500">No visits planned</p>' +
                    '<p class="text-xs text-gray-400 mt-1">Click "Plan Visit" to schedule your first visit</p>' +
                    '</div>'
                );
            } else {
                var html = '';
                $.each(filtered, function(i, p) {
                    var btnHtml = '';
                    if (p.open_visit_log_id) {
                        btnHtml = '<a href="' + BASE_URL + 'visits" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs bg-red-100 text-red-700 font-bold rounded-lg hover:bg-red-200 transition-colors flex-shrink-0"><i class="fa fa-sign-out"></i> Check Out</a>';
                    } else {
                        if (p.planned_date > todayStr) {
                            btnHtml = '<button onclick="alert(\'You can only check in on the scheduled date.\')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs bg-gray-100 text-gray-400 font-bold rounded-lg cursor-not-allowed flex-shrink-0"><i class="fa fa-map-marker"></i> Check In</button>';
                        } else {
                            btnHtml = '<a href="' + BASE_URL + 'visits" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs bg-green-100 text-green-700 font-bold rounded-lg hover:bg-green-200 transition-colors flex-shrink-0"><i class="fa fa-map-marker"></i> Check In</a>';
                        }
                    }

                    var dateLabel = p.planned_date === todayStr ? 'Today' : p.planned_date;
                    html +=
                        '<div class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">' +
                        '<div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center flex-shrink-0">' +
                        CRM.esc((p.planned_time && p.planned_time.length >= 5 ? p.planned_time.substring(0, 5) : '?')) +
                        '</div>' +
                        '<div class="flex-1 min-w-0">' +
                        '<p class="text-sm font-semibold text-gray-800 truncate">' + CRM.esc(p.customer_name) + '</p>' +
                        '<p class="text-xs text-gray-500 mt-0.5"><span class="font-medium">' + CRM.esc(dateLabel) + ' at ' + CRM.esc(p.planned_time || 'Anytime') + '</span> &bull; <span class="text-gray-400">' + CRM.esc(p.purpose || 'No purpose') + '</span></p>' +
                        '</div>' + btnHtml + '</div>';
                });
                $('#todays-plans-container').html(html);
            }
        }

        renderPlans('today');
        
        $('.dash-visit-tab').on('click', function() {
            $('.dash-visit-tab').removeClass('active text-blue-600 border-blue-600').addClass('text-gray-500 border-transparent hover:text-gray-700');
            $(this).removeClass('text-gray-500 border-transparent hover:text-gray-700').addClass('active text-blue-600 border-blue-600');
            renderPlans($(this).data('target'));
        });
    });
});
</script>
