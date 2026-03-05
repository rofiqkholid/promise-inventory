<div class="hidden animate-fadeIn" id="accounts-panel" role="tabpanel">
    <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-600 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="font-semibold text-gray-900 dark:text-white text-xs tracking-wider">Employee Accounts</h3>
            <p class="text-xs text-gray-500 font-medium">Manage credentials and ID data.</p>
        </div>
        <button onclick="openAddAccountModal()" class="w-full md:w-auto inline-flex items-center justify-center px-6 py-2 bg-amber-600 text-white text-sm font-semibold rounded-xs transition-all hover:bg-amber-700 gap-2 tracking-wider">
            <i class="fa-solid fa-user-plus text-xs"></i> Create Account
        </button>
    </div>
    <x-table id="AccountsTable">
        <thead>
            <tr>
                <th class="w-16">No</th>
                <th class="text-center w-32">NIK</th>
                <th class="text-left">Full Name</th>
                <th class="text-left">Email</th>
                <th class="w-32 text-center">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

<!-- MODAL -->
<div id="accountModal" tabindex="-1" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="relative w-full max-w-md">
        <div class="bg-white dark:bg-gray-800 rounded-xs overflow-hidden border border-slate-200 dark:border-gray-600">
            <div class="px-5 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-slate-200 dark:border-gray-600 flex justify-between items-center">
                <h3 id="accountModalTitle" class="text-lg font-medium text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                    <i class="fa-solid fa-id-card text-amber-500"></i> Account Identity
                </h3>
                <button onclick="closeModal('accountModal')" class="text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="accountForm" class="p-5">
                @csrf
                <input type="hidden" name="id" id="account_id_input">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-gray-400">NIK</label>
                            <input type="text" name="nik" id="account_nik_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xs text-sm font-medium" required>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-gray-400">Email</label>
                            <input type="email" name="email" id="account_email_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xs text-sm font-medium" required>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-400">Full Name</label>
                        <input type="text" name="name" id="account_name_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xs text-sm font-medium" required>
                    </div>
                    
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700 mt-2">
                        <p class="text-xs font-black text-amber-600 mb-3">Credentials</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block mb-1 text-xs font-medium text-gray-400">Password</label>
                                <input type="password" name="password" id="account_password_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xs text-sm font-medium">
                                <p id="password_hint" class="mt-1 text-[9px] text-gray-400 italic hidden">Empty to keep safe.</p>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-medium text-gray-400">Confirm</label>
                                <input type="password" name="password_confirmation" id="account_password_confirm_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xs text-sm font-medium">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="button" onclick="closeModal('accountModal')" class="flex-1 py-2 text-sm font-medium text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-xs transition-all">Abort</button>
                    <button type="submit" class="flex-1 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-xs shadow-sm active:scale-95 transition-all">Commit</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        accountsTable = window.defaultDataTable('#AccountsTable', {
            ajax: "{{ route('inventory.users.data') }}",
            columns: [
                { data: 'DT_RowIndex', className: 'text-center font-medium text-gray-400' },
                { data: 'nik', className: 'text-center font-medium text-xs text-amber-600 bg-amber-50 dark:bg-amber-900/40 px-2 py-1 rounded border border-amber-100 dark:border-amber-800' },
                { data: 'name', className: 'font-medium' },
                { data: 'email', className: 'text-gray-500 text-sm' },
                { data: 'action', className: 'text-center', orderable: false }
            ]
        });

        $('#AccountsTable').on('click', '.edit-user-btn', function() {
            $.get("{{ url('inventory/users') }}/" + $(this).data('id'), function(data) {
                $('#account_id_input').val(data.id); $('#account_nik_input').val(data.nik); $('#account_name_input').val(data.name); $('#account_email_input').val(data.email);
                $('#account_password_input, #account_password_confirm_input').val(''); $('#password_hint').show();
                $('#accountModalTitle').html('<i class="fa-solid fa-user-pen text-amber-500"></i> Edit Account');
                $('#accountModal').removeClass('hidden').addClass('flex');
            });
        });

        $('#AccountsTable').on('click', '.delete-user-btn', function() {
            deleteItem("{{ url('inventory/users') }}/" + $(this).data('id'), accountsTable, 'Delete Account?', 'Record will be wiped.');
        });

        $('#accountForm').on('submit', e => handleFormSubmit(e, "{{ route('inventory.users.store') }}", 'accountModal', accountsTable));
    });

    function openAddAccountModal() { 
        $('#accountForm')[0].reset(); 
        $('#account_id_input').val(''); 
        $('#password_hint').hide(); 
        $('#accountModalTitle').html('<i class="fa-solid fa-user-plus text-amber-600"></i> Add Account'); 
        $('#accountModal').removeClass('hidden').addClass('flex'); 
    }
</script>
@endpush
