<!DOCTYPE html>
<html>
<head>
    <title>Laporan Transaksi Kasir</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            background-color: #007bff;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .summary {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        .summary p {
            margin: 5px 0;
            font-size: 14px;
        }
        .summary strong {
            color: #007bff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #e9ecef;
        }
        .detail-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .detail-list li {
            margin-bottom: 5px;
            font-size: 11px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #6c757d;
        }
        .total-row {
            background-color: #28a745;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Transaksi Kasir</h1>
        <p>Dibuat pada: {{ now()->format('d-m-Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <p><strong>Tanggal Mulai:</strong> {{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }}</p>
        <p><strong>Tanggal Akhir:</strong> {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }}</p>
        @if($shift_id)
            <p><strong>Shift ID:</strong> {{ $shift_id }}</p>
        @endif
        <p><strong>Total Transaksi:</strong> {{ $total_transactions }}</p>
        <p><strong>Total Penjualan:</strong> Rp {{ number_format($total_sales, 0, ',', '.') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Kasir</th>
                <th>Total</th>
                <th>Detail Produk</th>
                @if(isset($transactions[0]) && $transactions[0]->user)
                    <th>User</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') }}</td>
                    <td>{{ $transaction->customer_name }}</td>
                    <td>{{ $transaction->casher }}</td>
                    <td>Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
                    <td>
                        <ul class="detail-list">
                            @foreach($transaction->details as $detail)
                                <li>{{ $detail->product->name }}<br>
                                {{ $detail->quantity }} x Rp {{ number_format($detail->price, 0, ',', '.') }} = Rp {{ number_format($detail->total_price, 0, ',', '.') }}</li>
                            @endforeach
                        </ul>
                    </td>
                    @if(isset($transaction->user))
                        <td>{{ $transaction->user->name }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dihasilkan secara otomatis oleh sistem kasir.</p>
    </div>
</body>
</html>