@extends('layouts.main')

@section('title', 'Hasil Penanganan Perbaikan dari Monitoring - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Hasil Penanganan Perbaikan dari Monitoring')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">

        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Hasil Penanganan Perbaikan dari Monitoring Form</h5>
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
                    {!! Form::model($data, ['route' => ['perbaikan::perbaikan-close'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('perbaikan::perbaikan-close'), 'class' => 'form-horizontal']) !!}
                @endif
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Foto Investigasi :</label>
                            <div class="col-lg-10">
                                <img src="{{ asset('uploads/perbaikan/monitoring/'.$data->foto_investigasi) }}" width="400px" height="400px">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Foto Investigasi 2 :</label>
                            <div class="col-lg-10">
                                <img src="{{ asset('uploads/perbaikan/monitoring/'.$data->foto_investigasi2) }}" width="400px" height="400px">
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
                            <label class="col-form-label col-lg-2">Uraian Penanganan :</label>
                            <label class="col-form-label col-lg-10">{!! $data->uraian !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Foto :</label>
                            <div class="col-lg-10">
                                <img src="{{ asset('uploads/perbaikan/monitoring/'.$data->foto) }}" width="400px" height="400px">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Foto 2 :</label>
                            <div class="col-lg-10">
                                <img src="{{ asset('uploads/perbaikan/monitoring/'.$data->foto2) }}" width="400px" height="400px">
                            </div>
                        </div>

                	</fieldset>

                	<div class="text-right">
						<button type="submit" class="btn btn-primary legitRipple">Close</button>
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
});
</script>
@endsection