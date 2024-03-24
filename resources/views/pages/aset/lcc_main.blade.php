@extends('layouts.main')

@section('title', 'Asset - Life Cycle Cost - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Asset - Life Cycle Cost')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Life Cycle Cost</h5>
			</div>

            <div class="card-body">
                <!-- BEGIN GENERAL FORM-->
            	<fieldset class="mb-3">
            		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Nama Aset</label>
                        <label class="col-form-label col-lg-10">
                            {!! $aset->nama_aset !!}
                        </label>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Kode Aset</label>
                        <label class="col-form-label col-lg-10">
                            {!! $aset->kode_aset !!}
                        </label>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Tahun Pemasangan</label>
                        <label class="col-form-label col-lg-10">
                            {!! !empty($aset->tahun_pasang)?$aset->tahun_pasang:"-" !!}
                        </label>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Bagian</label>
                        <label class="col-form-label col-lg-10">
                            {!! !empty($aset->bagiannya)?ucwords(strtolower($aset->bagiannya->name)):"-" !!}
                        </label>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Instalasi</label>
                        <label class="col-form-label col-lg-10">
                            {!! !empty($aset->instalasi)?$aset->instalasi->name:"-" !!}
                        </label>
                    </div>
            	</fieldset>
                <!-- END GENERAL FORM-->

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
                <!-- TAB -->
                <ul class="nav nav-tabs nav-tabs-highlight nav-justified">
                    <li class="nav-item"><a href="#akusisi" class="nav-link active" data-toggle="tab">Akuisisi</a></li>
                    <li class="nav-item"><a href="#oc-pompa" class="nav-link" data-toggle="tab">Biaya Operasional</a></li>
                    <li class="nav-item"><a href="#mc-pompa" class="nav-link" data-toggle="tab">Biaya Pemeliharaan</a></li>
                    <li class="nav-item"><a href="#penghapusan" class="nav-link" data-toggle="tab">Penghapusan</a></li>
                    <li class="nav-item"><a href="#lcc" class="nav-link" data-toggle="tab">LCC</a></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="akusisi">
                        @include('pages.aset.lcc_akuisisi')
                    </div>

                    <div class="tab-pane fade" id="oc-pompa">
                        @include('pages.aset.lcc_oc')
                    </div>

                    <div class="tab-pane fade" id="mc-pompa">
                        @include('pages.aset.lcc_mc')
                    </div>

                    <div class="tab-pane fade" id="penghapusan">
                        @include('pages.aset.lcc_penghapusan')
                    </div>

                    <div class="tab-pane fade" id="lcc">
                        @include('pages.aset.lcc')
                    </div>
                </div>
                <!-- end:TAB -->
            </div>
        </div>
    <!-- /form inputs -->
@endsection


@section('css')
<!-- <link href="{{asset('global_assets/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" /> -->

@endsection
@section('js')
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>
<script src="{{asset('global_assets/plugins/datatables/datatables.min.js')}}" type="text/javascript"></script>

<script src="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.js')}}"></script>
<link href="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.css')}}" rel="stylesheet"/>

<script src="{{ url('global_assets/js/plugins/notifications/noty.min.js') }}"></script>
<script>
$(document).ready(function () {
    $(".select2").select2();

    $(".datepicker").datepicker({
        format: "yyyy-mm-dd",
    });
});
</script>
@endsection