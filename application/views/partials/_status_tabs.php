<ul class="nav nav-tabs" id="status-tabs" style="margin-bottom:15px">
    <li class="<?= !$sf ? 'active' : '' ?>"><a href="#" data-status="">All</a></li>
    <li class="<?= $sf==='active' ? 'active' : '' ?>"><a href="#" data-status="active">Active</a></li>
    <li class="<?= $sf==='inactive' ? 'active' : '' ?>"><a href="#" data-status="inactive">Inactive</a></li>
    <?php if($is_admin): ?>
    <li class="<?= $sf==='deleted' ? 'active' : '' ?>">
        <a href="#" data-status="deleted" style="color:#e74c3c"><i class="fa fa-trash"></i> Deleted</a>
    </li>
    <?php endif; ?>
</ul>
<div id="deleted-banner" class="alert alert-danger" style="<?= $sf==='deleted' ? '' : 'display:none' ?>;border-radius:0;margin:0">
    <i class="fa fa-exclamation-triangle"></i>
    <strong>Deleted Records.</strong> Hidden from all normal users. Use <strong>Restore</strong> to recover.
</div>
