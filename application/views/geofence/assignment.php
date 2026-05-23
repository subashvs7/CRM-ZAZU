<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-users text-emerald-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Zone Assignment</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('geofence') ?>" class="hover:text-blue-600 transition-colors">Geofence</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Assignment</span>
            </nav>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
        <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
            <i class="fa fa-users text-emerald-600 text-sm"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-gray-800">Assign Zones to Staff</h3>
            <p class="text-xs text-gray-400 mt-0.5">Zone assignment controls geofence scope per staff member</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Zone Name</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Type</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Auto Check-in</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            <?php foreach($zones as $z): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-semibold text-gray-700"><?= esc_html($z['name']) ?></td>
                <td class="px-5 py-3 text-gray-500 capitalize"><?= esc_html($z['zone_type']) ?></td>
                <td class="px-5 py-3">
                    <?php if($z['auto_checkin']): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-lg bg-green-100 text-green-700">Yes</span>
                    <?php else: ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-500">No</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
