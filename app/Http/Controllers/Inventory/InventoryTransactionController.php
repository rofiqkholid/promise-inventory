<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\InventoryTransaction;
use App\Models\InventoryModel\InventoryProduct;
use App\Models\InventoryModel\TransactionCategory;
use App\Models\InventoryModel\CoilCenter;
use App\Models\InventoryModel\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryTransactionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->data($request);
        }
        
        $products = InventoryProduct::join('products', 'inv_t_product_detail.product_id', '=', 'products.id')
            ->leftJoin('inv_m_revision as r', 'r.id', '=', 'inv_t_product_detail.revision_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'inv_t_product_detail.unit_id')
            ->select(
                'inv_t_product_detail.id', 
                'products.part_no', 
                'products.part_name', 
                'r.code as revision', 
                'inv_t_product_detail.pcs_per_unit',
                'inv_t_product_detail.weight_kg',
                'inv_t_product_detail.gross_coil',
                'inv_t_product_detail.top_coil',
                'inv_t_product_detail.end_coil',
                'inv_t_product_detail.pitch',
                'inv_t_product_detail.pcs_per_pitch',
                'u.name as unit_name'
            )
            ->where('inv_t_product_detail.is_active', 1)
            ->orderBy('products.part_no')
            ->get();
        
        $categories = TransactionCategory::select('id', 'code', 'name', 'effect')->orderBy('name')->get();
        
        $coilCenters = CoilCenter::select('id', 'code', 'name')->orderBy('code')->get();
        $suppliers = Supplier::select('id', 'code', 'name')->orderBy('code')->get();
        
        $pics = \App\Models\User::select('id', 'name')->orderBy('name')->get();

        return view('inventory.inventory_transaction', compact('products', 'categories', 'coilCenters', 'suppliers', 'pics'));
    }

    public function data(Request $request)
    {
        $query = InventoryTransaction::with(['product.product', 'product.revision', 'user', 'transactionCategory']);

        // Filter by Product
        if ($request->has('product_detail_id') && !empty($request->product_detail_id)) {
            $prodId = $request->product_detail_id;
            if (!is_numeric($prodId)) {
                $decoded = \App\Models\InventoryModel\InventoryProduct::decodeHash($prodId);
                if ($decoded) $prodId = $decoded;
            }
            $query->where('product_detail_id', $prodId);
        }

        // Filter by Category
        if ($request->filled('category_id')) {
            $catId = $request->category_id;
            if (!is_numeric($catId)) {
                $decoded = \App\Models\InventoryModel\TransactionCategory::decodeHash($catId);
                if ($decoded) $catId = $decoded;
            }
            $query->where('transaction_category_id', $catId);
        }

        // Filter by PIC (User)
        if ($request->filled('pic_id')) {
            $picId = $request->pic_id;
            if (!is_numeric($picId)) {
            }
            $query->where('user_id', $picId);
        }

        // Filter by Date Range
        if ($request->filled('date_range') && str_contains($request->date_range, ' - ')) {
            [$start, $end] = explode(' - ', $request->date_range);
            if ($start && $end) {
                $query->whereBetween('transaction_date', [
                    \Carbon\Carbon::parse($start)->startOfDay(),
                    \Carbon\Carbon::parse($end)->endOfDay()
                ]);
            }
        } else {
            // Default to current month if no filter
            $query->whereMonth('transaction_date', date('m'))
                  ->whereYear('transaction_date', date('Y'));
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
                  ->orWhereHas('user', function($q3) use ($search) {
                      $q3->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('product.product', function($q2) use ($search) {
                      $q2->where('part_no', 'like', '%' . $search . '%');
                  });
            });
        }

        // Sorting - Align with Blade: 0:Timestamp, 1:Part, 2:Category, 3:Qty, 4:PIC, 5:Action
        if ($request->has('order')) {
            $sortableColumns = [
                0 => 'inv_t_inventory_transaction.transaction_date',
                1 => 'products.part_no',
                2 => 'inv_m_transaction_category.name',
                3 => 'inv_t_inventory_transaction.qty',
                4 => 'users.name',
            ];
            
            $colIndex = $request->input('order.0.column');
            $dir = $request->input('order.0.dir', 'desc');
            $colName = $sortableColumns[$colIndex] ?? 'inv_t_inventory_transaction.created_at';

            if ($colName === 'products.part_no') {
                $query->join('inv_t_product_detail', 'inv_t_product_detail.id', '=', 'inv_t_inventory_transaction.product_detail_id')
                      ->join('products', 'products.id', '=', 'inv_t_product_detail.product_id')
                      ->select('inv_t_inventory_transaction.*')
                      ->orderBy('products.part_no', $dir);
            } elseif ($colName === 'users.name') {
                $query->join('users', 'users.id', '=', 'inv_t_inventory_transaction.user_id')
                      ->select('inv_t_inventory_transaction.*')
                      ->orderBy('users.name', $dir);
            } elseif ($colName === 'inv_m_transaction_category.name') {
                $query->join('inv_m_transaction_category', 'inv_m_transaction_category.id', '=', 'inv_t_inventory_transaction.transaction_category_id')
                      ->select('inv_t_inventory_transaction.*')
                      ->orderBy('inv_m_transaction_category.name', $dir);
            } else {
                $query->orderBy($colName, $dir);
                if (str_contains($colName, 'transaction_date')) $query->orderBy('inv_t_inventory_transaction.created_at', 'desc');
            }
        } else {
            $query->orderBy('inv_t_inventory_transaction.transaction_date', 'desc')->orderBy('inv_t_inventory_transaction.created_at', 'desc');
        }

        $perPage = $request->input('length', 10);
        $start = $request->input('start', 0);
        
        // Use a consistent base count
        $total = InventoryTransaction::count();
            
        $filtered = $query->count();
        $transactions = $query->skip($start)->take($perPage)->get();

        // Transform for DataTable
        $data = $transactions->map(function($item) {
            return [
                'id' => $item->hash_id,
                'transaction_date' => $item->transaction_date ? $item->transaction_date->format('Y-m-d') : '-',
                'part_no' => ($item->product->product->part_no ?? '-') . ($item->product->revision ? ' - ' . $item->product->revision->code : ''),
                'product_name' => $item->product->product->part_name ?? '-',
                'category' => $item->transactionCategory->code ?? '-',
                'qty' => $item->qty,
                'pic_name' => $item->user->name ?? '-',
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

    public function store(Request $request)
    {
        // Decode inputs before validation
        $data = $request->all();
        
        if (isset($data['product_detail_id']) && !is_numeric($data['product_detail_id'])) {
            $data['product_detail_id'] = \App\Models\InventoryModel\InventoryProduct::decodeHash($data['product_detail_id']);
        }
        if (isset($data['transaction_category_id']) && !is_numeric($data['transaction_category_id'])) {
            $data['transaction_category_id'] = \App\Models\InventoryModel\TransactionCategory::decodeHash($data['transaction_category_id']);
        }
        
        // user_id is now handled automatically via Auth::id()
        $data['user_id'] = Auth::id();
        if (isset($data['coil_center_id']) && !is_numeric($data['coil_center_id'])) {
            $data['coil_center_id'] = \App\Models\InventoryModel\CoilCenter::decodeHash($data['coil_center_id']);
        }
        if (isset($data['supplier_id']) && !is_numeric($data['supplier_id'])) {
            $data['supplier_id'] = \App\Models\InventoryModel\Supplier::decodeHash($data['supplier_id']);
        }
        if (isset($data['destination_id']) && !is_numeric($data['destination_id'])) {
            $data['destination_id'] = \App\Models\InventoryModel\Supplier::decodeHash($data['destination_id']);
        }

        // Replace request data
        $request->merge($data);

        $request->validate([
            'product_detail_id' => 'required|exists:inv_t_product_detail,id',
            'transaction_date' => 'required|date',
            'qty' => 'required|numeric|min:0.01',
            'transaction_category_id' => 'required|exists:inv_m_transaction_category,id',
            'user_id' => 'required',
            'remark' => 'nullable|string',
            'coil_center_id' => 'nullable|exists:inv_m_coil_center,id',
            'supplier_id' => 'nullable|exists:inv_m_supplier,id',
            'destination_id' => 'nullable|exists:inv_m_supplier,id',
        ]);

        // 1. Strict Parameter Check for Coil Products
        $product = InventoryProduct::with('unit')->findOrFail($request->product_detail_id);
        if ($product->isCoil()) {
            if ($product->gross_coil <= 0 || $product->top_coil <= 0 || $product->end_coil <= 0 || $product->pitch <= 0) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Transaction Blocked: This Coil product has incomplete Master Data (Gross Coil, Top/End Coil, or Pitch). Please update the Master Data before performing transactions.'
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Get Category Effect
            $category = TransactionCategory::findOrFail($request->transaction_category_id);

            // Save Transaction
            $transaction = InventoryTransaction::create([
                'product_detail_id' => $request->product_detail_id,
                'transaction_date' => $request->transaction_date,
                'qty' => $request->qty,
                'transaction_category_id' => $request->transaction_category_id,
                'user_id' => $request->user_id,
                'remark' => $request->remark,
                'coil_center_id' => $request->coil_center_id,
                'supplier_id' => $request->supplier_id,
                'destination_id' => $request->destination_id,
            ]);

            // Update Stock
            $product = InventoryProduct::findOrFail($request->product_detail_id);
            $stockChange = $request->qty * $category->effect;
            
            $product->current_stock_qty += $stockChange;
            $product->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaction saved successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getCategories()
    {
        $categories = TransactionCategory::select('id', 'code', 'name', 'effect')->orderBy('name')->get();
        return response()->json($categories);
    }

    public function edit($id)
    {
        $decodedId = InventoryTransaction::decodeHash($id);
        if (!$decodedId) return response()->json(['error' => 'Invalid ID'], 404);

        $transaction = InventoryTransaction::with(['product.product', 'user', 'transactionCategory'])->find($decodedId);
        if (!$transaction) return response()->json(['error' => 'Transaction not found'], 404);

        return response()->json([
            'id' => $transaction->hash_id,
            'transaction_date' => $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : null,
            'product_detail_id' => $transaction->product->hash_id,
            'part_no' => $transaction->product->product->part_no ?? null,
            'product_name' => $transaction->product->product->part_name ?? null,
            'transaction_category_id' => $transaction->transactionCategory->hash_id,
            'qty' => $transaction->qty,
            'user_id' => $transaction->user_id, 
            'pic_name' => $transaction->user->name ?? null,
            'remark' => $transaction->remark,
            'coil_center_id' => $transaction->coil_center_id ? \App\Models\InventoryModel\CoilCenter::encodeHash($transaction->coil_center_id) : null,
            'supplier_id' => $transaction->supplier_id ? \App\Models\InventoryModel\Supplier::encodeHash($transaction->supplier_id) : null,
            'destination_id' => $transaction->destination_id ? \App\Models\InventoryModel\Supplier::encodeHash($transaction->destination_id) : null,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->hasAppRole('supervisor') && !Auth::user()->hasAppRole('admin')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to edit transactions.'], 403);
        }

        $decodedId = InventoryTransaction::decodeHash($id);
        $transaction = InventoryTransaction::findOrFail($decodedId);

        $data = $request->all();
        if (isset($data['product_detail_id']) && !is_numeric($data['product_detail_id'])) {
            $data['product_detail_id'] = \App\Models\InventoryModel\InventoryProduct::decodeHash($data['product_detail_id']);
        }
        if (isset($data['transaction_category_id']) && !is_numeric($data['transaction_category_id'])) {
            $data['transaction_category_id'] = \App\Models\InventoryModel\TransactionCategory::decodeHash($data['transaction_category_id']);
        }
        if (isset($data['coil_center_id']) && !is_numeric($data['coil_center_id']) && $data['coil_center_id'] != null) {
            $data['coil_center_id'] = \App\Models\InventoryModel\CoilCenter::decodeHash($data['coil_center_id']);
        }
        if (isset($data['supplier_id']) && !is_numeric($data['supplier_id']) && $data['supplier_id'] != null) {
            $data['supplier_id'] = \App\Models\InventoryModel\Supplier::decodeHash($data['supplier_id']);
        }
        if (isset($data['destination_id']) && !is_numeric($data['destination_id']) && $data['destination_id'] != null) {
            $data['destination_id'] = \App\Models\InventoryModel\Supplier::decodeHash($data['destination_id']);
        }

        $request->merge($data);

        $request->validate([
            'product_detail_id' => 'required|exists:inv_t_product_detail,id',
            'transaction_date' => 'required|date',
            'qty' => 'required|numeric|min:0.01',
            'transaction_category_id' => 'required|exists:inv_m_transaction_category,id',
            'remark' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Revert stock from old values
            $oldProduct = InventoryProduct::findOrFail($transaction->product_detail_id);
            $oldCategory = TransactionCategory::findOrFail($transaction->transaction_category_id);
            $oldProduct->current_stock_qty -= ($transaction->qty * $oldCategory->effect);
            $oldProduct->save();

            // Update transaction
            $transaction->update([
                'product_detail_id' => $request->product_detail_id,
                'transaction_date' => $request->transaction_date,
                'qty' => $request->qty,
                'transaction_category_id' => $request->transaction_category_id,
                'remark' => $request->remark,
                'coil_center_id' => $request->coil_center_id,
                'supplier_id' => $request->supplier_id,
                'destination_id' => $request->destination_id,
            ]);

            // Apply new stock values
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

    public function destroy($id)
    {
        if (!Auth::user()->hasAppRole('supervisor') && !Auth::user()->hasAppRole('admin')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to delete transactions.'], 403);
        }

        $decodedId = InventoryTransaction::decodeHash($id);
        $transaction = InventoryTransaction::findOrFail($decodedId);

        DB::beginTransaction();
        try {
            // Revert stock
            $product = InventoryProduct::findOrFail($transaction->product_detail_id);
            $category = TransactionCategory::findOrFail($transaction->transaction_category_id);
            $product->current_stock_qty -= ($transaction->qty * $category->effect);
            $product->save();

            $transaction->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaction deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
