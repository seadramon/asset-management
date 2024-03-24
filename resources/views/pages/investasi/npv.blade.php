@extends('layouts.main')

@section('title', 'Form perhitungan NPV untuk Perencanaan Investasi - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Form perhitungan NPV untuk Perencanaan Investasi')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Form perhitungan NPV</h5>
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
                {!! Form::open(['url' => route('investasi::npv-store'), 'class' => 'form-horizontal']) !!}
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">

                		<div class="form-group row">
                            <label class="col-form-label col-lg-2">Judul</label>
                            <div class="col-lg-10">
                                {!! Form::textarea("judul", null, ['class'=>'form-control', 'id'=>'judul', 'required', 'rows' => '3']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Tahun</label>
                            <div class="col-lg-10">
                                {!! Form::text("tahun", null, ['class'=>'form-control yearpicker', 'id'=>'tahun', 'required']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Umur Ekonomis</label>
                            <div class="col-lg-10">
                                {!! Form::select('umur_ekonomis', $umurEkonomis, null, ['class'=>'form-control select2', 'id'=>'umur_ekonomis', 'required']) !!}
                            </div>
                        </div>

	                    <div class="form-group row">
							<label class="col-form-label col-lg-2">Lokasi</label>
							<div class="col-lg-10">
                                {!! Form::select('instalasi_id', $instalasi, null, ['class'=>'form-control select2', 'id'=>'instalasi', 'required']) !!}
							</div>
						</div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Nilai investasi / RAB</label>
                            <div class="col-lg-10">
                                {!! Form::text("rab", null, ['class'=>'form-control numberkey', 'id'=>'rab', 'required']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Discount rate</label>
                            <div class="col-lg-4">
                                {!! Form::text("discount_rate", null, ['class'=>'form-control', 'id'=>'discount_rate', 'required']) !!}
                                <span class="form-text text-muted">(BI rate ditambah 5%)</span>
                            </div>
                            <label class="col-form-label col-lg-6">%</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Cash In Flow</label>
                            <div class="col-lg-10">
                                {!! Form::text("cash_in", null, ['class'=>'form-control numberkey', 'id'=>'cash_in', 'required']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Cash Out Flow</label>
                            <div class="col-lg-10">
                                {!! Form::text("cash_out", null, ['class'=>'form-control numberkey', 'id'=>'cash_out', 'required']) !!}
                            </div>
                        </div>

                        <div id="beforepart"></div>
                	</fieldset>

                	<div class="text-right">
						<button type="submit" name="simpancetak" class="btn btn-success legitRipple">Simpan & Cetak</button>
						<button type="submit" name="cetak" class="btn btn-primary legitRipple">Cetak</button>
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

    $(".yearpicker").datepicker({
        format: "yyyy",
        viewMode: "years", 
        minViewMode: "years"
    });

    $('.numberkey').keyup(function(event) {
		// skip for arrow keys
	  	if(event.which >= 37 && event.which <= 40) return;

	  	// format number
	  	$(this).val(function(index, value) {
	    	return value
	    	.replace(/\D/g, "")
	    	.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	  	});
	});
});
</script>
@endsection