<div class="box box-default">
    <div class="box-body">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group"><label>From</label><input type="date" id="from-date" class="form-control" value="<?= date('Y-m-01') ?>"></div>
            </div>
            <div class="col-md-3">
                <div class="form-group"><label>To</label><input type="date" id="to-date" class="form-control" value="<?= date('Y-m-t') ?>"></div>
            </div>
            <?php if(isset($staff)): ?>
            <div class="col-md-3">
                <div class="form-group"><label>Staff</label>
                    <select id="staff-filter" class="form-control select2">
                        <option value="">-- All Staff --</option>
                        <?php foreach($staff as $s): ?><option value="<?= $s['id'] ?>"><?= esc_html($s['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-md-2" style="padding-top:25px">
                <button class="btn btn-primary" id="btn-filter"><i class="fa fa-search"></i> Filter</button>
            </div>
        </div>
    </div>
</div>
