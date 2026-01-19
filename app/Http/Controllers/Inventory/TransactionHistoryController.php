<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\InventoryTransaction;
use App\Models\InventoryModel\InventoryProduct;
use App\Models\InventoryModel\TransactionCategory;
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
            ->select('inv_t_product_detail.id', 'products.part_no', 'products.part_name', 'inv_t_product_detail.revision')
            ->where('inv_t_product_detail.is_active', 1)
            ->orderBy('products.part_no')
            ->get();
        
        $categories = TransactionCategory::select('id', 'code', 'name', 'effect')->orderBy('name')->get();
        $pics = PIC::where('is_active', 1)->orderBy('name')->get();

        return view('inventory.transaction_history', compact('products', 'categories', 'pics'));
    }
 public function getData(Request $request)
{
    $query = InventoryTransaction::with([
        'product.product',
        'pic',
        'transactionCategory'
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
    if (!empty($request->pic_id)) {
        $picId = Pic::decodeHash($request->pic_id);
        $query->where('pic_id', $picId);
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
              ->orWhereHas('pic', function ($q3) use ($search) {
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
    $columns = [
        'transaction_date',
        'product_detail_id',
        'transaction_category_id',
        'qty',
        'pic_id',
        'remark'
    ];

    $orderColumnIndex = $request->input('order.0.column', 0);
    $orderDirection   = $request->input('order.0.dir', 'desc');

    $query->orderBy(
        $columns[$orderColumnIndex] ?? 'transaction_date',
        $orderDirection
    );

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
                         ($item->product->revision ? ' - '.$item->product->revision : ''),
            'product_name' => $item->product->product->part_name ?? '-',
            'category' => $item->transactionCategory->code ?? '-',
            'qty' => $item->qty,
            'pic_name' => $item->pic->name ?? '-',
            'remark' => $item->remark
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

        $transaction = InventoryTransaction::with(['product.product', 'pic', 'transactionCategory'])->find($decodedId);
        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        return response()->json([
            'id' => $transaction->hash_id,
            'transaction_date' => $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : null,
            'product_detail_id' => $transaction->product->hash_id,
            'part_no' => $transaction->product->product->part_no ?? null,
            'product_name' => $transaction->product->product->part_name ?? null,
            'transaction_category_id' => $transaction->transactionCategory->hash_id,
            'qty' => $transaction->qty,
            'pic_id' => $transaction->pic->hash_id,
            'remark' => $transaction->remark,
        ]);
    }

    public function update(Request $request, $id)
{
    // 1. Decode ID transaksi utama
    $decodedId = InventoryTransaction::decodeHash($id);
    $transaction = InventoryTransaction::findOrFail($decodedId);

    // 2. Decode inputs (mengikuti pola method store Anda)
    $data = $request->all();
    if (isset($data['product_detail_id']) && !is_numeric($data['product_detail_id'])) {
        $data['product_detail_id'] = \App\Models\InventoryModel\InventoryProduct::decodeHash($data['product_detail_id']);
    }
    if (isset($data['transaction_category_id']) && !is_numeric($data['transaction_category_id'])) {
        $data['transaction_category_id'] = \App\Models\InventoryModel\TransactionCategory::decodeHash($data['transaction_category_id']);
    }
    if (isset($data['pic_id']) && !is_numeric($data['pic_id'])) {
        $data['pic_id'] = \App\Models\InventoryModel\PIC::decodeHash($data['pic_id']);
    }

    $request->merge($data);

    $request->validate([
        'product_detail_id' => 'required|exists:inv_t_product_detail,id',
        'transaction_date' => 'required|date',
        'qty' => 'required|integer|min:1',
        'transaction_category_id' => 'required|exists:inv_m_transaction_category,id',
        'pic_id' => 'required|exists:inv_m_pic,id',
        'remark' => 'nullable|string',
    ]);

    DB::beginTransaction();
    try {
        // A. KEMBALIKAN STOK LAMA (Revert)
        $oldProduct = InventoryProduct::findOrFail($transaction->product_detail_id);
        $oldCategory = TransactionCategory::findOrFail($transaction->transaction_category_id);
        $oldProduct->current_stock_qty -= ($transaction->qty * $oldCategory->effect);
        $oldProduct->save();

        // B. UPDATE DATA TRANSAKSI
        $transaction->update([
            'product_detail_id' => $request->product_detail_id,
            'transaction_date' => $request->transaction_date,
            'qty' => $request->qty,
            'transaction_category_id' => $request->transaction_category_id,
            'pic_id' => $request->pic_id,
            'remark' => $request->remark,
        ]);

        // C. TERAPKAN STOK BARU
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