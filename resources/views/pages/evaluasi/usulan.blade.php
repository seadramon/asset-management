@extends('layouts.main')

@section('title', 'Prioritas Perbaikan dan Penggantian Aset Produksi dan Distribusi - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Prioritas Perbaikan dan Penggantian Aset Produksi dan Distribusi')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">&nbsp;</h5>
			</div>

            <div class="card-body">
                @if (count($errors) > 0)
                    <div class="alert alert-danger alert-styled-right alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        @foreach(array_unique($errors->all()) as $err)
                            {{ $err }}<br>
                        @endforeach
                    </div>
                @endif
                <div class="alert alert-danger" style="display:none"></div>

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
                {!! Form::open(['url' => route('evaluasi::store-prioritas'), 'class' => 'form-horizontal', 'id' => 'fm_52w']) !!}
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">

	                    <div class="form-group row">
							<label class="col-form-label col-lg-2">Lokasi</label>
							<div class="col-lg-10">
                                {!! Form::select('instalasi_id', $instalasi, null, ['class'=>'form-control select2', 'id'=>'instalasi']) !!}
							</div>
						</div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Equipment</label>
                            <div class="col-lg-10">
                                {!! Form::select('equipment_id', $template, null, ['class'=>'form-control select2', 'id'=>'template']) !!}
                            </div>
                        </div>

                        <div class="row">
                            <dir class="col-md-12">
                                <table class="table table-hover" id="tabel" style="width: 100%;">
                                    <thead>
                                        <tr>                                    
                                            <th>Komponen</th>
                                            <th>Strategi yang dipilih</th>
                                            <th>Nilai RAB</th>
                                            <th>Kelayakan Operasional</th>
                                            <th>Kelayakan Keuangan</th>
                                            <th>Waktu Kebutuhan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="part">
                                        <tr>
                                            <td colspan="6" align="center">Data Kosong</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </dir>
                        </div>

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

<script src="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.js')}}"></script>
<link href="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.css')}}" rel="stylesheet"/>
<script>
$(document).ready(function () {
    $(".select2").select2();

    $("#yearpicker").datepicker({
        format: "yyyy",
        viewMode: "years", 
        minViewMode: "years"
    });

    $('#template').change(function () {
        if ($(this).val() != '') {
            $('#part').empty();

            $.ajax({
                type: "get",
                url: "{{url('evaluasi/part-prioritas')}}/" + $(this).val(),
                success: function(result) {
                    console.log(result);
                    $('#part').html(result);
                }
            })
        }
    });

    $('#instalasi').change(function () {
        $('#asset').empty();
        $('#asset').append('<option value="">Pilih Asset</option>');

        $('#template').empty();
        $('#template').append('<option value="">Pilih Equipment</option>');        
        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('mstrategi/AssetSelect')}}/" + $(this).val(),
                success: function(result) {
                    $('#asset').append(result.data);
                    $('#template').append(result.template);
                }
            })
        }
    });
});
</script>
@endsection