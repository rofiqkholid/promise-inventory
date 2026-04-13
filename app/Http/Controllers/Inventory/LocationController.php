<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('inventory.master-data.location');
    }

    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = Location::query();

        // Handle Search
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
            $query->where('name', 'like', '%' . $searchValue . '%');
        }

        // Handle Sorting
        $sortBy = $request->get('order')[0]['column'] ?? 1;
        $sortDir = $request->get('order')[0]['dir'] ?? 'asc';
        $columns = $request->get('columns');
        $sortColumn = $columns[$sortBy]['data'] ?? 'name';
        
        if (in_array($sortColumn, ['name', 'is_active', 'created_at'])) {
            $query->orderBy($sortColumn, $sortDir);
        }

        // Handle Pagination
        $perPage = $request->get('length', 10);
        $start = $request->get('start', 0);
        $draw = $request->get('draw', 1);

        $totalRecords = Location::count();
        $filteredRecords = $query->count();
        $locations = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $locations
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:inv_m_locations,name',
            'is_active' => 'required|boolean',
        ]);

        Location::create($validated);

        return response()->json(['success' => true, 'message' => 'Location created successfully.']);
    }

    /**
     * Display the specified resource for editing.
     */
    public function show($id)
    {
        $location = Location::findByHashOrFail($id);
        return response()->json($location);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $location = Location::findByHashOrFail($id);
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('inv_m_locations')->ignore($location->id),
            ],
            'is_active' => 'required|boolean',
        ]);

        $location->update($validated);

        return response()->json(['success' => true, 'message' => 'Location updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $location = Location::findByHashOrFail($id);
        
        // Check if location is used in STO details
        $isUsed = \App\Models\InventoryModel\StoDetail::where('location_id', $location->id)->exists();
        
        if ($isUsed) {
            return response()->json([
                'success' => false, 
                'message' => 'Cannot delete location. It is already used in Stock Opname records.'
            ], 422);
        }

        $location->delete();
        return response()->json(['success' => true, 'message' => 'Location deleted successfully.']);
    }
}
