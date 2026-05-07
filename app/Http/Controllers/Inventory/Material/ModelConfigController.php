<?php

namespace App\Http\Controllers\Inventory\Material;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Models;
use App\Models\InventoryModel\Material\ModelStatus;

class ModelConfigController extends Controller
{
    public function index()
    {
        $customers = \DB::table('customers')->orderBy('code')->get(['id', 'code', 'name']);
        return view('inventory.material.master-data.model-config', compact('customers'));
    }

    public function data(Request $request)
    {
        $query = \DB::table('models')
            ->leftJoin('customers', 'models.customer_id', '=', 'customers.id')
            ->leftJoin('inv_m_model_status', 'models.id', '=', 'inv_m_model_status.model_id')
            ->select(
                \DB::raw('MIN(models.id) as id'),
                'models.name',
                'customers.code as customer_code',
                \DB::raw('MAX(inv_m_model_status.project_status) as project_status'),
                \DB::raw('MAX(inv_m_model_status.regular_start_date) as regular_start_date'),
                \DB::raw('MAX(inv_m_model_status.regular_expired_date) as regular_expired_date')
            )
            ->groupBy('models.name', 'customers.code');

        $searchItem = $request->search;
        $searchValue = is_array($searchItem) ? ($searchItem['value'] ?? '') : (string) $searchItem;
        
        if (!empty($searchValue)) {
            $query->where(function($q) use ($searchValue) {
                $q->where('models.name', 'like', "%{$searchValue}%")
                  ->orWhere('customers.code', 'like', "%{$searchValue}%");
            });
        }

        // Custom Filters
        if ($request->filled('filter_customer')) {
            $query->where('models.customer_id', $request->filter_customer);
        }

        if ($request->filled('filter_status')) {
            $query->where('inv_m_model_status.project_status', $request->filter_status);
        }

        if ($request->filled('filter_validity')) {
            $today = \Carbon\Carbon::today()->toDateString();
            if ($request->filter_validity === 'active') {
                $query->where(function($q) use ($today) {
                    $q->whereNull('inv_m_model_status.regular_expired_date')
                      ->orWhere('inv_m_model_status.regular_expired_date', '>=', $today);
                });
            } elseif ($request->filter_validity === 'expired') {
                $query->whereNotNull('inv_m_model_status.regular_expired_date')
                      ->where('inv_m_model_status.regular_expired_date', '<', $today);
            }
        }

        $totalResults = $query->get();
        $total = $totalResults->count();
        $pages = $request->input('length', 10);
        $start = $request->input('start', 0);
        
        // Handle sorting if provided by DataTables
        $order = $request->input('order.0');
        if ($order) {
            $columns = ['customers.code', 'models.name', 'project_status'];
            $colIndex = intval($order['column']);
            if (isset($columns[$colIndex - 1])) { // column 0 is index
                $query->orderBy($columns[$colIndex - 1], $order['dir']);
            }
        } else {
            $query->orderBy('models.name', 'asc');
        }

        $data = $query->skip($start)->take($pages)->get();

        $hashids = new \Hashids\Hashids(config('app.key') . Models::class, config('hashids.connections.main.length', 10), config('hashids.connections.main.alphabet', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'));

        $today = \Carbon\Carbon::today()->toDateString();
        $formatted = $data->map(function($row) use ($hashids, $today) {
            $isExpired = $row->regular_expired_date && $row->regular_expired_date < $today;
            return [
                'hash_id' => $hashids->encode($row->id),
                'name' => $row->name,
                'customer_code' => $row->customer_code,
                'project_status' => $row->project_status ?? 'Project',
                'regular_start_date' => $row->regular_start_date,
                'regular_expired_date' => $row->regular_expired_date,
                'is_expired' => $isExpired,
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $formatted
        ]);
    }

    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'model_hash_id' => 'required|string',
            'field' => 'required|string|in:project_status,regular_start_date,regular_expired_date', // easy to expand config fields
            'value' => 'nullable|string'
        ]);

        $hashids = new \Hashids\Hashids(config('app.key') . Models::class, config('hashids.connections.main.length', 10), config('hashids.connections.main.alphabet', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'));
        $decoded = $hashids->decode($validated['model_hash_id']);
        
        if (empty($decoded)) {
            return response()->json(['success' => false, 'message' => 'Invalid Model ID'], 400);
        }

        $modelId = $decoded[0];

        // Find the representative model to get its details
        $representativeModel = \DB::table('models')->find($modelId);
        if (!$representativeModel) {
            return response()->json(['success' => false, 'message' => 'Model not found'], 404);
        }

        // Find all models with same name and customer_id (to handle business-level duplicates)
        $idsToUpdate = \DB::table('models')
            ->where('name', $representativeModel->name)
            ->where('customer_id', $representativeModel->customer_id)
            ->pluck('id');

        $today = \Carbon\Carbon::today()->toDateString();
        $statusChanged = false;

        // Update the model config logic for ALL matching IDs
        foreach ($idsToUpdate as $id) {
            $status = ModelStatus::updateOrCreate(
                ['model_id' => $id],
                [$validated['field'] => $validated['value']]
            );

            // Evaluate dates immediately if a date field was updated
            if (in_array($validated['field'], ['regular_start_date', 'regular_expired_date'])) {
                // Determine the correct status purely based on the start date relative to today
                $newStatus = 'Project'; // Default base state
                
                if ($status->regular_start_date && $status->regular_start_date <= $today) {
                    $newStatus = 'Regular';
                }
                
                // We no longer force project_status to 'Expired' in DB
                
                if ($newStatus !== $status->project_status) {
                    $status->project_status = $newStatus;
                    $status->save();
                    $statusChanged = true;
                }
            }
        }

        return response()->json([
            'success' => true, 
            'message' => 'Configuration updated successfully',
            'status_changed' => $statusChanged
        ]);
    }
}
