<div class="flex items-center gap-4 mb-6">
    <a href="<?= base_url('dashboard') ?>" class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center text-gray-600 transition-colors">
        <i class="fa fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="text-xl font-bold text-gray-800">Add New Contact</h1>
        <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
            <span class="text-gray-500">Workspace</span>
            <i class="fa fa-angle-right text-[10px]"></i>
            <span class="text-gray-500">Contact Book</span>
            <i class="fa fa-angle-right text-[10px]"></i>
            <span class="text-gray-600 font-medium">Add Contact</span>
        </nav>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Left Column: Form -->
    <div class="lg:col-span-5 xl:col-span-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
            <div class="p-6 pb-4 border-b border-gray-50">
                <h3 class="text-base font-bold text-gray-800">Contact Information</h3>
                <p class="text-xs text-gray-500 mt-1">Enter the details of the new contact.</p>
            </div>
            
            <form id="contact-form" action="<?= base_url('workspace/process_contact') ?>" method="POST" class="p-6 flex-grow flex flex-col">
                <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                <input type="hidden" name="id" value="">

                <div class="space-y-4 flex-grow">
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-[13px]" placeholder="Enter full name" required>
                    </div>

                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Phone <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <input type="text" name="phone" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-[13px]" placeholder="Enter phone number">
                    </div>

                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Email <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <input type="email" name="email" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-[13px]" placeholder="Enter email address">
                    </div>

                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Company Name <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <input type="text" name="company_name" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-[13px]" placeholder="Enter company name">
                    </div>

                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Job Title <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <input type="text" name="job_title" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-[13px]" placeholder="Enter job title">
                    </div>

                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Address <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <input type="text" name="address" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-[13px]" placeholder="Enter address">
                    </div>

                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Notes <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-[13px] resize-none" placeholder="Enter additional notes"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-50">
                    <button type="button" class="px-5 py-2 text-gray-700 font-medium bg-gray-50 border border-gray-200 hover:bg-gray-100 rounded-lg transition-colors text-[13px]" onclick="$('form')[0].reset()">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm text-[13px]">
                        Save Contact
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Right Column: Contact List -->
    <div class="lg:col-span-7 xl:col-span-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
            <div class="p-6 pb-4 border-b border-gray-50">
                <h3 class="text-base font-bold text-gray-800">Contact List</h3>
                <p class="text-xs text-gray-500 mt-1">All saved contacts in your account.</p>
            </div>
            
            <div class="p-6">
                <!-- DataTables custom header elements wrapper -->
                <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                    <div class="relative flex-grow max-w-md">
                        <i class="fa fa-search absolute left-3.5 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" id="dt-search" placeholder="Search contacts..." class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" class="px-4 py-2 border border-gray-200 rounded-lg flex items-center justify-center hover:bg-gray-50 text-gray-700 transition-colors text-sm font-medium">
                            <i class="fa fa-filter mr-2 text-gray-400"></i> Filter
                        </button>
                        <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-lg flex items-center justify-center hover:bg-blue-700 transition-colors text-sm font-medium shadow-sm" onclick="$('form')[0].reset(); $('input[name=name]').focus();">
                            <i class="fa fa-plus mr-1.5"></i> Add Contact
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table id="contacts-table" class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="text-xs font-bold text-gray-800 border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-4 w-12">#</th>
                                <th class="px-4 py-4">Name</th>
                                <th class="px-4 py-4">Company</th>
                                <th class="px-4 py-4">Phone</th>
                                <th class="px-4 py-4">Email</th>
                                <th class="px-4 py-4">Created On</th>
                                <th class="px-4 py-4 w-16">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-[13px] divide-y divide-gray-50"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Clean up Datatables default styling */
.dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_length { display: none; }
table.dataTable tbody tr { background-color: transparent !important; }
table.dataTable tbody tr:hover { background-color: #f8fafc !important; }
table.dataTable thead th, table.dataTable thead td { border-bottom: 0 !important; }
table.dataTable.no-footer { border-bottom: 0 !important; }

/* Custom scrollbar for table container */
.custom-scrollbar::-webkit-scrollbar { height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<!-- Edit Contact Modal -->
<div id="editContactModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/30 backdrop-blur-sm transition-opacity" onclick="closeEditModal()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all w-full max-w-lg">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-800">Edit Contact</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500 transition-colors" onclick="closeEditModal()">
                        <i class="fa fa-times text-lg"></i>
                    </button>
                </div>
                <form id="edit-contact-form" action="<?= base_url('workspace/process_contact') ?>" method="POST" class="p-6">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="edit_id" value="">
                    
                    <div class="space-y-4 max-h-[60vh] overflow-y-auto custom-scrollbar pr-2">
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="edit_name" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-[13px]" required>
                        </div>
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Phone</label>
                            <input type="text" name="phone" id="edit_phone" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-[13px]">
                        </div>
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Email</label>
                            <input type="email" name="email" id="edit_email" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-[13px]">
                        </div>
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Company Name</label>
                            <input type="text" name="company_name" id="edit_company_name" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-[13px]">
                        </div>
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Job Title</label>
                            <input type="text" name="job_title" id="edit_job_title" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-[13px]">
                        </div>
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Address</label>
                            <input type="text" name="address" id="edit_address" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-[13px]">
                        </div>
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Notes</label>
                            <textarea name="notes" id="edit_notes" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-[13px] resize-none"></textarea>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-50">
                        <button type="button" class="px-5 py-2 text-gray-700 font-medium bg-gray-50 border border-gray-200 hover:bg-gray-100 rounded-lg transition-colors text-[13px]" onclick="closeEditModal()">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm text-[13px]">
                            Update Contact
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function closeEditModal() {
    $('#editContactModal').addClass('hidden');
}

$(function() {
    var table = $('#contacts-table').DataTable({
        processing: true, 
        serverSide: true,
        ajax: { 
            url: BASE_URL + 'workspace/contacts_dt'
        },
        columns: [
            {data: 0, className: 'px-4 py-3 text-gray-500 text-xs'},
            {data: 1, className: 'px-4 py-3 font-medium text-gray-900'},
            {data: 2, className: 'px-4 py-3 text-gray-600 font-medium'},
            {data: 3, className: 'px-4 py-3 text-gray-700 font-medium'},
            {data: 4, className: 'px-4 py-3 text-gray-700'},
            {data: 5, className: 'px-4 py-3 text-gray-600 font-medium text-xs'},
            {data: 6, orderable: false, className: 'px-4 py-3 text-center'}
        ],
        order: [[1, 'asc']],
        dom: '<"top">rt<"bottom flex items-center justify-between text-xs text-gray-500 pt-4 mt-2 border-t border-gray-50"ip><"clear">',
        language: {
            info: "Showing _START_ to _END_ of _TOTAL_ contacts",
            paginate: {
                previous: "<i class='fa fa-angle-left'></i>",
                next: "<i class='fa fa-angle-right'></i>"
            }
        },
        drawCallback: function() {
            $('.dataTables_paginate > .pagination').addClass('flex gap-1 items-center');
            $('.dataTables_paginate .paginate_button').addClass('w-8 h-8 flex items-center justify-center bg-white border border-gray-200 rounded hover:bg-gray-50 text-gray-600 cursor-pointer transition-colors');
            $('.dataTables_paginate .paginate_button.current').addClass('bg-blue-600 border-blue-600 text-white hover:bg-blue-700').removeClass('bg-white text-gray-600');
            $('.dataTables_paginate .paginate_button.disabled').addClass('opacity-50 cursor-not-allowed hover:bg-white');
        }
    });

    // Custom search input binding
    $('#dt-search').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Handle Add Contact Form AJAX Submit
    $('#contact-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: res.message,
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        form[0].reset();
                        table.draw(false);
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred.' });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('Save Contact');
            }
        });
    });

    // Handle Edit Contact Form AJAX Submit
    $('#edit-contact-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: res.message,
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        closeEditModal();
                        table.draw(false);
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred.' });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('Update Contact');
            }
        });
    });

    // Open Edit Modal & Fetch Data
    $('#contacts-table').on('click', '.btn-edit-contact', function() {
        var id = $(this).data('id');
        $.ajax({
            url: BASE_URL + 'workspace/get_contact/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    var c = res.data.contact;
                    $('#edit_id').val(c.id);
                    $('#edit_name').val(c.name);
                    $('#edit_phone').val(c.phone);
                    $('#edit_email').val(c.email);
                    $('#edit_company_name').val(c.company_name);
                    $('#edit_job_title').val(c.job_title);
                    $('#edit_address').val(c.address);
                    $('#edit_notes').val(c.notes);
                    $('#editContactModal').removeClass('hidden');
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                }
            }
        });
    });

    // Handle Delete Contact
    $('#contacts-table').on('click', '.btn-delete-contact', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + 'workspace/delete_contact/' + id,
                    type: 'POST',
                    data: {
                        [CI3_CSRF_NAME]: CI3_CSRF_HASH
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: res.message,
                                confirmButtonColor: '#2563eb'
                            }).then(() => {
                                table.draw(false);
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                        }
                    }
                });
            }
        });
    });
});
</script>
