@extends('layouts.main')

@section('title', 'Key Performance Indicator - Asset Management')

@section('pagetitle', 'Cetak Key Performance Indicator')

@section('content') 
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="kondisi-form">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Cetak KPI</h5>
                <div class="text-right">
                    <a href="{{ route('kpi::kpi-index-setting') }}" class="btn btn-info btn-sm">Setting Minggu</a>
                </div>
            </div>

            <div class="card-body">
                <!-- BEGIN FORM-->
                {!! Form::open(['url' => route('kpi::kpi-cetak'), 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data']) !!}
                    <fieldset class="mb-3">
                        <input type="hidden" class="hidden" name="_token" value="{{csrf_token()}}">

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Pilih</label>
                            <div class="col-lg-10">
                                <?php /*{!! Form::select('spv', $jabatan, null, ['class'=>'form-control select2', 'id'=>'spv']) !!} */?>
                                <select class="form-control" name="bagian" id="bagian">
                                    <?php $n = 0; ?>
                                    <?php $arr = []; ?>
                                    @foreach($jabatan as $row)
                                        <?php $n++; ?>
                                        @if($row->kodejabatan!='034 ')
                                            @if($row->leveljab=='2')
                                                @if($n!=1)
                                                    </optgroup>
                                                @endif
                                                    <optgroup label="{{$row->namajabatan}}">
                                                        @else
                                                            @if ( !in_array($row->kodejabatan, ['061 ', '062 ', '063 ', '225 ', '072 ']) )
                                                                <option value="{{$row->kodejabatan.'#'.$row->namajabatan.'#'.$row->leveljab.'#'.$row->parentjab}}">{{$row->namajabatan}}</option>
                                                                <?php $arr[] = $row->kodejabatan.'#'.$row->namajabatan.'#'.$row->leveljab.'#'.$row->parentjab; ?>
                                                            @endif
                                                        @if($n==count($jabatan))
                                                    </optgroup>
                                                @endif
                                            @endif
                                        @endif                                    
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <?php //dd($arr); ?>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Periode</label>
                            <div class="col-lg-10">
                                {!! Form::text('periode', null, ['class'=>'form-control', 'id'=>'monthpicker', 'required']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">&nbsp;</label>
                            <input type="text" class="hidden" name="export" value="">
                            <div class="col-lg-5">
                                <button type="submit" class="btn btn-danger btn-block"><i class="fa fa-file-pdf-o" ></i>&nbsp;Cetak</button> 
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

    $("#pdf").click(function () {
        $("[name=export]").val('pdf');
    });
    $("#excel").click(function () {
        $("[name=export]").val('excel');
    });
});
</script>
@endsection