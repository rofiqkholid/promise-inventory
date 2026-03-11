<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>QR Labels</title>
<style>
  @page {
    size: 4.13in 5.83in;
    margin: 0;
  }

  html, body {
    padding: 0;
    margin: 0;
  }
  body {
    font-family: Arial, Helvetica, sans-serif;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    background: #fff;
  }

  .label {
    box-sizing: border-box;
    width: 4.13in;
    height: 5.83in;
    padding: 0.15in;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: stretch;
    gap: 0.05in;
    position: relative;
    page-break-after: always;
  }
  .label.last {
    page-break-after: auto;
  }

  .ga-status-center {
    text-align: center;
    margin: 2px 0;
    z-index: 10;
  }
  .ga-status-text {
    background: #000;
    color: #fff;
    padding: 3px 20px;
    font-weight: 900;
    font-size: 18px;
    border-radius: 3px;
    display: inline-block;
    letter-spacing: 3px;
    line-height: 1.1;
  }

  .qr-wrap {
    flex: 0 0 35%;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .qr-wrap svg,
  .qr-wrap img,
  .qr-wrap canvas,
  .qr-wrap div {
    width: 75% !important;
    height: 75% !important;
    object-fit: contain;
  }

  .details {
    flex: 1;
    border: 1px solid #000;
    padding: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  .details .title {
    text-align: center;
    font-weight: bold;
    font-size: 16px;
    padding: 8px 0;
    margin: 0;
    background: #fff;
    border-bottom: 1.5px solid #000;
    letter-spacing: 1px;
  }

  .details table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13.5px;
    margin-top: 5px;
  }
  .details td {
    padding: 3px 8px;
    vertical-align: top;
    line-height: 1.3;
  }
  .details .titleDetail {
    width: 75px;
    font-weight: bold;
    color: #444;
  }
  .details .colon {
    width: 10px;
    font-weight: bold;
    text-align: center;
  }
  .details .valueContent {
    font-weight: 500;
  }

  /* Dimension info styling */
  .dim-info {
    font-size: 9px;
    color: #666;
    font-weight: normal;
    display: block;
    margin-top: 2px;
  }

  .badge {
    background: #000;
    color: #fff;
    padding: 2px 6px;
    margin-left: 4px;
    font-weight: bold;
    font-size: 14px;
    border-radius: 2px;
    display: inline-block;
    line-height: 1;
    vertical-align: middle;
  }

  .dim-unit {
    color: #000;
    font-weight: bold;
    margin-right: 2px;
  }

  @media screen {
    body {
      display: grid;
      place-content: start center;
      gap: 12px;
      background: #f3f4f6;
      padding: 12px;
    }
    .label {
      box-shadow: 0 0 0.5rem rgba(0,0,0,0.15);
      border: 1px solid #e5e7eb;
      background: #fff;
    }
  }
</style>
</head>
<body>

  @foreach($products as $product)
    <section class="label {{ $loop->last ? 'last' : '' }}">
      @php
          // Extract GA/Non-GA from material or other source if available
          preg_match('/(Non-GA|GA)/i', $product->material, $matches);
          $gaStatus = $matches[0] ?? null;
      @endphp
      
      <div class="qr-wrap">
        {!! $product->qrcode !!}
      </div>

      @if($gaStatus)
      <div class="ga-status-center">
        <span class="ga-status-text">{{ strtoupper($gaStatus) }}</span>
      </div>
      @endif

      <div class="details">
        <div class="title">PRODUCT IDENTIFICATION</div>
        <table>
          <tr>
            <td class="titleDetail">Part No</td>
            <td class="colon">:</td>
            <td class="valueContent"><b>{{ $product->item_no }}</b></td>
          </tr>
          <tr>
            <td class="titleDetail">Part Name</td>
            <td class="colon">:</td>
            <td class="valueContent">{{ $product->item_name }}</td>
          </tr>
          <tr>
            <td class="titleDetail">Model</td>
            <td class="colon">:</td>
            <td class="valueContent">{{ $product->model_name }}</td>
          </tr>
          <tr>
            <td class="titleDetail">Customer</td>
            <td class="colon">:</td>
            <td class="valueContent">{{ $product->partner_code }}</td>
          </tr>
          <tr>
            <td class="titleDetail">Dimension</td>
            <td class="colon">:</td>
            <td class="valueContent">
                @php
                    $dimValues = explode(' x ', $product->dimension);
                    $dimLabelsRaw = str_replace(['(', ')'], '', $product->dimension_label);
                    $dimLabels = explode(' x ', $dimLabelsRaw);
                    $dimFormatted = [];
                    foreach($dimValues as $i => $v) {
                        $l = isset($dimLabels[$i]) ? trim($dimLabels[$i]) : '';
                        // Bold specific headers for precision look
                        $dimFormatted[] = ($l ? "<span class='dim-unit'>$l:</span>" : "") . trim($v);
                    }
                @endphp
                {!! implode(' &nbsp; ', $dimFormatted) !!}
            </td>
          </tr>
          <tr>
            <td class="titleDetail">Material</td>
            <td class="colon">:</td>
            <td class="valueContent">
                @php
                    $mat = preg_replace('/\(?(Non-GA|GA)\)?/i', '', $product->material);
                @endphp
                {{ trim($mat) }}
            </td>
          </tr>
        </table>
      </div>
    </section>
  @endforeach

</body>
</html>
