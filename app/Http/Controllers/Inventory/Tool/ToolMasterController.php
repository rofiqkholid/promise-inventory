<?php

namespace App\Http\Controllers\Inventory\Tool;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Tool\TolTool;
use App\Models\InventoryModel\Tool\TolCategory;
use App\Models\InventoryModel\Tool\TolLocation;
use Illuminate\Http\Request;

class ToolMasterController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson() || $request->ajax()) {
            $draw   = (int) $request->input('draw', 1);
            $start  = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value');

            $query = TolTool::with(['category', 'location'])->where('is_active', true);
            $recordsTotal = (clone $query)->count();

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('brand', 'like', "%$search%")
                      ->orWhere('spec_code', 'like', "%$search%")
                      ->orWhere('material_type', 'like', "%$search%")
                      ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%$search%"));
                });
            }

            $recordsFiltered = (clone $query)->count();
            $data = $query->orderBy('name')->skip($start)->take($length)->get();

            $formatted = $data->map(fn($t) => [
                'id'               => $t->id,
                'category_id'      => $t->category_id,
                'category_name'    => $t->category?->name ?? '-',
                'location_id'      => $t->location_id,
                'location_name'    => $t->location?->name ?? '-',
                'moving_type'      => $t->category?->moving_type ?? 'fast',
                'name'             => $t->name,
                'brand'            => $t->brand,
                'spec_code'        => $t->spec_code,
                'diameter'         => $t->diameter,
                'length'           => $t->length,
                'material_type'    => $t->material_type,
                'hrc'              => $t->hrc,
                'uom'              => $t->uom,
                'pcs_per_unit'     => $t->pcs_per_unit,
                'price_per_unit'   => $t->price_per_unit,
                'limit_stock'      => $t->limit_stock,
                'qty_min'          => $t->qty_min,
                'qty_max'          => $t->qty_max,
                'std_lifetime_yrs' => $t->std_lifetime_yrs,
                'action'           => '',
            ]);

            return response()->json([
                'draw' => $draw, 'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered, 'data' => $formatted,
            ]);
        }

        $categories = TolCategory::where('is_active', true)->orderBy('name')->get();
        $locations = TolLocation::where('is_active', true)->orderBy('name')->get();
        return view('inventory.tool.master.index', compact('categories', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'      => 'required|exists:tol_m_categories,id',
            'location_id'      => 'required|exists:tol_m_locations,id',
            'name'             => 'required|string|max:150',
            'brand'            => 'required|string|max:100',
            'spec_code'        => 'nullable|string|max:100',
            'diameter'         => 'nullable|numeric|min:0',
            'length'           => 'nullable|numeric|min:0',
            'material_type'    => 'nullable|string|max:50',
            'hrc'              => 'nullable|string|max:20',
            'uom'              => 'required|string|max:20',
            'pcs_per_unit'     => 'required|integer|min:1',
            'price_per_unit'   => 'nullable|numeric|min:0',
            'limit_stock'      => 'nullable|integer|min:0',
            'qty_min'          => 'nullable|integer|min:0',
            'qty_max'          => 'nullable|integer|min:0',
            'std_lifetime_yrs' => 'nullable|integer|min:1',
        ]);

        TolTool::create($validated);
        return response()->json(['status' => 'success', 'message' => 'Tool Specification created successfully.']);
    }

    public function update(Request $request, $id)
    {
        $tool = TolTool::findOrFail($id);
        $validated = $request->validate([
            'category_id'      => 'required|exists:tol_m_categories,id',
            'location_id'      => 'required|exists:tol_m_locations,id',
            'name'             => 'required|string|max:150',
            'brand'            => 'required|string|max:100',
            'spec_code'        => 'nullable|string|max:100',
            'diameter'         => 'nullable|numeric|min:0',
            'length'           => 'nullable|numeric|min:0',
            'material_type'    => 'nullable|string|max:50',
            'hrc'              => 'nullable|string|max:20',
            'uom'              => 'required|string|max:20',
            'pcs_per_unit'     => 'required|integer|min:1',
            'price_per_unit'   => 'nullable|numeric|min:0',
            'limit_stock'      => 'nullable|integer|min:0',
            'qty_min'          => 'nullable|integer|min:0',
            'qty_max'          => 'nullable|integer|min:0',
            'std_lifetime_yrs' => 'nullable|integer|min:1',
        ]);

        $tool->update($validated);
        return response()->json(['status' => 'success', 'message' => 'Tool Specification updated successfully.']);
    }

    public function destroy($id)
    {
        $tool = TolTool::findOrFail($id);

        $hasFast = $tool->fastStock()->count() > 0;
        $hasSlow = $tool->slowBatches()->count() > 0;

        if ($hasFast || $hasSlow) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cannot delete Tool because it has inventory records.',
            ], 422);
        }

        $tool->update(['is_active' => false]);
        return response()->json(['status' => 'success', 'message' => 'Tool deactivated successfully.']);
    }
}
