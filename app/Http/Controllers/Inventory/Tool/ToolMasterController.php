<?php

namespace App\Http\Controllers\Inventory\Tool;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\TolTool;
use App\Models\InventoryModel\TolCategory;
use Illuminate\Http\Request;

class ToolMasterController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson() || $request->ajax()) {
            $data = TolTool::with('category')->orderBy('name', 'asc')->get();
            return response()->json(['data' => $data]);
        }
        
        $categories = TolCategory::orderBy('name')->get();
        return view('inventory.tool.master.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:App\Models\InventoryModel\TolCategory,id',
            'name' => 'required|string|max:150',
            'brand' => 'required|string|max:100',
            'spec_code' => 'nullable|string|max:100',
            'diameter' => 'nullable|numeric',
            'length' => 'nullable|numeric',
            'material_type' => 'nullable|string|max:50',
            'hrc' => 'nullable|string|max:50',
            'uom' => 'required|string|max:20',
            'pcs_per_unit' => 'required|integer|min:1',
        ]);

        TolTool::create($validated);
        return response()->json(['status' => 'success', 'message' => 'Tool Specification created successfully.']);
    }

    public function update(Request $request, $id)
    {
        $tool = TolTool::findOrFail($id);
        $validated = $request->validate([
            'category_id' => 'required|exists:App\Models\InventoryModel\TolCategory,id',
            'name' => 'required|string|max:150',
            'brand' => 'required|string|max:100',
            'spec_code' => 'nullable|string|max:100',
            'diameter' => 'nullable|numeric',
            'length' => 'nullable|numeric',
            'material_type' => 'nullable|string|max:50',
            'hrc' => 'nullable|string|max:50',
            'uom' => 'required|string|max:20',
            'pcs_per_unit' => 'required|integer|min:1',
        ]);

        $tool->update($validated);
        return response()->json(['status' => 'success', 'message' => 'Tool Specification updated successfully.']);
    }

    public function destroy($id)
    {
        $tool = TolTool::findOrFail($id);
        
        if ($tool->inventories()->count() > 0) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Cannot delete Tool because it has inventory records.'
            ], 422);
        }

        $tool->delete();
        return response()->json(['status' => 'success', 'message' => 'Tool Specification deleted successfully.']);
    }
}
