<?php

namespace App\Http\Controllers\Inventory\Tool;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Tool\TolLocation;
use Illuminate\Http\Request;

class ToolLocationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson() || $request->ajax()) {
            $draw   = (int) $request->input('draw');
            $start  = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value');

            $query = TolLocation::where('is_active', true);

            $recordsTotal = (clone $query)->count();

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%$search%")
                      ->orWhere('name', 'like', "%$search%")
                      ->orWhere('category', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
                });
            }

            $recordsFiltered = (clone $query)->count();
            $data = $query->orderBy('code')->skip($start)->take($length)->get();

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data
            ]);
        }
        return view('inventory.tool.location.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'        => 'required|string|max:50|unique:tol_m_locations,code',
            'name'        => 'required|string|max:100',
            'category'    => 'required|in:storage,machine,subcont',
            'description' => 'nullable|string',
        ]);

        TolLocation::create($validated);
        return response()->json(['status' => 'success', 'message' => 'Location created successfully.']);
    }

    public function update(Request $request, $id)
    {
        $location = TolLocation::findOrFail($id);
        $validated = $request->validate([
            'code'        => 'required|string|max:50|unique:tol_m_locations,code,' . $id,
            'name'        => 'required|string|max:100',
            'category'    => 'required|in:storage,machine,subcont',
            'description' => 'nullable|string',
        ]);

        $location->update($validated);
        return response()->json(['status' => 'success', 'message' => 'Location updated successfully.']);
    }

    public function destroy($id)
    {
        $location = TolLocation::findOrFail($id);

        if ($location->fastStock()->count() > 0 || $location->slowBatches()->count() > 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cannot delete Location because it has inventory records.',
            ], 422);
        }

        $location->delete();
        return response()->json(['status' => 'success', 'message' => 'Location deleted successfully.']);
    }
}
