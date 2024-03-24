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
                @if (isset($data))
                    {!! Form::model($data, ['route' => ['lcca::pemeliharaan-simpan'], 'class' => 'form-horizontal', 'id' => 'f_editPemeliharaan']) !!}
                    {!! Form::hidden('id', null) !!}

                    <?php 
                    $totalBiaya = $data->total_biaya;
                    ?>
                @else
                    {!! Form::open(['url' => route('lcca::pemeliharaan-simpan'), 'class' => 'form-horizontal', 'id' => 'f_editPemeliharaan']) !!}

                    <?php 
                    $totalBiaya = 0;
                    ?>
                @endif
                
                {!! Form::hidden('aset_id', $aset_id) !!}
                {!! Form::hidden('wo', $wo) !!}
                {!! Form::hidden('wo_id', $wo_id) !!}

            	<fieldset class="mb-3">
            		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Dokumen SPK</label>
                        <div class="col-sm-9">
                            {!! Form::textarea('spk', null, ['class'=>'form-control', 'id'=>'spk', 'required', 'rows' => '3']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Dokumen Berita Acara Hasil Pekerjaan</label>
                        <div class="col-sm-9">
                            {!! Form::textarea('berita_acara', null, ['class'=>'form-control', 'id'=>'berita_acara', 'required', 'rows' => '3']) !!}
                        </div>
                    </div>

                    <h2>Aktifitas</h2>
                    <div class="table-responsive">
                        <table class="table table-bordered" style="margin-top: 10px;margin-bottom: 10px;" id="tabelWait" width="100%">
                            <thead>
                                <tr>
                                    <th>Aktifitas</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Biaya Satuan</th>
                                    <th>Jumlah Biaya</th>
                                    <th>
                                        <button type="button" class="btn bg-success right" id="addClone"><i class="fa fa-plus"></i></button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $i = 0; 
                                $jmlTotal = 0;
                                ?>
                                @if (count($aktifitas) > 0)
                                    @foreach($aktifitas as $row)
                                        <tr>
                                            <td>
                                                <input type="text" name="arrAktifitas[{{ $i }}][name]" class="form-control" data-tipe="inputName" value="{{ $row->name }}">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="arrAktifitas[{{ $i }}][jumlah]" value="{{ $row->jumlah }}" data-tipe="inputJumlah">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="arrAktifitas[{{ $i }}][satuan]" value="{{ $row->satuan }}" data-tipe="inputSatuan">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="arrAktifitas[{{ $i }}][biaya]" value="{{ $row->biaya }}" data-tipe="inputBiaya">
                                            </td>
                                            <td>
                                                <?php
                                                $jmlBiaya = $row->jumlah * $row->biaya;
                                                $jmlTotal = $jmlTotal + $jmlBiaya;
                                                ?>
                                                <label>{{ 'Rp '.number_format($jmlBiaya,0,"",".") }}</label>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm removeBtnWait" onClick="remBtnWait($(this))"><i class="fa fa-times"></i></button>
                                            </td>
                                        </tr>
                                        <?php $i++; ?>
                                    @endforeach
                                @else
                                    <tr>
                                        <td>
                                            <input type="text" name="arrAktifitas[0][name]" class="form-control" data-tipe="inputName">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="arrAktifitas[0][jumlah]" data-tipe="inputJumlah">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="arrAktifitas[0][satuan]" data-tipe="inputSatuan">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="arrAktifitas[0][biaya]" data-tipe="inputBiaya">
                                        </td>
                                        <td><label></label></td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm removeBtnWait" onClick="remBtnWait($(this))"><i class="fa fa-times"></i></button>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" style="text-align: right;">
                                        <label>Total Biaya Pemeliharaan : {{ 'Rp '.number_format($totalBiaya, 0, "", ".") }}</label>
                                    </td>
                                    <td>&nbsp;</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Sukucadang -->
                    @include('pages.sukucadang.show')

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary legitRipple">Submit</button>
                        <a href="{{route('lcca::index', ['id' => $aset_id])}}">
                            <button type="button" class="btn btn-light legitRipple">Kembali</button></a>
                    </div>
            	</fieldset>
                {!! Form::close() !!}
                <!-- END GENERAL FORM-->
            </div>
        </div>
    <!-- /form inputs -->
@endsection


@section('css')
<!-- <link href="{{asset('global_assets/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" /> -->
<style type="text/css">
	table.dataTable thead th, table.dataTable thead td{
		border-bottom: 1px solid #a7a0a0;
	}
    .dataTables_length label{
        position: absolute;
        right: 0;
    }
</style>
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

    $('#tabelWait > tbody').find('tr:first').find('.removeBtnWait').hide();
});

var jmlDataScWait = <?php echo count($aktifitas) ?>;

if (jmlDataScWait > 0) {
    var iclWait= jmlDataScWait;
    console.log(jmlDataScWait);
} else {
    var iclWait= 0;
}
$("#addClone").click(function($e) {
    // console.log(iclWait);
    $('#tabelWait > tbody').find('tr:last').removeClass('hidden');

    /*if (iclWait > 0) {
        $("#tabelWait > tbody > tr:last").clone().appendTo('#tabelWait > tbody');       
        $('#tabelWait > tbody').find('tr:last').find('.removeBtnWait').show();
    }*/
    if (iclWait == 0) {
        iclWait++;
    }
    $("#tabelWait > tbody > tr:last").clone().appendTo('#tabelWait > tbody');       
    $('#tabelWait > tbody').find('tr:last').find('.removeBtnWait').show();

    $('#tabelWait > tbody').find('tr:last').find('input[data-tipe=inputName]').prop('name','arrAktifitas['+iclWait+'][name]').val('');

    $('#tabelWait > tbody').find('tr:last').find('input[data-tipe=inputJumlah]').prop('name','arrAktifitas['+iclWait+'][jumlah]').val('');

    $('#tabelWait > tbody').find('tr:last').find('input[data-tipe=inputSatuan]').prop('name','arrAktifitas['+iclWait+'][satuan]').val('');
    $('#tabelWait > tbody').find('tr:last').find('input[data-tipe=inputBiaya]').prop('name','arrAktifitas['+iclWait+'][biaya]').val('');
    $('#tabelWait > tbody').find('tr:last').find('label').html('');

    iclWait++;
});

var remBtnWait = function(e) {
    iclWait--;

    if (iclWait == 0) {
        $('#tabelWait > tbody').find('tr:last').addClass('hidden');

        $('#tabelWait > tbody').find('tr:last').find('input[data-tipe=inputName]').prop('name','arrAktifitas['+iclWait+'][name]').val('');

        $('#tabelWait > tbody').find('tr:last').find('input[data-tipe=inputJumlah]').prop('name','arrAktifitas['+iclWait+'][jumlah]').val('');

        $('#tabelWait > tbody').find('tr:last').find('input[data-tipe=inputSatuan]').prop('name','arrAktifitas['+iclWait+'][satuan]').val('');

        $('#tabelWait > tbody').find('tr:last').find('input[data-tipe=inputBiaya]').prop('name','arrAktifitas['+iclWait+'][biaya]').val('');
    } else {
        $(e).closest('tr').remove();
    }
}
</script>
@endsection