@extends('layouts.main')

@section('title', 'Entri 4w - Perawatan Rutin - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Entri 4w - Perawatan Rutin')

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
                {!! Form::open(['url' => route('mstrategi::mstrategi-simpanRutin4w'), 'class' => 'form-horizontal']) !!}
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">

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
                        @endif

                        <div class="form-group row">
                            <div class="col-lg-12 text-left">
                                <a href="#" id="showPerawatan" class="btn btn-info legitRipple">Tampilkan Perawatan</a>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12" id="part">
                                <!-- Load Here -->
                            </div>
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

<script src="{{ url('global_assets/js/plugins/notifications/noty.min.js') }}"></script>
<script>
$(document).ready(function () {
    $(".select2").select2();

    $("#year").datepicker({
        format: "yyyy",
        viewMode: "years", 
        minViewMode: "years"
    });

    $('#showPerawatan').click(function (event) {
        event.preventDefault();

        var block = $(this).closest('#part');
        $(block).block({
            message: '<span class="font-weight-semibold"><i class="icon-spinner4 spinner mr-2"></i>&nbsp; Loading data</span>',
            timeout: 2000, //unblock after 2 seconds
            overlayCSS: {
                backgroundColor: '#fff',
                opacity: 0.8,
                cursor: 'wait'
            },
            css: {
                border: 0,
                padding: '10px 15px',
                color: '#fff',
                width: 'auto',
                '-webkit-border-radius': 3,
                '-moz-border-radius': 3,
                backgroundColor: '#333'
            }
        });

        $("#part").html('');

        var week = $('#minggu').val();
        var tahun = $('#year').val();
        var lokasi = $('#instalasi').val();
        var komponen_id = $('#komponen_id').val();

        if ($('#bagian').val() != '') {
            var urlnya = "{{url('mstrategi/showPrwrutin')}}?week=" + week + '&bagian=' + $('#bagian').val() + '&tahun' + tahun + '&lokasi=' + lokasi;
            $.ajax({
                type: "get",
                url: urlnya,
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