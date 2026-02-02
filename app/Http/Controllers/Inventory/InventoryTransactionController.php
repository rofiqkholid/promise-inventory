<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\InventoryTransaction;
use App\Models\InventoryModel\InventoryProduct;
use App\Models\InventoryModel\TransactionCategory;
use App\Models\InventoryModel\PIC;
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
            ->select('inv_t_product_detail.id', 'products.part_no', 'products.part_name', 'inv_t_product_detail.revision', 'inv_t_product_detail.pcs_per_unit')
            ->where('inv_t_product_detail.is_active', 1)
            ->orderBy('products.part_no')
            ->get();
        
        $categories = TransactionCategory::select('id', 'code', 'name', 'effect')->orderBy('name')->get();
        // $pics = PIC::where('is_active', 1)->orderBy('name')->get(); // No longer needed for manual selection
        
        $coilCenters = CoilCenter::select('id', 'code', 'name')->orderBy('code')->get();
        $suppliers = Supplier::select('id', 'code', 'name')->orderBy('code')->get();

        return view('inventory.inventory_transaction', compact('products', 'categories', 'coilCenters', 'suppliers'));
    }

    public function data(Request $request)
    {
        $query = InventoryTransaction::with(['product.product', 'user', 'transactionCategory']);

        // Filter by Product
        if ($request->has('product_detail_id') && !empty($request->product_detail_id)) {
            $prodId = $request->product_detail_id;
            if (!is_numeric($prodId)) {
                $decoded = \App\Models\InventoryModel\InventoryProduct::decodeHash($prodId);
                if ($decoded) $prodId = $decoded;
            }
            $query->where('product_detail_id', $prodId);
        }

        // Filter by Month & Year
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        if (!empty($month)) {
            $query->whereMonth('transaction_date', $month);
        }
        if (!empty($year)) {
            $query->whereYear('transaction_date', $year);
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

        // Sorting
        $columns = ['id', 'transaction_date', 'product_detail_id', 'transaction_category_id', 'qty', 'user_id', 'remark'];
        $orderBy = $columns[$request->input('order.0.column')] ?? 'created_at';
        $orderDir = $request->input('order.0.dir') ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        $perPage = $request->input('length', 10);
        $start = $request->input('start', 0);
        
        // Use a consistent base count for the selected period
        $total = InventoryTransaction::whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->count();
            
        $filtered = $query->count();
        $transactions = $query->skip($start)->take($perPage)->get();

        // Transform for DataTable
        $data = $transactions->map(function($item) {
            return [
                'id' => $item->hash_id,
                'transaction_date' => $item->transaction_date ? $item->transaction_date->format('Y-m-d') : '-',
                'part_no' => ($item->product->product->part_no ?? '-') . ($item->product->revision ? ' - ' . $item->product->revision : ''),
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
            'qty' => 'required|integer|min:1',
            'transaction_category_id' => 'required|exists:inv_m_transaction_category,id',
            'user_id' => 'required',
            'remark' => 'nullable|string',
            'coil_center_id' => 'nullable|exists:inv_m_coil_center,id',
            'supplier_id' => 'nullable|exists:inv_m_supplier,id',
            'destination_id' => 'nullable|exists:inv_m_supplier,id',
        ]);

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
}
