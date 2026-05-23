/**
 * Dashboard module JS
 */
$(function() {
    // Attendance badge helper used by staff dashboard
    if (typeof CRM !== 'undefined') {
        CRM.att_badge = CRM.att_badge || function(s) {
            var m = { present: '<span class="label label-success">Present</span>', absent: '<span class="label label-danger">Absent</span>', half_day: '<span class="label label-warning">Half Day</span>', on_leave: '<span class="label label-info">On Leave</span>' };
            return m[s] || '<span class="label label-default">' + s + '</span>';
        };
    }
});
