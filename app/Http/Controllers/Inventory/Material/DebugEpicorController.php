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
                select 
                    b.VendorID, 
                    a.PartNum, 
                    a.BaseUnitPrice, 
                    a.PUM, 
                    a.EffectiveDate,
                    a.ExpirationDate
                from erp.VendPart a
                left join erp.Vendor b on b.VendorNum = a.VendorNum
                left join erp.part c on c.PartNum = a.PartNum
                where c.ClassID = 'RM'
                order by a.PartNum, a.EffectiveDate desc
            ");
            
            $epicorData = [];
            $epicorBaseMap = [];
            foreach ($epicorDataRaw as $row) {
                $pn = trim($row->PartNum);
                if (!isset($epicorData[$pn])) {
                    $epicorData[$pn] = $row;
                    $base = preg_replace('/(-000)?-R$/', '', $pn);
                    if (!isset($epicorBaseMap[$base])) {
                        $epicorBaseMap[$base] = $row;
                    }
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }

        $query = DB::table('products as p')
            ->leftJoin('customers as cust', 'cust.id', '=', 'p.customer_id')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->leftJoin('project_status as ps', 'm.status_id', '=', 'ps.id')
            ->where('p.is_delete', 0)
            ->whereIn('ps.name', ['Project', 'Regular']) // Filter only Project and Regular
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

        $data = $promiseProducts->map(function($p) use ($epicorData, $epicorBaseMap) {
            $partNo = trim($p->part_no);
            $lookupKeyDirect = $partNo . '-R';
            $epi = $epicorData[$lookupKeyDirect] ?? ($epicorBaseMap[$partNo] ?? null);

            return [
                'promise_part_no' => $partNo,
                'promise_part_name' => $p->part_name,
                'customer' => $p->customer_code,
                'model' => $p->model_name,
                'project_status' => $p->project_status_name ?? 'No Status',
                'target_epicor' => $epi ? $epi->PartNum : $lookupKeyDirect,
                'vendor_id' => $epi ? $epi->VendorID : '-',
                'epicor_price' => $epi ? (float) $epi->BaseUnitPrice : null,
                'epicor_pum' => $epi ? $epi->PUM : null,
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
        try {
            $allData = $this->getComparisonData($request);
            
            $draw = (int) $request->input('draw');
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);

            $recordsTotal = DB::table('products')->where('is_delete', 0)->count();
            $recordsFiltered = $allData->count();
            
            $pagedData = $allData->slice($start, $length)->values();

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $pagedData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'draw' => (int)$request->input('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function export(Request $request)
    {
        $data = $this->getComparisonData($request);

        $headers = [
            'Part No Promise',
            'Part Name',
            'Customer',
            'Model',
            'Project Status',
            'Sync Status',
            'Epicor PartNum',
            'Vendor ID',
            'Price',
            'PUM',
            'Effective Date',
            'Expiry Date'
        ];

        return Excel::download(new class($data, $headers) implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles {
            private $data;
            private $headers;

            public function __construct($data, $headers) {
                $this->data = $data;
                $this->headers = $headers;
            }

            public function collection() {
                return $this->data->map(function($item) {
                    return [
                        $item['promise_part_no'],
                        $item['promise_part_name'],
                        $item['customer'],
                        $item['model'],
                        $item['project_status'],
                        $item['status'],
                        $item['target_epicor'],
                        $item['vendor_id'],
                        $item['epicor_price'],
                        $item['epicor_pum'],
                        $item['epicor_effective'],
                        $item['epicor_expired'],
                    ];
                });
            }

            public function headings(): array {
                return $this->headers;
            }

            public function styles(Worksheet $sheet) {
                return [
                    1 => [
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '4F46E5']
                        ]
                    ],
                ];
            }
        }, 'epicor_comparison_' . date('Ymd_His') . '.xlsx');
    }
}
