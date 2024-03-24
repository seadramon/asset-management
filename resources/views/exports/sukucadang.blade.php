@foreach($data as $header)
    <table>
        <tr>
            <td>Nama</td>
            <td>{{ $header->nama }}</td>
        </tr>
        <tr>
            <td>Bagian</td>
            <td>{{ $header->bagian->name }}</td>
        </tr>
        <tr>
            <td>SPV</td>
            <td>{{ $header->namaSpv->nama }}</td>
        </tr>
        <tr>
            <td>Keterangan</td>
            <td>{{ $header->keterangan }}</td>
        </tr>
    </table>
    <br>
    <table border="1px">
        <thead>
            <tr>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Keterangan</th>
                <th>Gudang</th>
                <th>Dibeli Oleh</th>
            </tr>
        </thead>
        <tbody>
            @foreach($header->detail as $row)
                <tr>
                    <td>{{ $row->barang->nama }}</td>
                    <td>{{ $row->jumlah }}</td>
                    <td>{{ $row->keterangan }}</td>
                    <td>{{ $row->dibeli_by }}</td>    
                </tr>
            @endforeach
        </tbody>
    </table>
    <br>
    <br>
@endforeach