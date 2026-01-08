<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\CoilCenter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CoilCenterController extends Controller
{
    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = CoilCenter::query();

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
                  ->orWhere('name', 'like', '%' . $searchValue . '%')
                  ->orWhere('address', 'like', '%' . $searchValue . '%');
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

        $totalRecords = CoilCenter::count();
        $filteredRecords = $query->count();
        $coilCenters = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $coilCenters
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:inv_m_coil_center,code',
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        CoilCenter::create($validated);

        return response()->json(['success' => true, 'message' => 'Coil Center created successfully.']);
    }

    /**
     * Display the specified resource for editing.
     */
    public function show(CoilCenter $coilCenter)
    {
        return response()->json($coilCenter);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CoilCenter $coilCenter)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('inv_m_coil_center')->ignore($coilCenter->id),
            ],
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        $coilCenter->update($validated);

        return response()->json(['success' => true, 'message' => 'Coil Center updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoilCenter $coilCenter)
    {
        $coilCenter->delete();
        return response()->json(['success' => true, 'message' => 'Coil Center deleted successfully.']);
    }
}
