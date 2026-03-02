<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\TransactionCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('inventory.master-data.transaction-category');
    }

    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = TransactionCategory::query();

        // Handle Search (normalize DataTables search param)
        $searchParam = $request->get('search');
        $searchValue = '';
        if (is_array($searchParam)) {
            $searchValue = $searchParam['value'] ?? '';
        } elseif (is_object($searchParam)) {
            $searchValue = $searchParam->value ?? '';
        } else {
            $searchValue = (string) $searchParam;
        }

        if (!empty($searchValue)) {
            $query->where(function($q) use ($searchValue) {
                $q->where('code', 'like', '%' . $searchValue . '%')
                  ->orWhere('name', 'like', '%' . $searchValue . '%');
            });
        }

        // Handle Sorting
        $sortBy = $request->get('order')[0]['column'] ?? 1;
        $sortDir = $request->get('order')[0]['dir'] ?? 'asc';
        $columns = ['id', 'code', 'name', 'effect'];
        $sortColumn = $columns[$sortBy] ?? 'code';
        $query->orderBy($sortColumn, $sortDir);

        // Handle Pagination
        $perPage = $request->get('length', 10);
        $start = $request->get('start', 0);
        $draw = $request->get('draw', 1);

        $totalRecords = TransactionCategory::count();
        $filteredRecords = $query->count();
        $categories = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:inv_m_transaction_category,code',
            'name' => 'required|string|max:100',
            'effect' => 'required|integer|in:1,-1',
        ]);

        TransactionCategory::create($validated);

        return response()->json(['success' => true, 'message' => 'Category created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $transactionCategory = TransactionCategory::findByHashOrFail($id);
        return response()->json($transactionCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $transactionCategory = TransactionCategory::findByHashOrFail($id);
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('inv_m_transaction_category')->ignore($transactionCategory->id),
            ],
            'name' => 'required|string|max:100',
            'effect' => 'required|integer|in:1,-1',
        ]);

        $transactionCategory->update($validated);

        return response()->json(['success' => true, 'message' => 'Category updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $transactionCategory = TransactionCategory::findByHashOrFail($id);
        $transactionCategory->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
    }
}
