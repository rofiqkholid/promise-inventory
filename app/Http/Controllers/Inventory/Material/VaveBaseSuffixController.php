<?php

namespace App\Http\Controllers\Inventory\Material;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Material\VaveBaseSuffix;
use App\Models\Customers;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class VaveBaseSuffixController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('inventory.material.master-data.vave-base-suffix');
    }

    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = VaveBaseSuffix::query();

        // Handle Search
        $searchParam = $request->get('search');
        $searchValue = is_array($searchParam) ? ($searchParam['value'] ?? '') : (string)$searchParam;

        if (!empty($searchValue)) {
            $query->where(function($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                  ->orWhere('remark', 'like', '%' . $searchValue . '%')
                  ->orWhere('base_type', 'like', '%' . $searchValue . '%');
            });
        }

        // Handle Sorting
        $sortBy = $request->get('order')[0]['column'] ?? 1;
        $sortDir = $request->get('order')[0]['dir'] ?? 'asc';
        
        $columnsMap = [
            0 => 'is_active',
            1 => 'name',
            2 => 'base_type',
            3 => 'remark'
        ];
        
        $sortColumn = $columnsMap[$sortBy] ?? 'name';
        $query->orderBy($sortColumn, $sortDir);

        // Pagination
        $perPage = $request->get('length', 10);
        $start = $request->get('start', 0);
        $draw = $request->get('draw', 1);

        $totalRecords = VaveBaseSuffix::count();
        $filteredRecords = $query->count();
        $data = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'base_type' => ['required', Rule::in(['EBD', 'SQ'])],
            'name' => 'required|string|max:100',
            'remark' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        VaveBaseSuffix::create($validated);

        return response()->json(['success' => true, 'message' => 'VA/VE Suffix created successfully.']);
    }

    /**
     * Display the specified resource for editing.
     */
    public function show($id)
    {
        $suffix = VaveBaseSuffix::findByHashOrFail($id);
        return response()->json($suffix);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $suffix = VaveBaseSuffix::findByHashOrFail($id);
        $validated = $request->validate([
            'base_type' => ['required', Rule::in(['EBD', 'SQ'])],
            'name' => 'required|string|max:100',
            'remark' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $suffix->update($validated);

        return response()->json(['success' => true, 'message' => 'VA/VE Suffix updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $suffix = VaveBaseSuffix::findByHashOrFail($id);

        // Check for usage in VAVE analysis baseline
        $isUsed = \App\Models\InventoryModel\Material\VaveBase::where('vave_base_suffix_id', $suffix->id)->exists();
        if ($isUsed) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this suffix. It is already used in VAVE Analysis baseline data.'
            ], 422);
        }

        $suffix->delete();
        return response()->json(['success' => true, 'message' => 'VA/VE Suffix deleted successfully.']);
    }
}
