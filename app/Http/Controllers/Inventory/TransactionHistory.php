<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\InventoryTransaction;
use Illuminate\Http\Request;

class TransactionHistory extends Controller
{
 public function index()
 {
    return view('inventory.transaction_history');
 }
 public function getData(Request $request)
    {
        $query = InventoryTransaction::with(['product.product', 'pic', 'transactionCategory']);

        // Filter by Product
        if ($request->has('product_detail_id') && !empty($request->product_detail_id)) {
            // Decode HashID if provided
            $prodId = $request->product_detail_id;
            if (!is_numeric($prodId)) {
                $decoded = \App\Models\InventoryModel\InventoryProduct::decodeHash($prodId);
                if ($decoded) $prodId = $decoded;
            }
            $query->where('product_detail_id', $prodId);
        }

        // Global Search
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->whereHas('transactionCategory', function($q4) use ($search) {
                        $q4->where('name', 'like', '%' . $search . '%')
                           ->orWhere('code', 'like', '%' . $search . '%');
                  })
                  ->orWhere('remark', 'like', '%' . $search . '%')
                  ->orWhereHas('pic', function($q3) use ($search) {
                      $q3->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('product.product', function($q2) use ($search) {
                      $q2->where('part_no', 'like', '%' . $search . '%');
                  });
            });
        }

        // Sorting
        $columns = ['id', 'transaction_date', 'product_detail_id', 'transaction_category_id', 'qty', 'pic_id', 'remark'];
        $orderBy = $columns[$request->input('order.0.column')] ?? 'created_at';
        $orderDir = $request->input('order.0.dir') ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        $perPage = $request->input('length', 10);
        $start = $request->input('start', 0);
        
        $total = InventoryTransaction::count();
        $filtered = $query->count();
        $transactions = $query->skip($start)->take($perPage)->get();

        // Transform for DataTable
        $data = $transactions->map(function($item) {
            return [
                'id' => $item->hash_id, // Return HashID
                'transaction_date' => $item->transaction_date ? $item->transaction_date->format('Y-m-d') : '-',
                'part_no' => ($item->product->product->part_no ?? '-') . ($item->product->revision ? ' - ' . $item->product->revision : ''),
                'product_name' => $item->product->product->part_name ?? '-',
                'category' => $item->transactionCategory->code ?? '-',
                'qty' => $item->qty,
                'pic_name' => $item->pic->name ?? '-',
                'remark' => $item->remark,
            ];
        });

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        ]);
    }
}