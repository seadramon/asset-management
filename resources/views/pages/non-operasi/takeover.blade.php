@extends('layouts.main')

@section('title', 'Take Over - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Take Over')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">{{ 'Take Over '. $title .' Form'}}</h5>
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
                    {!! Form::model($data, ['route' => ['non-operasi::takeover-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('non-operasi::takeover-simpan'), 'class' => 'form-horizontal']) !!}
                @endif
                	<fieldset class="mb-3">
                        {!! Form::hidden('wo', $wo) !!}
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">

	                    <div class="form-group row" id="petugasField">
                            <label class="col-form-label col-lg-2">Judul</label>
                            @if ($wo == 'AduanNonOperasi')
                                <label class="col-form-label col-lg-10">{{ $data->judul }}</label>
                            @else
                                <label class="col-form-label col-lg-10">{{ $data->nama }}</label>
                            @endif
                        </div>

                        <div class="form-group row" id="petugasField">
                            <label class="col-form-label col-lg-2">Instalasi</label>
                            <label class="col-form-label col-lg-10">{{ $data->instalasi->name }}</label>
                        </div>

                        <div class="form-group row" id="petugasField">
							<label class="col-form-label col-lg-2">Petugas</label>
							<div class="col-lg-10">
                                {!! Form::select('petugas_id', $petugas, null, ['class'=>'form-control select2', 'id'=>'petugas']) !!}
							</div>
						</div>

                        <div id="beforepart"></div>

                	</fieldset>

                	<div class="text-right">
						<button type="submit" class="btn btn-primary legitRipple">Simpan</button>
                        @if ($wo == 'AduanNonOperasi')
                            <a href="{{route('non-operasi::aduan-index')}}">
                                <button type="button" class="btn btn-light legitRipple">Kembali</button></a>
                        @else
                            <a href="{{route('non-operasi::usulan-index')}}">
                                <button type="button" class="btn btn-light legitRipple">Kembali</button></a>
                        @endif
					</div>
                {!! Form::close() !!}
                <!-- END FORM-->
            </div>
        </div>
    <!-- /form inputs -->
@endsection
@section('js')
<script src="{{asset('global_assets/plugins/datatables/datatables.min.js')}}" type="text/javascript"></script>
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>
<script>
$(document).ready(function () {
    $(".select2").select2();
});
</script>
@endsection