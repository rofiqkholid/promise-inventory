<?php

namespace App\Http\Controllers\Inventory\Tool;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Tool\TolTool;
use App\Models\InventoryModel\Tool\TolCategory;
use Illuminate\Http\Request;

class ToolInformationController extends Controller
{
    /**
     * Display the search and catalog tool information dashboard.
     */
    public function index()
    {
        $categories = TolCategory::where('is_active', true)->orderBy('name')->get();
        return view('inventory.tool.information.index', compact('categories'));
    }

    /**
     * Search tools based on term, category.
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $categoryId = $request->input('category_id');

        $tools = TolTool::with(['category', 'sketch'])
            ->where('is_active', true);

        if (!empty($categoryId)) {
            $tools->where('category_id', $categoryId);
        }

        if (!empty($query)) {
            $tools->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('brand', 'like', "%{$query}%")
                  ->orWhere('spec_code', 'like', "%{$query}%")
                  ->orWhere('material_type', 'like', "%{$query}%");
            });
        }

        $results = $tools->orderBy('name')->take(20)->get()->map(function ($tool) {
            return [
                'id' => $tool->id,
                'name' => $tool->name,
                'brand' => $tool->brand,
                'spec_code' => $tool->spec_code ?? '-',
                'category_name' => $tool->category?->name ?? '-',
                'moving_type' => $tool->category?->moving_type ?? 'fast',
                'sketch_image' => $tool->sketch?->image_path ? asset('storage/' . $tool->sketch->image_path) : null,
            ];
        });

        return response()->json($results);
    }

    /**
     * Retrieve complete detailed information for a specific tool.
     */
    public function show($id)
    {
        $tool = TolTool::with([
            'category',
            'sketch',
            'settings',
            'fastStock.location',
            'slowBatches.location'
        ])
        ->where('is_active', true)
        ->findOrFail($id);

        // Process stock levels depending on moving type
        $stockInfo = [];
        $totalQty = 0;

        if ($tool->category?->moving_type === 'fast') {
            $stockInfo = $tool->fastStock->map(function ($fs) {
                return [
                    'location' => $fs->location?->name ?? '-',
                    'qty' => $fs->current_qty,
                    'last_updated' => $fs->last_updated_at ? $fs->last_updated_at->format('d M Y H:i') : '-',
                ];
            });
            $totalQty = $tool->fastStock->sum('current_qty');
        } else {
            $stockInfo = $tool->slowBatches->where('status', 'active')->map(function ($sb) {
                return [
                    'id_number' => $sb->id_number,
                    'location' => $sb->location?->name ?? '-',
                    'qty' => $sb->qty_current,
                    'purchase_date' => $sb->purchase_date ? $sb->purchase_date->format('d M Y') : '-',
                    'physical_rate' => $sb->physical_rate . '%',
                ];
            });
            $totalQty = $tool->slowBatches->where('status', 'active')->sum('qty_current');
        }

        // Return unified structure
        return response()->json([
            'tool' => [
                'id' => $tool->id,
                'name' => $tool->name,
                'brand' => $tool->brand,
                'spec_code' => $tool->spec_code ?? '-',
                'diameter' => $tool->diameter ?? '-',
                'length' => $tool->length ?? '-',
                'material_type' => $tool->material_type ?? '-',
                'hrc' => $tool->hrc ?? '-',
                'uom' => $tool->uom,
                'qty_min' => $tool->qty_min ?? 0,
                'qty_max' => $tool->qty_max ?? 0,
                'category_name' => $tool->category?->name ?? '-',
                'moving_type' => $tool->category?->moving_type ?? 'fast',
                'sketch_image' => $tool->sketch?->image_path ? asset('storage/' . $tool->sketch->image_path) : null,
                'sketch_name' => $tool->sketch?->name ?? '-',
            ],
            'settings' => $tool->settings,
            'stock' => [
                'total_qty' => $totalQty,
                'details' => $stockInfo,
            ]
        ]);
    }
}
