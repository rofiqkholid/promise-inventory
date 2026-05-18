@extends('layouts.app')

@section('title', 'STO Event Details')

@section('content')
<div class="text-gray-900 dark:text-gray-100">

    {{-- Breadcrumb & Actions --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.tool.sto.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 hover:text-primary-600 transition-all"><i class="fa-solid fa-arrow-left"></i></a>
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold tracking-tighter">{{ $event->name }}</h2>
                    @php
                        $statusCls = match($event->status) {
                            'draft'     => 'bg-gray-100 text-gray-700',
                            'submitted' => 'bg-blue-100 text-blue-700',
                            'approved'  => 'bg-emerald-100 text-emerald-700',
                            'rejected'  => 'bg-red-100 text-red-700',
                            default     => 'bg-gray-100 text-gray-700'
                        };
                    @endphp
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $statusCls }}">{{ $event->status }}</span>
                </div>
                <p class="text-[10px] text-gray-500 font-mono mt-1">{{ $event->code }} • {{ $event->period_start->format('d M Y') }}</p>
            </div>
        </div>

        <div class="flex gap-2">
            @if($event->status === 'draft')
                <button type="button" id="btnSubmitSTO" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest transition-all">
                    <i class="fa-solid fa-paper-plane"></i> Submit for Approval
                </button>
            @endif

            @if($event->status === 'submitted' && Auth::user()->hasRole('approver|manager|admin')) {{-- Assume roles --}}
                <button type="button" id="btnApproveSTO" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest transition-all">
                    <i class="fa-solid fa-check-double"></i> Approve STO
                </button>
                <button type="button" id="btnRejectSTO" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-red-600 hover:bg-red-700 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest transition-all">
                    <i class="fa-solid fa-ban"></i> Reject
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left: Event Info & Stats --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-xs border border-gray-200 dark:border-gray-800 p-5">
                <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-primary-500"></i> Event Information
                </h4>
                <div class="space-y-3">
                    <div class="flex justify-between border-b border-gray-50 dark:border-gray-800 pb-2">
                        <span class="text-[10px] text-gray-500 uppercase font-medium">Created By</span>
                        <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $event->creator?->name }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-50 dark:border-gray-800 pb-2">
                        <span class="text-[10px] text-gray-500 uppercase font-medium">Approved By</span>
                        <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $event->approver?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-50 dark:border-gray-800 pb-2">
                        <span class="text-[10px] text-gray-500 uppercase font-medium">Fast Moving Items</span>
                        <span class="text-xs font-bold font-mono">{{ $event->fastDetails->count() }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-50 dark:border-gray-800 pb-2">
                        <span class="text-[10px] text-gray-500 uppercase font-medium">Slow Moving Items</span>
                        <span class="text-xs font-bold font-mono">{{ $event->slowDetails->count() }}</span>
                    </div>
                    <div class="pt-2">
                        <span class="text-[10px] text-gray-500 uppercase font-medium block mb-1">Description</span>
                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed italic">{{ $event->description ?: 'No description provided.' }}</p>
                    </div>
                    @if($event->rejection_note)
                    <div class="pt-2 p-3 bg-red-50 dark:bg-red-900/20 rounded-xs border border-red-100 dark:border-red-800">
                        <span class="text-[10px] text-red-600 uppercase font-bold block mb-1">Rejection Note</span>
                        <p class="text-xs text-red-700 dark:text-red-400 leading-relaxed">{{ $event->rejection_note }}</p>
                    </div>
                    @endif
                </div>
            </div>

            @if($event->status === 'draft')
            <div class="bg-white dark:bg-gray-900 rounded-xs border border-gray-200 dark:border-gray-800 p-5">
                <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-4">Add Items</h4>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="showMdl('modal-add-fast')" class="flex flex-col items-center justify-center gap-2 p-4 bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800 rounded-xs hover:bg-primary-100 dark:hover:bg-primary-800 transition-all group">
                        <i class="fa-solid fa-bolt text-primary-600 text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[9px] font-bold uppercase tracking-widest text-primary-700 dark:text-primary-400">Fast Moving</span>
                    </button>
                    <button type="button" onclick="showMdl('modal-add-slow')" class="flex flex-col items-center justify-center gap-2 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 rounded-xs hover:bg-emerald-100 dark:hover:bg-emerald-800 transition-all group">
                        <i class="fa-solid fa-clock-rotate-left text-emerald-600 text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[9px] font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-400">Slow Moving</span>
                    </button>
                </div>
            </div>
            @endif
        </div>

        {{-- Right: Details Tables --}}
        <div class="lg:col-span-2 space-y-8">
            
            {{-- Fast Moving Items --}}
            <div class="bg-white dark:bg-gray-900 rounded-xs border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
                    <h5 class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Fast Moving Details</h5>
                    <span class="px-2 py-0.5 rounded-xs bg-primary-100 text-primary-700 text-[9px] font-bold">{{ $event->fastDetails->count() }} Items</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/30 text-[9px] font-bold tracking-wider text-gray-400">
                            <tr>
                                <th class="px-6 py-3">Tool Name</th>
                                <th class="px-6 py-3">Location</th>
                                <th class="px-6 py-3 text-center">Sys Qty</th>
                                <th class="px-6 py-3 text-center">Phys Qty</th>
                                <th class="px-6 py-3 text-center">Diff</th>
                                <th class="px-6 py-3">Note</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            @forelse($event->fastDetails as $item)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $item->tool->name }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $item->tool->brand }}</div>
                                </td>
                                <td class="px-6 py-4 text-[10px] font-medium text-gray-600 dark:text-gray-400 uppercase tracking-tight">
                                    {{ $item->location?->code }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono font-bold text-gray-500">{{ $item->system_qty }}</td>
                                <td class="px-6 py-4 text-center font-mono font-bold text-gray-900 dark:text-white">{{ $item->physical_qty }}</td>
                                <td class="px-6 py-4 text-center font-mono font-bold {{ $item->adjustment_qty > 0 ? 'text-emerald-600' : ($item->adjustment_qty < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                    {{ $item->adjustment_qty > 0 ? '+' : '' }}{{ $item->adjustment_qty }}
                                </td>
                                <td class="px-6 py-4 text-gray-500 italic truncate max-w-[150px]">{{ $item->note ?: '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400 italic">No fast moving items added yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Slow Moving Items --}}
            <div class="bg-white dark:bg-gray-900 rounded-xs border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-800 bg-emerald-50/20 dark:bg-emerald-900/10 flex justify-between items-center">
                    <h5 class="text-[10px] font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-400">Slow Moving Details</h5>
                    <span class="px-2 py-0.5 rounded-xs bg-emerald-100 text-emerald-700 text-[9px] font-bold">{{ $event->slowDetails->count() }} Batches</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/30 text-[9px] font-bold tracking-wider text-gray-400">
                            <tr>
                                <th class="px-6 py-3">ID Number / Tool</th>
                                <th class="px-6 py-3 text-center">Check Result</th>
                                <th class="px-6 py-3 text-center">Physical Rate</th>
                                <th class="px-6 py-3 text-right">Age (Y)</th>
                                <th class="px-6 py-3 text-right">Asset Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            @forelse($event->slowDetails as $item)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-[10px] font-mono font-bold text-emerald-600 mb-0.5">{{ $item->batch->id_number }}</div>
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $item->batch->tool->name }}</div>
                                </td>
                                <td class="px-6 py-4 text-center uppercase">
                                    <span class="px-2 py-0.5 rounded-full text-[8px] font-bold {{ $item->physical_check === 'ok' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $item->physical_check }}</span>
                                </td>
                                <td class="px-6 py-4 text-center font-mono font-bold text-gray-900 dark:text-white">{{ $item->physical_rate }}%</td>
                                <td class="px-6 py-4 text-right font-mono text-gray-500">{{ $item->age_years }}</td>
                                <td class="px-6 py-4 text-right font-mono font-bold text-emerald-600">Rp {{ number_format($item->remaining_value, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400 italic">No slow moving batches added yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Add Fast --}}
<div id="modal-add-fast" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 shadow-2xl transition-all">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4">
            <h3 class="text-[11px] font-bold uppercase tracking-widest">Add Fast Moving Item</h3>
            <button class="close-modal text-gray-400 hover:text-gray-500"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="formAddFast" class="p-6">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 uppercase tracking-wider">Tool</label>
                    <select name="tool_id" required class="select2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                        <option value="">Select Tool</option>
                        @foreach($fastTools as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->brand }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 uppercase tracking-wider">Location</label>
                    <select name="location_id" required class="select2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                        <option value="">Select Location</option>
                        @foreach($locations as $l)
                            <option value="{{ $l->id }}">{{ $l->code }} — {{ $l->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 uppercase tracking-wider">Physical Quantity</label>
                    <input type="number" name="physical_qty" required min="0" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                </div>
                <div>
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 uppercase tracking-wider">Note</label>
                    <textarea name="note" rows="2" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all"></textarea>
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-3">
                <button type="submit" class="w-full px-5 py-3 bg-primary-600 hover:bg-primary-700 text-[10px] font-bold text-white uppercase tracking-widest transition-all">Add to List</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Add Slow --}}
<div id="modal-add-slow" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 shadow-2xl transition-all">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4">
            <h3 class="text-[11px] font-bold uppercase tracking-widest text-emerald-600">Add Slow Moving Batch</h3>
            <button class="close-modal text-gray-400 hover:text-gray-500"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="formAddSlow" class="p-6">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 uppercase tracking-wider">Asset (ID Number)</label>
                    <select name="batch_id" required class="select2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                        <option value="">Select Asset</option>
                        @foreach($slowBatches as $b)
                            <option value="{{ $b->id }}" data-rate="{{ $b->physical_rate }}">{{ $b->id_number }} — {{ $b->tool->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 uppercase tracking-wider">Check Result</label>
                        <select name="physical_check" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                            <option value="ok">OK</option>
                            <option value="nok">NOK (Retired)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 uppercase tracking-wider">Physical Rate (%)</label>
                        <input type="number" name="physical_rate" value="100" required min="0" max="100" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 uppercase tracking-wider">Note / Reason</label>
                    <textarea name="note" rows="2" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all"></textarea>
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-3">
                <button type="submit" class="w-full px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-[10px] font-bold text-white uppercase tracking-widest transition-all">Add to List</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {
        // Form Add Fast
        $('#formAddFast').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('inventory.tool.sto.addItemFast', $event->id) }}",
                type: "POST",
                data: $(this).serialize(),
                success: (res) => { window.showToast('success', res.message); window.location.reload(); },
                error: (err) => { window.showToast('error', err.responseJSON?.message || 'Error'); }
            });
        });

        // Form Add Slow
        $('select[name="batch_id"]', '#modal-add-slow').on('change', function() {
            const rate = $('option:selected', this).data('rate');
            if (rate !== undefined) {
                $('input[name="physical_rate"]', '#modal-add-slow').val(rate);
            }
        });

        $('#formAddSlow').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('inventory.tool.sto.addItemSlow', $event->id) }}",
                type: "POST",
                data: $(this).serialize(),
                success: (res) => { window.showToast('success', res.message); window.location.reload(); },
                error: (err) => { window.showToast('error', err.responseJSON?.message || 'Error'); }
            });
        });

        // Submit STO
        $('#btnSubmitSTO').on('click', function() {
            Swal.fire({
                title: 'Submit STO?',
                text: "You won't be able to edit items after submission.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Submit'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("{{ route('inventory.tool.sto.submit', $event->id) }}", {_token: "{{ csrf_token() }}"}, function(res) {
                        window.showToast('success', res.message);
                        window.location.reload();
                    });
                }
            });
        });

        // Approve STO
        $('#btnApproveSTO').on('click', function() {
            Swal.fire({
                title: 'Approve STO?',
                text: "Stock levels will be updated based on physical input.",
                icon: 'success',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Approve'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("{{ route('inventory.tool.sto.approve', $event->id) }}", {_token: "{{ csrf_token() }}"}, function(res) {
                        window.showToast('success', res.message);
                        window.location.reload();
                    });
                }
            });
        });

        // Reject STO
        $('#btnRejectSTO').on('click', function() {
            Swal.fire({
                title: 'Reject STO?',
                input: 'textarea',
                inputLabel: 'Rejection Reason',
                inputPlaceholder: 'Type your reason here...',
                showCancelButton: true,
                confirmButtonText: 'Reject',
                confirmButtonColor: '#ef4444'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("{{ route('inventory.tool.sto.reject', $event->id) }}", {
                        _token: "{{ csrf_token() }}",
                        note: result.value
                    }, function(res) {
                        window.showToast('success', res.message);
                        window.location.reload();
                    });
                }
            });
        });

        // Global Modal Logic
        window.showMdl = (id) => { $(`#${id}`).removeClass('hidden'); $(document).trigger('select2:reinit', [$(`#${id}`)]); };
        $(document).on('click', '.close-modal', function() { $(this).closest('.modal-container').addClass('hidden'); });
    });
</script>
@endpush
