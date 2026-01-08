<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\InventoryTransaction;
use App\Models\InventoryModel\InventoryProduct;
use App\Models\InventoryModel\TransactionCategory;
use App\Models\InventoryModel\PIC;
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
            ->select('inv_t_product_detail.id', 'products.part_no', 'products.part_name', 'inv_t_product_detail.revision')
            ->where('inv_t_product_detail.is_active', 1)
            ->orderBy('products.part_no')
            ->get();
        
        $categories = TransactionCategory::select('code', 'name', 'effect')->orderBy('name')->get();
        $pics = PIC::where('is_active', 1)->orderBy('name')->get();

        return view('inventory.inventory_transaction', compact('products', 'categories', 'pics'));
    }

    public function data(Request $request)
    {
        $query = InventoryTransaction::with(['product.product', 'pic']);

        // Filter by Product
        if ($request->has('product_detail_id') && !empty($request->product_detail_id)) {
            $query->where('product_detail_id', $request->product_detail_id);
        }

        // Global Search
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('category', 'like', '%' . $search . '%')
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
        $columns = ['id', 'transaction_date', 'product_detail_id', 'category', 'qty', 'pic_id', 'remark'];
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
                'id' => $item->id,
                'transaction_date' => $item->transaction_date ? $item->transaction_date->format('Y-m-d') : '-',
                'part_no' => ($item->product->product->part_no ?? '-') . ($item->product->revision ? ' - ' . $item->product->revision : ''),
                'product_name' => $item->product->product->part_name ?? '-',
                'category' => $item->category,
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

    public function store(Request $request)
    {
        $request->validate([
            'product_detail_id' => 'required|exists:inv_t_product_detail,id',
            'transaction_date' => 'required|date',
            'qty' => 'required|integer|min:1',
            'category' => 'required|string',
            'pic_id' => 'required|exists:inv_m_pic,id',
            'remark' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Get Category Effect
            $category = TransactionCategory::where('code', $request->category)->first();
            if (!$category) {
                throw new \Exception("Transaction Category not found.");
            }

            // Save Transaction
            $transaction = InventoryTransaction::create([
                'product_detail_id' => $request->product_detail_id,
                'transaction_date' => $request->transaction_date,
                'qty' => $request->qty,
                'category' => $request->category,
                'pic_id' => $request->pic_id,
                'remark' => $request->remark,
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
        $categories = TransactionCategory::select('code', 'name', 'effect')->orderBy('name')->get();
        return response()->json($categories);
    }
}
