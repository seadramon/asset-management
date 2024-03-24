@extends('layouts.main')

@section('title', 'Jadwal Kerja Pompa - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Jadwal Kerja Pompa')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Jadwal Kerja Pompa Form</h5>
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
                @if ($data!='')
                    {!! Form::model($data, ['route' => ['jadwalkerja::jadwalkerja-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('jadwalkerja::jadwalkerja-simpan'), 'class' => 'form-horizontal']) !!}
                @endif
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="text" class="hidden" name="tipe" value="{{($data=='')?'0':'1'}}">

	                    <div class="form-group row">
							<label class="col-form-label col-lg-2">Aset</label>
							<div class="col-lg-10">
                                @if ($data!='')
                                    {!! Form::text("namaaset", $namaaset, ['class'=>'form-control', 'id'=>'namaaset', 'disabled']) !!} 
                                    {!! Form::hidden('equipment_id', null) !!}
                                    {!! Form::hidden('minggu', null) !!}
                                @else
                                    {!! Form::select('equipment_id', $aset, null, ['class'=>'form-control select2', 'id'=>'aset']) !!}
                                @endif
							</div>
						</div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Minggu</label>
                            <div class="col-lg-10">
                                {!! Form::select('minggu[]', $week, $weekval, ['class'=>'form-control select2', 'id'=>'minggu', 'multiple']) !!}
                            </div>
                        </div>

                        <div id="beforepart"></div>

                	</fieldset>

                	<div class="text-right">
						<button type="submit" class="btn btn-primary legitRipple">Simpan</button>
                        <a href="{{route('jadwalkerja::jadwalkerja-index')}}">
                            <button type="button" class="btn btn-light legitRipple">Kembali</button></a>
					</div>
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
});
</script>
@endsection