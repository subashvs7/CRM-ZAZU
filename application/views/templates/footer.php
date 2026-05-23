    </div><!-- /page content -->
</div><!-- /#main -->

<script>
/* ─── CSRF auto-refresh ──────────────────────────────────────────── */
$(document).ajaxComplete(function(e,xhr){
    var t=xhr.getResponseHeader('X-CSRF-Token');
    if(t){ CI3_CSRF_HASH=t; $('[name="'+CI3_CSRF_NAME+'"]').val(t); }
});

/* ─── Sidebar toggle ─────────────────────────────────────────────── */
function toggleSidebar(){
    if(window.innerWidth < 1024){
        document.body.classList.toggle('sidebar-open');
    } else {
        document.body.classList.toggle('sidebar-collapsed');
    }
}
function closeSidebar(){
    document.body.classList.remove('sidebar-open');
}

/* ─── Submenu accordion ──────────────────────────────────────────── */
function toggleSubmenu(id, btn){
    var el=document.getElementById(id);
    if(!el) return;
    el.classList.toggle('open');
    var arrow=btn ? btn.querySelector('.sub-arrow') : null;
    if(arrow) arrow.style.transform=el.classList.contains('open')?'rotate(90deg)':'';
}

/* ─── Notification + User dropdowns ─────────────────────────────── */
function toggleNotifDropdown(){
    var n=document.getElementById('notif-dropdown');
    var u=document.getElementById('user-dropdown');
    if(u) u.classList.add('hidden');
    if(n) n.classList.toggle('hidden');
}
function toggleUserMenu(){
    var d=document.getElementById('user-dropdown');
    var n=document.getElementById('notif-dropdown');
    if(n) n.classList.add('hidden');
    if(d) d.classList.toggle('hidden');
}

/* close dropdowns on outside click */
document.addEventListener('click',function(e){
    if(!e.target.closest('#notif-wrapper')){
        var d=document.getElementById('notif-dropdown'); if(d) d.classList.add('hidden');
    }
    if(!e.target.closest('#user-menu-wrapper')){
        var d=document.getElementById('user-dropdown'); if(d) d.classList.add('hidden');
    }
});

/* ─── Notification polling ───────────────────────────────────────── */
function loadNotifications(){
    $.getJSON(BASE_URL+'notifications/count',function(res){
        var d=res.data||{};
        var $cnt=$('#notif-count');
        if(d.count>0){ $cnt.text(d.count).removeClass('hidden'); } else { $cnt.addClass('hidden'); }
        if(d.items && d.items.length){
            var html='';
            $.each(d.items,function(i,n){
                html+='<li class="notif-item'+(n.is_read?'':' unread')+'">' +
                    '<a href="#" class="block mark-notif-read" data-id="'+n.id+'">' +
                    '<div class="flex items-start gap-2 py-0.5">' +
                    '<i class="fa fa-circle text-'+(n.is_read?'gray-300':'amber-400')+' mt-1 text-[8px] flex-shrink-0"></i>' +
                    '<div class="min-w-0"><p class="text-[13px] text-gray-800 font-medium leading-snug truncate">'+CRM.esc(n.title)+'</p>' +
                    '<p class="notif-time">'+CRM.time_ago(n.created_at)+'</p></div>' +
                    '</div></a></li>';
            });
            $('#notif-list').html(html);
        }
    });
}
loadNotifications();
setInterval(loadNotifications,60000);

$(document).on('click','.mark-notif-read',function(e){
    e.preventDefault();
    $.post(BASE_URL+'notifications/mark_read',{id:$(this).data('id'),[CI3_CSRF_NAME]:CI3_CSRF_HASH},function(){ loadNotifications(); });
});

/* ─── Modal containers for CRM.load_modal() ─────────────────────── */
$(function(){
    if(!$('#ajax-modal').length)
        $('body').append('<div class="modal fade" id="ajax-modal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"></div></div></div>');
    if(!$('#ajax-modal-lg').length)
        $('body').append('<div class="modal fade" id="ajax-modal-lg" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"></div></div></div>');
    CRM.init_plugins();
});
</script>

<?php if(isset($page_js)): ?>
<script src="<?= base_url('assets/js/crm.'.$page_js.'.js') ?>"></script>
<?php endif; ?>

<?php if($current_role==='field_staff'): ?>
<script>
(function(){
    'use strict';
    var PING_MS=<?= (int)get_setting('gps_ping_interval',600) ?>*1000;
    if(PING_MS<10000) PING_MS=600000;
    var GPS_OPTS={enableHighAccuracy:true,timeout:20000,maximumAge:60000};
    var lastPing=null, timer=null, denied=false;

    function dot(color,label,title){
        $('#gps-status-dot').css('background',color);
        $('#gps-status-label').text(label||'GPS');
        $('#gps-nav-item').attr('title',title||'');
    }

    function sendPing(lat,lng,acc,batt){
        $.post(BASE_URL+'gps/ping',{lat:lat,lng:lng,accuracy:acc||'',battery:batt!==null?batt:'',[CI3_CSRF_NAME]:CI3_CSRF_HASH})
        .done(function(r){
            if(r.status==='success'){
                lastPing=new Date();
                var t=lastPing.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit',hour12:false});
                dot('#16a34a','GPS ✓','Sent '+t+' · '+lat.toFixed(4)+', '+lng.toFixed(4));
            }
        }).fail(function(){ dot('#dc2626','GPS ✗','Ping failed'); });
    }

    function ping(){
        if(denied) return;
        dot('#f59e0b','GPS…','Acquiring location…');
        navigator.geolocation.getCurrentPosition(function(p){
            var lat=p.coords.latitude,lng=p.coords.longitude,acc=p.coords.accuracy;
            if(navigator.getBattery){
                navigator.getBattery().then(function(b){ sendPing(lat,lng,acc,Math.round(b.level*100)); }).catch(function(){ sendPing(lat,lng,acc,null); });
            } else { sendPing(lat,lng,acc,null); }
        },function(e){
            if(e.code===1){ denied=true; dot('#9ca3af','GPS','Permission denied'); clearInterval(timer); }
            else if(e.code===2){ dot('#dc2626','GPS ✗','No signal'); }
            else { dot('#dc2626','GPS ✗','Timeout'); }
        },GPS_OPTS);
    }

    $(function(){
        if(!navigator.geolocation){ dot('#9ca3af','GPS','Not supported'); return; }
        ping();
        timer=setInterval(ping,PING_MS);
        document.addEventListener('visibilitychange',function(){
            if(!document.hidden && lastPing && (Date.now()-lastPing)/1000>PING_MS/2000){
                clearInterval(timer); ping(); timer=setInterval(ping,PING_MS);
            }
        });
    });
})();
</script>
<?php endif; ?>

<?php if(isset($inline_js)): ?>
<script><?= $inline_js ?></script>
<?php endif; ?>

</body>
</html>
