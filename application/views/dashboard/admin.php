<!-- KPI Row -->
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <a href="<?= base_url('customers') ?>"
       class="group relative bg-gradient-to-br from-blue-500 to-blue-700 text-white rounded-2xl p-5 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-0.5 overflow-hidden block">
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fa fa-building-o text-white text-lg"></i>
                </div>
                <span class="text-blue-200 text-xs font-medium">All time</span>
            </div>
            <p class="text-3xl font-extrabold tracking-tight" id="kpi-customers">—</p>
            <p class="text-blue-100 text-sm mt-1 font-medium">Total Customers</p>
        </div>
        <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-white/5 rounded-full"></div>
    </a>

    <a href="<?= base_url('leads') ?>"
       class="group relative bg-gradient-to-br from-emerald-500 to-green-700 text-white rounded-2xl p-5 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-0.5 overflow-hidden block">
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fa fa-filter text-white text-lg"></i>
                </div>
                <span class="text-green-200 text-xs font-medium">Active</span>
            </div>
            <p class="text-3xl font-extrabold tracking-tight" id="kpi-leads">—</p>
            <p class="text-green-100 text-sm mt-1 font-medium">Active Leads</p>
        </div>
        <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-white/5 rounded-full"></div>
    </a>

    <a href="<?= base_url('orders') ?>"
       class="group relative bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-2xl p-5 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-0.5 overflow-hidden block">
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fa fa-shopping-cart text-white text-lg"></i>
                </div>
                <span class="text-amber-200 text-xs font-medium">This month</span>
            </div>
            <p class="text-3xl font-extrabold tracking-tight" id="kpi-orders">—</p>
            <p class="text-amber-100 text-sm mt-1 font-medium">Total Orders</p>
        </div>
        <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-white/5 rounded-full"></div>
    </a>

    <a href="<?= base_url('orders/approval') ?>"
       class="group relative bg-gradient-to-br from-red-500 to-rose-700 text-white rounded-2xl p-5 shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-0.5 overflow-hidden block">
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fa fa-clock-o text-white text-lg"></i>
                </div>
                <span class="text-red-200 text-xs font-medium">Needs action</span>
            </div>
            <p class="text-3xl font-extrabold tracking-tight" id="kpi-pending">—</p>
            <p class="text-red-100 text-sm mt-1 font-medium">Pending Approvals</p>
        </div>
        <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-white/5 rounded-full"></div>
    </a>
</div>

<!-- Charts row -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
    <!-- Revenue chart -->
    <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-800">Monthly Revenue</h3>
                <p class="text-xs text-gray-400 mt-0.5">Current month daily breakdown</p>
            </div>
            <span class="px-2.5 py-1 bg-blue-50 text-blue-600 text-xs font-semibold rounded-lg">
                <i class="fa fa-bar-chart mr-1"></i> Revenue
            </span>
        </div>
        <div class="p-4">
            <div id="revenue-chart" style="height:280px"></div>
        </div>
    </div>

    <!-- Lead Pipeline -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-800">Lead Pipeline</h3>
                <p class="text-xs text-gray-400 mt-0.5">By stage</p>
            </div>
            <a href="<?= base_url('leads/pipeline') ?>"
               class="px-2.5 py-1 bg-green-50 text-green-600 text-xs font-semibold rounded-lg hover:bg-green-100 transition-colors">
                Kanban <i class="fa fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="p-5" id="pipeline-summary">
            <div class="text-center py-6 text-gray-300"><i class="fa fa-spinner fa-spin fa-lg"></i></div>
        </div>
    </div>
</div>

<!-- Bottom row -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    <!-- Top Field Staff -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="fa fa-trophy text-amber-600 text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Top Field Staff</h3>
                    <p class="text-xs text-gray-400">This month by visits</p>
                </div>
            </div>
            <a href="<?= base_url('reports/staff_sales') ?>" class="text-xs text-blue-600 hover:underline">Full report</a>
        </div>
        <div class="p-4">
            <table class="w-full" id="top-staff-table">
                <thead>
                    <tr class="text-[11px] font-semibold text-gray-400 uppercase">
                        <th class="pb-2 text-left w-6">#</th>
                        <th class="pb-2 text-left">Staff</th>
                        <th class="pb-2 text-right">Visits</th>
                        <th class="pb-2 text-right">Leads</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50"></tbody>
            </table>
        </div>
    </div>

    <!-- Today's Activity -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fa fa-bolt text-purple-600 text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Today's Activity</h3>
                    <p class="text-xs text-gray-400"><?= date('D, d M Y') ?></p>
                </div>
            </div>
            <a href="<?= base_url('tracking/live') ?>"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                <i class="fa fa-map-marker"></i> Live Map
            </a>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="bg-blue-50 rounded-xl p-4 text-center">
                    <p class="text-3xl font-extrabold text-blue-600 leading-none" id="visits-today">—</p>
                    <p class="text-xs text-blue-500 font-semibold mt-2 uppercase tracking-wide">Visits Today</p>
                </div>
                <div class="bg-green-50 rounded-xl p-4 text-center">
                    <p class="text-3xl font-extrabold text-green-600 leading-none" id="visits-month">—</p>
                    <p class="text-xs text-green-500 font-semibold mt-2 uppercase tracking-wide">This Month</p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-400 mt-2">
                <i class="fa fa-info-circle"></i>
                <span>Click Live Map to see real-time staff locations.</span>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
$(function(){
    $.getJSON(BASE_URL+'dashboard/kpi_data',function(res){
        var d=res.data;
        $('#kpi-customers').text(d.total_customers||0);
        $('#kpi-leads').text(d.total_leads||0);
        $('#kpi-orders').text(d.total_orders||0);
        $('#kpi-pending').text(d.pending_orders||0);
        $('#visits-today').text(d.visits_today||0);
        $('#visits-month').text(d.visits_month||0);

        // Revenue chart
        var days=[],amounts=[];
        $.each(d.monthly_orders||[],function(i,r){ days.push(r.d); amounts.push(((r.total||0)/100).toFixed(2)); });
        new ApexCharts(document.getElementById('revenue-chart'),{
            chart:{type:'area',height:280,toolbar:{show:false},fontFamily:'inherit'},
            series:[{name:'Revenue (₹)',data:amounts}],
            xaxis:{categories:days,labels:{style:{fontSize:'11px',colors:'#94a3b8'}}},
            yaxis:{labels:{formatter:function(v){return '₹'+parseFloat(v).toLocaleString('en-IN');},style:{fontSize:'11px',colors:'#94a3b8'}}},
            colors:['#2563eb'],
            stroke:{curve:'smooth',width:2.5},
            fill:{type:'gradient',gradient:{shadeIntensity:1,opacityFrom:0.3,opacityTo:0.02,stops:[0,90,100]}},
            dataLabels:{enabled:false},
            grid:{borderColor:'#f1f5f9',strokeDashArray:3},
            tooltip:{y:{formatter:function(v){return '₹'+parseFloat(v).toLocaleString('en-IN');}}}
        }).render();

        // Pipeline stages
        var stageColors={new:'#94a3b8',contacted:'#06b6d4',qualified:'#3b82f6',proposal:'#f59e0b',negotiation:'#f97316',won:'#22c55e',lost:'#ef4444'};
        var html='';
        $.each(d.lead_pipeline||[],function(i,r){
            var pct=d.total_leads>0?Math.min(100,Math.round(r.cnt/d.total_leads*100)):0;
            var col=stageColors[r.lead_status]||'#94a3b8';
            html+='<div class="mb-3.5">' +
                '<div class="flex items-center justify-between mb-1.5">' +
                '<span class="text-xs font-semibold text-gray-600 capitalize">'+CRM.esc(r.lead_status)+'</span>' +
                '<span class="text-xs font-bold text-gray-800">'+r.cnt+' <span class="text-gray-400 font-normal">('+pct+'%)</span></span>' +
                '</div>' +
                '<div class="bg-gray-100 rounded-full h-2">' +
                '<div class="h-2 rounded-full transition-all" style="width:'+pct+'%;background:'+col+'"></div>' +
                '</div></div>';
        });
        $('#pipeline-summary').html(html||'<p class="text-sm text-gray-400 text-center py-6">No pipeline data</p>');

        // Top staff
        var rows='';
        $.each(d.top_staff||[],function(i,r){
            var medals=['🥇','🥈','🥉'];
            rows+='<tr>' +
                '<td class="py-2.5 pr-2">' +
                (i<3 ? '<span class="text-base">'+medals[i]+'</span>' : '<span class="text-xs text-gray-400 font-bold">'+( i+1)+'</span>') +
                '</td>' +
                '<td class="py-2.5"><div class="flex items-center gap-2">' +
                '<div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-[11px] font-bold flex items-center justify-center flex-shrink-0">'+CRM.esc((r.name||'?')[0].toUpperCase())+'</div>' +
                '<span class="text-[13px] font-medium text-gray-700">'+CRM.esc(r.name)+'</span>' +
                '</div></td>' +
                '<td class="py-2.5 text-right font-bold text-blue-600 text-sm">'+r.visit_count+'</td>' +
                '<td class="py-2.5 text-right text-gray-500 text-sm">'+(r.lead_count||0)+'</td>' +
                '</tr>';
        });
        $('#top-staff-table tbody').html(rows||'<tr><td colspan="4" class="py-8 text-center text-gray-400 text-sm">No data yet</td></tr>');
    });
});
</script>
