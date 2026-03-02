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
    gap: 0.10in;
    page-break-after: always;
  }
  .label.last {
    page-break-after: auto;
  }

  .qr-wrap {
    flex: 0 0 45%;
    display: flex;
    justify-content: center;
    align-items: center;
    /* padding: 0.1in; */
  }
  .qr-wrap svg,
  .qr-wrap img,
  .qr-wrap canvas,
  .qr-wrap div {
    width: 90% !important;
    height: 90% !important;
    object-fit: contain;
  }

  .details {
    flex: 1;
    border: 1px solid #000;
    padding: 0.08in;
    display: flex;
    flex-direction: column;
    gap: 0.06in;
  }

  .details .title {
    text-align: center;
    font-weight: bold;
    font-size: 18px;
    padding-bottom: 0.06in;
    border-bottom: 1px solid #000;
  }

  .details table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
  }
  .details td {
    padding: 3px 4px;
    vertical-align: top;
    line-height: 1.2;
  }
  .details .titleDetail {
    width: 25%;
    font-weight: bold;
  }

  /* Dimension info styling */
  .dim-info {
    font-size: 9px;
    color: #666;
    font-weight: normal;
    display: block;
    margin-top: 2px;
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
      <div class="qr-wrap">
        {!! $product->qrcode !!}
      </div>
      <div class="details">
        <div class="title">PRODUCT IDENTIFICATION</div>
        <table>
          <tr>
            <td class="titleDetail">Part No</td>
            <td>:</td>
            <td><b>{{ $product->item_no }}</b></td>
          </tr>
          <tr>
            <td class="titleDetail">Part Name</td>
            <td>:</td>
            <td>{{ $product->item_name }}</td>
          </tr>
          <tr>
            <td class="titleDetail">Model</td>
            <td>:</td>
            <td>{{ $product->model_name }}</td>
          </tr>
          <tr>
            <td class="titleDetail">Customer</td>
            <td>:</td>
            <td>{{ $product->partner_code }}</td>
          </tr>
          <tr>
            <td class="titleDetail">Dimension</td>
            <td>:</td>
            <td>
                {{ $product->dimension }}
                <span class="dim-info">{{ $product->dimension_label }}</span>
            </td>
          </tr>
          <tr>
            <td class="titleDetail">Material</td>
            <td>:</td>
            <td>{{ $product->material }}</td>
          </tr>
        </table>
      </div>
    </section>
  @endforeach

</body>
</html>
