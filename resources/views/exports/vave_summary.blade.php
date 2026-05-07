<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
    <table>
       <thead>
            <tr>
                <td colspan="24" style="font-weight: bold; font-size: 14pt; text-align: center;">VA/VE MATERIAL EFFICIENCY SUMMARY</td>
            </tr>
            <tr>
                <td colspan="24" style="text-align: center; font-size: 10pt; color: #666;">Report Generated: {{ date('Y-m-d H:i') }}</td>
            </tr>
            <tr>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">No</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">Customer</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">Model</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">Part No</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">Part Name</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">{{ isset($isRegular) && $isRegular ? 'SQ Reference' : 'EBD Reference' }}</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">{{ isset($isRegular) && $isRegular ? 'Change Status (SQ)' : 'Change Status (EBD)' }}</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">{{ isset($isRegular) && $isRegular ? 'Gross Wt SQ' : 'Gross Wt EBD' }}</th>
                
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">Latest Revision</th>
                
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">Spec</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">Stage (Revision)</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">t</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">W</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">L1</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">L2</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">Pitch</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">Gross Wt</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">Net Wt</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">Scrap (kg)</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">Yield Ratio (%)</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">{{ isset($isRegular) && $isRegular ? 'Status (vs SQ)' : 'Status (vs EBD)' }}</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">Price</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">Cost</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">Gap (kg)</th>
                <th style="font-weight: bold; text-align: center; background-color: #f1f5f9; border: 1px solid #000000;">Gap (IDR)</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Pre-calculate spans for Customer and Model
                $custSpans = [];
                $modelSpans = [];
                foreach($products as $p) {
                    $cKey = $p->customer_code ?: 'N/A';
                    $mKey = $cKey . '|' . ($p->model_name ?: 'N/A');
                    $sCount = count($p->stages);
                    
                    $custSpans[$cKey] = ($custSpans[$cKey] ?? 0) + $sCount;
                    $modelSpans[$mKey] = ($modelSpans[$mKey] ?? 0) + $sCount;
                }
                
                $doneCust = [];
                $doneModel = [];
            @endphp

            @foreach($products as $index => $p)
                @php
                    $rowCount = count($p->stages);
                    $baselineCost = $p->baseline_cost;
                    $cKey = $p->customer_code ?: 'N/A';
                    $mKey = $cKey . '|' . ($p->model_name ?: 'N/A');
                @endphp
                @foreach($p->stages as $sIndex => $stage)
                <tr>
                    @if($sIndex === 0)
                        <td rowspan="{{ $rowCount }}" style="border: 1px solid #000000; text-align: center; vertical-align: top;">{{ $index + 1 }}</td>
                        
                        @if(!isset($doneCust[$cKey]))
                            <td rowspan="{{ $custSpans[$cKey] }}" style="border: 1px solid #000000; text-align: center; vertical-align: middle; font-weight: bold; background-color: #fafafa;">{{ $p->customer_code }}</td>
                            @php $doneCust[$cKey] = true; @endphp
                        @endif

                        @if(!isset($doneModel[$mKey]))
                            <td rowspan="{{ $modelSpans[$mKey] }}" style="border: 1px solid #000000; text-align: center; vertical-align: middle; font-weight: bold; background-color: #fafafa;">{{ $p->model_name }}</td>
                            @php $doneModel[$mKey] = true; @endphp
                        @endif

                        <td rowspan="{{ $rowCount }}" style="border: 1px solid #000000; vertical-align: top;">{{ $p->part_no }}</td>
                        <td rowspan="{{ $rowCount }}" style="border: 1px solid #000000; vertical-align: top;">{{ $p->part_name }}</td>
                        
                       {{-- New Columns --}}
                        <td rowspan="{{ $rowCount }}" style="border: 1px solid #000000; vertical-align: top; text-align: center; background-color: #fffbeb;">{{ $p->baseline_name }}</td>
                        
                        {{-- CHANGE STATUS (Product Level) --}}
                        @php
                            $pChange = $p->change_status ?? '-';
                            $pChangeBg = '#ffffff';
                            if ($pChange === 'CHANGE') $pChangeBg = '#fef3c7';
                            elseif ($pChange === 'NO CHANGE') $pChangeBg = '#f3f4f6';
                            elseif ($pChange === 'NEW') $pChangeBg = '#ecfdf5';
                        @endphp
                        <td rowspan="{{ $rowCount }}" style="border: 1px solid #000000; vertical-align: top; text-align: center; background-color: {{ $pChangeBg }}; font-weight: bold;">{{ $pChange }}</td>

                        <td rowspan="{{ $rowCount }}" style="border: 1px solid #000000; vertical-align: top; text-align: center; background-color: #fffbeb; font-weight: bold;">{{ number_format($p->baseline_weight, 3) }}</td>
                    @endif
                    
                    {{-- KOLOM Latest Revision: 1 if latest, 0 otherwise --}}
                    @php
                        $isLatest = ($sIndex === $rowCount - 1) ? 1 : 0;
                        $revBg = $isLatest ? '#f0fdf4' : '#ffffff';
                    @endphp
                    <td style="border: 1px solid #000000; text-align: center; background-color: {{ $revBg }}; font-weight: bold;">
                        {{ $isLatest }}
                    </td>

                    <td style="border: 1px solid #000000;">{{ $stage['spec'] }}</td>
                    <td style="border: 1px solid #000000; font-weight: bold;">{{ strtoupper($stage['name']) }}</td>
                    <td style="border: 1px solid #000000; text-align: center;">{{ number_format($stage['t'], 2, '.', '') }}</td>
                    <td style="border: 1px solid #000000; text-align: center;">{{ number_format($stage['w'], 2, '.', '') }}</td>
                    <td style="border: 1px solid #000000; text-align: center;">{{ number_format($stage['l1'], 2, '.', '') }}</td>
                    <td style="border: 1px solid #000000; text-align: center;">{{ number_format($stage['l2'], 2, '.', '') }}</td>
                    <td style="border: 1px solid #000000; text-align: center;">{{ number_format($stage['pitch'], 2, '.', '') }}</td>
                    <td style="border: 1px solid #000000; text-align: center;">{{ number_format($stage['theoretical_weight'], 3, '.', '') }}</td>
                    <td style="border: 1px solid #000000; text-align: center;">{{ number_format($stage['net_weight'], 3, '.', '') }}</td>
                    <td style="border: 1px solid #000000; text-align: center; font-style: italic; color: #64748b;">{{ number_format($stage['theoretical_weight'] - $stage['net_weight'], 3) }}</td>
                    <td style="border: 1px solid #000000; text-align: center; font-weight: bold;">{{ number_format($stage['budomari'], 2) }}%</td>

                    @php
                        $status = '-';
                        $bgColor = '#ffffff';
                        $textColor = '#000000';
                        $hasBaseline = $p->baseline_weight > 0;
                        
                        if (!$stage['is_baseline'] && $hasBaseline && $stage['theoretical_weight'] > 0) {
                            $savingWeight = $p->baseline_weight - $stage['theoretical_weight'];
                            if ($savingWeight > 0.0001) {
                                $status = 'MERIT';
                                $bgColor = '#dcfce7';
                                $textColor = '#166534';
                            } else if ($savingWeight < -0.0001) {
                                $status = 'LOSS';
                                $bgColor = '#fee2e2';
                                $textColor = '#991b1b';
                            } else {
                                $status = 'NO CHANGE';
                            }
                        }
                    @endphp
                    <td style="border: 1px solid #000000; text-align: center; background-color: {{ $bgColor }}; color: {{ $textColor }}; font-weight: bold;">
                        {{ $status }}
                    </td>

                    <td style="border: 1px solid #000000; text-align: right;{{ $stage['material_price'] <= 0 && (isset($isRegular) && $isRegular) ? ' color: #dc2626; font-style: italic;' : '' }}">
                        {{ $stage['material_price'] > 0 ? number_format($stage['material_price'], 2) : (isset($isRegular) && $isRegular ? 'No Epicor Data' : '0.00') }}
                    </td>
                    <td style="border: 1px solid #000000; text-align: right; background-color: #f8fafc; font-weight: bold;{{ $stage['material_price'] <= 0 && (isset($isRegular) && $isRegular) ? ' color: #dc2626; font-style: italic;' : '' }}">
                        {{ $stage['material_price'] > 0 ? number_format($stage['cost'], 2) : (isset($isRegular) && $isRegular ? 'N/A' : '0.00') }}
                    </td>
                    
                   @php
                        // 1. GAP KG (Selisih Gross Weight)
                        $gapKg = 0;
                        $saving = 0;
                        $gapKgColor = '#000000'; $gapKgBg = '#ffffff';
                        $savingColor = '#000000'; $savingBg = '#ffffff';

                        if (!$stage['is_baseline'] && $hasBaseline && $stage['theoretical_weight'] > 0) {
                            $gapKg = $p->baseline_weight - $stage['theoretical_weight'];
                            $roundGapKg = round((float)$gapKg, 3);
                            
                            if ($roundGapKg > 0) {
                                $gapKgColor = '#166534'; $gapKgBg = '#f0fdf4'; // Merit
                            } elseif ($roundGapKg < 0) {
                                $gapKgColor = '#991b1b'; $gapKgBg = '#fef2f2'; // Loss
                            }

                            // 2. GAP IDR (Selisih Cost)
                            $saving = $baselineCost - $stage['cost'];
                            $roundSaving = round((float)$saving, 2);

                            if ($stage['material_price'] > 0 || !(isset($isRegular) && $isRegular)) {
                                if ($roundSaving > 0) {
                                    $savingColor = '#166534'; $savingBg = '#f0fdf4'; // Merit
                                } elseif ($roundSaving < 0) {
                                    $savingColor = '#991b1b'; $savingBg = '#fef2f2'; // Loss
                                } else {
                                    $savingColor = '#000000'; $savingBg = '#ffffff';
                                }
                            } else {
                                $savingColor = '#9ca3af'; $savingBg = '#f9fafb';
                            }
                        }
                    @endphp

                    {{-- KOLOM GAP (kg) --}}
                    <td style="border: 1px solid #000000; text-align: right; color: {{ $gapKgColor }}; background-color: {{ $gapKgBg }}; font-weight: bold;">
                        {{ (!$stage['is_baseline'] && $hasBaseline && $stage['theoretical_weight'] > 0) ? number_format($gapKg, 3) : '-' }}
                    </td>

                    {{-- KOLOM GAP (IDR) --}}
                    <td style="border: 1px solid #000000; text-align: right; color: {{ $savingColor }}; background-color: {{ $savingBg }}; font-weight: bold;">
                        {{ (!$stage['is_baseline'] && $hasBaseline && $stage['theoretical_weight'] > 0) ? ($stage['material_price'] > 0 || !(isset($isRegular) && $isRegular) ? number_format($saving, 2) : 'N/A') : '-' }}
                    </td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
