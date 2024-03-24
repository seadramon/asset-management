<h5>Akusisi</h5>

{!! Form::model($aset, ['route' => ['lcca::akuisisi-simpan'], 'class' => 'form-horizontal']) !!}
    {!! Form::hidden('id', null) !!}
    <table class="table table-bordered" id="tabel-akuisisi" style="width: 100%;">
        <thead>
            <tr>                                    
                <th>Elemen Biaya</th>
                <th>Biaya</th>
                <th>Dokumen SPK</th>
                <th>Dokumen Berita Acara Hasil Pekerjaan (Penjamin Kualitas)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="4"><b>Biaya Pengadaan</b></td>
            </tr>
            <tr>
                <td valign="top">Nilai Perolehan</td>
                <td>
                    <label>
                        {!! !empty($aset->harga)?number_format($aset->harga,0,",","."):"0" !!}
                    </label>
                    {!! Form::hidden('harga', null, ['class'=>'form-control', 'id'=>'harga', 'required']) !!}
                </td>
                <td>
                    {!! Form::textarea('akuisisi_spk', null, ['class'=>'form-control', 'id'=>'akuisisi_spk', 'required', 'rows' => '3']) !!}
                </td>
                <td>
                    {!! Form::textarea('akuisisi_berita_acara', null, ['class'=>'form-control', 'id'=>'akuisisi_berita_acara', 'required', 'rows' => '3']) !!}
                </td>
            </tr>
        </tbody>
    </table><br>

    <button id="akuisisi-btn" class="btn btn-primary legitRipple">Submit</button>
{!! Form::close() !!}