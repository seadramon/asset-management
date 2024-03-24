@extends('layouts.main')

@section('title', 'Penugasan Perbaikan Dari Aduan - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Penugasan Perbaikan Dari Aduan')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Penugasan Perbaikan Dari Aduan Form</h5>
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
                    {!! Form::model($data, ['route' => ['perbaikan::penugasanAduan-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('perbaikan::penugasanAduan-simpan'), 'class' => 'form-horizontal']) !!}
                @endif
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="text" class="hidden" name="isedit" value="{{($data=='')?'0':'1'}}">
                        <input type="hidden" name="tipe" value="aduan">
                        <input type="hidden" name="tanggal" value="{{ date('Y-m-d') }}">
                        <input type="hidden" name="status" value="0">

                        {!! Form::hidden("aduan_id", $aduan_id, ['class'=>'form-control', 'id'=>'id']) !!}
                        {!! Form::hidden("instalasi_id", $aset->instalasi_id, ['class'=>'form-control', 'id'=>'instalasi_id']) !!}
                        {!! Form::hidden("komponen_id", $aset->id, ['class'=>'form-control', 'id'=>'komponen_id']) !!}
                        {!! Form::hidden("bagian_id", $aset->bagian, ['class'=>'form-control', 'id'=>'bagian_id']) !!}

                        <div class="form-group row">
                                <label class="col-form-label col-lg-2">Judul Kerusakan</label>
                                <div class="col-lg-10">
                                    <div class="form-control-plaintext">{{ $keluhan->judul }}</div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Indikasi Kerusakan</label>
                                <div class="col-lg-10">
                                    <div class="form-control-plaintext">{!! $keluhan->ind_kerusakan !!}</div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Catatan Kerusakan</label>
                                <div class="col-lg-10">
                                    <div class="form-control-plaintext">{!! $keluhan->catatankerusakan !!}</div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Foto Kondisi</label>
                                <div class="col-lg-10">
                                    @if (!empty($keluhan->path_kondisi))
                                        <img src="{{ url('pic-api/gambar/aduan&'.$period.'&'.$keluhan->path_kondisi) }}" width="300px" height="300px">
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Foto Kerusakan</label>
                                <div class="col-lg-10">
                                    @if (!empty($keluhan->path_kerusakan))
                                        <img src="{{ url('pic-api/gambar/aduan&'.$keluhan->path_kerusakan) }}" width="300px" height="300px">
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Foto Lokasi</label>
                                <div class="col-lg-10">
                                    @if (!empty($keluhan->path_lokasi))
                                        <img src="{{ env('PEMELIHARAAN_PATH').$keluhan->path_lokasi }}" width="300px" height="300px">
                                    @endif
                                </div>
                            </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Petugas</label>
                            <div class="col-lg-10">
                                {!! Form::select('petugas_id', $petugas, null, ['class'=>'form-control select2', 'id'=>'petugas', 'required']) !!}
                            </div>
                        </div>

                        <div id="beforepart"></div>

                	</fieldset>

                	<div class="text-right">
						<button type="submit" class="btn btn-primary legitRipple">Simpan</button>
                        <a href="{{route('perbaikan::perbaikan-index')}}">
                            <button type="button" class="btn btn-light legitRipple">Kembali</button></a>
					</div>
                {!! Form::close() !!}
                <!-- END FORM-->
            </div>
        </div>
    <!-- /form inputs -->
@endsection
@section('js')
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>
<script>
$(document).ready(function () {
    $(".select2").select2();

    $('#instalasi').change(function () {
        $('#aset').empty();
        $('#aset').append('<option value="">Pilih Aset</option>');

        $('#petugas').empty();

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('perbaikan/lokasiSelect')}}/" + $(this).val(),
                success: function(result) {
                    // $('#aset').append(result.data);
                    $('#aset').append(result.template);
                    $('#petugas').append(result.petugas);
                }
            })
        }
    });

    /*$('#aset').change(function () {
        $('#petugas').empty();
        $('#petugas').append('<option value="">Pilih Petugas</option>');

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('perbaikan/asetSelect')}}/" + $(this).val(),
                success: function(result) {
                    // $('#aset').append(result.data);
                    $('#petugas').append(result.petugas);
                }
            })
        }
    });*/
});
</script>
@endsection