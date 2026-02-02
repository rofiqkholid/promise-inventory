<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
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

        $columns = ['id', 'code', 'name', 'email', 'phone', 'promise_supp_id'];
        $orderColumn = $columns[$orderColumnIdx] ?? 'id';

        $query = DB::table('inv_m_supplier as s')
            ->leftJoin('suppliers as gs', 'gs.id', '=', 's.promise_supp_id')
            ->select([
                's.id',
                's.promise_supp_id',
                DB::raw("COALESCE(s.code, gs.code) as code"),
                DB::raw("COALESCE(s.name, gs.name) as name"),
                DB::raw("COALESCE(s.email, gs.email) as email"),
                DB::raw("COALESCE(s.phone, gs.phone) as phone"),
                DB::raw("COALESCE(s.address, gs.address) as address"),
                DB::raw("CASE WHEN s.promise_supp_id IS NOT NULL THEN 1 ELSE 0 END as is_linked")
            ]);

        // Hashids instantiation moved to trait

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('s.code', 'like', "%{$search}%")
                  ->orWhere('s.name', 'like', "%{$search}%")
                  ->orWhere('s.email', 'like', "%{$search}%")
                  ->orWhere('s.phone', 'like', "%{$search}%")
                  ->orWhere('s.address', 'like', "%{$search}%")
                  ->orWhere('gs.code', 'like', "%{$search}%")
                  ->orWhere('gs.name', 'like', "%{$search}%");
            });
        }

        $recordsTotal = DB::table('inv_m_supplier')->count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data->map(fn($r) => [
                'hash_id' => Supplier::encodeHash($r->id),
                'code' => $r->code,
                'name' => $r->name,
                'email' => $r->email,
                'phone' => $r->phone,
                'promise_supp_id' => $r->promise_supp_id ? Supplier::encodeHash($r->promise_supp_id) : null,
                'is_linked' => $r->is_linked
            ])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $request->all();
        
        // Transform empty strings to null for optional fields
        foreach (['code', 'name', 'email', 'phone', 'address', 'promise_supp_id'] as $key) {
            if (isset($input[$key]) && trim((string)$input[$key]) === '') {
                $input[$key] = null;
            }
        }

        // Decode promise_supp_id if present
        if (!empty($input['promise_supp_id'])) {
            $decoded = Supplier::decodeHash($input['promise_supp_id']);
            $input['promise_supp_id'] = $decoded;
        }

        if (!empty($input['promise_supp_id'])) {
             $input['code'] = null;
             $input['name'] = null;
             $input['email'] = null;
             $input['phone'] = null;
             $input['address'] = null;
        }

        $request->replace($input);

        $rules = [
            'promise_supp_id' => 'nullable|integer',
            'code' => 'required_without:promise_supp_id|nullable|string|max:50|unique:inv_m_supplier,code',
            'name' => 'required_without:promise_supp_id|nullable|string|max:50',
            'email' => 'nullable|email|max:50',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ];

        $validated = $request->validate($rules);

        // Ensure forced nulls are passed to create
        if (!empty($input['promise_supp_id'])) {
             $validated['code'] = null;
             $validated['name'] = null;
             $validated['email'] = null;
             $validated['phone'] = null;
             $validated['address'] = null;
        }

        Supplier::create($validated);

        return response()->json(['success' => true, 'message' => 'Supplier created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $supplier = Supplier::findByHashOrFail($id);
        
        $data = DB::table('inv_m_supplier as s')
            ->leftJoin('suppliers as gs', 'gs.id', '=', 's.promise_supp_id')
            ->select([
                's.id',
                's.promise_supp_id',
                DB::raw("COALESCE(s.code, gs.code) as code"),
                DB::raw("COALESCE(s.name, gs.name) as name"),
                DB::raw("COALESCE(s.email, gs.email) as email"),
                DB::raw("COALESCE(s.phone, gs.phone) as phone"),
                DB::raw("COALESCE(s.address, gs.address) as address"),
                DB::raw("CASE WHEN s.promise_supp_id IS NOT NULL THEN 1 ELSE 0 END as is_linked")
            ])
            ->where('s.id', $supplier->id)
            ->first();

        $data->hash_id = Supplier::encodeHash($data->id);
        $data->promise_supp_id = $data->promise_supp_id ? Supplier::encodeHash($data->promise_supp_id) : null;
        unset($data->id);

        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findByHashOrFail($id);
        $input = $request->all();
        
        // Transform empty strings to null
        foreach (['code', 'name', 'email', 'phone', 'address', 'promise_supp_id'] as $key) {
            if (isset($input[$key]) && trim((string)$input[$key]) === '') {
                $input[$key] = null;
            }
        }

        // Decode promise_supp_id if present
        if (!empty($input['promise_supp_id'])) {
            $decoded = Supplier::decodeHash($input['promise_supp_id']);
            $input['promise_supp_id'] = $decoded;
        }

        $rules = [
            'promise_supp_id' => 'nullable|integer',
            'code' => 'required|string|max:50|unique:inv_m_supplier,code,' . $supplier->id,
            'name' => 'required|string|max:50',
            'email' => 'nullable|email|max:50',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ];

        $request->replace($input);
        $validated = $request->validate($rules);

        $supplier->update($validated);

        return response()->json(['success' => true, 'message' => 'Supplier updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $supplier = Supplier::findByHashOrFail($id);
        $supplier->delete();

        return response()->json(['success' => true, 'message' => 'Supplier deleted successfully.']);
    }

    /**
     * Get global suppliers from promise-dev for Select2.
     */
    public function getGlobal(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $page = max(1, (int) $request->get('page', 1));
        $limit = 10;
        $skip = ($page - 1) * $limit;

        $query = DB::table('suppliers as s')
            ->select('s.id', 's.code', 's.name');

        if (!empty($q)) {
            $query->where(function ($w) use ($q) {
                $w->where('s.code', 'like', "%{$q}%")
                  ->orWhere('s.name', 'like', "%{$q}%");
            });
        }

        $total = $query->count();
        $rows = $query->orderBy('s.name')->skip($skip)->take($limit)->get();

        return response()->json([
            'results' => $rows->map(fn($s) => [
                'id' => $s->id,
                'text' => ($s->code ? "({$s->code}) " : "") . $s->name,
            ]),
            'pagination' => ['more' => ($skip + $limit) < $total],
        ]);
    }

    /**
     * Get detail of a global supplier.
     */
    public function getGlobalDetail($id)
    {
        $supplier = DB::table('suppliers')
            ->where('id', $id)
            ->first();

        return response()->json($supplier);
    }
}
