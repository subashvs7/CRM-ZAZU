<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-cubes text-blue-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Products</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Products</span>
            </nav>
        </div>
    </div>
</div>

<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>

<!-- Tab Nav -->
<div class="flex border-b border-gray-200 mb-5">
    <button class="px-4 py-2.5 text-sm font-semibold border-b-2 border-blue-600 text-blue-600 prod-tab-btn" data-tab="tab-products">Products</button>
    <button class="px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 prod-tab-btn" data-tab="tab-categories">Categories</button>
</div>

<!-- Products Tab -->
<div id="tab-products" class="prod-tab-pane">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fa fa-cubes text-blue-600 text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Product Catalogue</h3>
                    <p class="text-xs text-gray-400 mt-0.5">All products and pricing</p>
                </div>
            </div>
            <button id="btn-add-product" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                <i class="fa fa-plus"></i> Add Product
            </button>
        </div>
        <div class="p-4 overflow-x-auto">
            <table id="products-table" class="w-full">
                <thead><tr><th>#</th><th>Name</th><th>SKU</th><th>Category</th><th>Unit</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Categories Tab -->
<div id="tab-categories" class="prod-tab-pane hidden">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="fa fa-tags text-amber-600 text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Categories</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Product groupings</p>
                </div>
            </div>
            <button id="btn-add-cat" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-amber-600 text-white font-semibold rounded-xl hover:bg-amber-700 transition-colors shadow-sm">
                <i class="fa fa-plus"></i> Add Category
            </button>
        </div>
        <div class="p-4 overflow-x-auto">
            <table id="cats-table" class="w-full">
                <thead><tr><th>#</th><th>Name</th><th>Parent</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="product-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Product</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="product-form">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="product-id" value="0">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Name *</label>
                                <input type="text" name="name" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">SKU *</label>
                                <input type="text" name="sku" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Category</label>
                                <select name="category_id" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 select2">
                                    <option value="">-- None --</option>
                                    <?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>"><?= esc_html($c['name']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Unit</label>
                                <input type="text" name="unit" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="pcs">
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Price (₹) *</label>
                                <input type="number" step="0.01" name="price" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Min Price (₹)</label>
                                <input type="number" step="0.01" name="min_price" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="0">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Stock</label>
                                <input type="number" name="stock" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="0">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Description</label>
                                <textarea name="description" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50" data-dismiss="modal">Cancel</button>
                <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700" id="btn-save-product"><i class="fa fa-save mr-1"></i> Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="cat-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Category</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="cat-form">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="cat-id" value="0">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Name *</label>
                            <input type="text" name="name" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Parent</label>
                            <select name="parent_id" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 select2">
                                <option value="">-- Root --</option>
                                <?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>"><?= esc_html($c['name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50" data-dismiss="modal">Cancel</button>
                <button class="px-4 py-2 text-sm bg-amber-600 text-white rounded-xl hover:bg-amber-700" id="btn-save-cat">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
var prodTable = $('#products-table').DataTable({
    processing:true, serverSide:true,
    ajax:{url:BASE_URL+'admin/products_dt', data:function(d){d.status_filter=window.currentStatusFilter||'';}},
    columns:[{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6},{data:7},{data:8,orderable:false}],
    order:[[0,'desc']]
});
var catsTable = $('#cats-table').DataTable({
    processing:true, serverSide:true,
    ajax:{url:BASE_URL+'admin/categories_dt'},
    columns:[{data:0},{data:1},{data:2},{data:3},{data:4,orderable:false}], order:[[0,'desc']]
});
window.mainTable = prodTable;

$('.prod-tab-btn').on('click', function(){
    $('.prod-tab-btn').removeClass('border-blue-600 text-blue-600').addClass('border-transparent text-gray-500');
    $(this).removeClass('border-transparent text-gray-500').addClass('border-blue-600 text-blue-600');
    $('.prod-tab-pane').addClass('hidden');
    $('#'+$(this).data('tab')).removeClass('hidden');
});

$('#btn-add-product').click(function(){
    $('#product-form')[0].reset(); $('#product-id').val(0);
    CRM.clear_errors($('#product-form'));
    $('#product-modal .modal-title').text('Add Product');
    $('#product-modal').modal('show');
    CRM.init_plugins($('#product-modal'));
});

$(document).on('click', '.btn-edit-product', function(){
    var id = $(this).data('id');
    $.get(BASE_URL+'admin/get_product/'+id, function(res){
        if (res.status !== 'success') { CRM.toast('error', res.message||'Failed to load.'); return; }
        var d = res.data;
        $('#product-form')[0].reset();
        CRM.clear_errors($('#product-form'));
        $('#product-id').val(d.id);
        $('#product-form [name=name]').val(d.name||'');
        $('#product-form [name=sku]').val(d.sku||'');
        $('#product-form [name=unit]').val(d.unit||'pcs');
        $('#product-form [name=price]').val(d.price||'');
        $('#product-form [name=min_price]').val(d.min_price||0);
        $('#product-form [name=stock]').val(d.stock||0);
        $('#product-form [name=description]').val(d.description||'');
        $('#product-form [name=category_id]').val(d.category_id||'').trigger('change');
        $('#product-modal .modal-title').text('Edit Product');
        $('#product-modal').modal('show');
        CRM.init_plugins($('#product-modal'));
    });
});

$('#btn-save-product').click(function(){
    var $btn=$(this); CRM.btn_loading($btn);
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

$('#btn-add-cat').click(function(){
    $('#cat-form')[0].reset(); $('#cat-id').val(0);
    $('#cat-modal .modal-title').text('Add Category');
    $('#cat-modal').modal('show');
    CRM.init_plugins($('#cat-modal'));
});

$(document).on('click', '.btn-edit-cat', function(){
    var id = $(this).data('id');
    $.get(BASE_URL+'admin/get_category/'+id, function(res){
        if (res.status !== 'success') { CRM.toast('error', res.message||'Failed to load.'); return; }
        var d = res.data;
        $('#cat-form')[0].reset();
        $('#cat-id').val(d.id);
        $('#cat-form [name=name]').val(d.name||'');
        $('#cat-form [name=parent_id]').val(d.parent_id||'').trigger('change');
        $('#cat-modal .modal-title').text('Edit Category');
        $('#cat-modal').modal('show');
        CRM.init_plugins($('#cat-modal'));
    });
});

$('#btn-save-cat').click(function(){
    $.ajax({url:BASE_URL+'admin/save_category',method:'POST',data:new FormData($('#cat-form')[0]),processData:false,contentType:false,
        success:function(res){if(res.status==='success'){CRM.toast('success',res.message);$('#cat-modal').modal('hide');catsTable.ajax.reload(null,false);}else CRM.toast('error',res.message);}
    });
});
</script>
