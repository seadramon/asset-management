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
            @include('pages.dashboard.data-aset')
            
            <div>
                <div id="type-chart-container"><?php !empty($types)?'Chart will render here!':'Data Aset Kosong' ?></div>
                <div id="kategori-chart-container"><?php !empty($asetKategori)?'Chart will render here!':'Data Aset Kosong' ?></div>
                <div id="non-kategori-chart-container"><?php !empty($nonAsetKategori)?'Chart will render here!':'Data Aset Kosong' ?></div>

                <div id="kondisi-chart-container"><?php !empty($asetKondisi)?'Chart will render here!':'Data Aset Kosong' ?></div>
                <div id="non-kondisi-chart-container"><?php !empty($nonAsetKondisi)?'Chart will render here!':'Data Aset Kosong' ?></div>
            </div>
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