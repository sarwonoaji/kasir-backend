<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Barang Masuk</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background: #eee; }
        .no-border td { border: none; }
    </style>
</head>
<body>

<h2>BUKTI BARANG MASUK</h2>

<table class="no-border">
    <tr>
        <td width="20%">No Transaksi</td>
        <td>: {{ $data->no_transaksi }}</td>
    </tr>
    <tr>
        <td>Tanggal</td>
        <td>: {{ $data->date }}</td>
    </tr>
    <tr>
        <td>Catatan</td>
        <td>: {{ $data->remark ?? '-' }}</td>
    </tr>
</table>

<br>

<table>
    <thead>
        <tr>
            <th width="5%">No</th>
            <th>Produk</th>
            <th width="10%">Qty</th>
            <th width="15%">Harga</th>
            <th width="20%">Total</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalQty = 0;
            $grandTotal = 0;
        @endphp

        @foreach ($data->details as $i => $d)
            @php
                $totalQty += $d->quantity;
                $grandTotal += $d->total_price;
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $d->product->name ?? '-' }}</td>
                <td align="center">{{ $d->quantity }}</td>
                <td align="right">{{ number_format($d->price) }}</td>
                <td align="right">{{ number_format($d->total_price) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2">TOTAL</th>
            <th>{{ $totalQty }}</th>
            <th></th>
            <th align="right">{{ number_format($grandTotal) }}</th>
        </tr>
    </tfoot>
</table>

<br><br>

<table class="no-border">
    <tr>
        <td width="60%"></td>
        <td align="center">
            Dicetak tanggal {{ now()->format('d-m-Y') }}<br><br><br>
            _______________________<br>
            Petugas
        </td>
    </tr>
</table>

</body>
</html>
