<?php

namespace App\Http\Controllers\Inventory\Material;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Material\InventoryTransaction;
use App\Models\InventoryModel\Material\InventoryProduct;
use App\Models\InventoryModel\Material\TransactionCategory;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryModel\PIC;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionHistoryController extends Controller
{
 public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->data($request);
        }
        
        $products = InventoryProduct::join('products', 'inv_t_product_detail.product_id', '=', 'products.id')
            ->leftJoin('models as m', 'm.id', '=', 'inv_t_product_detail.model_id')
            ->leftJoin('inv_m_revision', 'inv_t_product_detail.revision_id', '=', 'inv_m_revision.id')
            ->select('inv_t_product_detail.id', 'products.part_no', 'products.part_name', 'inv_m_revision.code as revision', 'm.name as model_name')
            ->where('inv_t_product_detail.is_active', 1)
            ->orderBy('products.part_no')
            ->get();
        
        $categories = TransactionCategory::select('id', 'code', 'name', 'effect')->orderBy('name')->get();
        $pics = \App\Models\User::orderBy('name')->get();

        return view('inventory.material.transaction_history', compact('products', 'categories', 'pics'));
    }
 public function getData(Request $request)
{
    $query = InventoryTransaction::with([
        'product.product',
        'product.model',
        'product.revision',
        'user',
        'transactionCategory',
        'coilCenter',
        'supplier',
        'destination'
    ]);

    /* =====================================================
     * FILTER: PRODUCT
     * ===================================================== */
    if (!empty($request->product_detail_id)) {
        $prodId = InventoryProduct::decodeHash($request->product_detail_id);
        $query->where('product_detail_id', $prodId);
    }

    /* =====================================================
     * FILTER: CATEGORY (SPESIFIK)
     * contoh: IN, OUT-EVENT, OUT-TRIAL
     * ===================================================== */
    if (!empty($request->transaction_category_id)) {
        $categoryId = TransactionCategory::decodeHash(
            $request->transaction_category_id
        );

        $query->where('transaction_category_id', $categoryId);
    }

    /* =====================================================
     * FILTER: PIC
     * ===================================================== */
    if (!empty($request->user_id)) {
        $query->where('user_id', $request->user_id);
    }

    /* =====================================================
     * FILTER: DATE RANGE
     * format: YYYY-MM-DD - YYYY-MM-DD
     * ===================================================== */
   if ($request->filled('date_range') && str_contains($request->date_range, ' - ')) {

    [$start, $end] = explode(' - ', $request->date_range);

    if ($start && $end) {
        $query->whereBetween('transaction_date', [
            Carbon::parse($start)->startOfDay(),
            Carbon::parse($end)->endOfDay()
        ]);
    }
}


    /* =====================================================
     * GLOBAL SEARCH (DATATABLE)
     * ===================================================== */
    if (!empty($request->search['value'])) {
        $search = $request->search['value'];

        $query->where(function ($q) use ($search) {
            $q->whereHas('transactionCategory', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('code', 'like', "%{$search}%");
                })
              ->orWhereHas('user', function ($q3) use ($search) {
                    $q3->where('name', 'like', "%{$search}%");
                })
              ->orWhereHas('product.product', function ($q4) use ($search) {
                    $q4->where('part_no', 'like', "%{$search}%")
                       ->orWhere('part_name', 'like', "%{$search}%");
                })
              ->orWhere('remark', 'like', "%{$search}%");
        });
    }

    /* =====================================================
     * SORTING
     * ===================================================== */
    $sortableColumns = [
        0 => 'id',                  // No
        1 => 'transaction_date',    // Trans. Date
        2 => 'updated_at',          // Timestamp
        3 => 'model_name',          // Model
        4 => 'part_no',             // Part Details
        5 => 'transaction_category_id', // Category
        6 => 'origin_destination',  // Origin / Destination
        7 => 'qty',                 // Qty
        8 => 'pic_name',            // PIC
        9 => 'remark'               // Remarks
    ];
    
    $orderColumnIndex = $request->input('order.0.column', 1);
    $orderDirection   = $request->input('order.0.dir', 'desc');
    $orderCol = $sortableColumns[$orderColumnIndex] ?? 'transaction_date';

    if ($orderCol === 'model_name') {
        $query->join('inv_t_product_detail', 'inv_t_product_detail.id', '=', 'inv_t_inventory_transaction.product_detail_id')
              ->leftJoin('models', 'models.id', '=', 'inv_t_product_detail.model_id')
              ->orderBy('models.name', $orderDirection)
              ->select('inv_t_inventory_transaction.*');
    } elseif ($orderCol === 'part_no') {
        $query->join('inv_t_product_detail', 'inv_t_product_detail.id', '=', 'inv_t_inventory_transaction.product_detail_id')
              ->join('products', 'products.id', '=', 'inv_t_product_detail.product_id')
              ->orderBy('products.part_no', $orderDirection)
              ->select('inv_t_inventory_transaction.*'); // Avoid column collision
    } elseif ($orderCol === 'pic_name') {
        $query->join('users', 'users.id', '=', 'inv_t_inventory_transaction.user_id')
              ->orderBy('users.name', $orderDirection)
              ->select('inv_t_inventory_transaction.*');
    } elseif ($orderCol === 'transaction_category_id') {
        $query->join('inv_m_transaction_category', 'inv_m_transaction_category.id', '=', 'inv_t_inventory_transaction.transaction_category_id')
              ->orderBy('inv_m_transaction_category.code', $orderDirection)
              ->select('inv_t_inventory_transaction.*');
    } else {
        $query->orderBy(
            in_array($orderCol, ['transaction_date', 'updated_at', 'qty', 'remark']) ? $orderCol : 'transaction_date',
            $orderDirection
        );
    }

    /* =====================================================
     * PAGINATION (DATATABLE)
     * ===================================================== */
    $totalData     = InventoryTransaction::count();
    $filteredData  = $query->count();

    $transactions = $query
        ->skip($request->start)
        ->take($request->length)
        ->get();

    /* =====================================================
     * TRANSFORM RESPONSE
     * ===================================================== */
    $data = $transactions->map(function ($item) {
        return [
            'id' => $item->hash_id,
            'transaction_date' => optional($item->transaction_date)->format('Y-m-d'),
            'part_no' => ($item->product->product->part_no ?? '-') .
                         ($item->product->revision ? '-'.$item->product->revision->code : ''),
            'model_name' => $item->product->model->name ?? 'No Model',
            'product_name' => $item->product->product->part_name ?? '-',
            'category' => $item->transactionCategory->code ?? '-',
            'qty' => $item->qty,
            'pic_name' => $item->user->name ?? '-',
            'origin_destination' => collect([
                $item->coilCenter?->code,
                $item->supplier?->code,
                $item->destination?->code ? '(To: ' . $item->destination->code . ')' : null
            ])->filter()->implode(' '),
            'remark' => $item->remark,
            'created_at' => $item->created_at ? $item->created_at->format('d M Y H:i:s') : '-',
            'updated_at' => $item->updated_at ? $item->updated_at->format('d M Y H:i:s') : '-',
        ];
    });

    return response()->json([
        'draw' => intval($request->input('draw', 1)),
        'recordsTotal' => $totalData,
        'recordsFiltered' => $filteredData,
        'data' => $data
    ]);
}

    public function edit($id)
    {
        $decodedId = InventoryTransaction::decodeHash($id);
        if (!$decodedId) {
            return response()->json(['error' => 'Invalid ID'], 404);
        }

        $transaction = InventoryTransaction::with(['product.product', 'product.revision', 'user', 'transactionCategory'])->find($decodedId);
        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        return response()->json([
            'id' => $transaction->hash_id,
            'transaction_date' => $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : null,
            'product_detail_id' => $transaction->product->hash_id,
            'part_no' => ($transaction->product->product->part_no ?? null) . ($transaction->product->revision ? '-' . $transaction->product->revision->code : ''),
            'product_name' => $transaction->product->product->part_name ?? null,
            'transaction_category_id' => $transaction->transactionCategory->hash_id,
            'qty' => $transaction->qty,
            'user_id' => $transaction->user_id, 
            'pic_name' => $transaction->user->name ?? null,
            'remark' => $transaction->remark,
        ]);
    }

    public function update(Request $request, $id)
{
    //Decode ID transaksi utama
    $decodedId = InventoryTransaction::decodeHash($id);
    $transaction = InventoryTransaction::findOrFail($decodedId);

    //Decode inputs
    $data = $request->all();
    if (isset($data['product_detail_id']) && !is_numeric($data['product_detail_id'])) {
        $data['product_detail_id'] = \App\Models\InventoryModel\Material\InventoryProduct::decodeHash($data['product_detail_id']);
    }
    if (isset($data['transaction_category_id']) && !is_numeric($data['transaction_category_id'])) {
        $data['transaction_category_id'] = \App\Models\InventoryModel\Material\TransactionCategory::decodeHash($data['transaction_category_id']);
    }
    if (isset($data['user_id']) && !is_numeric($data['user_id'])) {
        // user_id is coming as numeric or can be extracted from Auth if missing
    }

    $request->merge($data);

    $request->validate([
        'product_detail_id' => 'required|exists:inv_t_product_detail,id',
        'transaction_date' => 'required|date',
        'qty' => 'required|integer|min:1',
        'transaction_category_id' => 'required|exists:inv_m_transaction_category,id',
        'user_id' => 'required|exists:users,id', 
        'remark' => 'nullable|string',
    ]);

    DB::beginTransaction();
    try {
        $oldProduct = InventoryProduct::findOrFail($transaction->product_detail_id);
        $oldCategory = TransactionCategory::findOrFail($transaction->transaction_category_id);
        $oldProduct->current_stock_qty -= ($transaction->qty * $oldCategory->effect);
        $oldProduct->save();

        $transaction->update([
            'product_detail_id' => $request->product_detail_id,
            'transaction_date' => $request->transaction_date,
            'qty' => $request->qty,
            'transaction_category_id' => $request->transaction_category_id,
            'user_id' => $request->user_id ?? $transaction->user_id,
            'remark' => $request->remark,
        ]);

        $newProduct = InventoryProduct::findOrFail($request->product_detail_id);
        $newCategory = TransactionCategory::findOrFail($request->transaction_category_id);
        $newProduct->current_stock_qty += ($request->qty * $newCategory->effect);
        $newProduct->save();

        DB::commit();
        return response()->json(['success' => true, 'message' => 'Transaction updated successfully.']);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
}
