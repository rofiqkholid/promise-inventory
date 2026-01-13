<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\SubContractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubContractorController extends Controller
{
    /**
     * Display a listing of the resource (for DataTables).
     */
    public function data(Request $request)
    {
        $draw = $request->get('draw', 1);
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $search = $request->get('search')['value'] ?? '';
        $orderColumnIdx = $request->get('order')[0]['column'] ?? 0;
        $orderDir = $request->get('order')[0]['dir'] ?? 'asc';

        $columns = ['id', 'code', 'name', 'service_type'];
        $orderColumn = $columns[$orderColumnIdx] ?? 'id';

        $query = SubContractor::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('service_type', 'like', "%{$search}%");
            });
        }

        $recordsTotal = SubContractor::count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:inv_m_sub_contractor,code',
            'name' => 'nullable|string|max:255',
            'service_type' => 'nullable|string|max:100',
        ]);

        SubContractor::create($validated);

        return response()->json(['success' => true, 'message' => 'Sub Contractor created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $subContractor = SubContractor::findByHashOrFail($id);
        return response()->json($subContractor);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $subContractor = SubContractor::findByHashOrFail($id);
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:inv_m_sub_contractor,code,' . $subContractor->id,
            'name' => 'nullable|string|max:255',
            'service_type' => 'nullable|string|max:100',
        ]);

        $subContractor->update($validated);

        return response()->json(['success' => true, 'message' => 'Sub Contractor updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $subContractor = SubContractor::findByHashOrFail($id);
        $subContractor->delete();

        return response()->json(['success' => true, 'message' => 'Sub Contractor deleted successfully.']);
    }
}
