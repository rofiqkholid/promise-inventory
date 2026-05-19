<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Tool QR Label - {{ $tool->name }}</title>
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
    border: 2px solid #000;
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
    background: #000;
    color: #fff;
    border-bottom: 2px solid #000;
    letter-spacing: 1px;
  }

  .details table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13.5px;
    margin-top: 5px;
  }
  .details td {
    padding: 5px 10px;
    vertical-align: top;
    line-height: 1.3;
    border-bottom: 1px solid #eee;
  }
  .details .titleDetail {
    width: 95px;
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

  .moving-badge {
    background: #3b82f6;
    color: #fff;
    padding: 2px 6px;
    font-weight: bold;
    font-size: 11px;
    border-radius: 3px;
    display: inline-block;
    letter-spacing: 0.5px;
  }

  @media print {
    .no-print {
      display: none !important;
    }
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

  .download-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(30, 41, 59, 0.95);
    color: #fff;
    border: none;
    padding: 6px 10px;
    font-size: 10px;
    font-weight: bold;
    border-radius: 4px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    z-index: 50;
    transition: all 0.2s;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    text-decoration: none;
    font-family: sans-serif;
  }
  .download-btn:hover {
    background: #0f172a;
    transform: scale(1.05);
  }
  .download-btn:active {
    transform: scale(0.95);
  }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <section class="label last">
    <button class="no-print download-btn" onclick="downloadSVG('QR-Tool-{{ str_replace(' ', '-', strtolower($tool->name)) }}.svg')">
      <i class="fa-solid fa-download"></i> Download SVG
    </button>
    <div class="qr-wrap">
      {!! $qrCode !!}
    </div>

    <div class="details">
      <div class="title">TOOL IDENTIFICATION</div>
      <table>
        <tr>
          <td class="titleDetail">Tool Name</td>
          <td class="colon">:</td>
          <td class="valueContent"><b>{{ $tool->name }}</b></td>
        </tr>
        <tr>
          <td class="titleDetail">Brand</td>
          <td class="colon">:</td>
          <td class="valueContent">{{ $tool->brand }}</td>
        </tr>
        <tr>
          <td class="titleDetail">Spec Code</td>
          <td class="colon">:</td>
          <td class="valueContent">
            @if($tool->spec_code)
              <span style="font-family: monospace; font-weight: bold;">{{ $tool->spec_code }}</span>
            @else
              -
            @endif
          </td>
        </tr>
        <tr>
          <td class="titleDetail">Dimension</td>
          <td class="colon">:</td>
          <td class="valueContent">{{ $tool->dimension ?? '-' }} @if($tool->length) x {{ $tool->length }}@endif</td>
        </tr>
        <tr>
          <td class="titleDetail">Category</td>
          <td class="colon">:</td>
          <td class="valueContent">{{ $tool->category?->name ?? '-' }}</td>
        </tr>
        <tr>
          <td class="titleDetail">Moving Type</td>
          <td class="colon">:</td>
          <td class="valueContent">
            <span class="moving-badge">FAST MOVING</span>
          </td>
        </tr>
      </table>
    </div>
  </section>

  <script>
    function downloadSVG(filename) {
      const svgEl = document.querySelector('.qr-wrap svg');
      if (!svgEl) return;
      const serializer = new XMLSerializer();
      let source = serializer.serializeToString(svgEl);
      if(!source.match(/^<svg[^>]+xmlns="http\:\/\/www\.w3\.org\/2000\/svg"/)){
        source = source.replace(/^<svg/, '<svg xmlns="http://www.w3.org/2000/svg"');
      }
      if(!source.match(/^<svg[^>]+xmlns\:xlink="http\:\/\/www\.w3\.org\/1999\/xlink"/)){
        source = source.replace(/^<svg/, '<svg xmlns:xlink="http://www.w3.org/1999/xlink"');
      }
      source = '<?xml version="1.0" encoding="utf-8"?>\n' + source;
      const url = "data:image/svg+xml;charset=utf-8," + encodeURIComponent(source);
      const link = document.createElement("a");
      link.href = url;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  </script>
</body>
</html>
