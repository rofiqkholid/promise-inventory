<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>QR Labels</title>
<style>
  @page {
    size: A6 portrait;
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
      display: flex;
      background: #f3f4f6;
      padding: 0;
      margin: 0;
      height: 100vh;
      overflow: hidden;
    }
    
    /* Control Panel Sidebar */
    .print-panel {
      width: 340px;
      background: #ffffff;
      border-right: 1px solid #e5e7eb;
      padding: 24px;
      display: flex;
      flex-direction: column;
      height: 100vh;
      box-sizing: border-box;
      box-shadow: 2px 0 8px rgba(0,0,0,0.05);
      z-index: 50;
      flex-shrink: 0;
    }
    
    .print-panel h2 {
      margin: 0 0 4px 0;
      font-size: 16px;
      font-weight: bold;
      color: #1e293b;
    }
    .print-panel p {
      margin: 0;
      font-size: 11px;
      color: #64748b;
      line-height: 1.4;
    }
    .panel-header {
      margin-bottom: 16px;
    }
    .panel-search {
      margin-bottom: 12px;
    }
    .panel-search input {
      width: 100%;
      box-sizing: border-box;
      padding: 8px 12px;
      font-size: 12px;
      border: 1px solid #cbd5e1;
      border-radius: 4px;
      outline: none;
      transition: all 0.2s;
    }
    .panel-search input:focus {
      border-color: #6366f1;
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    .panel-select-all {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 0;
      border-bottom: 1px solid #e2e8f0;
      font-size: 11px;
      font-weight: bold;
      color: #475569;
    }
    .panel-list {
      list-style: none;
      padding: 0;
      margin: 0;
      flex: 1;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-top: 12px;
    }
    .panel-item {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      padding: 10px;
      border-radius: 4px;
      background: #f8fafc;
      border: 1px solid #f1f5f9;
      transition: all 0.2s;
    }
    .panel-item:hover {
      background: #f1f5f9;
    }
    .panel-item-info {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }
    .panel-item-info .part-no {
      font-size: 12px;
      font-weight: bold;
      color: #1e293b;
      word-break: break-all;
    }
    .panel-item-info .part-desc {
      font-size: 10px;
      color: #64748b;
      line-height: 1.3;
    }
    .panel-item-info .part-meta {
      font-size: 9px;
      font-weight: bold;
      color: #94a3b8;
      text-transform: uppercase;
      margin-top: 2px;
    }
    .panel-footer {
      margin-top: 16px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      border-top: 1px solid #e2e8f0;
      padding-top: 16px;
      flex-shrink: 0;
    }
    .selection-status {
      font-size: 11px;
      font-weight: bold;
      color: #475569;
    }
    .selection-status span {
      color: #6366f1;
    }
    #btnPrintNow {
      width: 100%;
      padding: 10px;
      background: #6366f1;
      color: #fff;
      border: none;
      border-radius: 4px;
      font-size: 12px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.2s;
    }
    #btnPrintNow:hover {
      background: #4f46e5;
    }

    /* Print Preview Container */
    .print-preview-container {
      flex: 1;
      height: 100vh;
      overflow-y: auto;
      padding: 32px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 24px;
      box-sizing: border-box;
    }

    .label {
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      border: 1px solid #e2e8f0;
      background: #fff;
      flex-shrink: 0;
    }
  }

  @media print {
    .print-panel {
      display: none !important;
    }
    .print-preview-container {
      margin: 0 !important;
      padding: 0 !important;
      display: block !important;
      height: auto !important;
      overflow: visible !important;
    }
    .label {
      box-shadow: none !important;
      border: none !important;
    }
  }
</style>
</head>
<body>

  <aside class="print-panel">
    <div class="panel-header">
      <h2>Print Labels</h2>
      <p>Select which items to include. Unchecked items will be excluded from the print result.</p>
    </div>
    
    <div class="panel-search">
      <input type="text" id="panelSearch" placeholder="Search part number, model...">
    </div>
    
    <div class="panel-select-all">
      <input type="checkbox" id="panelSelectAll" checked style="cursor: pointer;">
      <label for="panelSelectAll" style="cursor: pointer; select-none">Select All</label>
    </div>
    
    <ul class="panel-list">
      @foreach($products as $product)
        <li class="panel-item" data-id="{{ $product->hash_id }}" data-search="{{ strtolower($product->item_no . ' ' . $product->item_name . ' ' . $product->model_name . ' ' . $product->partner_code) }}">
          <input type="checkbox" class="panel-item-check" data-id="{{ $product->hash_id }}" checked style="cursor: pointer; margin-top: 3px;">
          <div class="panel-item-info">
            <span class="part-no">{{ $product->item_no }}</span>
            <span class="part-desc">{{ $product->item_name }}</span>
            <span class="part-meta">{{ $product->partner_code }} &nbsp;|&nbsp; {{ $product->model_name }}</span>
          </div>
        </li>
      @endforeach
    </ul>
    
    <div class="panel-footer">
      <div class="selection-status">
        Selected: <span id="selectedCount">{{ count($products) }}</span> / {{ count($products) }} item(s)
      </div>
      <button id="btnPrintNow">Print Selected</button>
    </div>
  </aside>

  <main class="print-preview-container">
    @foreach($products as $product)
      <section class="label {{ $loop->last ? 'last' : '' }}" id="label-{{ $product->hash_id }}">
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
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('panelSearch');
      const selectAllCheckbox = document.getElementById('panelSelectAll');
      const itemCheckboxes = document.querySelectorAll('.panel-item-check');
      const printBtn = document.getElementById('btnPrintNow');
      const selectedCountSpan = document.getElementById('selectedCount');
      
      function updateLabelVisibility() {
        let selectedCount = 0;
        itemCheckboxes.forEach(chk => {
          const id = chk.getAttribute('data-id');
          const labelCard = document.getElementById('label-' + id);
          if (chk.checked) {
            if (labelCard) labelCard.style.display = 'flex';
            selectedCount++;
          } else {
            if (labelCard) labelCard.style.display = 'none';
          }
        });
        
        selectedCountSpan.innerText = selectedCount;
        selectAllCheckbox.checked = selectedCount === itemCheckboxes.length;
        
        // Update page break class correctly: only the last VISIBLE label should have class 'last'
        const visibleLabels = Array.from(document.querySelectorAll('.label')).filter(l => l.style.display !== 'none');
        document.querySelectorAll('.label').forEach(l => l.classList.remove('last'));
        if (visibleLabels.length > 0) {
          visibleLabels[visibleLabels.length - 1].classList.add('last');
        }
      }

      itemCheckboxes.forEach(chk => {
        chk.addEventListener('change', updateLabelVisibility);
      });

      selectAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        const visibleItems = Array.from(document.querySelectorAll('.panel-item')).filter(item => item.style.display !== 'none');
        visibleItems.forEach(item => {
          item.querySelector('.panel-item-check').checked = isChecked;
        });
        updateLabelVisibility();
      });

      // Local Search Filtering
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

        selectAllCheckbox.checked = visibleCount > 0 && checkedVisibleCount === visibleCount;
      });

      printBtn.addEventListener('click', function() {
        window.print();
      });
      
      // Run initial setup
      updateLabelVisibility();
    });
  </script>

</body>
</html>
