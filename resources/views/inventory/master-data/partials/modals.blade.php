{{-- Coil Center Modals --}}
<div id="modal-coil-center-add" class="modal-container hidden">
    <div class="relative p-4 w-full max-w-md">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal text-gray-400 absolute top-2.5 right-2.5 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Add Coil Center</h3>
            <form class="modal-form" data-action="{{ route('inventory.coilCenter.store') }}" data-table="coilCenterTable">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Code <span class="text-red-600">*</span></label>
                    <input type="text" name="code" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g. CC001">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                    <input type="text" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Address</label>
                    <textarea name="address" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="flex gap-4">
                    <button type="button" class="close-modal flex-1 px-5 py-2.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modal-coil-center-edit" class="modal-container hidden">
    <div class="relative p-4 w-full max-w-md">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal text-gray-400 absolute top-2.5 right-2.5 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Edit Coil Center</h3>
            <form class="modal-form" data-table="coilCenterTable">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Code <span class="text-red-600">*</span></label>
                    <input type="text" name="code" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                    <input type="text" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Address</label>
                    <textarea name="address" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="flex gap-4">
                    <button type="button" class="close-modal flex-1 px-5 py-2.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Similar modals for Material Spec, Unit, Rank - using same structure --}}
{{-- Material Spec Add Modal --}}
<div id="modal-material-spec-add" class="modal-container hidden">
    <div class="relative p-4 w-full max-w-md">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal text-gray-400 absolute top-2.5 right-2.5 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Add Material Spec</h3>
            <form class="modal-form" data-action="{{ route('inventory.materialSpec.store') }}" data-table="materialSpecTable">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Spec Name <span class="text-red-600">*</span></label>
                    <input type="text" name="spec_name" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g. JSC270CC">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Coating Type</label>
                    <select name="coating_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Select Coating Type</option>
                        <option value="GA">Galvanis (GA)</option>
                        <option value="Non-GA">Non-Galvanis (Non-GA)</option>
                    </select>
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="flex gap-4">
                    <button type="button" class="close-modal flex-1 px-5 py-2.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modal-material-spec-edit" class="modal-container hidden">
    <div class="relative p-4 w-full max-w-md">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal text-gray-400 absolute top-2.5 right-2.5 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Edit Material Spec</h3>
            <form class="modal-form" data-table="materialSpecTable">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Spec Name <span class="text-red-600">*</span></label>
                    <input type="text" name="spec_name" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Coating Type</label>
                    <select name="coating_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Select Coating Type</option>
                        <option value="Galvanis">Galvanis (GA)</option>
                        <option value="Non-Galvanis">Non-Galvanis (Non-GA)</option>
                    </select>
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="flex gap-4">
                    <button type="button" class="close-modal flex-1 px-5 py-2.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Unit Add Modal --}}
<div id="modal-unit-add" class="modal-container hidden">
    <div class="relative p-4 w-full max-w-md">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal text-gray-400 absolute top-2.5 right-2.5 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Add Unit</h3>
            <form class="modal-form" data-action="{{ route('inventory.unit.store') }}" data-table="unitTable">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Code <span class="text-red-600">*</span></label>
                    <input type="text" name="code" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g. SHT">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                    <input type="text" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g. Sheet">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="flex gap-4">
                    <button type="button" class="close-modal flex-1 px-5 py-2.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modal-unit-edit" class="modal-container hidden">
    <div class="relative p-4 w-full max-w-md">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal text-gray-400 absolute top-2.5 right-2.5 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Edit Unit</h3>
            <form class="modal-form" data-table="unitTable">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Code <span class="text-red-600">*</span></label>
                    <input type="text" name="code" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                    <input type="text" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="flex gap-4">
                    <button type="button" class="close-modal flex-1 px-5 py-2.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Rank Add Modal --}}
<div id="modal-rank-add" class="modal-container hidden">
    <div class="relative p-4 w-full max-w-md">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal text-gray-400 absolute top-2.5 right-2.5 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Add Rank</h3>
            <form class="modal-form" data-action="{{ route('inventory.rank.store') }}" data-table="rankTable">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Code <span class="text-red-600">*</span></label>
                    <input type="text" name="code" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g. 1-A">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Limit Value <span class="text-red-600">*</span></label>
                    <input type="number" name="limit_value" required min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g. 100">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
                    <textarea name="description" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="flex gap-4">
                    <button type="button" class="close-modal flex-1 px-5 py-2.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modal-rank-edit" class="modal-container hidden">
    <div class="relative p-4 w-full max-w-md">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal text-gray-400 absolute top-2.5 right-2.5 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Edit Rank</h3>
            <form class="modal-form" data-table="rankTable">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Code <span class="text-red-600">*</span></label>
                    <input type="text" name="code" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Limit Value <span class="text-red-600">*</span></label>
                    <input type="number" name="limit_value" required min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
                    <textarea name="description" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="flex gap-4">
                    <button type="button" class="close-modal flex-1 px-5 py-2.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Supplier Modals --}}
<div id="modal-supplier-add" class="modal-container hidden">
    <div class="relative p-4 w-full max-w-md">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal text-gray-400 absolute top-2.5 right-2.5 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Add Supplier</h3>
            <form class="modal-form" data-action="{{ route('inventory.supplier.store') }}" data-table="supplierTable">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Data Source</label>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="source_type" value="manual" checked class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <span class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">New (Manual)</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="source_type" value="global" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <span class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">From Global (Promise)</span>
                        </label>
                    </div>
                </div>

                <div id="global-supplier-container" class="mb-4 hidden">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Search Global Supplier</label>
                    <select id="global_supplier_search" class="select2-global-supplier w-full">
                        <option value="">Search by code or name...</option>
                    </select>

                    {{-- Card Preview for Global Supplier --}}
                    <div id="supplier-card-preview" class="mt-4 hidden relative p-4 bg-blue-50 dark:bg-gray-700 rounded-lg border border-blue-100 dark:border-gray-600">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="text-base font-semibold text-blue-900 dark:text-white" id="card-name">-</h4>
                                <p class="text-sm text-blue-700 dark:text-blue-300" id="card-code">-</p>
                            </div>
                            <div class="p-2 bg-blue-100 dark:bg-gray-600 rounded-full">
                                <i class="fa-solid fa-cloud text-blue-600 dark:text-blue-400"></i>
                            </div>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">Email</span>
                                <span class="font-medium text-gray-900 dark:text-gray-200" id="card-email">-</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">Phone</span>
                                <span class="font-medium text-gray-900 dark:text-gray-200" id="card-phone">-</span>
                            </div>
                            <div class="col-span-2">
                                <span class="block text-xs text-gray-500 dark:text-gray-400">Address</span>
                                <span class="font-medium text-gray-900 dark:text-gray-200" id="card-address">-</span>
                            </div>
                        </div>
                        <input type="hidden" name="promise_supp_id" id="add_promise_supp_id">
                    </div>
                </div>

                <div id="supplier-detail-fields" class="mt-4">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Code <span class="text-red-600">*</span></label>
                            <input type="text" name="code" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g. SUP001">
                            <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                        <input type="text" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Supplier Name">
                        <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                            <input type="email" name="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Phone</label>
                            <input type="text" name="phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Address</label>
                        <textarea name="address" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <button type="button" class="close-modal flex-1 px-5 py-2.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modal-supplier-edit" class="modal-container hidden">
    <div class="relative p-4 w-full max-w-md">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal text-gray-400 absolute top-2.5 right-2.5 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Edit Supplier</h3>
            <form class="modal-form" data-table="supplierTable">
                @csrf
                @method('PUT')
                {{-- Hidden promise_supp_id --}}
                <input type="hidden" name="promise_supp_id" id="edit_promise_supp_id">

                <div id="supplier-edit-detail-fields">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Code <span class="text-red-600">*</span></label>
                            <input type="text" name="code" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                        <input type="text" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                            <input type="email" name="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Phone</label>
                            <input type="text" name="phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Address</label>
                        <textarea name="address" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <button type="button" class="close-modal flex-1 px-5 py-2.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Transaction Category Modals --}}
<div id="modal-transaction-category-add" class="modal-container hidden">
    <div class="relative p-4 w-full max-w-md">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal text-gray-400 absolute top-2.5 right-2.5 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Add Transaction Category</h3>
            <form class="modal-form" data-action="{{ route('inventory.transactionCategory.store') }}" data-table="transactionCategoryTable">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Code <span class="text-red-600">*</span></label>
                    <input type="text" name="code" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g. IN-PUR">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name <span class="text-red-600">*</span></label>
                    <input type="text" name="name" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g. Purchase Incoming">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Effect <span class="text-red-600">*</span></label>
                    <select name="effect" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Select Effect</option>
                        <option value="1">Masuk / Penambahan (+)</option>
                        <option value="-1">Keluar / Pengurangan (-)</option>
                    </select>
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="flex gap-4">
                    <button type="button" class="close-modal flex-1 px-5 py-2.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modal-transaction-category-edit" class="modal-container hidden">
    <div class="relative p-4 w-full max-w-md">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal text-gray-400 absolute top-2.5 right-2.5 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Edit Transaction Category</h3>
            <form class="modal-form" data-table="transactionCategoryTable">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Code <span class="text-red-600">*</span></label>
                    <input type="text" name="code" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name <span class="text-red-600">*</span></label>
                    <input type="text" name="name" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Effect <span class="text-red-600">*</span></label>
                    <select name="effect" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Select Effect</option>
                        <option value="1">Masuk / Penambahan (+)</option>
                        <option value="-1">Keluar / Pengurangan (-)</option>
                    </select>
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="flex gap-4">
                    <button type="button" class="close-modal flex-1 px-5 py-2.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- PIC Modals --}}
<div id="modal-pic-add" class="modal-container hidden">
    <div class="relative p-4 w-full max-w-md">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal text-gray-400 absolute top-2.5 right-2.5 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Add PIC</h3>
            <form class="modal-form" data-action="{{ route('inventory.pic.store') }}" data-table="picTable">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name <span class="text-red-600">*</span></label>
                    <input type="text" name="name" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Name">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="flex gap-4">
                    <button type="button" class="close-modal flex-1 px-5 py-2.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modal-pic-edit" class="modal-container hidden">
    <div class="relative p-4 w-full max-w-md">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal text-gray-400 absolute top-2.5 right-2.5 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Edit PIC</h3>
            <form class="modal-form" data-table="picTable">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name <span class="text-red-600">*</span></label>
                    <input type="text" name="name" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="error-msg text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div class="flex gap-4">
                    <button type="button" class="close-modal flex-1 px-5 py-2.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal (Shared) --}}
<div id="modal-delete" class="modal-container hidden">
    <div class="relative p-4 w-full max-w-md">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal text-gray-400 absolute top-2.5 right-2.5 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this item?</p>
            <div class="flex justify-center gap-4">
                <button type="button" class="close-modal px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-600">No, cancel</button>
                <button type="button" id="confirmDelete" class="px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Yes, I'm sure</button>
            </div>
        </div>
    </div>
</div>

<style>
.modal-container {
    position: fixed;
    top: 0;
    right: 0;
    left: 0;
    bottom: 0;
    z-index: 50;
    display: none;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
    background-color: rgb(15 23 42 / 0.5);
}
.modal-container:not(.hidden) {
    display: flex;
}
</style>
