<?php

namespace App\Http\Controllers\Inventory\Material;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Material\Revision;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RevisionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('inventory.material.master-data.revision');
    }

    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = Revision::query();

        // Handle Search
        $searchParam = $request->get('search');
        $searchValue = is_array($searchParam) ? ($searchParam['value'] ?? '') : (string) $searchParam;

        if (!empty($searchValue)) {
            $query->where(function($q) use ($searchValue) {
                $q->where('code', 'like', '%' . $searchValue . '%')
                  ->orWhere('group_name', 'like', '%' . $searchValue . '%');
            });
        }

        // Handle Sorting
        $sortBy = $request->get('order')[0]['column'] ?? 2; // Default to sort_order
        $sortDir = $request->get('order')[0]['dir'] ?? 'asc';
        $columns = $request->get('columns');
        $sortColumn = $columns[$sortBy]['data'] ?? 'sort_order';
        
        if ($sortColumn === 'sort_order') {
            $query->orderBy('group_name', 'asc')->orderBy('sort_order', $sortDir);
        } else {
            $query->orderBy($sortColumn, $sortDir);
        }

        // Handle Pagination
        $perPage = $request->get('length', 10);
        $start = $request->get('start', 0);
        $draw = $request->get('draw', 1);

        $totalRecords = Revision::count();
        $filteredRecords = $query->count();
        $revisions = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $revisions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:inv_m_revision,code',
            'group_name' => 'required|string|max:50',
            'sort_order' => 'required|integer',
            'is_active' => 'required|boolean',
        ]);

        Revision::create($validated);

        return response()->json(['success' => true, 'message' => 'Revision created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $revision = Revision::findByHashOrFail($id);
        return response()->json($revision);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $revision = Revision::findByHashOrFail($id);
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('inv_m_revision')->ignore($revision->id),
            ],
            'group_name' => 'required|string|max:50',
            'sort_order' => 'required|integer',
            'is_active' => 'required|boolean',
        ]);

        $revision->update($validated);

        return response()->json(['success' => true, 'message' => 'Revision updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $revision = Revision::findByHashOrFail($id);
        
        // Check if revision is used in product details
        $isUsed = \App\Models\InventoryModel\Material\InventoryProduct::where('revision_id', $revision->id)->exists();
        
        if ($isUsed) {
            return response()->json([
                'success' => false, 
                'message' => 'Cannot delete revision. It is already used in product records. Try deactivating it instead.'
            ], 422);
        }

        $revision->delete();
        return response()->json(['success' => true, 'message' => 'Revision deleted successfully.']);
    }
}
