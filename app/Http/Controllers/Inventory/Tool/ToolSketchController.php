<?php

namespace App\Http\Controllers\Inventory\Tool;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Tool\TolSketch;
use App\Models\InventoryModel\Tool\TolCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ToolSketchController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $draw   = (int) $request->input('draw');
            $start  = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value');

            $query = TolSketch::with('category');

            $recordsTotal = (clone $query)->count();

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%$search%"));
                });
            }

            $recordsFiltered = (clone $query)->count();
            $data = $query->orderBy('created_at', 'desc')->skip($start)->take($length)->get();

            $formatted = $data->map(fn($r, $i) => [
                'DT_RowIndex' => $start + $i + 1,
                'id'          => $r->id,
                'name'        => $r->name,
                'category'    => $r->category?->name ?? '-',
                'image'       => '<img src="'.asset('storage/'.$r->image_path).'" referrerpolicy="no-referrer" class="h-12 w-12 object-cover rounded-xs border border-gray-200 cursor-pointer hover:scale-150 transition-all" onclick="window.previewImg(\''.asset('storage/'.$r->image_path).'\')">',
                'action'      => '
                    <div class="flex justify-center gap-1">
                        <button onclick="editSketch('.$r->id.')" class="w-8 h-8 flex items-center justify-center rounded-xs bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all"><i class="fa-solid fa-pen-to-square text-xs"></i></button>
                        <button onclick="deleteSketch('.$r->id.')" class="w-8 h-8 flex items-center justify-center rounded-xs bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all"><i class="fa-solid fa-trash text-xs"></i></button>
                    </div>'
            ]);

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $formatted
            ]);
        }

        $categories = TolCategory::orderBy('name')->get();
        return view('inventory.tool.sketch.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:tol_m_categories,id',
            'name'        => 'required|string|max:150',
            'image'       => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $path = $request->file('image')->store('tool-sketches', 'public');

        TolSketch::create([
            'category_id' => $validated['category_id'],
            'name'        => $validated['name'],
            'image_path'  => $path,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Sketch created successfully.']);
    }

    public function edit($id)
    {
        $sketch = TolSketch::findOrFail($id);
        return response()->json($sketch);
    }

    public function update(Request $request, $id)
    {
        $sketch = TolSketch::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:tol_m_categories,id',
            'name'        => 'required|string|max:150',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = [
            'category_id' => $validated['category_id'],
            'name'        => $validated['name'],
        ];

        if ($request->hasFile('image')) {
            // Delete old image
            if (Storage::disk('public')->exists($sketch->image_path)) {
                Storage::disk('public')->delete($sketch->image_path);
            }
            $data['image_path'] = $request->file('image')->store('tool-sketches', 'public');
        }

        $sketch->update($data);

        return response()->json(['status' => 'success', 'message' => 'Sketch updated successfully.']);
    }

    public function destroy($id)
    {
        $sketch = TolSketch::findOrFail($id);
        
        if (Storage::disk('public')->exists($sketch->image_path)) {
            Storage::disk('public')->delete($sketch->image_path);
        }

        $sketch->delete();

        return response()->json(['status' => 'success', 'message' => 'Sketch deleted successfully.']);
    }

    public function getByCategory($categoryId)
    {
        $sketches = TolSketch::where('category_id', $categoryId)->orderBy('name')->get();
        return response()->json($sketches);
    }
}
