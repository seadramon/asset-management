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
            <form method="get" action="{{ route('dashboard::link-prwrutin') }}">
                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Minggu</label>
                    <div class="col-lg-8">
                        {!! Form::select('minggu_ke', $week, null, ['class'=>'form-control select2', 'id'=>'minggu', 'required']) !!}
                    </div>
                    <div class="col-lg-2">
                        <input type="submit" value="Tampilkan" class="btn btn-info legitRipple">
                    </div>
                </div>
            </form>

            <h2>Menampilkan Urutan Minggu ke {{ $minggu_ke }}</h2>

            <iframe
                id="dashboardFrame"
                src="{{ $defaultUrl }}"
                frameborder="0"
                width="100%"
                height="600"
                allowtransparency
            ></iframe>
        </div>
    </div>
    <!-- /form inputs -->
@endsection

@section('js')
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>

<script>
$(document).ready(function () {
    $(".select2").select2();
});
</script>
@endsection