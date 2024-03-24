@extends('layouts.main')

@section('title', 'Asset - Life Cycle Cost Analysis - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Asset - Life Cycle Cost Analysis')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Life Cycle Cost Analysis</h5>
			</div>

            <div class="card-body">
                <!-- BEGIN GENERAL FORM-->
            	<fieldset class="mb-3">
            		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">

            		<div class="form-group row">
                        <label class="col-form-label col-lg-2">Bagian</label>
                        <div class="col-lg-10">
                            {!! Form::select("bagian", $bagian, null, ['class'=>'form-control select2', 'id'=>'bagian']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Instalasi</label>
                        <div class="col-lg-10">
                            {!! Form::select('instalasi_id', $instalasi, null, ['class'=>'form-control select2', 'id'=>'instalasi']) !!}
                        </div>
                    </div>

                    <div class="row">
                    	<div class="col-md-4 col-sm-4">
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Opsi A</label>
                                <div class="col-md-8">
                                    {!! Form::select("opsi_a", $opsi_a, null, ['class'=>'form-control select2', 'id'=>'opsi_a']) !!}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-4">
                        	<div class="form-group row">
                                <label class="col-md-4 col-form-label">Opsi B</label>
                                <div class="col-md-8">
                                    {!! Form::select("opsi_b", $opsi_b, null, ['class'=>'form-control select2', 'id'=>'opsi_b']) !!}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-4">
                        	<div class="form-group row">
                                <label class="col-md-4 col-form-label">Opsi C</label>
                                <div class="col-md-8">
                                    {!! Form::select("opsi_c", $opsi_c, null, ['class'=>'form-control select2', 'id'=>'opsi_c']) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-lg-12 text-left">
                            <a href="#" id="bandingkan" class="btn btn-info legitRipple">Bandingkan</a>
                        </div>
                    </div>
            	</fieldset>
                <!-- END GENERAL FORM-->

                <div class="table-responsive">
                	<table class="table table-bordered">
                		<thead class="thead-light">
                			<tr>
                				<th rowspan="2">Elemen Biaya</th>
                				<th>Opsi A</th>
                				<th>Opsi B</th>
                				<th>Opsi C</th>
                			</tr>
                			<tr>
                				<th>Biaya :</th>
                				<th>Biaya :</th>
                				<th>Biaya :</th>
                			</tr>
                		</thead>
                		<tbody id="contentPart">
                			<tr>
                				<td>Biaya Akuisisi</td>
                				<td></td>
                				<td></td>
                				<td></td>
                			</tr>
                			<tr>
                				<td>Biaya Operasional</td>
                				<td></td>
                				<td></td>
                				<td></td>
                			</tr>
                			<tr>
                				<td>Biaya Pemeliharaan</td>
                				<td></td>
                				<td></td>
                				<td></td>
                			</tr>
                			<tr>
                				<td>Biaya Penghapusan</td>
                				<td></td>
                				<td></td>
                				<td></td>
                			</tr>
                			<tr>
                				<td>Total Life Cycle Cost</td>
                				<td></td>
                				<td></td>
                				<td></td>
                			</tr>
                			<tr>
                				<td>Delta LCC</td>
                				<td></td>
                				<td></td>
                				<td></td>
                			</tr>

                			<tr class="thead-light">
                				<th rowspan="2">Cost elements relative to the lowest</th>
                				<th>Opsi A</th>
                				<th>Opsi B</th>
                				<th>Opsi C</th>
                			</tr>
                			<tr class="thead-light">
                				<th>Biaya :</th>
                				<th>Biaya :</th>
                				<th>Biaya :</th>
                			</tr>
                			<tr>
                				<td>Biaya Akuisisi</td>
                				<td></td>
                				<td></td>
                				<td></td>
                			</tr>
                			<tr>
                				<td>Biaya Operasional</td>
                				<td></td>
                				<td></td>
                				<td></td>
                			</tr>
                			<tr>
                				<td>Biaya Pemeliharaan</td>
                				<td></td>
                				<td></td>
                				<td></td>
                			</tr>
                			<tr>
                				<td>Biaya Penghapusan</td>
                				<td></td>
                				<td></td>
                				<td></td>
                			</tr>
                			<tr>
                				<td>Total Life Cycle Cost</td>
                				<td></td>
                				<td></td>
                				<td></td>
                			</tr>
                		</tbody>
                	</table>
                </div>
            </div>
        </div>
    <!-- /form inputs -->
@endsection


@section('css')
<!-- <link href="{{asset('global_assets/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" /> -->
<style type="text/css">
	table.dataTable thead th, table.dataTable thead td{
		border-bottom: 1px solid #a7a0a0;
	}
    .dataTables_length label{
        position: absolute;
        right: 0;
    }
</style>
@endsection
@section('js')
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>

<script src="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.js')}}"></script>
<link href="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.css')}}" rel="stylesheet"/>

<script src="{{ url('global_assets/js/plugins/notifications/noty.min.js') }}"></script>
<script>
$(document).ready(function () {
    $(".select2").select2();

    $(".datepicker").datepicker({
        format: "yyyy-mm-dd",
    });

    $("#instalasi").change(function(event) {
    	$('#opsi_a').empty();
    	$('#opsi_a').append('<option value="">Pilih Asset A</option>');
    	$('#opsi_b').empty();
    	$('#opsi_b').append('<option value="">Pilih Asset B</option>');
    	$('#opsi_c').empty();
    	$('#opsi_c').append('<option value="">Pilih Asset C</option>');

    	var bagian = $("#bagian").val();

    	if ($(this).val() != '') {
	    	$.ajax({
		        type: "get",
		        url: "{{url('lcca/assetSelect')}}?instalasi=" + $(this).val() + "&bagian=" + bagian,
		        success: function(result) {
		            $('#opsi_a').append(result.data);
		            $('#opsi_b').append(result.data);
		            $('#opsi_c').append(result.data);
		        }
		    })
    	}
    });

    $("#bandingkan").click(function(event) {
    	event.preventDefault();

    	$("#contentPart").html("");

    	var opsi_a = $("#opsi_a").val();
    	var opsi_b = $("#opsi_b").val();
    	var opsi_c = $("#opsi_c").val();

    	$.ajax({
            type: "get",
            url: "{{url('lcca/comparison')}}?opsi_a=" + opsi_a + '&opsi_b=' + opsi_b + '&opsi_c=' + opsi_c,
            success: function(result) {
                $("#contentPart").html(result);
            },
            error: function(jqxhr,textStatus,errorThrow) {
                console.log(jqxhr);
                console.log(textStatus);
                console.log(errorThrow);
            }
        })
    });
});
</script>
@endsection