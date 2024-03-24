@extends('layouts.main')

@section('title', 'Entri Perawatan Rutin - Manajemen Strategi - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Entri Perawatan Rutin - Manajemen Strategi')

@section('content') 
</style>
<style type="text/css">
    table {
  border-collapse: collapse;
  border-spacing: 0;
  width: 100%;
  border: 1px solid #ddd;
}

th, td {
  text-align: left;
  padding: 8px;
}
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Entri Perawatan Rutin Form</h5>
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
                {!! Form::open(['url' => route('mstrategi::mstrategi-simpanPrwRutin'), 'class' => 'form-horizontal']) !!}
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">

	                    <div class="form-group row">
                            <label class="col-form-label col-lg-2">Lokasi</label>
                            <div class="col-lg-10">
                                {!! Form::select('instalasi', $instalasi, null, ['class'=>'form-control select2', 'id'=>'instalasi']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
							<label class="col-form-label col-lg-2">Komponen</label>
							<div class="col-lg-8">
                                {!! Form::select('aset', $equipment, null, ['class'=>'form-control select2', 'id'=>'aset']) !!}
							</div>
                            <div class="col-lg-2">
                                <button type="button" id="addClone" class="btn btn-success legitRipple"> 
                                    <i class="fa fa-plus"></i> Frekuensi
                                </button>
                            </div>
						</div>

                        <!-- Tabel Detail -->
                        <div class="row" style="overflow-x: auto;">
                            <table class="table" id="tabelKomponen">
                                <thead>
                                    <tr>
                                        <th>Komponen&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th> 
                                        @foreach($perawatan as $kolom)
                                            <th>{{ $kolom->name }}</th>
                                        @endforeach
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input type="hidden" class="form-control" name="komponen[0][aset]" value="">
                                            <label id="komponenval0" class="komponenval"></label>
                                        </td>
                                        @foreach($perawatan as $kolom)
                                            <td>
                                                <input type="text" class="form-control" name="komponen[0][{{ $kolom->name.'@'.$kolom->id }}]" value="" placeholder="W(n)">
                                            </td>
                                        @endforeach
                                        <td><button type="button" class="btn btn-danger btn-sm removeBtn" onClick="remBtn($(this))"><i class="fa fa-times"></i></button></td>
                                    </tr>
                                </tbody>
                        </table>
                        </div>
                        <!-- ./end table detail -->

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
<script>
$(document).ready(function () {
    $(".select2").select2();

    if ($("#aset").val() == "") {
        $("#addClone").hide();
    } else {
        $("#addClone").show();
    }

    $('#instalasi').change(function () {
        $('#aset').empty();
        $('#aset').append('<option value="">Pilih Komponen</option>');

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('mstrategi/AssetPrwSelect')}}/" + $(this).val(),
                success: function(result) {
                    // $('#asset').append(result.data);
                    $('#aset').append(result.template);
                }
            })
        }
    });

    $('#aset').change(function () {
        $("#addClone").show();
        $('#part').html('');

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('mstrategi/Partpdm')}}/" + $(this).val(),
                success: function(result) {
                    $("#beforepart").html(result);
                    // $(result).insertAfter("#beforepart");
                }
            })
        }
    });

    $("#addClone").click(function() {
        $('#tabelKomponen > tbody').find('tr:last').removeClass('hidden');
        var komponenid = $("#kodefm").val();
        var komponen = $("#aset").find('option:selected').text();

        if (komponenid != "") {
            // fill the value mumet juuummm! >_<
            var data = null;
            var dataJson = null;
            var dataStore = (function(){
                var tmp = null;

                $.ajax({
                    async: false,
                    type: "get",
                    global:false,
                    url: "{{url('mstrategi/PrwrutinSelect')}}/" + $("#aset").val(),
                    success: function(result) {
                        tmp = result;
                    }
                });

                return {getResult : function()
                {
                    if (tmp) return tmp;
                }};
            })();

            if ($(dataStore.getResult()).length > 0) {
                data = $(dataStore.getResult())[0];
                dataJson = JSON.parse(data.perawatan);
            }

            if ($("#komponenval0").text().length > 0) {
                var mdCopy = $("#tabelKomponen > tbody > tr:last").clone().appendTo('#tabelKomponen > tbody');
                console.log(dataJson);
                $.each($(mdCopy).find('input'), function(){
                    var n = parseInt($(this).attr('name').match(/\d/g), 10);
                    var startString = $(this).attr('name').split("[")[0];
                    var endString = $(this).attr('name').split("][")[1];                
                    //increment the number
                    n++;
                    //rebuild the string and empty the value
                    $(this).attr('name', startString + "[" + n + "][" + endString);
                    $('input[name="komponen['+ n + '][' + endString +'"').val("");

                    // set the komponen label
                    $('input[name="komponen[' + n + '][aset]"').val($("#aset").val());

                    /*Fill the value in edit case*/
                    if (dataJson !== null) {
                        Object.keys(dataJson).map(function(key, index) {
                            $('input[name="komponen['+ n + '][' + key +']"').val(dataJson[key]);
                        });
                    }

                    // wes wes mbuhkah
                    $('#tabelKomponen > tbody').find('tr:last').find('label').prop('id','komponenval'+n).html(komponen);
                    $('#tabelKomponen > tbody').find('tr:last').find('.removeBtn').show();
                    $('#tabelKomponen > tbody').find('tr:last').find('#baris0').attr('id', 'baris'+n);
                });
            } else {
                /*Fill the value in edit case*/
                if (dataJson !== null) {
                    Object.keys(dataJson).map(function(key, index) {
                        console.log(key + ' => ' + dataJson[key]);
                        $('input[name="komponen[0][' + key +']"').val(dataJson[key]);
                    });
                }

                $('input[name="komponen[0][aset]"').val($("#aset").val());
                $('#tabelKomponen > tbody').find('tr:last').find('label').prop('id','komponenval0').html(komponen);
            }
        }
    });
});

var remBtn = function(e) {
    console.log(e);
    $(e).closest('tr').remove();
}
</script>
@endsection