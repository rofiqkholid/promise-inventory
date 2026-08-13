<?php

namespace App\Services\Inventory;

use App\Models\InventoryModel\Material\InventoryProduct;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProductService
{
    /**
     * Generate label data for one or multiple products.
     *
     * @param string|array $ids Hash ID or array of Hash IDs
     * @return array
     */
    public function generateLabelData($ids)
    {
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        $ids = (array) $ids;
        if (empty($ids)) return [];

        $numericIds = [];
        foreach ($ids as $id) {
            if (is_numeric($id)) {
                $numericIds[] = (int) $id;
            } else {
                try {
                    $decoded = InventoryProduct::decodeHash($id);
                    if ($decoded) {
                        $numericIds[] = $decoded;
                    }
                } catch (\Exception $e) {}
            }
        }

        if (empty($numericIds)) return [];

        $rawList = DB::table('inv_t_product_detail as p')
            ->join('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('customers as cust', 'cust.id', '=', 'prod.customer_id')
            ->leftJoin('models as model', 'model.id', '=', 'p.model_id')
            ->leftJoin('inv_m_material_spec as ms', 'ms.id', '=', 'p.material_spec_id')
            ->leftJoin('inv_m_rank as r', 'r.id', '=', 'p.rank_id')
            ->leftJoin('inv_m_revision as rev', 'rev.id', '=', 'p.revision_id')
            ->whereIn('p.id', $numericIds)
            ->select([
                'p.id', 'prod.part_no', 'prod.part_name', 'cust.code as customer_code', 'model.name as model_name',
                'rev.code as revision', 'p.thickness', 'p.width', 'p.length', 'p.length_2', 'p.pitch',
                'ms.spec_name as material_spec', 'ms.coating_type', 'r.code as rank_code',
            ])
            ->get()
            ->keyBy('id');

        $labels = [];
        foreach ($numericIds as $numId) {
            if (isset($rawList[$numId])) {
                $data = $rawList[$numId];
                $hashId = InventoryProduct::encodeHash($data->id);
                $inventoryProductObj = (object) ['hash_id' => $hashId, 'id' => $data->id];
                $labels[] = $this->formatLabelObject($inventoryProductObj, $data);
            }
        }

        return $labels;
    }

    /**
     * Format raw database data into a standardized Label Object.
     */
    protected function formatLabelObject($inventoryProduct, $data)
    {
        $dimVal = []; $dimLbl = [];
        if ((float)$data->thickness > 0) { $dimVal[] = (float)$data->thickness; $dimLbl[] = 'T'; }
        if ((float)$data->width > 0) { $dimVal[] = (float)$data->width; $dimLbl[] = 'W'; }
        if ((float)$data->length > 0) { $dimVal[] = (float)$data->length; $dimLbl[] = 'L'; }
        if ((float)$data->length_2 > 0) { $dimVal[] = (float)$data->length_2; $dimLbl[] = 'L2'; }
        if ((float)$data->pitch > 0) { $dimVal[] = (float)$data->pitch; $dimLbl[] = 'P'; }

        return (object) [
            'hash_id'       => $inventoryProduct->hash_id,
            'qrcode'        => QrCode::size(250)->errorCorrection('M')->margin(1)->generate(route('inventory.scanInfo', $inventoryProduct->hash_id)),
            'item_no'       => $data->part_no . ($data->revision ? ' - ' . $data->revision : ''),
            'item_name'     => $data->part_name,
            'model_name'    => $data->model_name ?? '-',
            'partner_code'  => $data->customer_code ?? '-',
            'dimension'     => implode(' x ', $dimVal),
            'dimension_label' => !empty($dimLbl) ? '(' . implode(' x ', $dimLbl) . ')' : '',
            'material'      => $data->material_spec ?? '',
            'coating_type'  => $data->coating_type ?? '',
        ];
    }
}
