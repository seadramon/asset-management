@extends('layouts.main')

@section('title', 'Laporan Pembobotan - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Laporan Pembobotan')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Laporan Pembobotan Form</h5>
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
                {!! Form::open(['url' => route('rpembobotan::rpembobotan-laporan'), 'class' => 'form-horizontal', 'target' => '_blank']) !!}
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">

	                    <div class="form-group row">
							<label class="col-form-label col-lg-2">Lokasi</label>
							<div class="col-lg-10">
                                {!! Form::select('instalasi', $instalasi, null, ['class'=>'form-control select2', 'id'=>'instalasi', 'required']) !!}
							</div>
						</div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Bagian</label>
                            <div class="col-lg-10">
                                {!! Form::select('bagian', $bagian, null, ['class'=>'form-control select2', 'id'=>'bagian', 'required']) !!}
                            </div>
                        </div>

                        <?php /*
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Minggu</label>
                            <div class="col-lg-4">
                                {!! Form::select('minggu1', $minggu, null, ['class'=>'form-control select2', 'id'=>'minggu1']) !!}
                            </div>
                            <label class="col-form-label col-lg-1" style="text-align: center;">s/d</label>
                            <div class="col-lg-5">
                                {!! Form::select('minggu2', $minggu, null, ['class'=>'form-control select2', 'id'=>'minggu2']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Tahun</label>
                            <div class="col-lg-10">
                                {!! Form::text('tahun', null, ['class'=>'form-control', 'id'=>'yearpicker']) !!}
                            </div>
                        </div>
                        */
                        ?>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Tanggal</label>
                            <div class="col-lg-10">
                                {!! Form::text('tanggal', null, ['class'=>'form-control', 'id'=>'datepicker']) !!}
                            </div>
                        </div>

                        <div id="beforepart"></div>

                	</fieldset>

                	<div class="text-right">
                        <button type="submit" class="btn btn-primary legitRipple">Cetak</button>
						<?php /*<a href="{{ url('clear') }}" class="btn btn-info legitRipple">Clear Cache</a> */?>
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

    $("#datepicker").datepicker();

    $("#yearpicker").datepicker({
        format: "yyyy",
        viewMode: "years", 
        minViewMode: "years"
    });
});
</script>
@endsection