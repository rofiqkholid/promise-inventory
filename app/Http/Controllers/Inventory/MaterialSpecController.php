<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\MaterialSpec;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaterialSpecController extends Controller
{
    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = MaterialSpec::query();

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
                $q->where('spec_name', 'like', '%' . $searchValue . '%')
                  ->orWhere('coating_type', 'like', '%' . $searchValue . '%');
            });
        }

        // Handle Sorting
        $sortBy = $request->get('order')[0]['column'] ?? 1;
        $sortDir = $request->get('order')[0]['dir'] ?? 'asc';
        $sortColumn = $request->get('columns')[$sortBy]['data'] ?? 'spec_name';
        $query->orderBy($sortColumn, $sortDir);

        // Handle Pagination
        $perPage = $request->get('length', 10);
        $start = $request->get('start', 0);
        $draw = $request->get('draw', 1);

        $totalRecords = MaterialSpec::count();
        $filteredRecords = $query->count();
        $materialSpecs = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $materialSpecs
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'spec_name' => 'required|string|max:100|unique:inv_m_material_spec,spec_name',
            'coating_type' => 'nullable|string|max:50',
        ]);

        MaterialSpec::create($validated);

        return response()->json(['success' => true, 'message' => 'Material Spec created successfully.']);
    }

    /**
     * Display the specified resource for editing.
     */
    public function show($id)
    {
        $materialSpec = MaterialSpec::findByHashOrFail($id);
        return response()->json($materialSpec);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $materialSpec = MaterialSpec::findByHashOrFail($id);
        $validated = $request->validate([
            'spec_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('inv_m_material_spec')->ignore($materialSpec->id),
            ],
            'coating_type' => 'nullable|string|max:50',
        ]);

        $materialSpec->update($validated);

        return response()->json(['success' => true, 'message' => 'Material Spec updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $materialSpec = MaterialSpec::findByHashOrFail($id);
        $materialSpec->delete();
        return response()->json(['success' => true, 'message' => 'Material Spec deleted successfully.']);
    }
}
