<h5>Biaya Penghapusan</h5>

{!! Form::model($aset, ['route' => ['lcca::penghapusan-simpan'], 'class' => 'form-horizontal']) !!}
    {!! Form::hidden('id', null) !!}
    <table class="table table-bordered" id="tabel-penghapusan" style="width: 100%;">
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
                <td colspan="4"><b>Biaya Penghapusan</b></td>
            </tr>
            <tr>
                <td valign="top">Biaya Pembongkaran</td>
                <td>
                    {!! Form::number('penghapusan_biaya', null, ['class'=>'form-control', 'id'=>'penghapusan_biaya', 'required', 'rows' => '3']) !!}
                </td>
                <td>
                    {!! Form::textarea('penghapusan_spk', null, ['class'=>'form-control', 'id'=>'penghapusan_spk', 'required', 'rows' => '3']) !!}
                </td>
                <td>
                    {!! Form::textarea('penghapusan_berita_acara', null, ['class'=>'form-control', 'id'=>'penghapusan_berita_acara', 'required', 'rows' => '3']) !!}
                </td>
            </tr>
        </tbody>
    </table><br>

    <button id="akuisisi-btn" class="btn btn-primary legitRipple">Submit</button>
{!! Form::close() !!}