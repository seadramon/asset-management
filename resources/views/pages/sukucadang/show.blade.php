<br>
<div class="row">
    <h3>Sukucadang</h3>
    <table class="table" id="tabelSc">
        <thead>
            <tr>                                    
                <th>Suku Cadang</th>
                <th>Jumlah</th>
                <th>Dibeli Oleh</th>
                <th>Keterangan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php //dd($dataSc); ?>
            @if (count($dataSc) > 0)
                @foreach($dataSc as $rowSc)
                    <tr>                                    
                        <td>
                            @if (!empty($rowSc->kode_alias))
                                @if ( in_array($rowSc->kode_alias, $keyPairKodeAlias) )
                                    {{ $pairKodeAlias[$rowSc->kode_alias] }}
                                @else
                                    <p style="color: red;">{{ "Kode alias tidak ditemukan" }}</p>
                                @endif
                            @endif
                        </td>
                        <td>{{ $rowSc->jumlah }}</td>
                        <td>{{$rowSc->dibeli_by}}</td>
                        <td>{{ $rowSc->keterangan }}</td>
                        <td>{{ $rowSc->status }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5">Kosong</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
<br><br>
<div class="row">
    <h3>Sukucadang Waiting List</h3>
    <table class="table" id="tabelSc">
        <thead>
            <tr>                                    
                <th>Suku Cadang</th>
                <th>Jumlah</th>
                <th>Dibeli Oleh</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php //dd($dataSc); ?>
            @if (count($dataScWait) > 0)
                @foreach($dataScWait as $rowSc)
                    <tr>                                    
                        <td>
                            @if (!empty($rowSc->kode_alias))
                                @if ( in_array($rowSc->kode_alias, $keyPairKodeAlias) )
                                    {{ $pairKodeAlias[$rowSc->kode_alias] }}
                                @else
                                    <p style="color: red;">{{ "Kode alias tidak ditemukan" }}</p>
                                @endif
                            @endif
                        </td>
                        <td>{{ $rowSc->jumlah }}</td>
                        <td>{{$rowSc->dibeli_by}}</td>
                        <td>{{ $rowSc->keterangan }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5">Kosong</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
<br><br>