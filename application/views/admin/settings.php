<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-sliders text-slate-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Settings</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Settings</span>
            </nav>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
        <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center">
            <i class="fa fa-sliders text-slate-600 text-sm"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-gray-800">App Settings</h3>
            <p class="text-xs text-gray-400 mt-0.5">Application configuration</p>
        </div>
    </div>
    <div class="p-6">
        <form id="settings-form">
            <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-4 pb-2 border-b border-gray-100">General</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Company Name</label>
                            <input type="text" name="company_name" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= esc_html($settings['company_name']??'') ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Timezone</label>
                            <input type="text" name="timezone" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= esc_html($settings['timezone']??'Asia/Kolkata') ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Currency</label>
                            <input type="text" name="currency" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= esc_html($settings['currency']??'INR') ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Order Prefix</label>
                            <input type="text" name="order_prefix" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= esc_html($settings['order_prefix']??'ORD') ?>">
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-4 pb-2 border-b border-gray-100">Tracking &amp; Attendance</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">GPS Ping Interval <span class="normal-case font-normal text-gray-400">(seconds)</span></label>
                            <input type="number" name="gps_ping_interval" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= esc_html($settings['gps_ping_interval']??'30') ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Face Match Threshold <span class="normal-case font-normal text-gray-400">(0–1)</span></label>
                            <input type="number" step="0.01" name="face_match_threshold" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= esc_html($settings['face_match_threshold']??'0.75') ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Attendance Window <span class="normal-case font-normal text-gray-400">(hours)</span></label>
                            <input type="number" name="attendance_window_hours" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= esc_html($settings['attendance_window_hours']??'2') ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-8 pt-5 border-t border-gray-100">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                    <i class="fa fa-save"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$('#settings-form').on('submit', function(e){
    e.preventDefault();
    var $btn = $(this).find('[type=submit]'); CRM.btn_loading($btn);
    $.ajax({
        url: BASE_URL+'admin/save_settings', method:'POST',
        data: new FormData(this), processData:false, contentType:false,
        success: function(res){ CRM.toast(res.status==='success'?'success':'error', res.message); },
        complete: function(){ CRM.btn_reset($btn); }
    });
});
</script>
