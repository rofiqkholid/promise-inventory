<?php

namespace App\Http\Controllers\Inventory\Material;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DebugEpicorController extends Controller
{
    public function index()
    {
        $customers = DB::table('customers')->select('id', 'code')->orderBy('code')->get();
        $models = DB::table('models')->select('id', 'name')->orderBy('name')->get();

        return view('inventory.material.debug.epicor_comparison', compact('customers', 'models'));
    }

    private function getComparisonData(Request $request)
    {
        try {
            $epicorDataRaw = DB::connection('second_db')->select("
                WITH PriceLatest AS (
                    select 
                        b.VendorID, 
                        a.PartNum, 
                        a.BaseUnitPrice, 
                        a.PUM, 
                        a.EffectiveDate,
                        a.ExpirationDate,
                        e.ConvFactor,
                        a.PricePerCode,
                        ROW_NUMBER() OVER (PARTITION BY a.PartNum ORDER BY a.EffectiveDate DESC) as RowNum
                    from erp.VendPart a
                    left join erp.Vendor b on b.VendorNum = a.VendorNum
                    left join erp.part c on c.PartNum = a.PartNum
                    left join erp.UOMClass d on d.Description = a.PartNum
                    left join erp.UOMConv e on e.UOMClassID = d.UOMClassID and e.UOMCode = a.PUM
                    where a.PartNum like '%-R%'
                )
                SELECT * FROM PriceLatest WHERE RowNum = 1
            ");
            
            $epicorData = [];
            foreach ($epicorDataRaw as $row) {
                $pn = trim($row->PartNum);
                $epicorData[$pn] = $row;
            }
        } catch (\Exception $e) {
            throw $e;
        }

        $query = DB::table('products as p')
            ->leftJoin('customers as cust', 'cust.id', '=', 'p.customer_id')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->leftJoin('project_status as ps', 'm.status_id', '=', 'ps.id')
            ->where('p.is_delete', 0)
            ->select([
                'p.id',
                'p.part_no',
                'p.part_name',
                'cust.code as customer_code',
                'm.name as model_name',
                'ps.name as project_status_name'
            ]);

        if ($request->filled('customer_id')) $query->where('p.customer_id', $request->customer_id);
        if ($request->filled('model_id')) $query->where('p.model_id', $request->model_id);
        if ($request->filled('search_part')) $query->where('p.part_no', 'like', "%{$request->search_part}%");

        $promiseProducts = $query->get();

        $data = $promiseProducts->map(function($p) use ($epicorData) {
            $partNo = trim($p->part_no);
            $lookupBase = $partNo . '-R';
            
            // Fuzzy Matching Logic:
            // 1. Try exact match: PartNo-R
            // 2. Fallback: Find PartNum in Epicor that matches PartNo-R followed ONLY by numbers
            $epi = $epicorData[$lookupBase] ?? null;
            
            if (!$epi) {
                // Regex: Starts with lookupBase followed by zero or more digits, then end of string
                $pattern = '/^' . preg_quote($lookupBase, '/') . '\d*$/';
                foreach ($epicorData as $epiPn => $epiRow) {
                    if (preg_match($pattern, $epiPn)) {
                        $epi = $epiRow;
                        break;
                    }
                }
            }

            $rawPrice = $epi ? (float) $epi->BaseUnitPrice : null;
            $convFactor = ($epi && $epi->ConvFactor) ? round((float) $epi->ConvFactor, 3) : 0;
            $pum = $epi ? trim($epi->PUM) : null;
            
            $convertedPrice = null;
            if ($rawPrice !== null) {
                if ($pum === 'SHEET' && $convFactor > 0) {
                    // SHEET: Apply conversion and round UP
                    $convertedPrice = ceil($rawPrice / $convFactor);
                } elseif ($pum === 'KG') {
                    // KG: Show raw data as is (no conversion)
                    $convertedPrice = $rawPrice;
                }
            }

            return [
                'promise_part_no' => $partNo,
                'promise_part_name' => $p->part_name,
                'customer' => $p->customer_code,
                'model' => $p->model_name,
                'project_status' => $p->project_status_name ?? 'No Status',
                'target_epicor' => $epi ? $epi->PartNum : $lookupBase,
                'vendor_id' => $epi ? $epi->VendorID : '-',
                'epicor_price' => $rawPrice,
                'converted_price' => $convertedPrice,
                'epicor_pum' => $pum,
                'epicor_conv_factor' => $convFactor,
                'epicor_effective' => $epi ? date('d/m/Y', strtotime($epi->EffectiveDate)) : null,
                'epicor_expired' => ($epi && $epi->ExpirationDate) ? date('d/m/Y', strtotime($epi->ExpirationDate)) : '-',
                'status' => $epi ? 'FOUND' : 'NOT_FOUND'
            ];
        });

        if ($request->filled('status')) {
            $data = $data->filter(function($item) use ($request) {
                return $item['status'] == $request->status;
            });
        }

        return $data;
    }

    public function data(Request $request)
    {
        $allData = $this->getComparisonData($request);
        
        $totalRecords = $allData->count();
        $length = $request->input('length', 15);
        $start = $request->input('start', 0);
        
        // Manual pagination on the collection
        $pagedData = ($length == -1) ? $allData : $allData->slice($start, $length);
        
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $pagedData->values()
        ]);
    }

    public function export(Request $request)
    {
        $data = $this->getComparisonData($request);
        
        return Excel::download(new class($data) implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function collection() { 
                return $this->data->map(function($item) {
                    return [
                        $item['promise_part_no'],
                        $item['promise_part_name'],
                        $item['customer'],
                        $item['model'],
                        $item['project_status'],
                        $item['target_epicor'],
                        $item['status'],
                        $item['vendor_id'],
                        $item['epicor_price'],
                        $item['converted_price'],
                        $item['epicor_pum'],
                        $item['epicor_conv_factor'],
                        $item['epicor_effective'],
                        $item['epicor_expired'],
                    ];
                });
            }
            public function headings(): array {
                return [
                    'Promise Part No', 'Part Name', 'Customer', 'Model', 'Project Status',
                    'Epicor Part Num', 'Sync Status', 'Vendor', 'Raw Price', 'Converted Price',
                    'PUM', 'Conv Factor', 'Effective Date', 'Expiration Date'
                ];
            }
            public function styles(Worksheet $sheet) {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        }, 'epicor_sync_debug_' . date('Ymd_His') . '.xlsx');
    }
}
