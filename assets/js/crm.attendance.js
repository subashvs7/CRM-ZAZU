/**
 * Attendance module JS
 * Single source of truth for #att-table DataTable init.
 * Loaded via page_js='attendance' in footer — runs AFTER all plugins are ready.
 */
$(function() {
    // Attendance index page — init only if NOT already initialised
    if ($('#att-table').length && !$.fn.DataTable.isDataTable('#att-table')) {
        window.mainTable = $('#att-table').DataTable({
            processing:  true,
            serverSide:  true,
            ajax: {
                url:  BASE_URL + 'attendance/datatable',
                data: function(d) { d.status_filter = window.currentStatusFilter || ''; }
            },
            columns: [
                {data:0}, {data:1}, {data:2}, {data:3}, {data:4},
                {data:5}, {data:6}, {data:7}, {data:8}, {data:9, orderable:false}
            ],
            order: [[1, 'desc']]
        });
        // Status-tab reload is handled globally by crm.core.js using window.mainTable
    }
});
