<!DOCTYPE html>
<html>
<head>
    <title>Laporan Transaksi</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        table tr td, table tr th {
            font-size: 9pt;
        }
    </style>
</head>
<body>
    <center>
        <h5>Laporan Transaksi</h5>
    </center>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Date</th>
                <th>Id Transaksi</th>
                <th>Nama</th>
                <th>Alamat</th>
                <th>Nilai Trx</th>
                <th>Status</th>
                <th>ID</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaksi as $x => $item)
                <tr>
                    <td>{{ ++$x }}</td>
                    <td>{{ $item->created_at }}</td>
                    <td>{{ $item->code_transaksi }}</td>
                    <td>{{ $item->nama_customer }}</td>
                    <td>{{ $item->alamat }}</td>
                    <td>{{ $item->total_harga }}</td>
                    <td>
                        <span class="{{ $item->status === 'Paid' ? 'badge bg-success text-white' : 'badge bg-danger text-white' }}">
                            {{ $item->status }}
                        </span>
                    </td>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->jumlah }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
