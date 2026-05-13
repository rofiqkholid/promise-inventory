<?php

namespace App\Http\Controllers\Inventory\Tool;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Tool\TolDestination;
use Illuminate\Http\Request;

class ToolDestinationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $draw   = (int) $request->input('draw');
            $start  = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value');

            $query = TolDestination::query();

            $recordsTotal = (clone $query)->count();

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%$search%")
                      ->orWhere('name', 'like', "%$search%");
                });
            }

            $recordsFiltered = (clone $query)->count();
            $data = $query->orderBy('code')->skip($start)->take($length)->get();

            $formatted = $data->map(fn($r, $i) => [
                'DT_RowIndex' => $start + $i + 1,
                'id'          => $r->id,
                'code'        => $r->code,
                'name'        => $r->name,
                'is_active'   => $r->is_active,
                'action'      => '
                    <div class="flex justify-center gap-1">
                        <button onclick="editDestination('.$r->id.')" class="w-8 h-8 flex items-center justify-center rounded-xs bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all"><i class="fa-solid fa-pen-to-square text-xs"></i></button>
                        <button onclick="deleteDestination('.$r->id.')" class="w-8 h-8 flex items-center justify-center rounded-xs bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all"><i class="fa-solid fa-trash text-xs"></i></button>
                    </div>'
            ]);

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $formatted
            ]);
        }

        return view('inventory.tool.destination.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:tol_m_destinations,code',
            'name' => 'required|string|max:150',
        ]);

        TolDestination::create($validated);

        return response()->json(['status' => 'success', 'message' => 'Destination created successfully.']);
    }

    public function edit($id)
    {
        $dest = TolDestination::findOrFail($id);
        return response()->json($dest);
    }

    public function update(Request $request, $id)
    {
        $dest = TolDestination::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:tol_m_destinations,code,'.$id,
            'name' => 'required|string|max:150',
        ]);

        $dest->update($validated);

        return response()->json(['status' => 'success', 'message' => 'Destination updated successfully.']);
    }

    public function destroy($id)
    {
        $dest = TolDestination::findOrFail($id);
        $dest->delete();
        return response()->json(['status' => 'success', 'message' => 'Destination deleted successfully.']);
    }
}
