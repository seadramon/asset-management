@extends('layouts.main')

@section('title', 'Entri PdM - Manajemen Strategi - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Entri PdM - Manajemen Strategi')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Entri PdM Form</h5>
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
                {!! Form::open(['url' => route('mstrategi::mstrategi-entripdm'), 'class' => 'form-horizontal']) !!}
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">

	                    <div class="form-group row">
                            <label class="col-form-label col-lg-2">Lokasi</label>
                            <div class="col-lg-10">
                                {!! Form::select('instalasi', $instalasi, null, ['class'=>'form-control select2', 'id'=>'instalasi']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
							<label class="col-form-label col-lg-2">Equipment</label>
							<div class="col-lg-10">
                                {!! Form::select('template', $equipment, null, ['class'=>'form-control select2', 'id'=>'equipment']) !!}
							</div>
						</div>

                        <div id="beforepart"></div>

                	</fieldset>

                	<div class="text-right">
						<button type="submit" class="btn btn-primary legitRipple">Simpan</button>
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
        $('#asset').empty();
        $('#asset').append('<option value="">Pilih Asset</option>');

        $('#equipment').empty();
        $('#equipment').append('<option value="">Pilih Equipment</option>');        
        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('mstrategi/AssetSelect')}}/" + $(this).val(),
                success: function(result) {
                    // $('#asset').append(result.data);
                    $('#equipment').append(result.template);
                }
            })
        }
    });

    $('#equipment').change(function () {
        $('#part').html('');

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('mstrategi/Partpdm')}}/" + $(this).val(),
                success: function(result) {
                    $("#beforepart").html(result);
                    // $(result).insertAfter("#beforepart");
                }
            })
        }
    });

    $('#komponen').change(function () {
        $('#part').html('');

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('mstrategi/Part')}}/" + $(this).val(),
                success: function(result) {
                    $("#beforepart").html(result);
                }
            })
        }
    });
});
</script>
@endsection