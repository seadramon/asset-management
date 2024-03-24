<!-- BEGIN FORM-->
@if ($data_proposal!='')
    {!! Form::model($data_proposal, ['route' => ['proposal::simpan'], 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data', 'id' => 'f_proposal']) !!}
    {!! Form::hidden('id', null) !!}
@else
    {!! Form::open(['url' => route('proposal::simpan'), 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data', 'id' => 'f_proposal']) !!}
@endif

{!! Form::hidden($f_key_proposal, $id) !!}
{!! Form::hidden('wo', $f_key_proposal) !!}
{!! Form::hidden('spv', $data->spv) !!}
{!! Form::hidden('nip_spv', $data->nip_spv) !!}
<div class="modal-body">

        <input type="text" class="hidden" name="_token" value="{{csrf_token()}}">
        <div class="form-group row">
            <label class="col-form-label col-sm-3">Nama Pekerjaan<span class="font-red">*</span></label>
            <div class="col-sm-9">
                {!! Form::textarea("nama", null, ['class'=>'form-control', 'rows' => '3', 'cols' => '3', 'id'=>'nama_proposal']) !!}
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-sm-3">Lokasi<span class="font-red">*</span></label>
            <div class="col-sm-9">
                {!! Form::textarea("lokasi", null, ['class'=>'form-control', 'rows' => '3', 'cols' => '3', 'id'=>'lokasi_proposal']) !!}
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-sm-3">Gambaran Umum<span class="font-red">*</span></label>
            <div class="col-sm-9">
                {!! Form::textarea("gambaran", null, ['class'=>'form-control', 'rows' => '3', 'cols' => '3', 'id'=>'gambaran_proposal']) !!}
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-sm-3">Kondisi Saat Ini<span class="font-red">*</span></label>
            <div class="col-sm-9">
                {!! Form::textarea("kondisi", null, ['class'=>'form-control', 'rows' => '3', 'cols' => '3', 'id'=>'kondisi_proposal']) !!}
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-sm-3">Manfaat Secara Teknis<span class="font-red">*</span></label>
            <div class="col-sm-9">
                {!! Form::textarea("manfaat_teknis", null, ['class'=>'form-control', 'rows' => '3', 'cols' => '3', 'id'=>'manfaat_teknis_proposal']) !!}
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-sm-3">Manfaat Secara Ekonomis<span class="font-red">*</span></label>
            <div class="col-sm-9">
                {!! Form::textarea("manfaat_ekonomis", null, ['class'=>'form-control', 'rows' => '3', 'cols' => '3', 'id'=>'manfaat_ekonomis_proposal']) !!}
            </div>
        </div>

        <legend class="text-uppercase font-size-sm font-weight-bold">Biaya</legend>

        <div class="form-group row">
            <label class="col-form-label col-sm-3">Nomor Perkiraan</label>
            <div class="col-sm-9">
                <?php /*<div class="form-control-plaintext">{{ $data->perkiraan_anggaran }}</div> */?>
                {!! Form::text("perkiraan_anggaran_proposal", null, ['class'=>'form-control', 'id'=>'perkiraan_anggaran_proposal', 'readonly']) !!}
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-sm-3">Tahun Anggaran</label>
            <div class="col-sm-9">
                <?php /*<div class="form-control-plaintext">{{ $data->tahun_anggaran }}</div> */?>
                {!! Form::text("tahun_anggaran_proposal", null, ['class'=>'form-control', 'id'=>'tahun_anggaran_proposal', 'readonly']) !!}
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-sm-3">Tanggal Pekerjaan Mulai<span class="font-red">*</span></label>
            <div class="col-sm-9">
                {!! Form::text("tgl_mulai", null, ['class'=>'form-control datepicker', 'id'=>'tgl_mulai_proposal']) !!}
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-sm-3">Tanggal Pekerjaan Selesai</label>
            <div class="col-sm-9">
                <?php /*
                @if (!empty($data->perkiraan))
                    <div class="form-control-plaintext">{{ changeDateFormat($data->perkiraan, 'Y-m-d H:i:s', 'Y-m-d') }}
                        {!! Form::hidden("perkiraan", changeDateFormat($data->perkiraan, 'Y-m-d H:i:s', 'Y-m-d'), ['class'=>'form-control', 'id'=>'perkiraan']) !!}
                    </div>
                @endif
                */?>
                {!! Form::text("perkiraan_proposal", null, ['class'=>'form-control', 'id'=>'perkiraan_proposal', 'readonly']) !!}
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-sm-3">Waktu</label>
            <div class="col-sm-7">
                {!! Form::text("waktu", null, ['class'=>'form-control', 'id'=>'waktu_proposal', 'readonly']) !!}
            </div>
            <label class="col-form-label col-sm-2">Hari</label>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-sm-3">Spesifikasi<span class="font-red">*</span></label>
            <div class="col-sm-9">
                {!! Form::text("spesifikasi", "seperti tersebut dalam PP", ['class'=>'form-control', 'id'=>'spesifikasi_proposal']) !!}
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-sm-3">Kesimpulan<span class="font-red">*</span></label>
            <div class="col-sm-9">
                {!! Form::textarea("kesimpulan", null, ['class'=>'form-control', 'rows' => '3', 'cols' => '3', 'id'=>'kesimpulan_proposal']) !!}
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-sm-3">Foto 1</label>
            <div class="col-sm-9">
                @if (!empty($data->foto_investigasi))
                    @if (in_array($f_key_proposal, ['usulan_id', 'aduan_non_op_id']))
                        <img src="{{ url('pic-api/gambar/non-operasi&'.$tipe.'&'.$data->id.'&'.$data->foto_investigasi) }}" width="300px" height="300px">
                    @else
                        <img src="{{ url('pic-api/gambar/perbaikan&'.$data->foto_investigasi) }}" width="300px" height="300px">
                    @endif
                @endif
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-sm-3">Foto 2<span class="font-red">*</span></label>
            <div class="col-sm-9">
                <?php /*
                @if (!empty($data->foto))
                    <img src="{{ url('pic-api/gambar/'.str_replace('/', '&', $data->foto)) }}" width="300px" height="300px">
                @endif
                */?>
                <div id="imgproposal"></div>
                {!! Form::file('foto', ['class' => 'form-control-uniform-custom']) !!}
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-sm-3">Deskripsi<span class="font-red">*</span></label>
            <div class="col-sm-9">
                {!! Form::textarea("deskripsi", null, ['class'=>'form-control', 'rows' => '3', 'cols' => '3', 'id'=>'deskripsi_proposal']) !!}
            </div>
        </div>
</div>

<div class="modal-footer">
    <!-- <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>     -->
    <button type="submit" id="btnProposal" class="btn btn-primary">Simpan</button>
</div>
{!! Form::close() !!}
<!-- END FORM-->      