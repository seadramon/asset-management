@extends('layouts.main')

@section('title', 'Entri 4w - Manajemen Strategi - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Entri 4w - Manajemen Strategi')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Entri 4w Form</h5>
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
                {!! Form::open(['url' => route('mstrategi::mstrategi-entri4w'), 'class' => 'form-horizontal']) !!}
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">

                        <?php /*
                        <div class="form-group row">
							<label class="col-form-label col-lg-2">Komponen</label>
							<div class="col-lg-10">
                                {!! Form::select('ms_52w_id', $aset, null, ['class'=>'form-control select2', 'id'=>'aset']) !!}
							</div>
						</div>
                        */ 
                        // $bagian = bagian();
                        
                        ?>
                       <div class="form-group row">
                            <label class="col-form-label col-lg-2">Tahun</label>
                            <div class="col-lg-10">
                                {!! Form::text('tahun', $tahun, ['class'=>'form-control', 'id'=>'year', 'required']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Minggu</label>
                            <div class="col-lg-10">
                                {!! Form::select('urutan_minggu', $week, null, ['class'=>'form-control select2', 'id'=>'minggu', 'required']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Lokasi</label>
                            <div class="col-lg-10">
                                {!! Form::select('instalasi_id', $instalasi, null, ['class'=>'form-control select2', 'id'=>'instalasi']) !!}
                            </div>
                        </div>

                        @if (namaRole() == 'Super Administrator')
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Bagian</label>
                                <div class="col-lg-10">
                                    {!! Form::select("bagian", $bagian, null, ['class'=>'form-control select2', 'id'=>'bagian']) !!}
                                </div>
                            </div>
                        @else
                            {!! Form::hidden('bagian', bagian()[0], ['class'=>'form-control', 'id'=>'bagian']) !!}
                        @endif

                        <div class="form-group row">
                            <div class="col-lg-12 text-left">
                                <a href="#" id="showKomponen" class="btn btn-info legitRipple">Tampilkan Komponen</a>
                            </div>
                        </div>

                        <?php /*
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Bagian</label>
                            <div class="col-lg-10">
                                {!! Form::select('bagian', $bagian, null, ['class'=>'form-control select2', 'id'=>'bagian']) !!}
                            </div>
                        </div>
                        */?>

                        <div id="part"></div>

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

    $("#year").datepicker({
        format: "yyyy",
        viewMode: "years", 
        minViewMode: "years"
    });

    $('#showKomponen').click(function (event) {
    // $('#aset').change(function () {
        event.preventDefault();
        $("#part").html('');
        console.log('test');

        var week = $('#minggu').val();
        var tahun = $('#year').val();
        var lokasi = $('#instalasi').val();
console.log("{{url('mstrategi/WeekSelectNew')}}/" + week + '/' + $('#bagian').val() + '/' + tahun + '/' + lokasi);
        if ($('#bagian').val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('mstrategi/WeekSelectNew')}}/" + week + '/' + $('#bagian').val() + '/' + tahun + '/' + lokasi,
                success: function(result) {
                    $("#part").html(result);
                },
                error: function(jqxhr,textStatus,errorThrow) {
                    console.log(jqxhr);
                    console.log(textStatus);
                    console.log(errorThrow);

                }
            })
        }
    });
});
</script>
@endsection