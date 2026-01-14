@extends('layouts.app')
@section('title', 'Inventory Product Management')
@section('header-title', 'Inventory Product')

@section('content')
<div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-xl overflow-hidden h-full flex flex-col border border-slate-200 dark:border-slate-700">
                <div class="p-4 md:p-6 overflow-x-auto flex-1 bg-white dark:bg-gray-800">
                    <table id="TransactionHistoryTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-400">
                            <tr class="text-center">
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Product</th>
                                <th class="px-6 py-3">Category</th>
                                <th class="px-6 py-3">Qty</th>
                                <th class="px-6 py-3">PIC</th>
                                <th class="px-6 py-3">Remark</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        $('#TransactionHistoryTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('inventory.transactionHistory.getData') }}",
                type: 'GET',
                data: function (d) {
                    d.product_detail_id = $('#filter_product').val(); 
                }
            },
            columns: [
                { 
                    data: 'transaction_date', 
                    name: 'transaction_date' 
                },
                { 
                    data: null, 
                    name: 'product_name',
                    render: function(data, type, row) {
                        return `<div class="font-medium text-slate-900 dark:text-white">${row.part_no}</div>
                                <div class="text-xs text-gray-500">${row.product_name}</div>`;
                    }
                },
                { 
                    data: 'category', 
                    name: 'category' 
                },
                { 
                    data: 'qty', 
                    name: 'qty', 
                    className: 'text-center font-bold' 
                },
                { 
                    data: 'pic_name', 
                    name: 'pic_name' 
                },
                { 
                    data: 'remark', 
                    name: 'remark',
                    render: function(data) {
                        return data ? `<span class="italic text-gray-400 text-xs">${data}</span>` : '-';
                    }
                }
            ],
            language: {
                processing: '<div class="p-4 text-blue-500">Loading data...</div>',
            },
            order: [[0, 'desc']] 
        });
        $('#filter_product').on('change', function() {
            $('#TransactionHistoryTable').DataTable().ajax.reload();
        });
    });
</script>
@endpush