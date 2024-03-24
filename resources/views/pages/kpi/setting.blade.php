@extends('layouts.main')

@section('title', 'Setting Minggu KPI - Asset Management')

@section('pagetitle', 'Setting Minggu KPI')

@section('content') 
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="kondisi-form">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Form Setting</h5>
            </div>

            <div class="card-body">
                <!-- BEGIN FORM-->
                @if (isset($data))
                    {!! Form::model($data, ['route' => ['kpi::kpi-setting-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('kpi::kpi-setting-simpan'), 'class' => 'form-horizontal']) !!}
                @endif
                    <fieldset class="mb-3">
                        <input type="hidden" class="hidden" name="_token" value="{{csrf_token()}}">

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Periode</label>
                            <div class="col-lg-10">
                                {!! Form::text('periode', null, ['class'=>'form-control', 'id'=>'monthpicker', 'required']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Start</label>
                            <div class="col-lg-10">
                                {!! Form::text('start', null, ['class'=>'form-control datepicker', 'id'=>'start', 'required']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">End</label>
                            <div class="col-lg-10">
                                {!! Form::text('end', null, ['class'=>'form-control datepicker', 'id'=>'end', 'required']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Closed</label>
                            <div class="col-lg-10">
                                {!! Form::text('closed', null, ['class'=>'form-control', 'id'=>'closed', 'disabled']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">&nbsp;</label>
                            <div class="col-lg-5">
                                <button type="submit" class="btn btn-primary btn-block">Simpan</button> 
                            </div>
                            <!-- <div class="col-lg-5">
                                <button type="submit" class="btn btn-success btn-block"><i class="fa fa-file-excel-o" ></i>&nbsp;Cetak</button> 
                            </div> -->
                        </div>
                    </fieldset>
                </form>
                <!-- END FORM-->
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    <!-- /form inputs -->
@endsection
@section('js')
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>

<script src="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.js')}}"></script>
<link href="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.css')}}" rel="stylesheet"/>

<script>
$(document).ready(function () {
    $(".select2").select2();

    $("#monthpicker").datepicker({
        format: "MM-yyyy",
        viewMode: "months", 
        minViewMode: "months"
    });

    $(".datepicker").datepicker({
        format: "yyyy-mm-dd",
    });

    $("#pdf").click(function () {
        $("[name=export]").val('pdf');
    });
    $("#excel").click(function () {
        $("[name=export]").val('excel');
    });
});
</script>
@endsection