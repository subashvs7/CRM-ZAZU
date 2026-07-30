<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('workspace/contact_book') ?>" class="w-10 h-10 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-xl flex items-center justify-center transition-colors">
            <i class="fa fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Add New Contact</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('workspace/contact_book') ?>" class="hover:text-blue-600 transition-colors">Workspace</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <a href="<?= base_url('workspace/contact_book') ?>" class="hover:text-blue-600 transition-colors">Contact Book</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Add Contact</span>
            </nav>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 max-w-2xl mx-auto">
    <form action="<?= base_url('workspace/process_contact') ?>" method="POST" class="p-6">
        <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
            <input type="text" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Enter full name" required>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-xs text-gray-400">(Optional)</span></label>
            <input type="text" name="phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Enter phone number">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-xs text-gray-400">(Optional)</span></label>
            <input type="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Enter email address">
        </div>

        <div class="mb-8">
            <label class="block text-sm font-medium text-gray-700 mb-1">Company Name <span class="text-xs text-gray-400">(Optional)</span></label>
            <input type="text" name="company_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Enter company name">
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="<?= base_url('workspace/contact_book') ?>" class="px-5 py-2 text-gray-600 font-medium bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors text-sm">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm text-sm">Save Contact</button>
        </div>
    </form>
</div>
