<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-6">
    <!-- Left Column: Upload Form -->
    <div class="xl:col-span-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 h-full flex flex-col">
            <div class="p-6 border-b border-gray-100">
                <h2 id="form-title" class="text-lg font-bold text-gray-800">Upload New Asset</h2>
                <p class="text-sm text-gray-500 mt-1">Add asset details and upload file.</p>
            </div>
            <form id="upload-form" action="<?= base_url('product_hub/process_upload') ?>" method="POST" enctype="multipart/form-data" class="p-6 flex-1">
                <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>" id="csrf-token">
                <input type="hidden" name="id" id="asset_id" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product <span class="text-xs text-gray-400">(Optional)</span></label>
                        <select name="product_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm select2">
                            <option value="">-- Select Product --</option>
                            <?php foreach($products as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= esc_html($p['name']) ?> <?= !empty($p['category_name']) ? '('.esc_html($p['category_name']).')' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 text-red-500"><span class="text-gray-700">Asset Type</span> *</label>
                        <select name="asset_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" required>
                            <option value="">-- Select Type --</option>
                            <option value="Document">Document</option>
                            <option value="Brochure">Brochure</option>
                            <option value="Video">Video</option>
                            <option value="Image">Image</option>
                            <option value="Presentation">Presentation</option>
                            <option value="PPT">PPT</option>
                            <option value="Link">Link</option>
                        </select>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1 text-red-500"><span class="text-gray-700">Title</span> *</label>
                    <input type="text" name="title" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Enter asset title" required>
                </div>

                <div class="mb-5 relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-xs text-gray-400">(Optional)</span></label>
                    <textarea name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm resize-none" placeholder="Enter description"></textarea>
                    <span class="absolute bottom-3 right-3 text-xs text-gray-400">0/500</span>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1 text-red-500"><span class="text-gray-700">Upload File</span> *</label>
                    <p class="text-[11px] text-gray-400 mb-2">Allowed: pdf, doc, docx, ppt, pptx, mp4, jpg, png - Max 50MB</p>
                    <div class="relative border-2 border-dashed border-gray-300 rounded-xl px-6 py-12 text-center hover:bg-gray-50 transition-colors">
                        <input type="file" name="asset_file" id="asset-file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fa fa-cloud-upload text-xl text-gray-500"></i>
                        </div>
                        <p class="text-sm text-gray-500">Drag & drop file here or <span class="text-blue-600 font-medium">click to browse</span></p>
                        <p id="file-name" class="text-xs text-blue-600 font-medium mt-2"></p>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1 uppercase font-bold text-[10px]">OR External Link <span class="text-gray-400 capitalize font-normal">(For videos or website links)</span></label>
                    <input type="url" name="link_url" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-gray-50" placeholder="https://example.com/demo-video">
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                    <button type="button" class="btn-cancel px-5 py-2.5 text-gray-600 font-medium bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors text-sm">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm text-sm">Upload Asset</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: Asset List -->
    <div class="xl:col-span-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 h-full flex flex-col">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-800">Asset List</h2>
                <p class="text-sm text-gray-500 mt-1 mb-4">View and manage all uploaded assets.</p>

                <div class="flex flex-wrap items-center gap-3 justify-between">
                    <div class="relative w-full sm:w-auto flex-1 min-w-[240px] max-w-sm">
                        <i class="fa fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" id="custom-search" class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Search assets...">
                    </div>
                    
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <select id="filter-type" class="px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium">
                            <option value="All Types">All Types</option>
                            <option value="Documents">Documents</option>
                            <option value="Videos">Videos</option>
                            <option value="Images">Images</option>
                            <option value="Presentations">Presentations</option>
                        </select>
                        <select class="px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium">
                            <option>All Status</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="px-6 border-b border-gray-100 flex items-center gap-6 text-sm overflow-x-auto">
                <a href="#" data-type="All" id="tab-all" class="type-tab py-3 text-blue-600 font-semibold border-b-2 border-blue-600 whitespace-nowrap">All (0)</a>
                <a href="#" data-type="Documents" id="tab-documents" class="type-tab py-3 text-gray-500 hover:text-gray-700 font-medium whitespace-nowrap">Documents (0)</a>
                <a href="#" data-type="Videos" id="tab-videos" class="type-tab py-3 text-gray-500 hover:text-gray-700 font-medium whitespace-nowrap">Videos (0)</a>
                <a href="#" data-type="Images" id="tab-images" class="type-tab py-3 text-gray-500 hover:text-gray-700 font-medium whitespace-nowrap">Images (0)</a>
                <a href="#" data-type="Presentations" id="tab-presentations" class="type-tab py-3 text-gray-500 hover:text-gray-700 font-medium whitespace-nowrap">Presentations (0)</a>
            </div>

            <div class="p-0 overflow-x-auto flex-1">
                <table id="assets-table" class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="text-[11px] text-gray-500 bg-white border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 font-bold">#</th>
                            <th class="px-6 py-4 font-bold">Title</th>
                            <th class="px-6 py-4 font-bold">Product</th>
                            <th class="px-6 py-4 font-bold">Type</th>
                            <th class="px-6 py-4 font-bold">File / Link</th>
                            <th class="px-6 py-4 font-bold">Uploaded By</th>
                            <th class="px-6 py-4 font-bold">Uploaded On</th>
                            <th class="px-6 py-4 font-bold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            
        </div>
    </div>
</div>

<script>
$(function() {
    let currentType = 'All';

    if ($.fn.select2) {
        $('.select2').select2({ width: '100%' });
    }

    $('#asset-file').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $('#file-name').text(fileName);
        } else {
            $('#file-name').text('');
        }
    });

    var table = $('#assets-table').DataTable({
        processing: true, 
        serverSide: true,
        ajax: { 
            url: BASE_URL + 'product_hub/datatable',
            data: function(d) {
                d.type = currentType;
            }
        },
        dom: '<"hidden"f>rt<"p-6 flex items-center justify-between border-t border-gray-100"i<"flex items-center gap-2"p>>',
        columns: [
            {data: 0, className: 'px-6 py-4 text-gray-500 align-middle'},
            {data: 1, className: 'px-6 py-4 font-medium text-gray-900 align-middle'},
            {data: 3, className: 'px-6 py-4 text-gray-500 align-middle'},
            {data: 2, className: 'px-6 py-4 align-middle'},
            {data: 4, className: 'px-6 py-4 align-middle text-blue-600 hover:underline'},
            {data: null, className: 'px-6 py-4 text-gray-800 align-middle font-medium', render: function() { return 'admin'; }},
            {data: 5, className: 'px-6 py-4 text-gray-500 align-middle text-xs'},
            {
                data: 0, 
                orderable: false, 
                className: 'px-6 py-4 text-center align-middle',
                render: function(data) {
                    return `<div class="flex items-center justify-center gap-4 text-gray-400 text-lg">
                        <a href="${BASE_URL}product_hub/asset_details/${data}" class="hover:text-gray-700 transition-colors" title="View"><i class="fa fa-eye"></i></a>
                        <a href="javascript:void(0)" class="hover:text-blue-600 transition-colors btn-edit" data-id="${data}" title="Edit"><i class="fa fa-pencil"></i></a>
                        <a href="javascript:void(0)" class="hover:text-red-600 transition-colors btn-delete" data-id="${data}" title="Delete"><i class="fa fa-trash"></i></a>
                    </div>`;
                }
            }
        ],
        order: [[0, 'desc']],
        drawCallback: function() {
            $('.dataTables_paginate > .pagination').addClass('flex items-center gap-1');
            $('.dataTables_paginate .paginate_button').addClass('w-8 h-8 flex items-center justify-center bg-white border border-gray-200 text-gray-600 rounded hover:bg-gray-50 text-sm cursor-pointer');
            $('.dataTables_paginate .paginate_button.current').addClass('bg-blue-600 border-blue-600 text-white hover:bg-blue-700').removeClass('bg-white text-gray-600');
            $('.dataTables_paginate .paginate_button.disabled').addClass('opacity-50 cursor-not-allowed hover:bg-white');
            
            let infoText = $('.dataTables_info').text();
            $('.dataTables_info').html(`<span class="text-sm text-gray-500">${infoText}</span>`);
            
            $('#assets-table tbody tr').each(function() {
                // Type formatting
                let typeCell = $(this).find('td').eq(3);
                let typeText = typeCell.text().trim();
                let typeClass = '';
                
                if (typeText === 'Document' || typeText === 'Brochure') {
                    typeClass = 'bg-blue-50 text-blue-600';
                } else if (typeText === 'Video') {
                    typeClass = 'bg-green-50 text-green-600';
                } else if (typeText === 'Image') {
                    typeClass = 'bg-orange-50 text-orange-600';
                } else if (typeText === 'PPT' || typeText === 'Presentation') {
                    typeClass = 'bg-purple-50 text-purple-600';
                } else if (typeText === 'Link') {
                    typeClass = 'bg-gray-100 text-gray-600';
                } else {
                    typeClass = 'bg-blue-50 text-blue-600';
                }
                
                typeCell.html('<span class="px-3 py-1 text-xs font-semibold rounded-full ' + typeClass + '">' + typeText + '</span>');
                
                // Title formatting with icon
                let titleCell = $(this).find('td').eq(1);
                let titleText = titleCell.text().trim();
                let iconClass = 'fa-file-text text-gray-400';
                let iconBg = 'bg-gray-100';
                
                if (typeText === 'Document' || typeText === 'Brochure') {
                    iconClass = 'fa-file-pdf-o text-red-500';
                    iconBg = 'bg-red-50';
                } else if (typeText === 'Video') {
                    iconClass = 'fa-play-circle text-green-500';
                    iconBg = 'bg-green-50';
                } else if (typeText === 'Image') {
                    iconClass = 'fa-image text-orange-500';
                    iconBg = 'bg-orange-50';
                } else if (typeText === 'PPT' || typeText === 'Presentation') {
                    iconClass = 'fa-file-powerpoint-o text-orange-600';
                    iconBg = 'bg-orange-50';
                } else if (typeText === 'Link') {
                    iconClass = 'fa-globe text-blue-500';
                    iconBg = 'bg-blue-50';
                }
                
                titleCell.html('<div class="flex items-center gap-3"><div class="w-8 h-8 rounded-lg flex items-center justify-center ' + iconBg + '"><i class="fa ' + iconClass + ' text-base"></i></div><span class="font-semibold">' + titleText + '</span></div>');

                // File/Link remove standard link classes and keep it simpler
                let fileCell = $(this).find('td').eq(4);
                let fileHtml = fileCell.html();
                // strip out the standard fa icons added from controller
                let fileText = $(fileHtml).text().replace('Download', '').replace('View', '').trim();
                
                if(typeText === 'Document' || typeText === 'Brochure' || typeText === 'Image' || typeText === 'PPT' || typeText === 'Presentation'){
                     fileCell.html('<div class="text-sm font-medium text-gray-800">' + fileText + '</div><div class="text-[11px] text-gray-400 mt-0.5">File</div>');
                } else {
                    fileCell.html('<div class="text-sm font-medium text-gray-800">' + fileText + '</div>');
                }

                // Uploaded On
                let uploadedOnCell = $(this).find('td').eq(6);
                let uploadedOnText = uploadedOnCell.text().trim();
                uploadedOnCell.html('<div class="text-sm text-gray-800">' + uploadedOnText + '</div>');
                
            });
        }
    });

    // Custom Search
    $('#custom-search').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Form Submission (AJAX)
    $('#upload-form').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        let btn = $(this).find('button[type="submit"]');
        let ogText = btn.text();
        btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    toastr.success(res.message || 'Asset saved successfully.');
                    resetForm();
                    table.draw();
                    loadCounts();
                } else {
                    toastr.error(res.message || 'Error occurred.');
                }
            },
            error: function(xhr) {
                let res = xhr.responseJSON;
                toastr.error(res && res.message ? res.message : 'An error occurred.');
            },
            complete: function() {
                btn.prop('disabled', false).text(ogText);
            }
        });
    });

    function resetForm() {
        $('#upload-form')[0].reset();
        $('#asset_id').val('');
        $('#file-name').text('');
        if ($.fn.select2) {
            $('.select2').val('').trigger('change');
        }
        $('#form-title').text('Upload New Asset');
        $('#upload-form').find('button[type="submit"]').text('Upload Asset');
    }

    $('.btn-cancel').on('click', function(e) {
        e.preventDefault();
        resetForm();
    });

    // Edit Click
    $(document).on('click', '.btn-edit', function() {
        let id = $(this).data('id');
        $.get(BASE_URL + 'product_hub/get_asset/' + id, function(res) {
            if (res.status === 'success') {
                let data = res.data;
                $('#asset_id').val(data.id);
                $('select[name="product_id"]').val(data.product_id).trigger('change');
                $('select[name="asset_type"]').val(data.asset_type);
                $('input[name="title"]').val(data.title);
                $('textarea[name="description"]').val(data.description);
                
                if (data.file_link && data.file_link.startsWith('http')) {
                    $('input[name="link_url"]').val(data.file_link);
                    $('#file-name').text('');
                } else {
                    $('input[name="link_url"]').val('');
                    $('#file-name').text(data.file_link ? data.file_link.split('/').pop() : '');
                }

                $('#form-title').text('Edit Asset');
                $('#upload-form').find('button[type="submit"]').text('Update Asset');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                toastr.error('Failed to fetch asset data.');
            }
        });
    });

    // Delete Click
    $(document).on('click', '.btn-delete', function() {
        let id = $(this).data('id');
        let csrf_name = '<?= $csrf_name ?>';
        let csrf_hash = $('#csrf-token').val();
        
        if (confirm('Are you sure you want to delete this asset?')) {
            let data = { id: id };
            data[csrf_name] = csrf_hash;
            
            $.post(BASE_URL + 'product_hub/delete_asset', data, function(res) {
                if (res.status === 'success') {
                    toastr.success('Asset deleted successfully.');
                    table.draw();
                    loadCounts();
                } else {
                    toastr.error('Could not delete asset.');
                }
            });
        }
    });

    // Tabs functionality
    $('.type-tab').on('click', function(e) {
        e.preventDefault();
        $('.type-tab').removeClass('text-blue-600 font-semibold border-b-2 border-blue-600').addClass('text-gray-500 font-medium');
        $(this).removeClass('text-gray-500 font-medium').addClass('text-blue-600 font-semibold border-b-2 border-blue-600');
        
        currentType = $(this).data('type');
        
        // Sync the dropdown if it matches
        if ($(`#filter-type option[value="${currentType}"]`).length) {
            $('#filter-type').val(currentType);
        } else if (currentType === 'All') {
            $('#filter-type').val('All Types');
        }
        
        table.draw();
    });

    // Filter Type Dropdown
    $('#filter-type').on('change', function() {
        let val = $(this).val();
        currentType = val === 'All Types' ? 'All' : val;
        
        $('.type-tab').removeClass('text-blue-600 font-semibold border-b-2 border-blue-600').addClass('text-gray-500 font-medium');
        $(`.type-tab[data-type="${currentType}"]`).removeClass('text-gray-500 font-medium').addClass('text-blue-600 font-semibold border-b-2 border-blue-600');
        
        table.draw();
    });

    // Dynamic Counts
    function loadCounts() {
        $.get(BASE_URL + 'product_hub/get_asset_counts', function(res) {
            if (res.status === 'success') {
                $('#tab-all').text(`All (${res.data.All})`);
                $('#tab-documents').text(`Documents (${res.data.Documents})`);
                $('#tab-videos').text(`Videos (${res.data.Videos})`);
                $('#tab-images').text(`Images (${res.data.Images})`);
                $('#tab-presentations').text(`Presentations (${res.data.Presentations})`);
            }
        });
    }

    loadCounts(); // Initial load
});
</script>
