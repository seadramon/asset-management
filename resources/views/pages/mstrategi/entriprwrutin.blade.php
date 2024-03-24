@extends('layouts.main')

@section('title', 'Entri Perawatan Rutin - Manajemen Strategi - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Entri Perawatan Rutin - Manajemen Strategi')

@section('content') 
</style>
<style type="text/css">
    table {
  border-collapse: collapse;
  border-spacing: 0;
  width: 100%;
  border: 1px solid #ddd;
}

th, td {
  text-align: left;
  padding: 8px;
}
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Entri Perawatan Rutin Form</h5>
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
                {!! Form::open(['url' => route('mstrategi::mstrategi-simpanPrwRutin'), 'class' => 'form-horizontal']) !!}
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">

	                    <div class="form-group row">
                            <label class="col-form-label col-lg-2">Lokasi</label>
                            <div class="col-lg-10">
                                {!! Form::select('instalasi', $instalasi, null, ['class'=>'form-control select2', 'id'=>'instalasi']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
							<label class="col-form-label col-lg-2">Komponen</label>
							<div class="col-lg-10">
                                {!! Form::select('aset', $equipment, null, ['class'=>'form-control select2', 'id'=>'aset']) !!}
							</div>
                            <!-- <div class="col-lg-2">
                                <button type="button" id="addClone" class="btn btn-success legitRipple"> 
                                    <i class="fa fa-plus"></i> Frekuensi
                                </button>
                            </div> -->
						</div>

                        <!-- Tabel Detail -->
                        <label><i>Contoh Pengisian frekuensi : W3</i></label>
                        <div class="row" style="overflow-x: auto;">
                            <table class="table" id="tabelKomponen">
                                <thead>
                                    <tr>
                                        <th>Part</th> 
                                        @foreach($perawatan as $kolom)
                                            <th>{{ $kolom->name }}</th>
                                        @endforeach
                                        <!-- <th>Action</th> -->
                                    </tr>
                                </thead>
                                <tbody id="part">
                                    
                                </tbody>
                        </table>
                        </div>
                        <!-- ./end table detail -->

                	</fieldset>

                    <?php 
                    $curWeek = date('W');
                    $nipception = ['10901554'];
                    ?>
                    @if ((date('m') == '12') && ($curWeek % 4 == 0) || in_array(trim(\Auth::user()->userid), $nipception))
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
<script>
$(document).ready(function () {
    $(".select2").select2();

    $('#instalasi').change(function () {
        $('#aset').empty();
        $('#aset').append('<option value="">Pilih Komponen</option>');

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('mstrategi/AssetPrwSelect')}}/" + $(this).val(),
                success: function(result) {
                    // $('#asset').append(result.data);
                    $('#aset').append(result.template);
                }
            })
        }
    });

    $('#aset').change(function () {
        $('#part').empty();

        if ($("#aset").val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('mstrategi/PrwrutinSelect')}}/" + $("#aset").val(),
                success: function(result) {
                    $("#part").html(result);
                }
            })
        }
    });
});

var remBtn = function(e) {
    console.log(e);
    $(e).closest('tr').remove();
}
</script>
@endsection