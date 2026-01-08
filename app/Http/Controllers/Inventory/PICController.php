<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\PIC;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PICController extends Controller
{
    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = PIC::query();

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
            $query->where('name', 'like', '%' . $searchValue . '%');
        }

        // Handle Sorting
        $sortBy = $request->get('order')[0]['column'] ?? 1;
        $sortDir = $request->get('order')[0]['dir'] ?? 'asc';
        $columns = ['id', 'name', 'is_active'];
        $sortColumn = $columns[$sortBy] ?? 'name';
        $query->orderBy($sortColumn, $sortDir);

        // Handle Pagination
        $perPage = $request->get('length', 10);
        $start = $request->get('start', 0);
        $draw = $request->get('draw', 1);

        $totalRecords = PIC::count();
        $filteredRecords = $query->count();
        $pics = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $pics
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        PIC::create($validated + ['is_active' => 1]);

        return response()->json(['success' => true, 'message' => 'PIC created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(PIC $pIC)
    {
        return response()->json($pIC);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pIC = PIC::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $pIC->update($validated);

        return response()->json(['success' => true, 'message' => 'PIC updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pIC = PIC::findOrFail($id);
        $pIC->delete();
        return response()->json(['success' => true, 'message' => 'PIC deleted successfully.']);
    }
}
