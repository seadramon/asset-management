@extends('layouts.main')

@section('title', 'Master Komponen - Asset Management')

@section('pagetitle', 'Master Komponen')

@section('content') 
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card {{(empty($data))?'hidden':''}}" id="komponen-form">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Komponen Forms</h5>
            </div>

            <div class="card-body">
                <!-- BEGIN FORM-->
                <div class="alert alert-info alert-styled-right alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    Mengubah Kode Form dapat mempengaruhi data pada Penjadwalan perawatan rutin. Setting kembali PdM, 52 Weeks dan 4 Week pada aset yang telah diganti kode form nya agar sesuai Part Perawatan dengan Kode Form tersebut.
                </div>

                @if (!empty($data))
                    {!! Form::model($data, ['route' => ['master::komponen-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('master::komponen-simpan'), 'class' => 'form-horizontal']) !!}
                @endif
                    <fieldset class="mb-3">
                        <input type="text" class="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="text" class="hidden" name="tipe" value="{{(empty($data))?'0':'1'}}">
                        <input type="text" class="hidden" name="kode" value="{{(empty($data))?'':$data->id }}">
                            
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Equipment</label>
                            <div class="col-lg-10">
                                @if (empty($data))
                                    {!! Form::select('equipment_id', $equipment, null, ['class'=>'form-control select2', 'id'=>'equipment_id', 'style'=>'width:100%']) !!}
                                @else
                                    {!! Form::text("namaequipment", $namaequipment, ['class'=>'form-control', 'id'=>'namaequipment', 'disabled']) !!}

                                    {!! Form::hidden('equipment_id', $data->id, ['class'=>'form-control', 'id'=>'id', 'readonly']) !!}
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Komponen</label>
                            <div class="col-lg-8">
                                {!! Form::select('komponen', $komponen, null, ['class'=>'form-control select2', 'id'=>'komponen', 'style'=>'width:100%']) !!}
                            </div>
                            <div class="col-lg-2">
                                <button type="button" id="addClone" class="btn btn-success legitRipple"> 
                                    <i class="fa fa-plus"></i> Komponen
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <table class="table" id="tabelKomponen">
                            <thead>
                                <tr>                                    
                                    <th>Nama Komponen</th>
                                    <th>Bagian</th>
                                    <th>Kode Form</th>
                                    <th>Availability</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (!empty($komponens))
                                    <?php $i = 0; ?>
                                    <input type="hidden" name="currentId" value="{{$currentKomponen}}">
                                    @foreach($komponens as $row)
                                        <tr>
                                            <td>
                                                <label id="komponenval{{$i}}" class="komponenval">{{ $row->nama_aset }}</label>
                                                <input type="hidden" class="form-control" name="komponen[{{$i}}][komponen_id]" value="{{ $row->id }}">
                                            </td>
                                            <td>
                                                {!! Form::select("komponen[$i][bagian]", $bagian, $row->bagian, ['class'=>'form-control komponenselect2', 'style'=>'width:100%']) !!}
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" placeholder="Masukkan Kode Form" name="komponen[{{$i}}][kode_fm]" value="{{ $row->kode_fm }}">
                                            </td>
                                            <td>
                                                <?php 
                                                $checked = '';
                                                if ($row->availability > 0) {
                                                    $checked = "checked";
                                                }
                                                ?>
                                                <input type="checkbox" name="komponen[{{$i}}][availability]" value="1" {{ $checked }}> Availability
                                            </td>
                                            <td><button type="button" class="btn btn-danger btn-sm removeBtn" onClick="remBtn($(this))"><i class="fa fa-times"></i></button></td>
                                        </tr>
                                        <?php $i++; ?>
                                    @endforeach
                                @else
                                    <tr class="hidden">
                                        <td>
                                            <label id="komponenval0" class="komponenval"></label>
                                            <input type="hidden" class="form-control" name="komponen[0][komponen_id]" value="">
                                        </td>
                                        <td>
                                            {!! Form::select('komponen[0][bagian]', $bagian, null, ['class'=>'form-control komponenselect2', 'style'=>'width:100%']) !!}
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" placeholder="Masukkan Kode Form" name="komponen[0][kode_fm]">
                                        </td>
                                        <td>
                                            <input type="checkbox" name="komponen[0][availability]" value="1"> Availability
                                        </td>
                                        <td><button type="button" class="btn btn-danger btn-sm removeBtn" onClick="remBtn($(this))"><i class="fa fa-times"></i></button></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        </div>
                    </fieldset>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary legitRipple">Simpan</button> 
                        <a href="{{route('master::komponen-link')}}">
                            <button type="button" class="btn btn-light legitRipple">Kembali</button></a>
                    </div>
                </form>
                <!-- END FORM-->
            </div>
        </div>

        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="card {{empty($data)?'':'hidden'}}" id="komponen-data">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Komponen List</h5>
            </div>

            <div class="card-body">
                @foreach ($errors->all() as $message)
                    <div class="alert alert-danger alert-styled-right alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        {{ $message }}
                    </div>
                @endforeach
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
                <button type="button" id="tambah-btn" class="btn btn-success legitRipple"> 
                    <i class="fa fa-plus"></i> Tambah Baru
                </button>

                <table class="table datatable-basic" id="tabel">
                    <thead>
                        <tr>                                    
                            <th>ID</th>
                            <th>Bagian</th>
                            <th>Kode Aset</th>
                            <th>Nama</th>
                            <th>Equipment</th>
                            <th>Instalasi</th>
                            <th>Menu</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
            </div>
        </div>
        <!-- END EXAMPLE TABLE PORTLET-->
        <!-- END PAGE BASE CONTENT -->
    <!-- /form inputs -->
@endsection

@section('js')
<script src="{{asset('global_assets/plugins/datatables/datatables.min.js')}}" type="text/javascript"></script>
<script src="{{asset('global_assets/plugins/select2/js/select2.full.min.js')}}" type="text/javascript"></script>
<script>
$(document).ready(function () {
    // $('#tabelKomponen > tbody').find('tr:first').find('.removeBtn').hide();

    if ($("#komponen").val() == "") {
        $("#addClone").hide();
    } else {
        $("#addClone").show();
    }

    var data = "{{count((array)$data)}}";
    if (data > 0) {
        $("[name=template]").val("{{@$data->ms_template_id}}").change();
    }
    $('#tabel').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('master::komponen-data') }}",
        "columns": [
            {data: 'id', defaultContent: '-'},
            {data: 'bagiannya.name', name: 'master.name', defaultContent: '-',searchable: false},
            {data: 'kode_aset', name: 'kode_aset', defaultContent: '-'},
            {data: 'nama_aset', defaultContent: '-'},
            {data: 'p_equipment', defaultContent: '-',searchable: false},
            {data: 'instalasi.name', name: 'instalasi.name', defaultContent: '-'},
            {data: 'menu', orderable: false, searchable: false}
        ],
        "order": [[0, 'asc']],
        "autoWidth": false,
        "dom": '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
        "pagingType": "simple_numbers",
        "language": {
            search: '<span>Filter:</span> _INPUT_',
            searchPlaceholder: 'Type to filter...',
            lengthMenu: '<span>Show:</span> _MENU_',
            paginate: { 'first': 'First', 'last': 'Last', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
        }
    });

    $(".select2").select2();
    $(".komponenselect2").select2();

    $("#tambah-btn").click(function () {
        $("#komponen-form").removeClass('hidden');
        $("#komponen-data").addClass('hidden');
    });

    $("#komponen").change(function() {
        if ($(this).val() != "") {
            $("#addClone").show();
        }
    })
});

    var jmlKomponen = <?php echo count($komponens) ?>;

    if (jmlKomponen > 0) {
        var icl= jmlKomponen;
    } else {
        var icl= 0;
    }
// var icl = 0;
    $("#addClone").click(function() {
        console.log(icl);
        $('#tabelKomponen > tbody').find('tr:last').removeClass('hidden');

        var komponenid = $("#komponen").val();
        var komponen = $("#komponen").find('option:selected').text();

        if (komponenid != "") {
            if (icl > 0) {
                $(".komponenselect2").select2('destroy');        
                $("#tabelKomponen > tbody > tr:last").clone().appendTo('#tabelKomponen > tbody');
                $(".komponenselect2").select2();        

                $('#tabelKomponen > tbody').find('tr:last').find('.removeBtn').show();
            } else {
                $('#komponenval0').html(komponen);
            }

            // $('.komponenval').html(komponen);
            $('#tabelKomponen > tbody').find('tr:last').find('label').prop('id','komponenval'+icl).html(komponen);
            $('#tabelKomponen > tbody').find('tr:last').find('input[type=hidden]').prop('name','komponen['+icl+'][komponen_id]').val(komponenid);
            $('#tabelKomponen > tbody').find('tr:last').find('select').prop('name','komponen['+icl+'][bagian]');
            $('#tabelKomponen > tbody').find('tr:last').find('input[type=text]').prop('name','komponen['+icl+'][kode_fm]').val('');
            $('#tabelKomponen > tbody').find('tr:last').find('input[type=checkbox]').prop('name','komponen['+icl+'][availability]');

            icl++;
        }
  });

  var remBtn = function(e) {
    /*var trLen = $("#tabelKomponen tr").length;
    console.log(icl);*/
    // if (trLen == 2) {        
        // $('#tabelKomponen > tbody').find('tr:last').addClass('hidden');
        // next
        /*$('#tabelKomponen > tbody').find('tr:last').find('label').prop('id','komponenval0').html('');
        $('#tabelKomponen > tbody').find('tr:last').find('input[type=hidden]').prop('name','komponen[0][komponen_id]').val('');
        $('#tabelKomponen > tbody').find('tr:last').find('select').prop('name','komponen[0][bagian]');
        $('#tabelKomponen > tbody').find('tr:last').find('input[type=text]').prop('name','komponen[0][kode_fm]').val('');*/
    // } else {
        $(e).closest('tr').remove();
    // }
  }
</script>
@endsection