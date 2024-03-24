@extends('layouts.main')

@section('title', 'View Usulan - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'View Usulan')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">

            <div class="card-header header-elements-inline">
                <h5 class="card-title">View Usulan</h5>
            </div>

            <div class="card-body">
                @if (Session::has('error'))
                    <div class="alert alert-danger alert-styled-right alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        {{ Session::get('error', 'Error') }}
                    </div>
                @endif
                @if (Session::has('success'))
                    <div class="alert alert-success alert-styled-right alert-arrow-right alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        {{ Session::get('success', 'Success') }}
                    </div>
                @endif
                <!-- BEGIN FORM-->
                @if ($data!='')
                    {!! Form::model($data, ['route' => ['perbaikan::perbaikan-metode-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('perbaikan::perbaikan-metode-simpan'), 'class' => 'form-horizontal']) !!}
                @endif
                    <fieldset class="mb-3">
                        <input type="text" class="hidden" name="_token" value="{{csrf_token()}}">
                        
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Judul</label>
                            <div class="col-lg-10">
                                <div class="form-control-plaintext">{{ $data->nama }}</div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Rencana Penggunaan Anggaran</label>
                            <div class="col-lg-10"><?php  ?>
                                <div class="form-control-plaintext">{{ !empty($data->penggunaan_anggaran)?strtoupper($data->penggunaan_anggaran):"" }}</div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Tujuan</label>
                            <div class="col-lg-10">
                                <div class="form-control-plaintext">{!! $data->tujuan !!}</div>
                            </div>
                        </div>

                        @if (!empty($data->aset_id))
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Nama Aset</label>
                                <div class="col-lg-10">
                                    <div class="form-control-plaintext">{!! $data->aset->nama_aset.' ( '.$data->aset->kode_aset.' )' !!}</div>
                                </div>
                            </div>
                        @endif

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Instalasi</label>
                            <div class="col-lg-10">
                                <div class="form-control-plaintext">{!! $data->instalasi->name !!}</div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Lokasi</label>
                            <div class="col-lg-10">
                                <div class="form-control-plaintext">{!! $data->lokasi !!}</div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">SPV</label>
                            <div class="col-lg-10">
                                <div class="form-control-plaintext">{!! $data->jabatan->namajabatan !!}</div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Keterangan</label>
                            <div class="col-lg-10">
                                <div class="form-control-plaintext">{!! $data->keterangan !!}</div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Foto Kondisi</label>
                            <div class="col-lg-10">
                                @if (!empty($data->foto_kondisi))
                                    <img src="{{ url('pic-api/gambar/non-operasi&usulan&'.$data->id.'&'.$data->foto_kondisi) }}" width="300px" height="300px">
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Foto Investigasi</label>
                            <div class="col-lg-10">
                                @if (!empty($data->foto_investigasi))
                                    <img src="{{ url('pic-api/gambar/non-operasi&usulan&'.$data->id.'&'.$data->foto_investigasi) }}" width="300px" height="300px">
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Foto Investigasi 2</label>
                            <div class="col-lg-10">
                                @if (!empty($data->foto_investigasi2))
                                    <img src="{{ url('pic-api/gambar/non-operasi&usulan&'.$data->id.'&'.$data->foto_investigasi2) }}" width="300px" height="300px">
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Hasil Investigasi</label>
                            <label class="col-form-label col-lg-10">{!! $data->hasil_investigasi !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Petugas</label>
                            <div class="col-lg-10">
                                <div class="form-control-plaintext">{!! !empty($data->petugas)?$data->petugas->nama:"" !!}</div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Target Penyelesaian</label>
                            <label class="col-form-label col-lg-10">{!! $data->perkiraan !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Target Penyelesaian Revisi</label>
                            <label class="col-form-label col-lg-10">{!! $data->perkiraan_revisi !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Pelaksana Pekerjaan</label>
                            <label class="col-form-label col-lg-10">{!! $data->metode !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Sifat</label>
                            <div class="col-lg-10">
                                <div class="form-control-plaintext">{!! $data->sifat !!}</div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Material</label>
                            <label class="col-form-label col-lg-10">{!! $data->material !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Perencanaan</label>
                            <div class="col-lg-10">
                                @if (!empty($data->perencanaan))
                                    <?php
                                    $temp = explode(";", $data->perencanaan);
                                    if (is_array($temp)) {
                                        foreach ($temp as $row) {
                                            $arrTmp = pathinfo($row);
                                    ?>
                                            @if (in_array($arrTmp['extension'], ['pdf', 'doc', 'docx']))
                                                <a href="{{ url('pic-api/gambar/non-operasi&usulan&'.$data->id.'&'.$row) }}">{{ $row }}</a>
                                            @else
                                                <img src="{{ url('pic-api/gambar/non-operasi&usulan&'.$data->id.'&'.$row) }}" width="300px" height="300px">
                                            @endif
                                            <br>
                                    <?php
                                        }
                                    }
                                    ?>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Jenis Penanganan Pekerjaan</label>
                            <label class="col-form-label col-lg-10">{!! $data->jenis_penanganan !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Catatan Revisi Petugas</label>
                            <label class="col-form-label col-lg-10">{!! $data->petugas_catatan !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Catatan Revisi Manajer Pemeliharaan/Trandist</label>
                            <label class="col-form-label col-lg-10">{!! $data->catatan_rev_manajer !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Catatan Manajer PPP</label>
                            <label class="col-form-label col-lg-10">{!! $data->ms_ppp_catatan !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Catatan Revisi Manajer Dalpro</label>
                            <label class="col-form-label col-lg-10">{!! $data->dalpro_catatan !!}</label>
                        </div>

                        @if ($cekMasukProposal)
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Proposal</label>
                                <label class="col-form-label col-lg-10">
                                    <a href="{{ route('proposal::pekerjaan', ['wo' => 'usulan_id', 'id' => $data->id, 'report' => 'true']) }}" target="_blank">Lihat Proposal</a>
                                </label>
                            </div>
                        @endif

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Tahun Anggaran</label>
                            <label class="col-form-label col-lg-10">{!! $data->tahun_anggaran !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Perkiraan Anggaran</label>
                            <label class="col-form-label col-lg-10">{!! $data->perkiraan_anggaran !!}</label>
                        </div>
                    </fieldset>
                    {!! Form::close() !!}

                    <!-- Sukucadang -->
                    @include('pages.sukucadang.show')

                    @if (in_array(namaRole(), config('custom.pko'))  && in_array($data->status, config('custom.pko-statusdisplay')) )
                        {!! Form::model($data, ['route' => ['non-operasi::usulan-ded-simpan'], 'class' => 'form-horizontal']) !!}
                        {!! Form::hidden('id', null) !!}
                            <fieldset class="mb-3">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Status Proses DED</label>
                                    <div class="col-lg-10">
                                        {!! Form::select('status', $statusDed, null, ['class'=>'form-control select2', 'id'=>'status']) !!}
                                    </div>
                                </div>

                                <div class="form-group row d-none" id="tolakded">
                                    <label class="col-form-label col-lg-2">Catatan</label>
                                    <div class="col-lg-10">
                                        {!! Form::textarea("pko_catatan", null, ['class'=>'form-control', 'id'=>'pko_catatan']) !!}
                                    </div>
                                </div>
                            </fieldset>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary legitRipple">Simpan</button>
                                <a href="{{route('non-operasi::usulan-index')}}">
                                    <button type="button" class="btn btn-light legitRipple">Kembali</button></a>
                            </div>
                        {!! Form::close() !!}
                    @elseif ( (trim(\Auth::user()->userid) == trim(getMsPpp()->nip)) && $data->status == '1.3' )
                        <h3>Konfirmasi</h3>

                        {!! Form::model($data, ['route' => ['non-operasi::msppp-simpan'], 'class' => 'form-horizontal']) !!}
                        {!! Form::hidden('id', null) !!}
                        {!! Form::hidden('wo', 'usulan') !!}
                            <fieldset class="mb-3">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Konfirmasi</label>
                                    <div class="col-lg-10">
                                        {!! Form::select('status', $statusMsppp, null, ['class'=>'form-control select2', 'id'=>'statusMsppp']) !!}
                                    </div>
                                </div>

                                <div class="form-group row d-none" id="tolakmsppp">
                                    <label class="col-form-label col-lg-2">Catatan</label>
                                    <div class="col-lg-10">
                                        {!! Form::textarea("ms_ppp_catatan", null, ['class'=>'form-control', 'id'=>'ms_ppp_catatan']) !!}
                                    </div>
                                </div>
                            </fieldset>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary legitRipple">Simpan</button>
                                <a href="{{route('non-operasi::usulan-index')}}">
                                    <button type="button" class="btn btn-light legitRipple">Kembali</button></a>
                            </div>
                        {!! Form::close() !!}
                    @else
                        <div class="text-right">
                            <a href="{{route('non-operasi::usulan-index')}}">
                                <button type="button" class="btn btn-light legitRipple">Kembali</button></a>
                        </div>
                    @endif
                <!-- END FORM-->
            </div>
        </div>
    <!-- /form inputs -->
@endsection
@section('js')
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>

<script src="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.js')}}"></script>
<link href="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.css')}}" rel="stylesheet"/>
<script>
$(document).ready(function () {
    $(".select2").select2();

    $(".datepicker").datepicker({
        format:'yyyy-mm-dd',
        formatSubmit: 'yyyy-mm-dd'
    });

    $("#status").on('change', function() {
        var status = $(this).val();

        if ($("#tolakded").hasClass('d-none') == false) {
            $("#tolakded").addClass('d-none');
        }

        if ($("#pko_catatan")[0].hasAttribute('required')) {
            $("#pko_catatan").prop('required',false);   
        }

        if (status == '99') {
            console.log('tolak');
            $("#tolakded").removeClass('d-none');

            $("#pko_catatan").prop('required',true);
        }
    });

    $("#statusMsppp").on('change', function() {
        var status = $(this).val();

        if ($("#tolakmsppp").hasClass('d-none') == false) {
            $("#tolakmsppp").addClass('d-none');
        }

        if ($("#ms_ppp_catatan")[0].hasAttribute('required')) {
            $("#ms_ppp_catatan").prop('required',false);   
        }

        if (status == '99' || status == '3.4') {
            console.log('tolak');
            $("#tolakmsppp").removeClass('d-none');

            $("#ms_ppp_catatan").prop('required',true);
        }
    });
});
</script>
@endsection