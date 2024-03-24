@extends('layouts.main')

@section('title', 'Home - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
@stop

@section('pagetitle', 'Dashboard')

@section('content')
<!-- Theme JS files -->
<!-- <script type="text/javascript" src="https://cdn.fusioncharts.com/fusioncharts/latest/fusioncharts.js"></script>
<script type="text/javascript" src="https://cdn.fusioncharts.com/fusioncharts/latest/themes/fusioncharts.theme.fusion.js"></script> -->

<script type="text/javascript" src="{{ asset('global_assets/plugins/fusioncharts/js/fusioncharts.js') }}"></script>
<script type="text/javascript" src="{{ asset('global_assets/plugins/fusioncharts/js/themes/fusioncharts.theme.fusion.js') }}"></script>
    <!-- Form inputs -->
    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title">&nbsp;</h5>
        </div>

        <div class="card-body">
            {!! Form::open(['url' => route('index-dashboard'), 'class' => 'form-horizontal', 'id' => 'f_dashboard']) !!}
                <fieldset class="mb-3">
                    <input type="text" class="hidden" name="_token" value="{{csrf_token()}}">

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Lokasi</label>
                        <div class="col-lg-10">
                            {!! Form::select('instalasi_id', $instalasi, null, ['class'=>'form-control select2', 'id'=>'instalasi', 'required']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Komponen</label>
                        <div class="col-lg-10">
                            {!! Form::select('komponen_id', $komponen, null, ['class'=>'form-control select2', 'id'=>'komponen', 'required']) !!}
                            {!! Form::hidden('kode_fm', null, ['id' => 'kode_fm']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Tanggal</label>
                        <div class="col-lg-10">
                            <input type="text" name="tanggal" class="form-control daterange-basic">
                        </div>
                    </div>

                </fieldset>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary legitRipple">Tampilkan</button>
                </div>
            {!! Form::close() !!}

            <?php
                if (!empty($arrData)) {
                    $fusionTable = new FusionTable($schema, $data);
                    $timeSeries = new TimeSeries($fusionTable);

                    $timeSeries->AddAttribute("caption", sprintf("{ 
                                                    text: 'Monitoring %s'
                                                  }", $namaAset));
                                                  
                    $timeSeries->AddAttribute("series", "'Pengukuran'");

                    $timeSeries->AddAttribute('yaxis', '[
                                    {
                                        "plot":"Jumlah",
                                        "title":"Jumlah",
                                        "referenceline":[
                                            {
                                                "label":"Batas Perawatan",
                                                "value":"70",
                                                "style":{
                                                    "marker":{
                                                        "stroke-dasharray":[4,3]
                                                    }
                                                }
                                            }
                                        ]
                                    }
                                ]');            
                                
                    // chart object
                    $Chart = new FusionCharts("timeseries", "MyFirstChart" , "900", "600", "chart-container", "json", $timeSeries);

                    // Render the chart
                    $Chart->render();
                }

            ?>
                <center>
                    <div id="chart-container"><?php !empty($arrData)?'Chart will render here!':'Data Monitoring Kosong' ?></div>
                </center>
        </div>
    </div>
    <!-- /form inputs -->
@endsection

@section('js')
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js"></script>
<script src="{{ url('global_assets/js/plugins/ui/moment/moment.min.js') }}"></script>
<script src="{{ url('global_assets/js/plugins/pickers/daterangepicker.js') }}"></script>

<script>
$(document).ready(function () {
    $(".select2").select2();

    /*Daterange masa*/
    $('.daterange-basic').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD',
                cancelLabel: 'Clear'
            },
            autoUpdateInput: false
        }, function(chosen_date) {
            $('.daterange-basic').val(chosen_date.format('YYYY-MM-DD'));
        });

    $('.daterange-basic').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
    });

    $('.daterange-basic').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
    /*End Daterange*/

    $('#instalasi').change(function () {
        $('#komponen').empty();
        $('#komponen').append('<option value="">Pilih Asset</option>');

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('dashboard-assetSelect')}}/" + $(this).val(),
                success: function(result) {
                    console.log(result.data);
                    $('#komponen').append(result.data);
                }
            })
        }
    });

    $('#komponen').change(function () {
        $('#kode_fm').empty();

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('dashboard-getAssetKodefm')}}/" + $(this).val(),
                success: function(result) {
                    // console.log(result.data.kode_fm);
                    $('#kode_fm').val(result.data.kode_fm);
                }
            })
        }
    });
});
</script>
@endsection