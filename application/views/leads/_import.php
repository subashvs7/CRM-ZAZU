<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-upload text-blue-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Import Leads</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <a href="<?= base_url('leads') ?>" class="hover:text-blue-600 transition-colors">Leads</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Import</span>
            </nav>
        </div>
    </div>
    <a href="<?= base_url('leads') ?>"
       class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors self-start sm:self-auto">
        <i class="fa fa-arrow-left text-gray-500"></i> Back to Leads
    </a>
</div>

<div class="max-w-2xl space-y-4">

    <!-- Info Card -->
    <div class="flex items-start gap-3 p-4 bg-blue-50 border border-blue-200 rounded-2xl">
        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
            <i class="fa fa-info-circle text-blue-600 text-sm"></i>
        </div>
        <div>
            <p class="text-sm font-semibold text-blue-800">CSV Format Requirements</p>
            <p class="text-xs text-blue-600 mt-1">Your CSV file must have these columns in order:</p>
            <div class="flex flex-wrap gap-1.5 mt-2">
                <?php foreach(['title', 'customer_phone', 'source', 'lead_status', 'expected_value', 'expected_close_date'] as $col): ?>
                <code class="inline-flex items-center px-2 py-0.5 bg-blue-100 text-blue-800 rounded-md text-xs font-mono font-semibold"><?= $col ?></code>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Upload Form -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                <i class="fa fa-file-text-o text-emerald-600 text-sm"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-800">Upload CSV File</h3>
                <p class="text-xs text-gray-400">Select your prepared CSV file to import</p>
            </div>
        </div>
        <div class="p-6">
            <form id="import-form" method="POST" action="<?= base_url('leads/import_process') ?>" enctype="multipart/form-data">
                <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                <div class="mb-6">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Select CSV File *</label>
                    <div class="border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center hover:border-blue-300 transition-colors">
                        <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <i class="fa fa-cloud-upload text-gray-400 text-xl"></i>
                        </div>
                        <p class="text-sm text-gray-500 mb-2">Click to select or drag and drop your CSV</p>
                        <input type="file" name="csv_file" id="csv-file" accept=".csv" required
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        <p class="text-xs text-gray-400 mt-2">Only .csv files are accepted</p>
                    </div>
                </div>
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-emerald-600 text-white text-sm font-bold rounded-xl hover:bg-emerald-700 transition-colors shadow-sm">
                    <i class="fa fa-upload"></i> Start Import
                </button>
            </form>
        </div>
    </div>

    <!-- Tips -->
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <p class="text-xs font-bold text-amber-700 uppercase tracking-wide mb-3">
            <i class="fa fa-lightbulb-o mr-1"></i> Tips
        </p>
        <ul class="space-y-1.5 text-xs text-amber-700">
            <li class="flex items-start gap-2"><i class="fa fa-check-circle mt-0.5 flex-shrink-0"></i> Make sure the first row is a header row</li>
            <li class="flex items-start gap-2"><i class="fa fa-check-circle mt-0.5 flex-shrink-0"></i> <code>source</code> values: field, call, referral, online, walk_in</li>
            <li class="flex items-start gap-2"><i class="fa fa-check-circle mt-0.5 flex-shrink-0"></i> <code>lead_status</code> values: new, contacted, qualified, proposal, negotiation, won, lost</li>
            <li class="flex items-start gap-2"><i class="fa fa-check-circle mt-0.5 flex-shrink-0"></i> <code>expected_close_date</code> format: YYYY-MM-DD</li>
        </ul>
    </div>
</div>

<script>
$('#import-form').on('submit', function(e) {
    e.preventDefault();
    CRM.submit_form($(this), function(res) {
        CRM.toast('success', 'Import complete: ' + (res.data.imported || 0) + ' leads imported.');
        setTimeout(function() { window.location = BASE_URL + 'leads'; }, 1500);
    });
});
</script>
