@extends('layouts.main')

@section('title', 'Analisa Perbaikan Aduan - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Analisa Perbaikan Aduan')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Analisa Perbaikan Aduan Form</h5>
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
                    {!! Form::model($data, ['route' => ['perbaikan::penugasanAduanClose-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('perbaikan::penugasanAduanClose-simpan'), 'class' => 'form-horizontal']) !!}
                @endif
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">
                        {!! Form::hidden("aduan_id", null, ['class'=>'form-control', 'id'=>'aduan_id']) !!}
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Foto Investigasi :</label>
                            <div class="col-lg-10">
                                <img src="{{ url('pic-api/gambar/perbaikan&'.$data->foto_investigasi) }}" width="400px" height="400px">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Foto Investigasi 2 :</label>
                            <div class="col-lg-10">
                                @if (!empty($data->foto_investigasi2))
                                    <img src="{{ url('pic-api/gambar/perbaikan&'.$data->foto_investigasi2) }}" width="400px" height="400px">
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Penyebab :</label>
                            <label class="col-form-label col-lg-10">{!! $data->penyebab !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Kondisi :</label>
                            <label class="col-form-label col-lg-10">{!! $data->kondisi !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Perkiraan :</label>
                            <div class="col-lg-10">
                                <label class="col-form-label col-lg-10">{!! $data->perkiraan !!}</label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Metode :</label>
                            <div class="col-lg-10">
                                <label class="col-form-label col-lg-10">{!! $data->metode !!}</label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Uraian Penanganan</label>
                            <div class="col-lg-10">
                                {!! Form::textarea("uraian", null, ['class'=>'form-control', 'id'=>'uraian', 'readonly']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Foto</label>
                            <div class="col-lg-10">
                                <img src="{{ url('pic-api/gambar/perbaikan&'.$data->foto) }}" width="400px" height="400px">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Foto 2</label>
                            <div class="col-lg-10">
                                @if (!empty($data->foto_investigasi2))
                                    <img src="{{ url('pic-api/gambar/perbaikan&'.$data->foto2) }}" width="400px" height="400px">
                                @endif
                            </div>
                        </div>

                        <div id="beforepart"></div>

                	</fieldset>

                	<div class="text-right">
						<button type="submit" class="btn btn-primary legitRipple">Tutup Aduan</button>
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

<script src="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.js')}}"></script>
<link href="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.css')}}" rel="stylesheet"/>

<script>
$(document).ready(function () {
    $(".select2").select2();

    $(".datepicker").datepicker({
        format: "yyyy-mm-dd"
    });
});
</script>
@endsection