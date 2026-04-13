<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Rank;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('inventory.master-data.rank');
    }

    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = Rank::query();

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
                  ->orWhere('description', 'like', '%' . $searchValue . '%')
                  ->orWhere('limit_value', 'like', '%' . $searchValue . '%');
            });
        }

        // Handle Sorting
        $sortBy = $request->get('order')[0]['column'] ?? 1;
        $sortDir = $request->get('order')[0]['dir'] ?? 'asc';
        $sortColumn = $request->get('columns')[$sortBy]['data'] ?? 'code';
        $query->orderBy($sortColumn, $sortDir);

        // Handle Pagination
        $perPage = $request->get('length', 10);
        $start = $request->get('start', 0);
        $draw = $request->get('draw', 1);

        $totalRecords = Rank::count();
        $filteredRecords = $query->count();
        $ranks = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $ranks
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:inv_m_rank,code',
            'limit_value' => 'required|integer|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        Rank::create($validated);

        return response()->json(['success' => true, 'message' => 'Rank created successfully.']);
    }

    /**
     * Display the specified resource for editing.
     */
    public function show($id)
    {
        $rank = Rank::findByHashOrFail($id);
        return response()->json($rank);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $rank = Rank::findByHashOrFail($id);
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('inv_m_rank')->ignore($rank->id),
            ],
            'limit_value' => 'required|integer|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        $rank->update($validated);

        return response()->json(['success' => true, 'message' => 'Rank updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $rank = Rank::findByHashOrFail($id);

        // Check usage in product detail
        $isUsed = \App\Models\InventoryModel\InventoryProduct::where('rank_id', $rank->id)->exists();
        if ($isUsed) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete rank. It is already used in product specifications.'
            ], 422);
        }

        $rank->delete();
        return response()->json(['success' => true, 'message' => 'Rank deleted successfully.']);
    }
}
