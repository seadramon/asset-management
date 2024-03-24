@extends('layouts.main')

@section('title', 'Entri 52W - Manajemen Strategi - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Entri 52W - Manajemen Strategi')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Entri 52W Form</h5>
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
                {!! Form::open(['url' => route('mstrategi::mstrategi-simpan52w'), 'class' => 'form-horizontal', 'id' => 'fm_52w']) !!}
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Tahun</label>
                            <div class="col-lg-10">
                                {!! Form::text('tahun', null, ['class'=>'form-control', 'id'=>'yearpicker']) !!}
                            </div>
                        </div>

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
                                            <th>Frekuensi</th>
                                            <th>Minggu Mulai</th>
                                            <th>Jumlah Orang</th>
                                            <th>Total Durasi (Jam)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="part">
                                        <tr>
                                            <td colspan="5" align="center">Data Kosong</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </dir>
                        </div>

                	</fieldset>

                    <?php 
                    $curWeek = date('W');
                    $nipception = ['10601441', '10901554'];
                    ?>
                    @if (((date('m') == '12') && ($curWeek % 4 == 0)) || in_array(trim(\Auth::user()->userid), $nipception))
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary legitRipple">Simpan</button>
                        </div>
                    @endif
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
        // $('#part').empty();
        // $('#komponen').append('<option value="">Pilih Komponen</option>');

        if ($(this).val() != '') {
            $('#part').empty();
            var tahun = $('#yearpicker').val();

            $.ajax({
                type: "get",
                url: "{{url('mstrategi/KomponenSelect')}}/" + $(this).val() + "/" + tahun,
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