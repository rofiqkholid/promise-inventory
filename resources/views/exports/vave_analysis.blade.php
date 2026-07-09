<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
    <table>
        <tr>
            <td colspan="{{ $totalCols }}" style="font-weight: bold; font-size: 16pt; text-align: center;">VA/VE MATERIAL ANALYSIS REPORT</td>
        </tr>
        <tr>
            <td colspan="{{ $totalCols }}" style="text-align: center; font-size: 11pt;">PART NO: {{ $product->part_no }} | PART NAME: {{ $product->part_name }}</td>
        </tr>
        <tr>
            <td colspan="{{ $totalCols }}"></td>
        </tr>
        
        {{-- Header Row --}}
        <tr>
            <td style="font-weight: bold; border: 1px solid #000000; background-color: #f3f4f6; width: 30px;">PARAMETER</td>
            <td style="font-weight: bold; border: 1px solid #000000; background-color: #dbeafe; text-align: center; width: 25px;">{{ isset($isRegular) && $isRegular ? 'PLAN (SQ)' : 'PLAN (BASE)' }}</td>
            <td style="font-weight: bold; border: 1px solid #000000; background-color: #d1fae5; text-align: center; width: 25px;">ACTUAL (REV)</td>
            <td style="font-weight: bold; border: 1px solid #000000; background-color: #f3f4f6; text-align: center; width: 20px;">VARIANCE (Δ)</td>
            @foreach($baseHistory as $r)
                <td style="font-weight: bold; border: 1px solid #000000; background-color: #f9fafb; color: #6b7280; text-align: center; width: 20px;">{{ isset($isRegular) && $isRegular ? 'HISTORY (SQ)' : 'HISTORY (BASE)' }}</td>
            @endforeach
            @foreach($revisions as $idx => $rev)
                @if($idx > 0)
                    <td style="font-weight: bold; border: 1px solid #000000; background-color: #f9fafb; color: #6b7280; text-align: center; width: 20px;">HISTORY (REV)</td>
                @endif
            @endforeach
        </tr>
        
        {{-- Identifier Row --}}
        <tr>
            <td style="border: 1px solid #000000; font-weight: bold; background-color: #f9fafb;">Revision / Name</td>
            <td style="border: 1px solid #000000; text-align: center; font-weight: bold; background-color: #dbeafe;">
                {{ $vaveBase->base_name ?? 'Base' }}
                {{ $vaveBase->suffix ? ' - ' . $vaveBase->suffix->name : '' }}
            </td>
            <td style="border: 1px solid #000000; text-align: center; font-weight: bold; background-color: #d1fae5;">Rev {{ $latestRev->revision->code ?? '-' }}</td>
            <td style="border: 1px solid #000000; text-align: center; background-color: #f9fafb;">Actual - Plan</td>
            @foreach($baseHistory as $r)
                <td style="border: 1px solid #000000; text-align: center; color: #6b7280; background-color: #f9fafb;">
                    {{ $r->base_name }}
                    {{ $r->suffix ? ' - ' . $r->suffix->name : '' }}
                </td>
            @endforeach
            @foreach($revisions as $idx => $rev)
                @if($idx > 0)
                    <td style="border: 1px solid #000000; text-align: center; color: #6b7280; background-color: #f9fafb;">Rev {{ $rev->revision->code ?? '-' }}</td>
                @endif
            @endforeach
        </tr>

        {{-- Section: Specification --}}
        <tr>
            <td colspan="{{ $totalCols }}" style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000000; padding: 5px;">1. SPECIFICATION</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000000;">Material Spec</td>            <td style="border: 1px solid #000000; text-align: center;">{{ $vaveBase->materialSpec->spec_name ?? '-' }}</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ $latestRev->materialSpec->spec_name ?? '-' }}</td>
            <td style="border: 1px solid #000000; text-align: center; background-color: #f9fafb;">-</td>
            @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center;">{{ $r->materialSpec->spec_name ?? '-' }}</td> @endforeach
            @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center;">{{ $rev->materialSpec->spec_name ?? '-' }}</td> @endif @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000000;">Unit Type</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ $vaveBase->unit->name ?? '-' }}</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ $latestRev->unit->name ?? '-' }}</td>
            <td style="border: 1px solid #000000; text-align: center; background-color: #f9fafb;">-</td>
            @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center;">{{ $r->unit->name ?? '-' }}</td> @endforeach
            @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center;">{{ $rev->unit->name ?? '-' }}</td> @endif @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000000;">Dimensions (mm)</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ $vaveBase->fmt_dimension }}</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ $latestRev->fmt_dimension }}</td>
            <td style="border: 1px solid #000000; text-align: center; background-color: #f9fafb;">-</td>
            @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center;">{{ $r->fmt_dimension }}</td> @endforeach
            @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center;">{{ $rev->fmt_dimension }}</td> @endif @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000000;">Thickness (mm)</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ number_format($vaveBase->thickness, 2) }}</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ number_format($latestRev->thickness, 2) }}</td>
            <td style="border: 1px solid #000000; text-align: center; background-color: #f9fafb;">{{ ($deltaT > 0 ? '+' : '') . number_format($deltaT, 2) }}</td>
            @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($r->thickness, 2) }}</td> @endforeach
            @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($rev->thickness, 2) }}</td> @endif @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000000;">Width (mm)</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ number_format($vaveBase->width, 2) }}</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ number_format($latestRev->width, 2) }}</td>
            <td style="border: 1px solid #000000; text-align: center; background-color: #f9fafb;">{{ ($deltaWi > 0 ? '+' : '') . number_format($deltaWi, 2) }}</td>
            @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($r->width, 2) }}</td> @endforeach
            @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($rev->width, 2) }}</td> @endif @endforeach
        </tr>
 
        @php
            $unitName = strtolower($vaveBase->unit->name ?? '');
            $isTrapezoid = strpos($unitName, 'trapezoid') !== false;
            $isCoil = strpos($unitName, 'coil') !== false;
        @endphp
 
        @if($isCoil)
            <tr>
                <td style="border: 1px solid #000000;">Pitch (mm)</td>
                <td style="border: 1px solid #000000; text-align: center;">{{ number_format($vaveBase->pitch, 2) }}</td>
                <td style="border: 1px solid #000000; text-align: center;">{{ number_format($latestRev->pitch, 2) }}</td>
                <td style="border: 1px solid #000000; text-align: center; background-color: #f9fafb;">{{ ($deltaP > 0 ? '+' : '') . number_format($deltaP, 2) }}</td>
                @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($r->pitch, 2) }}</td> @endforeach
                @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($rev->pitch, 2) }}</td> @endif @endforeach
            </tr>
        @elseif($isTrapezoid)
            <tr>
                <td style="border: 1px solid #000000;">Length 1 (L1)</td>
                <td style="border: 1px solid #000000; text-align: center;">{{ number_format($vaveBase->length, 2) }}</td>
                <td style="border: 1px solid #000000; text-align: center;">{{ number_format($latestRev->length, 2) }}</td>
                <td style="border: 1px solid #000000; text-align: center; background-color: #f9fafb;">{{ ($deltaL > 0 ? '+' : '') . number_format($deltaL, 2) }}</td>
                @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($r->length, 2) }}</td> @endforeach
                @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($rev->length, 2) }}</td> @endif @endforeach
            </tr>
            <tr>
                <td style="border: 1px solid #000000;">Length 2 (L2)</td>
                <td style="border: 1px solid #000000; text-align: center;">{{ number_format($vaveBase->length_2, 2) }}</td>
                <td style="border: 1px solid #000000; text-align: center;">{{ number_format($latestRev->length_2, 2) }}</td>
                <td style="border: 1px solid #000000; text-align: center; background-color: #f9fafb;">{{ ($deltaL2 > 0 ? '+' : '') . number_format($deltaL2, 2) }}</td>
                @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($r->length_2, 2) }}</td> @endforeach
                @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($rev->length_2, 2) }}</td> @endif @endforeach
            </tr>
        @else
            <tr>
                <td style="border: 1px solid #000000;">Length (mm)</td>
                <td style="border: 1px solid #000000; text-align: center;">{{ number_format($vaveBase->length, 2) }}</td>
                <td style="border: 1px solid #000000; text-align: center;">{{ number_format($latestRev->length, 2) }}</td>
                <td style="border: 1px solid #000000; text-align: center; background-color: #f9fafb;">{{ ($deltaL > 0 ? '+' : '') . number_format($deltaL, 2) }}</td>
                @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($r->length, 2) }}</td> @endforeach
                @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($rev->length, 2) }}</td> @endif @endforeach
            </tr>
        @endif

        {{-- Section: Yield & Weight --}}
        <tr>
            <td colspan="{{ $totalCols }}" style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000000; padding: 5px;">2. YIELD &amp; WEIGHT</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000000;">Density</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ number_format($vaveBase->density, 3) }}</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ number_format($latestRev->density, 3) }}</td>
            <td style="border: 1px solid #000000; text-align: center; background-color: #f9fafb;">{{ ($deltaD > 0 ? '+' : '') . number_format($deltaD, 3) }}</td>
            @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($r->density, 3) }}</td> @endforeach
            @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($rev->density, 3) }}</td> @endif @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000000;">Pcs / Pitch</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ number_format($vaveBase->pcs_per_pitch ?? 0, 0) }}</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ number_format($latestRev->pcs_per_pitch ?? 0, 0) }}</td>
            <td style="border: 1px solid #000000; text-align: center; background-color: #f9fafb;">{{ ($deltaPcsPitch > 0 ? '+' : '') . number_format($deltaPcsPitch, 0) }}</td>
            @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($r->pcs_per_pitch ?? 0, 0) }}</td> @endforeach
            @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($rev->pcs_per_pitch ?? 0, 0) }}</td> @endif @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000000;">Pcs / Unit</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ number_format($vaveBase->pcs_per_unit ?? 0, 0) }}</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ number_format($latestRev->pcs_per_unit ?? 0, 0) }}</td>
            <td style="border: 1px solid #000000; text-align: center; background-color: #f9fafb;">{{ ($deltaPcsUnit > 0 ? '+' : '') . number_format($deltaPcsUnit, 0) }}</td>
            @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($r->pcs_per_unit ?? 0, 0) }}</td> @endforeach
            @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($rev->pcs_per_unit ?? 0, 0) }}</td> @endif @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000000;">Gross Weight (kg)</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ number_format($baseW, 3) }}</td>
            <td style="border: 1px solid #000000; text-align: center; font-weight: bold;">{{ number_format($actW, 3) }}</td>
            @php $colorW = $deltaW > 0 ? '#dc2626' : ($deltaW < 0 ? '#16a34a' : '#000000'); @endphp
            <td style="border: 1px solid #000000; text-align: center; font-weight: bold; color: {{ $colorW }};">{{ ($deltaW > 0 ? '+' : '') . number_format($deltaW, 3) }}</td>
            @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($r->weight_kg, 3) }}</td> @endforeach
            @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($rev->weight_kg, 3) }}</td> @endif @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000000;">Net Weight/Part (kg)</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ is_null($vaveBase->net_weight) || $vaveBase->net_weight === '' || $vaveBase->net_weight <= 0 ? '-' : number_format($vaveBase->net_weight, 3) }}</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ is_null($latestRev->net_weight) || $latestRev->net_weight === '' || $latestRev->net_weight <= 0 ? '-' : number_format($latestRev->net_weight, 3) }}</td>
            <td style="border: 1px solid #000000; text-align: center; background-color: #f9fafb;">{{ is_null($vaveBase->net_weight) || is_null($latestRev->net_weight) || $vaveBase->net_weight === '' || $latestRev->net_weight === '' || $vaveBase->net_weight <= 0 || $latestRev->net_weight <= 0 ? '-' : ($deltaNW > 0 ? '+' : '') . number_format($deltaNW, 3) }}</td>
            @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center;">{{ is_null($r->net_weight) || $r->net_weight === '' || $r->net_weight <= 0 ? '-' : number_format($r->net_weight, 3) }}</td> @endforeach
            @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center;">{{ is_null($rev->net_weight) || $rev->net_weight === '' || $rev->net_weight <= 0 ? '-' : number_format($rev->net_weight, 3) }}</td> @endif @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000000; font-style: italic; color: #64748b;">Scrap (kg)</td>
            <td style="border: 1px solid #000000; text-align: center; color: #64748b;">{{ is_null($baseScrap) ? '-' : number_format($baseScrap, 3) }}</td>
            <td style="border: 1px solid #000000; text-align: center; color: #64748b;">{{ is_null($actScrap) ? '-' : number_format($actScrap, 3) }}</td>
            <td style="border: 1px solid #000000; text-align: center; background-color: #f9fafb; color: #64748b;">{{ is_null($deltaScrap) ? '-' : ($deltaScrap > 0 ? '+' : '') . number_format($deltaScrap, 3) }}</td>
            @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center; color: #64748b;">{{ is_null($r->net_weight) || $r->net_weight === '' || $r->net_weight <= 0 ? '-' : number_format($r->weight_kg - $r->net_weight, 3) }}</td> @endforeach
            @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center; color: #64748b;">{{ is_null($rev->net_weight) || $rev->net_weight === '' || $rev->net_weight <= 0 ? '-' : number_format($rev->weight_kg - $rev->net_weight, 3) }}</td> @endif @endforeach
        </tr>

        @php
            $calcBud = function($i) {
                $g = (float)($i->weight_kg ?? 0);
                $n = (float)($i->net_weight ?? 0);
                return ($g > 0 && $n > 0) ? ($n / $g) * 100 : 0;
            };
        @endphp

        <tr>
            <td style="border: 1px solid #000000;">Yield Ratio (%)</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ number_format($baseBud, 1) }}%</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ number_format($actBud, 1) }}%</td>
            <td style="border: 1px solid #000000; text-align: center; background-color: #f9fafb; font-weight: bold; color: {{ $deltaBud >= 0 ? '#16a34a' : '#dc2626' }};">{{ ($deltaBud > 0 ? '+' : '') . number_format($deltaBud, 1) }}%</td>
            @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($calcBud($r), 1) }}%</td> @endforeach
            @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center;">{{ number_format($calcBud($rev), 1) }}%</td> @endif @endforeach
        </tr>

        {{-- Section: Commercial --}}
        <tr>
            <td colspan="{{ $totalCols }}" style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000000; padding: 5px;">3. COMMERCIAL (ESTIMATE)</td>
        </tr>
        <tr>            <td style="border: 1px solid #000000;">Price/kg (IDR)</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ $vaveBase->material_price > 0 ? number_format($vaveBase->material_price, 0) : (isset($isRegular) && $isRegular ? 'No Epicor Data' : '0') }}</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ $latestRev->material_price > 0 ? number_format($latestRev->material_price, 0) : (isset($isRegular) && $isRegular ? 'No Epicor Data' : '0') }}</td>
            @php $deltaPrice = (float)($latestRev->material_price ?? 0) - (float)($vaveBase->material_price ?? 0); @endphp
            <td style="border: 1px solid #000000; text-align: center; background-color: #f9fafb;">{{ ($vaveBase->material_price > 0 && $latestRev->material_price > 0) || !(isset($isRegular) && $isRegular) ? ($deltaPrice > 0 ? '+' : '') . number_format($deltaPrice, 0) : 'N/A' }}</td>
            @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center;">{{ $r->material_price > 0 ? number_format($r->material_price, 0) : (isset($isRegular) && $isRegular ? 'No Epicor Data' : '0') }}</td> @endforeach
            @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center;">{{ $rev->material_price > 0 ? number_format($rev->material_price, 0) : (isset($isRegular) && $isRegular ? 'No Epicor Data' : '0') }}</td> @endif @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000000;">Material Cost (IDR)</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ $vaveBase->material_price > 0 ? number_format($baseW * $vaveBase->material_price, 0) : (isset($isRegular) && $isRegular ? 'N/A' : '0') }}</td>
            <td style="border: 1px solid #000000; text-align: center; font-weight: bold;">{{ $latestRev->material_price > 0 ? number_format($actW * $latestRev->material_price, 0) : (isset($isRegular) && $isRegular ? 'N/A' : '0') }}</td>
            @php 
                $baseCost = $baseW * $vaveBase->material_price;
                $actCost = $actW * $latestRev->material_price;
                $deltaCost = $actCost - $baseCost;
                $colorC = $deltaCost > 0 ? '#dc2626' : ($deltaCost < 0 ? '#16a34a' : '#000000'); 
            @endphp
            <td style="border: 1px solid #000000; text-align: center; font-weight: bold; color: {{ ($vaveBase->material_price > 0 && $latestRev->material_price > 0) || !(isset($isRegular) && $isRegular) ? $colorC : '#9ca3af' }};">{{ ($vaveBase->material_price > 0 && $latestRev->material_price > 0) || !(isset($isRegular) && $isRegular) ? ($deltaCost > 0 ? '+' : '') . number_format($deltaCost, 0) : 'N/A' }}</td>
            @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center;">{{ $r->material_price > 0 ? number_format($r->weight_kg * $r->material_price, 0) : (isset($isRegular) && $isRegular ? 'N/A' : '0') }}</td> @endforeach
            @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center;">{{ $rev->material_price > 0 ? number_format($rev->weight_kg * $rev->material_price, 0) : (isset($isRegular) && $isRegular ? 'N/A' : '0') }}</td> @endif @endforeach
        </tr>
 
        {{-- Section: Additional Info --}}
        <tr>
            <td colspan="{{ $totalCols }}" style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000000; padding: 5px;">4. ADDITIONAL INFO</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000000;">Remark</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ $vaveBase->remark ?? '-' }}</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ $latestRev->remark ?? '-' }}</td>
            <td style="border: 1px solid #000000; text-align: center; background-color: #f9fafb;">-</td>
            @foreach($baseHistory as $r) <td style="border: 1px solid #000000; text-align: center;">{{ $r->remark ?? '-' }}</td> @endforeach
            @foreach($revisions as $idx => $rev) @if($idx > 0) <td style="border: 1px solid #000000; text-align: center;">{{ $rev->remark ?? '-' }}</td> @endif @endforeach
        </tr>

        {{-- Impact Summary --}}
        <tr>
            <td colspan="{{ $totalCols }}"></td>
        </tr>
       @php
            // Gunakan pembulatan (round) agar nilai desimal tersembunyi tetap terbaca sebagai 0
            $roundedImpact = round((float)$impactPct, 2);

            if ($roundedImpact == 0) {
                $finalText = 'NO CHANGE (0.00%)';
                $finalBg = '#ffffff'; // Background putih
                $finalColor = '#000000'; // Text hitam
            } else {
                $finalText = ($isSaving ? 'MERIT' : 'LOSS') . ' (' . ($isSaving ? 'IMPROVEMENT ' : 'INCREASE ') . number_format($impactPct, 2) . '%)';
                $finalBg = $statusBg; 
                $finalColor = $statusColor; 
            }
        @endphp
        <tr>
            <td style="border: 1px solid #000000; font-weight: bold; background-color: #f9fafb;">ANALYSIS STATUS</td>
            <td colspan="{{ $totalCols - 1 }}" style="border: 1px solid #000000; text-align: left; font-weight: bold; background-color: {{ $finalBg }}; color: {{ $finalColor }};">
                 {{ $finalText }}
            </td>
        </tr>
    </table>
</body>
</html>

