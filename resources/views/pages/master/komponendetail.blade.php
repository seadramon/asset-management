@extends('layouts.main')

@section('title', 'Master Komponen Detail - Asset Management')

@section('pagetitle', 'Master Komponen Detail')

@section('content') 
        <!-- BEGIN PAGE BASE CONTENT -->
        <!-- <div class="card {{(empty($data))?'hidden':''}}" id="komponendetail-form"> -->
        <div class="card" id="komponendetail-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Komponen Detail Form</h5>
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
                <form action="{{route('master::komponendetail-simpan')}}" method="post" class="form-horizontal">
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="text" class="hidden" name="tipe" value="{{(empty($data))?'0':'1'}}">
                        <input type="text" class="hidden" name="kode" value="{{(empty($data))?'':$data->id }}">
                            
	                    <div class="form-group row">
							<label class="col-form-label col-lg-2">Kode Form</label>
							<div class="col-lg-8">
								{!! Form::select('kode_fm', $kodefm, null, ['class'=>'form-control select2', 'id'=>'kodefm', 'required', 'style' => 'width:100%']) !!}
							</div>
                            <div class="col-lg-2">
                                <button type="button" id="addClone" class="btn btn-success legitRipple"> 
                                    <i class="fa fa-plus"></i> Attribut
                                </button>
                            </div>
						</div>

                        <!-- Tabel Detail -->
                        <div class="row">
                            <table class="table" id="tabelKomponen">
                            <thead>
                                <tr>                                    
                                    <th>Part</th>
                                    <th>Mode Kegagalan</th>
                                    <th>Efek Kegagalan</th>
                                    <th>Penyebab Kegagalan</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                    <!-- <tr class="hidden"> -->
                                    <tr class="starter">
                                        <td>
                                            <!-- <label id="komponenval0" class="komponenval"></label> -->
                                            <input type="text" class="form-control" name="komponen[0][part]" value="">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="komponen[0][mode_gagal]" value="">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="komponen[0][efek_gagal]" value="">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="komponen[0][penyebab_gagal]" value="">
                                        </td>
                                        <td><button type="button" class="btn btn-danger btn-sm removeBtn" onClick="remBtn($(this))"><i class="fa fa-times"></i></button></td>
                                    </tr>
                            </tbody>
                        </table>
                        </div>
                        <!-- ./end table detail -->
                	</fieldset>

                	<div class="text-right">
						<button type="submit" class="btn btn-primary legitRipple">Simpan</button> 
	                    <a href="{{route('master::komponendetail-link')}}">
	                    	<button type="button" class="btn btn-light legitRipple">Kembali</button></a>
					</div>
                </form>
                <!-- END FORM-->
            </div>
        </div>

        <!-- END PAGE BASE CONTENT -->
    <!-- /form inputs -->
@endsection

@section('js')
<script src="{{asset('global_assets/plugins/datatables/datatables.min.js')}}" type="text/javascript"></script>
<script src="{{asset('global_assets/plugins/select2/js/select2.full.min.js')}}" type="text/javascript"></script>
<script>
$(document).ready(function () {
    if ($("#kodefm").val() == "") {
        $("#addClone").hide();
    } else {
        $("#addClone").show();
    }

//    alert('aa');
    var data = "{{!empty($data)?1:0}}";
    //alert(data);
    if (data > 0) {
        //alert(data);
        $("[name=komponen]").val("{{@$data->ms_komponen_id}}").change();
    }

    $(".select2").select2();
    
    $("#tambah-btn").click(function () {
        $("#komponendetail-form").removeClass('hidden');
        $("#komponendetail-data").addClass('hidden');
    });

    $("#kodefm").change(function() {
        if ($(this).val() != "") {
            $("#addClone").show();

            $.ajax({
                type: "get",
                url: "{{url('master/KomponenDetailSelect')}}/" + $(this).val(),
                success: function(result) {
                    $("#tabelKomponen > tbody").empty();
                    $("#tabelKomponen > tbody").html(result);
                }
            });
        } else {
            $("#addClone").hide();
        }
    })
});

// var icl={{count($attribut)+1}};
// var icl = 0;
    $("#addClone").click(function() {
        $('#tabelKomponen > tbody').find('tr:last').removeClass('hidden');

        var komponenid = $("#kodefm").val();
        var komponen = $("#kodefm").find('option:selected').text();

        if (komponenid != "") {
            var mdCopy = $("#tabelKomponen > tbody > tr:last").clone().appendTo('#tabelKomponen > tbody');

            $.each($(mdCopy).find('input[type=text]'), function(){
                var n = parseInt($(this).attr('name').match(/\d/g), 10);
                var startString = $(this).attr('name').split("[")[0];
                var endString = $(this).attr('name').split("][")[1];
console.log(endString);
                //increment the number
                n++;

                //rebuild the string
                $(this).attr('name', startString + "[" + n + "][" + endString);
                $('#tabelKomponen > tbody').find('tr:last').find('.removeBtn').show();
            });
        }
  });

  var remBtn = function(e) {
    console.log(e);
    $(e).closest('tr').remove();
  }
</script>
@endsection