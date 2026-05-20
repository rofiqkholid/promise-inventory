<?php

namespace App\Http\Controllers\Inventory\Tool;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Tool\TolCategory;
use Illuminate\Http\Request;

class ToolCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson() || $request->ajax()) {
            $draw   = (int) $request->input('draw');
            $start  = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value');

            $query = TolCategory::query();

            $recordsTotal = (clone $query)->count();

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('code_prefix', 'like', "%$search%")
                      ->orWhere('moving_type', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
                });
            }

            $recordsFiltered = (clone $query)->count();
            $data = $query->orderBy('name', 'asc')->skip($start)->take($length)->get();

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data
            ]);
        }
        return view('inventory.tool.category.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:100',
            'moving_type'  => 'required|in:fast,slow',
            'code_prefix'  => 'required_if:moving_type,slow|nullable|string|max:10|unique:tol_m_categories,code_prefix',
            'description'  => 'nullable|string',
        ]);

        if (isset($validated['code_prefix'])) {
            $validated['code_prefix'] = strtoupper($validated['code_prefix']);
        }

        TolCategory::create($validated);
        return response()->json(['status' => 'success', 'message' => 'Tool Category created successfully.']);
    }

    public function update(Request $request, $id)
    {
        $category = TolCategory::findOrFail($id);
        $validated = $request->validate([
            'name'         => 'required|string|max:100',
            'moving_type'  => 'required|in:fast,slow',
            'code_prefix'  => 'required_if:moving_type,slow|nullable|string|max:10|unique:tol_m_categories,code_prefix,' . $id,
            'description'  => 'nullable|string',
        ]);

        if (isset($validated['code_prefix'])) {
            $validated['code_prefix'] = strtoupper($validated['code_prefix']);
        }

        $category->update($validated);
        return response()->json(['status' => 'success', 'message' => 'Tool Category updated successfully.']);
    }

    public function destroy($id)
    {
        $category = TolCategory::findOrFail($id);
        
        // Prevent deletion if items exist
        if ($category->tools()->count() > 0) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Cannot delete Category because it is used in Tool Specifications.'
            ], 422);
        }

        $category->delete();
        return response()->json(['status' => 'success', 'message' => 'Tool Category deleted successfully.']);
    }
}
