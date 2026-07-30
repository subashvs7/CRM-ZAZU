<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-cubes text-blue-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Products Catalogue</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Product Hub</span>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Products</span>
            </nav>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-6">
    <!-- Left Column: Add Product Form -->
    <div class="xl:col-span-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 h-full flex flex-col">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 id="form-title" class="text-lg font-bold text-gray-800">Add Product</h2>
                    <p class="text-sm text-gray-500 mt-1">Enter product details to add to catalogue.</p>
                </div>
                <button type="button" class="btn-cancel px-3 py-1.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors flex items-center gap-1">
                    <i class="fa fa-refresh"></i> Reset Form
                </button>
            </div>
            
            <form id="product-form" class="p-6 flex-1 flex flex-col">
                <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>" id="csrf-token">
                <input type="hidden" name="id" id="product-id" value="0">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1.5">Product Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter product name" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1.5">Price (₹) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.01" name="price" class="w-full px-3 py-2 pr-8 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter price" required>
                            <i class="fa fa-rupee absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1.5">SKU <span class="text-red-500">*</span></label>
                        <input type="text" name="sku" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter SKU" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1.5">Min Price (₹)</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="min_price" class="w-full px-3 py-2 pr-8 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter min price">
                            <i class="fa fa-rupee absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1.5">Category <span class="text-red-500">*</span></label>
                        <select name="category_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 select2">
                            <option value="">-- Select Category --</option>
                            <?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>"><?= esc_html($c['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1.5">Description</label>
                        <textarea name="description" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none h-[42px]" placeholder="Enter description"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 mt-auto">
                    <button type="button" class="btn-cancel px-5 py-2 text-gray-600 font-semibold bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition-colors text-sm">Cancel</button>
                    <button type="button" id="btn-save-product" class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm text-sm flex items-center gap-2">
                        <i class="fa fa-save"></i> Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: Products List -->
    <div class="xl:col-span-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 h-full flex flex-col">
            <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Products List</h2>
                    <p class="text-sm text-gray-500 mt-1">View and manage all products in your catalogue.</p>
                </div>
                <!-- Optional Add button in header for mobile scrolling -->
                <button type="button" class="btn-cancel px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm text-sm flex items-center justify-center gap-2 lg:hidden">
                    <i class="fa fa-plus"></i> Add Product
                </button>
            </div>

            <div class="p-6 border-b border-gray-100 flex flex-wrap items-center gap-3 justify-between">
                <div class="relative w-full sm:w-auto flex-1 min-w-[240px] max-w-sm">
                    <i class="fa fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" id="custom-search" class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Search products...">
                </div>
                <div class="flex items-center gap-2">
                    <button class="px-4 py-2 bg-white border border-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors text-sm flex items-center gap-2">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                </div>
            </div>

            <div class="p-0 overflow-x-auto flex-1">
                <table id="products-table" class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="text-[11px] text-gray-500 bg-gray-50 border-b border-gray-200 uppercase">
                        <tr>
                            <th class="px-6 py-4 font-bold">#</th>
                            <th class="px-6 py-4 font-bold">SKU</th>
                            <th class="px-6 py-4 font-bold">Product Name</th>
                            <th class="px-6 py-4 font-bold">Category</th>
                            <th class="px-6 py-4 font-bold">Price (₹)</th>
                            <th class="px-6 py-4 font-bold">Status</th>
                            <th class="px-6 py-4 font-bold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            
        </div>
    </div>
</div>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function() {
    if ($.fn.select2) {
        $('.select2').select2({ width: '100%' });
    }

    var table = $('#products-table').DataTable({
        processing: true, 
        serverSide: true,
        ajax: { 
            url: BASE_URL + 'product_hub/products_dt'
        },
        dom: '<"hidden"f>rt<"p-6 flex items-center justify-between border-t border-gray-100"i<"flex items-center gap-2"p>>',
        columns: [
            {data: 0, className: 'px-6 py-4 text-gray-500 align-middle text-sm'},
            {data: 1, className: 'px-6 py-4 text-gray-500 align-middle text-sm uppercase'},
            {data: 2, className: 'px-6 py-4 font-semibold text-gray-800 align-middle text-sm'},
            {data: 3, className: 'px-6 py-4 text-gray-500 align-middle text-sm'},
            {data: 4, className: 'px-6 py-4 text-gray-800 align-middle text-sm'},
            {data: 5, className: 'px-6 py-4 align-middle'},
            {data: 6, orderable: false, className: 'px-6 py-4 align-middle text-center'}
        ],
        order: [[0, 'desc']],
        drawCallback: function() {
            $('.dataTables_paginate > .pagination').addClass('flex items-center gap-1');
            $('.dataTables_paginate .paginate_button').addClass('w-8 h-8 flex items-center justify-center bg-white border border-gray-200 text-gray-600 rounded hover:bg-gray-50 text-sm cursor-pointer');
            $('.dataTables_paginate .paginate_button.current').addClass('bg-blue-600 border-blue-600 text-white hover:bg-blue-700').removeClass('bg-white text-gray-600');
            $('.dataTables_paginate .paginate_button.disabled').addClass('opacity-50 cursor-not-allowed hover:bg-white');
            
            let infoText = $('.dataTables_info').text();
            $('.dataTables_info').html(`<span class="text-sm text-gray-500">${infoText}</span>`);
        }
    });

    $('#custom-search').on('keyup', function() {
        table.search(this.value).draw();
    });

    function resetForm() {
        $('#product-form')[0].reset(); 
        $('#product-id').val(0);
        if ($.fn.select2) {
            $('#product-form [name=category_id]').val('').trigger('change');
        }
        $('#form-title').text('Add Product');
        $('#btn-save-product').html('<i class="fa fa-save"></i> Save Product');
    }

    $('.btn-cancel').click(function(e){
        e.preventDefault();
        resetForm();
    });

    $(document).on('click', '.btn-edit-product', function(){
        var id = $(this).data('id');
        $.get(BASE_URL+'product_hub/get_product/'+id, function(res){
            if (res.status !== 'success') { 
                Swal.fire('Error!', res.message||'Failed to load data.', 'error'); 
                return; 
            }
            var d = res.data;
            $('#product-form')[0].reset();
            $('#product-id').val(d.id);
            $('#product-form [name=name]').val(d.name||'');
            $('#product-form [name=sku]').val(d.sku||'');

            
            $('#product-form [name=price]').val(d.price||'');
            $('#product-form [name=min_price]').val(d.min_price||'');
            $('#product-form [name=description]').val(d.description||'');
            
            if ($.fn.select2) {
                $('#product-form [name=category_id]').val(d.category_id||'').trigger('change');
            } else {
                $('#product-form [name=category_id]').val(d.category_id||'');
            }

            $('#form-title').text('Edit Product');
            $('#btn-save-product').html('<i class="fa fa-save"></i> Update Product');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    $(document).on('click', '.btn-product-status', function(){
        var id = $(this).data('id');
        var action = $(this).data('action');
        let csrf_name = '<?= $csrf_name ?>';
        let csrf_hash = $('#csrf-token').val();

        if (action === 'delete') {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    let data = { id: id, action: action };
                    data[csrf_name] = csrf_hash;
                    
                    $.post(BASE_URL+'product_hub/product_status', data, function(res){
                        if(res.status === 'success') {
                            Swal.fire('Deleted!', res.message, 'success');
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Error!', res.message, 'error');
                        }
                    });
                }
            });
        }
    });

    $('#btn-save-product').click(function(){
        // Using native HTML5 validation before submitting
        let formObj = document.getElementById('product-form');
        if (!formObj.checkValidity()) {
            formObj.reportValidity();
            return;
        }

        var $btn=$(this); 
        let ogText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        
        let formData = new FormData(formObj);

        $.ajax({
            url: BASE_URL+'product_hub/save_product',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res){
                if(res.status==='success') {
                    Swal.fire('Success!', res.message, 'success');
                    resetForm();
                    table.ajax.reload(null,false);
                } else {
                    let errorMsg = res.message;
                    if(res.errors) {
                        let errArr = [];
                        for(let key in res.errors) {
                            errArr.push(res.errors[key]);
                        }
                        if(errArr.length > 0) errorMsg = errArr.join('<br>');
                    }
                    Swal.fire('Validation Error', errorMsg, 'error');
                }
            },
            error: function(xhr) {
                let res = xhr.responseJSON;
                Swal.fire('Error!', res && res.message ? res.message : 'An error occurred.', 'error');
            },
            complete: function(){ 
                $btn.prop('disabled', false).html(ogText);
            }
        });
    });
});
</script>
