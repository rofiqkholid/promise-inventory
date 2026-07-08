<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>QR Labels</title>
<style id="dynamic-page-layout">
  @page {
    size: 45cm 7cm landscape;
    margin: 3mm;
  }
</style>
<style>
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

  /* ===================== DESIGN 1 (45x7 cm) ===================== */
  .label-d1 {
    box-sizing: border-box;
    width: 45cm;
    height: 7cm;
    display: flex;
    flex-direction: row; /* Membagi kolom utama dari kiri ke kanan */
    position: relative;
    page-break-after: always;
    border: 2px solid #000;
    overflow: hidden;
    background: #fff;
  }
  .label-d1.last {
    page-break-after: auto;
  }

  /* Garis pembatas tebal sesuai gambar */
  .border-b { border-bottom: 2px solid #000; }
  .border-r { border-right: 2px solid #000; }

  /* ===================== BAGIAN KIRI UTAMA ===================== */
  .main-left-section {
    width: 55%; /* Diperlebar dari sebelumnya 44% agar Part Name & Size lebih lega */
    display: flex;
    flex-direction: column;
  }

  .sub-row {
    display: flex;
    flex-direction: row;
    flex: 1;
  }

  /* Proporsi pembagian sub-kolom di bagian kiri */
  .col-part { width: 42%; display: flex; flex-direction: column; }
  .col-size { width: 38%; display: flex; flex-direction: column; }
  .col-model { width: 20%; display: flex; flex-direction: column; }

  .cell-header {
    font-weight: bold;
    font-size: 11pt;
    text-align: center;
    border-bottom: 2px solid #000;
    padding: 6px 2px;
    background: #fff;
    text-transform: uppercase;
  }

  .cell-body {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 6px;
    font-size: 12pt;
    font-weight: bold;
    text-align: center;
  }

  /* Sub-tabel untuk bagian SIZE */
  .size-sub-headers {
    display: flex;
    border-bottom: 2px solid #000;
  }
  .size-sub-headers div {
    flex: 1;
    font-size: 9.5pt;
    font-weight: bold;
    text-align: center;
    padding: 4px 1px;
    border-right: 2px solid #000;
  }
  .size-sub-headers div:last-child { border-right: none; }

  .size-values {
    display: flex;
    flex: 1;
  }
  .size-values div {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11pt;
    font-weight: bold;
    border-right: 2px solid #000;
  }
  .size-values div:last-child { border-right: none; }


  /* ===================== BARCODE BARIS PENUH ===================== */
  .col-barcode {
    width: 18%;
    display: flex;
    flex-direction: column;
  }
  .col-barcode .cell-body svg,
  .col-barcode .cell-body img {
    max-height: 5.5cm;
    max-width: 100%;
    width: auto;
    height: auto;
    display: block;
  }

  /* ===================== POSITION BARIS PENUH (KANAN) ===================== */
  .col-position {
    width: 27%; /* Diperkecil agar seimbang dengan kolom kiri yang melebar */
    display: flex;
    flex-direction: column;
  }
  
  .pos-arrow-container {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    position: relative;
  }

  .arrow-toggle-btn {
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .arrow-toggle-btn:focus { outline: none; }
  @media print { .arrow-toggle-btn { cursor: default; pointer-events: none; } }

  /* Tabel Posisi Manual (Front, Middle, Rear) di Bagian Bawah */
  .pos-table-bottom {
    height: 2.5cm; /* Ditambah tingginya agar kotak isiannya lebih tinggi pas diprint */
    display: flex;
    flex-direction: column;
    border-top: 2px solid #000;
  }
  .pos-table-headers {
    display: flex;
    border-bottom: 2px solid #000;
  }
  .pos-table-th {
    flex: 1;
    font-weight: bold;
    font-size: 10pt;
    text-align: center;
    padding: 4px 2px;
    border-right: 2px solid #000;
  }
  .pos-table-th:last-child { border-right: none; }
  
  .pos-table-values {
    display: flex;
    flex: 1;
  }
  .pos-table-td {
    flex: 1;
    border-right: 2px solid #000;
  }
  .pos-table-td:last-child { border-right: none; }

  /* Coating type bar — black background baris di bawah SPEC */
  .coating-bar {
    background: #000;
    color: #fff;
    text-align: center;
    font-weight: bold;
    font-size: 11pt;
    padding: 8px 6px;
    letter-spacing: 0.5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;
  }

  /* ===================== DESIGN 2 (A6 Portrait) ===================== */
  .label-d2 {
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
    border: 2px solid #000;
    overflow: hidden;
    background: #fff;
  }
  .label-d2.last {
    page-break-after: auto;
  }

  .label-d2 .ga-status-center {
    text-align: center;
    margin: 2px 0;
    z-index: 10;
  }
  .label-d2 .ga-status-text {
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

  .label-d2 .qr-wrap {
    flex: 0 0 35%;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .label-d2 .qr-wrap svg,
  .label-d2 .qr-wrap img,
  .label-d2 .qr-wrap canvas,
  .label-d2 .qr-wrap div {
    width: 75% !important;
    height: 75% !important;
    object-fit: contain;
  }

  .label-d2 .details {
    flex: 1;
    border: 1px solid #000;
    padding: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  .label-d2 .details .title {
    text-align: center;
    font-weight: bold;
    font-size: 16px;
    padding: 8px 0;
    margin: 0;
    background: #fff;
    border-bottom: 1.5px solid #000;
    letter-spacing: 1px;
  }

  .label-d2 .details table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13.5px;
    margin-top: 5px;
  }
  .label-d2 .details td {
    padding: 3px 8px;
    vertical-align: top;
    line-height: 1.3;
  }
  .label-d2 .details .titleDetail {
    width: 75px;
    font-weight: bold;
    color: #444;
  }
  .label-d2 .details .colon {
    width: 10px;
    font-weight: bold;
    text-align: center;
  }
  .label-d2 .details .valueContent {
    font-weight: 500;
  }

  /* Dimension info styling */
  .label-d2 .dim-info {
    font-size: 9px;
    color: #666;
    font-weight: normal;
    display: block;
    margin-top: 2px;
  }

  .label-d2 .badge {
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

  .label-d2 .dim-unit {
    color: #000;
    font-weight: bold;
    margin-right: 2px;
  }


  /* ===================== TAMPILAN PREVIEW LAYAR KOMPUTER ===================== */
  @media screen {
    body {
      display: flex;
      background: #f3f4f6;
      padding: 0;
      margin: 0;
      height: 100vh;
      overflow: hidden;
    }
    .print-panel {
      width: 300px;
      background: #ffffff;
      border-right: 1px solid #e5e7eb;
      padding: 20px;
      display: flex;
      flex-direction: column;
      height: 100vh;
      box-sizing: border-box;
      box-shadow: 2px 0 8px rgba(0,0,0,0.05);
      z-index: 50;
      flex-shrink: 0;
    }
    .print-panel h2 { margin: 0 0 4px 0; font-size: 15px; font-weight: bold; color: #1e293b; }
    .print-panel p { margin: 0; font-size: 11px; color: #64748b; line-height: 1.4; }
    .panel-header { margin-bottom: 14px; }
    .panel-search { margin-bottom: 10px; }
    .panel-search input {
      width: 100%; box-sizing: border-box; padding: 7px 10px; font-size: 12px;
      border: 1px solid #cbd5e1; border-radius: 4px; outline: none;
    }
    .panel-select-all { display: flex; align-items: center; gap: 8px; padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-size: 11px; font-weight: bold; color: #475569; }
    .panel-list { list-style: none; padding: 0; margin: 10px 0 0 0; flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 6px; }
    .panel-item { display: flex; align-items: flex-start; gap: 8px; padding: 8px; border-radius: 4px; background: #f8fafc; border: 1px solid #f1f5f9; }
    .panel-item-info { display: flex; flex-direction: column; gap: 2px; }
    .panel-item-info .part-no { font-size: 11px; font-weight: bold; color: #1e293b; }
    .panel-item-info .part-desc { font-size: 9px; color: #64748b; }
    .panel-footer { margin-top: 14px; display: flex; flex-direction: column; gap: 8px; border-top: 1px solid #e2e8f0; padding-top: 14px; flex-shrink: 0; }
    #btnPrintNow { width: 100%; padding: 10px; background: #6366f1; color: #fff; border: none; border-radius: 4px; font-size: 12px; font-weight: bold; cursor: pointer; }
    .print-preview-container { flex: 1; height: 100vh; overflow-y: auto; padding: 24px; display: flex; flex-direction: column; gap: 20px; box-sizing: border-box; align-items: center; }
    .label-d1 { box-shadow: 0 4px 14px rgba(0,0,0,0.12); flex-shrink: 0; transform-origin: top left; }
    .label-d2 { box-shadow: 0 4px 12px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; background: #fff; flex-shrink: 0; transform-origin: top left; }
  }

  @media print {
    .print-panel { display: none !important; }
    .print-preview-container { margin: 0 !important; padding: 0 !important; display: block !important; height: auto !important; overflow: visible !important; }
    .label-d1 { transform: none !important; margin-bottom: 0 !important; box-shadow: none !important; }
    .label-d2 { transform: none !important; margin-bottom: 0 !important; box-shadow: none !important; border: none !important; }
    
    /* Continuous / Multi-label printing mode styles */
    body.print-multi .label-d1,
    body.print-multi .label-d2 {
      page-break-after: auto !important;
      page-break-inside: avoid !important;
      break-inside: avoid !important;
      margin-bottom: 20px !important;
    }
  }
</style>
</head>
<body>

  <aside class="print-panel">
    <div class="panel-header">
      <h2>Print Labels</h2>
      <p>Select which items to include in the print result.</p>
    </div>
    
    <div style="margin-bottom: 12px;">
      <label for="templateSelect" style="font-size: 11px; font-weight: bold; color: #475569; display: block; margin-bottom: 4px;">Design Template</label>
      <select id="templateSelect" style="width: 100%; box-sizing: border-box; padding: 7px 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; cursor: pointer;">
        <option value="1">Template 1 (45cm x 7cm)</option>
        <option value="2">Template 2 (A6 Portrait)</option>
      </select>
    </div>

    <div style="margin-bottom: 12px;" id="printModeContainer">
      <label for="printModeSelect" style="font-size: 11px; font-weight: bold; color: #475569; display: block; margin-bottom: 4px;">Print Mode</label>
      <select id="printModeSelect" style="width: 100%; box-sizing: border-box; padding: 7px 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; cursor: pointer;">
        <option value="single">Single Label per Page (Single)</option>
        <option value="multi">Multiple Labels per Page (Continuous)</option>
      </select>
    </div>

    <div class="panel-search">
      <input type="text" id="panelSearch" placeholder="Search part number, model...">
    </div>
    <div class="panel-select-all">
      <input type="checkbox" id="panelSelectAll" checked style="cursor: pointer;">
      <label for="panelSelectAll" style="cursor: pointer;">Select All</label>
    </div>
    <ul class="panel-list">
      @foreach($products as $product)
        <li class="panel-item" data-id="{{ $product->hash_id }}" data-search="{{ strtolower($product->item_no . ' ' . $product->item_name . ' ' . $product->model_name . ' ' . $product->partner_code) }}">
          <input type="checkbox" class="panel-item-check" data-id="{{ $product->hash_id }}" checked style="cursor: pointer; margin-top: 3px;">
          <div class="panel-item-info">
            <span class="part-no">{{ $product->item_no }}</span>
            <span class="part-desc">{{ $product->item_name }}</span>
          </div>
        </li>
      @endforeach
    </ul>
    <div class="panel-footer">
      <div class="selection-status">Selected: <span id="selectedCount">{{ count($products) }}</span> / {{ count($products) }} item(s)</div>
      <button id="btnPrintNow">Print Selected</button>
    </div>
  </aside>

  <main class="print-preview-container">
    @foreach($products as $product)
      @php
        $dimLabelsRaw = str_replace(['(', ')'], '', $product->dimension_label ?? '');
        $dimLabels    = array_map('trim', explode(' x ', $dimLabelsRaw));
        $dimValues    = array_map('trim', explode(' x ', $product->dimension ?? ''));

        $dimMap = [];
        foreach ($dimLabels as $i => $lbl) {
            $dimMap[strtoupper($lbl)] = $dimValues[$i] ?? '';
        }

        $tVal = $dimMap['T']  ?? '';
        $wVal = $dimMap['W']  ?? '';
        $lVal = $dimMap['L']  ?? ($dimMap['L2'] ?? '');
        $specStr    = trim($product->material ?? '');
        $coatingStr = trim($product->coating_type ?? '');

        // Design 2 GA/Non-GA extraction
        preg_match('/(Non-GA|GA)/i', $product->material, $matches);
        $gaStatus = $matches[0] ?? null;
      @endphp

      <!-- Design 1: 45cm x 7cm -->
      <section class="label-d1 {{ $loop->last ? 'last' : '' }}" id="label-d1-{{ $product->hash_id }}">
        
        <div class="main-left-section border-r">
          
          <div class="sub-row border-b">
            <div class="col-part border-r">
              <div class="cell-header">PART NAME</div>
              <div class="cell-body">{{ $product->item_name }}</div>
            </div>
            
            <div class="col-size border-r">
              <div class="cell-header">SIZE</div>
              <div class="size-sub-headers">
                <div>T (mm)</div>
                <div>W (mm)</div>
                <div>L (mm)</div>
              </div>
              <div class="size-values">
                <div>{{ $tVal }}</div>
                <div>{{ $wVal }}</div>
                <div>{{ $lVal }}</div>
              </div>
            </div>

            <div class="col-model">
              <div class="cell-header">MODEL</div>
              <div class="cell-body">{{ $product->model_name }}</div>
            </div>
          </div>

          <div class="sub-row">
            <div class="col-part border-r">
              <div class="cell-header">PART NO</div>
              <div class="cell-body">{{ $product->item_no }}</div>
            </div>
            
            <div class="col-size border-r">
              <div class="cell-header">SPEC</div>
              <div class="cell-body" style="font-size: 12pt; font-weight: bold; text-align: center; justify-content: center; align-items: center;">
                {{ $specStr }}
              </div>
              @if($coatingStr)
              <div class="coating-bar">{{ $coatingStr }}</div>
              @endif
            </div>

            <div class="col-model">
              <div class="cell-header">CUSTOMER</div>
              <div class="cell-body">{{ $product->partner_code }}</div>
            </div>
          </div>

        </div>

        <div class="col-barcode border-r">
          <div class="cell-header">BARCODE</div>
          <div class="cell-body">
            {!! $product->qrcode !!}
          </div>
        </div>

        <div class="col-position">
          <div class="cell-header">POSITION</div>
          
          <div class="pos-arrow-container">
            <button class="arrow-toggle-btn" data-label-id="{{ $product->hash_id }}">
              <svg class="pos-arrow-svg" viewBox="0 0 120 150" width="110" height="135" xmlns="http://www.w3.org/2000/svg">
                <polygon class="arrow-poly" points="60,5 120,70 88,70 88,145 32,145 32,70 0,70" fill="#000"/>
              </svg>
            </button>
          </div>

          <div class="pos-table-bottom">
            <div class="pos-table-headers">
              <div class="pos-table-th">FRONT</div>
              <div class="pos-table-th">MIDDLE</div>
              <div class="pos-table-th">REAR</div>
            </div>
            <div class="pos-table-values">
              <div class="pos-table-td"></div>
              <div class="pos-table-td"></div>
              <div class="pos-table-td"></div>
            </div>
          </div>
        </div>

      </section>

      <!-- Design 2: A6 Portrait -->
      <section class="label-d2 {{ $loop->last ? 'last' : '' }}" id="label-d2-{{ $product->hash_id }}" style="display: none;">
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
                      $dimValues2 = explode(' x ', $product->dimension);
                      $dimLabelsRaw2 = str_replace(['(', ')'], '', $product->dimension_label);
                      $dimLabels2 = explode(' x ', $dimLabelsRaw2);
                      $dimFormatted2 = [];
                      foreach($dimValues2 as $i => $v) {
                          $l = isset($dimLabels2[$i]) ? trim($dimLabels2[$i]) : '';
                          // Bold specific headers for precision look
                          $dimFormatted2[] = ($l ? "<span class='dim-unit'>$l:</span>" : "") . trim($v);
                      }
                  @endphp
                  {!! implode(' &nbsp; ', $dimFormatted2) !!}
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
            @if($product->coating_type)
            <tr style="background: #000; color: #fff;">
              <td class="titleDetail" style="color: #fff; font-weight: bold; padding: 6px 8px;">Coating</td>
              <td class="colon" style="color: #fff; padding: 6px 8px;">:</td>
              <td class="valueContent" style="font-weight: bold; padding: 6px 8px; text-transform: uppercase;">
                {{ $product->coating_type }}
              </td>
            </tr>
            @endif
          </table>
        </div>
      </section>
    @endforeach
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput       = document.getElementById('panelSearch');
      const selectAllChk      = document.getElementById('panelSelectAll');
      const itemCheckboxes    = document.querySelectorAll('.panel-item-check');
      const printBtn          = document.getElementById('btnPrintNow');
      const selectedCountSpan = document.getElementById('selectedCount');
      const templateSelect    = document.getElementById('templateSelect');
      const printModeSelect   = document.getElementById('printModeSelect');
      const printModeContainer = document.getElementById('printModeContainer');
      const dynamicPageLayout = document.getElementById('dynamic-page-layout');

      const COOKIE_PREFIX = 'lbl_arrow_';
      const COOKIE_DAYS   = 30;

      function setCookie(name, value, days) {
        const d = new Date();
        d.setTime(d.getTime() + days * 86400000);
        document.cookie = `${name}=${value};expires=${d.toUTCString()};path=/;SameSite=Lax`;
      }

      function getCookie(name) {
        const prefix = name + '=';
        for (const part of document.cookie.split(';')) {
          const c = part.trim();
          if (c.startsWith(prefix)) return c.substring(prefix.length);
        }
        return null;
      }

      const ARROW_UP_PTS   = '60,5 120,70 88,70 88,145 32,145 32,70 0,70';
      const ARROW_DOWN_PTS = '60,145 0,75 32,75 32,5 88,5 88,75 120,75';

      function applyArrowDir(btn, dir) {
        const poly = btn.querySelector('.arrow-poly');
        if (!poly) return;
        poly.setAttribute('points', dir === 'down' ? ARROW_DOWN_PTS : ARROW_UP_PTS);
        btn.setAttribute('data-dir', dir);
      }

      document.querySelectorAll('.arrow-toggle-btn').forEach(btn => {
        const id  = btn.getAttribute('data-label-id');
        const dir = getCookie(COOKIE_PREFIX + id) || 'up';
        applyArrowDir(btn, dir);

        btn.addEventListener('click', function() {
          const current = this.getAttribute('data-dir') || 'up';
          const next    = current === 'up' ? 'down' : 'up';
          applyArrowDir(this, next);
          setCookie(COOKIE_PREFIX + id, next, COOKIE_DAYS);
        });
      });

      function updateLabelVisibility() {
        let selectedCount = 0;
        const activeTemplate = templateSelect.value;
        const activePrintMode = printModeSelect ? printModeSelect.value : 'single';

        if (activeTemplate === '1') {
          printModeContainer.style.display = 'block';
          if (activePrintMode === 'multi') {
            document.body.classList.add('print-multi');
          } else {
            document.body.classList.remove('print-multi');
          }
        } else {
          printModeContainer.style.display = 'none';
          document.body.classList.remove('print-multi');
        }

        itemCheckboxes.forEach(chk => {
          const id = chk.getAttribute('data-id');
          const labelCardD1 = document.getElementById('label-d1-' + id);
          const labelCardD2 = document.getElementById('label-d2-' + id);

          // Hide both first
          if (labelCardD1) labelCardD1.style.display = 'none';
          if (labelCardD2) labelCardD2.style.display = 'none';

          if (chk.checked) {
            selectedCount++;
            if (activeTemplate === '1') {
              if (labelCardD1) labelCardD1.style.display = 'flex';
            } else {
              if (labelCardD2) labelCardD2.style.display = 'flex';
            }
          }
        });
        selectedCountSpan.innerText = selectedCount;
        selectAllChk.checked = (selectedCount === itemCheckboxes.length);

        // Update page break class correctly: only the last VISIBLE label of the active template should have class 'last'
        const labelClass = activeTemplate === '1' ? '.label-d1' : '.label-d2';
        document.querySelectorAll('.label-d1, .label-d2').forEach(l => l.classList.remove('last'));
        const visibleLabels = Array.from(document.querySelectorAll(labelClass)).filter(l => l.style.display !== 'none');
        if (visibleLabels.length > 0) {
          visibleLabels[visibleLabels.length - 1].classList.add('last');
        }
      }

      itemCheckboxes.forEach(chk => chk.addEventListener('change', updateLabelVisibility));
      
      if(searchInput) {
        searchInput.addEventListener('input', function() {
          const query = this.value.toLowerCase().trim();
          let visibleCount = 0;
          let checkedVisibleCount = 0;

          document.querySelectorAll('.panel-item').forEach(item => {
            const searchStr = item.getAttribute('data-search');
            const chk = item.querySelector('.panel-item-check');
            if (searchStr.includes(query)) {
              item.style.display = 'flex';
              visibleCount++;
              if (chk.checked) {
                checkedVisibleCount++;
              }
            } else {
              item.style.display = 'none';
            }
          });
          selectAllChk.checked = visibleCount > 0 && checkedVisibleCount === visibleCount;
        });
      }

      selectAllChk.addEventListener('change', function() {
        const isChecked = this.checked;
        const visibleItems = Array.from(document.querySelectorAll('.panel-item')).filter(item => item.style.display !== 'none');
        visibleItems.forEach(item => {
          item.querySelector('.panel-item-check').checked = isChecked;
        });
        updateLabelVisibility();
      });

      printBtn.addEventListener('click', () => window.print());

      function scaleLabels() {
        const container = document.querySelector('.print-preview-container');
        if (!container) return;
        const containerWidth = container.clientWidth - 48;
        const activeTemplate = templateSelect.value;

        // Reset all sizes & styles first
        document.querySelectorAll('.label-d1, .label-d2').forEach(label => {
          label.style.width = '';
          label.style.height = '';
          label.style.transform = '';
          label.style.marginBottom = '';
          label.style.marginRight = '';
        });

        if (activeTemplate === '1') {
          container.style.alignItems = 'flex-start';
          const labelWidthPx   = 45 * 37.7953;
          const labelHeightPx  = 7  * 37.7953;
          const scale = Math.min(1, containerWidth / labelWidthPx);

          document.querySelectorAll('.label-d1').forEach(label => {
            label.style.width         = labelWidthPx + 'px';
            label.style.height        = labelHeightPx + 'px';
            label.style.transform     = `scale(${scale})`;
            label.style.marginBottom  = ((labelHeightPx * scale) - labelHeightPx) + 'px';
            label.style.marginRight   = ((labelWidthPx * scale) - labelWidthPx) + 'px';
          });
        } else {
          container.style.alignItems = 'center';
          const labelWidthPx   = 4.13 * 96;
          const labelHeightPx  = 5.83 * 96;
          const scale = Math.min(1, containerWidth / labelWidthPx);

          document.querySelectorAll('.label-d2').forEach(label => {
            label.style.width         = labelWidthPx + 'px';
            label.style.height        = labelHeightPx + 'px';
            label.style.transform     = `scale(${scale})`;
            label.style.marginBottom  = ((labelHeightPx * scale) - labelHeightPx) + 'px';
            label.style.marginRight   = ((labelWidthPx * scale) - labelWidthPx) + 'px';
          });
        }
      }

      function updatePageSize() {
        const activeTemplate = templateSelect.value;
        const activePrintMode = printModeSelect ? printModeSelect.value : 'single';
        if (activeTemplate === '1') {
          if (activePrintMode === 'multi') {
            dynamicPageLayout.innerHTML = `@page { size: auto; margin: 10mm; }`;
          } else {
            dynamicPageLayout.innerHTML = `@page { size: 45cm 7cm landscape; margin: 3mm; }`;
          }
        } else {
          dynamicPageLayout.innerHTML = `@page { size: A6 portrait; margin: 0; }`;
        }
      }

      templateSelect.addEventListener('change', function() {
        updatePageSize();
        updateLabelVisibility();
        scaleLabels();
      });

      if (printModeSelect) {
        printModeSelect.addEventListener('change', function() {
          updatePageSize();
          updateLabelVisibility();
          scaleLabels();
        });
      }

      // Initial runs
      updatePageSize();
      updateLabelVisibility();
      scaleLabels();
      
      window.addEventListener('resize', scaleLabels);
    });
  </script>
</body>
</html>