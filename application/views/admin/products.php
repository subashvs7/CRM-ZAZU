<section class="content-header">
    <h1>Products <small>Catalogue management</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>">Home</a></li><li class="active">Products</li></ol>
</section>
<section class="content">
<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>
<ul class="nav nav-tabs" id="prod-view-tabs" style="margin-bottom:15px">
    <li class="active"><a href="#tab-products" data-toggle="tab">Products</a></li>
    <li><a href="#tab-categories" data-toggle="tab">Categories</a></li>
</ul>
<div class="tab-content">
<div class="tab-pane active" id="tab-products">
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-cubes"></i> Products</h3>
        <div class="box-tools"><button class="btn btn-success btn-sm" id="btn-add-product"><i class="fa fa-plus"></i> Add Product</button></div>
    </div>
    <div class="box-body">
        <table id="products-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Name</th><th>SKU</th><th>Category</th><th>Unit</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
</div>
<div class="tab-pane" id="tab-categories">
<div class="box box-warning">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-tags"></i> Categories</h3>
        <div class="box-tools"><button class="btn btn-success btn-sm" id="btn-add-cat"><i class="fa fa-plus"></i> Add Category</button></div>
    </div>
    <div class="box-body">
        <table id="cats-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Name</th><th>Parent</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
</div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="product-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Product</h4></div>
            <div class="modal-body">
                <form id="product-form">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="product-id" value="0">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group"><label>Name *</label><input type="text" name="name" class="form-control" required></div>
                            <div class="form-group"><label>SKU *</label><input type="text" name="sku" class="form-control" required></div>
                            <div class="form-group"><label>Category</label>
                                <select name="category_id" class="form-control select2">
                                    <option value="">-- None --</option>
                                    <?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>"><?= esc_html($c['name']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group"><label>Unit</label><input type="text" name="unit" class="form-control" value="pcs"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group"><label>Price (₹) *</label><input type="number" step="0.01" name="price" class="form-control" required></div>
                            <div class="form-group"><label>Min Price (₹)</label><input type="number" step="0.01" name="min_price" class="form-control" value="0"></div>
                            <div class="form-group"><label>Stock</label><input type="number" name="stock" class="form-control" value="0"></div>
                            <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="4"></textarea></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btn-save-product"><i class="fa fa-save"></i> Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="cat-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Category</h4></div>
            <div class="modal-body">
                <form id="cat-form">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="cat-id" value="0">
                    <div class="form-group"><label>Name *</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label>Parent</label>
                        <select name="parent_id" class="form-control select2">
                            <option value="">-- Root --</option>
                            <?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>"><?= esc_html($c['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btn-save-cat">Save</button>
            </div>
        </div>
    </div>
</div>
</section>

<script>
var prodTable = $('#products-table').DataTable({
    processing:true,serverSide:true,
    ajax:{url:BASE_URL+'admin/products_dt',data:function(d){d.status_filter=window.currentStatusFilter||'';}},
    columns:[{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6},{data:7},{data:8,orderable:false}],
    order:[[0,'desc']]
});
var catsTable = $('#cats-table').DataTable({
    processing:true,serverSide:true,ajax:{url:BASE_URL+'admin/categories_dt'},
    columns:[{data:0},{data:1},{data:2},{data:3},{data:4,orderable:false}],order:[[0,'desc']]
});
$('#status-tabs a').on('click',function(e){e.preventDefault();$('#status-tabs li').removeClass('active');$(this).parent().addClass('active');window.currentStatusFilter=$(this).data('status');$('#deleted-banner').toggle(window.currentStatusFilter==='deleted');prodTable.ajax.reload();});
$('#btn-add-product').click(function(){$('#product-form')[0].reset();$('#product-id').val(0);CRM.clear_errors($('#product-form'));$('#product-modal').modal('show');CRM.init_plugins($('#product-modal'));});
$('#btn-save-product').click(function(){
    var $btn=$(this);CRM.btn_loading($btn);
    $.ajax({url:BASE_URL+'admin/save_product',method:'POST',data:new FormData($('#product-form')[0]),processData:false,contentType:false,
        success:function(res){if(res.status==='success'){CRM.toast('success',res.message);$('#product-modal').modal('hide');prodTable.ajax.reload(null,false);}else{CRM.show_errors($('#product-form'),res.errors||{});CRM.toast('error',res.message);}},
        complete:function(){CRM.btn_reset($btn);}
    });
});
$(document).on('click','.btn-product-status',function(){
    var id=$(this).data('id'),action=$(this).data('action');
    if(action==='delete'){if(!confirm('Delete?'))return;}
    $.post(BASE_URL+'admin/product_status',{id:id,action:action,[CI3_CSRF_NAME]:CI3_CSRF_HASH},function(res){if(res.status==='success'){CRM.toast('success',res.message);prodTable.ajax.reload(null,false);}});
});
$('#btn-add-cat').click(function(){$('#cat-form')[0].reset();$('#cat-id').val(0);$('#cat-modal').modal('show');CRM.init_plugins($('#cat-modal'));});
$('#btn-save-cat').click(function(){
    $.ajax({url:BASE_URL+'admin/save_category',method:'POST',data:new FormData($('#cat-form')[0]),processData:false,contentType:false,
        success:function(res){if(res.status==='success'){CRM.toast('success',res.message);$('#cat-modal').modal('hide');catsTable.ajax.reload(null,false);}else CRM.toast('error',res.message);}
    });
});
</script>
