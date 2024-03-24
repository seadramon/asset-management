@extends('layouts.main')

@section('title', 'Home - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('content')
    <!-- Form inputs -->
    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title">Form Entri Asset</h5>
            <!--
            <div class="header-elements">
                <div class="list-icons">
                    <a class="list-icons-item" data-action="collapse"></a>
                    <a class="list-icons-item" data-action="reload"></a>
                    <a class="list-icons-item" data-action="remove"></a>
                </div>
            </div>
            -->
        </div>

        <div class="card-body">
            <form action="{{url('/aset/entri')}}" method="post" class="form-horizontal" enctype="multipart/form-data">
            	<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">
                <input type="text" class="hidden" name="tipe" value="{{($data=='')?'0':'1'}}">
                <input type="text" class="hidden" name="kode" value="{{($data=='')?'':$data->id }}">
                <fieldset class="mb-3">
                    <!-- <legend class="text-uppercase font-size-sm font-weight-bold">Basic inputs</legend> -->
                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Kategori</label>
                        <div class="col-lg-10">
                            <select class="form-control select2" id="kat" name="kategori" style="width: 100%;">
                                <option value="">Pilih Kategori</option>
                                @foreach($kategori as $row)
                                <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Sub Kategori</label>
                        <div class="col-lg-10">
                            <select class="form-control select2" name="subkategori" style="width: 100%;">
                                <option value="">Pilih Sub Kategori</option>
                                @if($data!='')
                                @foreach($subkategori as $row)
                                <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Sub Sub Kategori</label>
                        <div class="col-lg-10">
                            <select class="form-control select2" name="subsubkategori" style="width: 100%;">
                                <option value="">Pilih Sub Sub Kategori</option> 
                                @if($data!='')
                                @foreach($subsubkategori as $row)
                                <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <!--sisi kiri-->
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">No Aktiva</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control input-circle" name="no_aktiva" placeholder="Masukkan Nomor Aktiva " value="{{$data->no_aktiva or ''}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">No SPK</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control input-circle" name="no_spk" placeholder="Masukkan Nomor SPK" value="{{$data->no_spk or ''}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Nama Aset</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control input-circle" name="nama_aset" placeholder="Masukkan Nama Aset" value="{{$data->nama_aset or ''}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Jumlah</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control input-circle" name="jumlah" placeholder="Masukkan Jumlah Aset" value="{{$data->jumlah or ''}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Latitude</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control input-circle" name="lat" placeholder="Masukkan Latitude" value="{{$data->lat or ''}}">
                                </div>
                            </div>
                        </div>
                        <!--sisi kanan-->
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">No SPMU</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control input-circle" name="no_spmu" placeholder="Masukkan Nomor SPMU" value="{{$data->no_spmu or ''}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">No BK</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control input-circle" name="no_bk" placeholder="Masukkan Nomor BK" value="{{$data->no_bk or ''}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Nomor Urut</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control input-circle" name="nomor_urut" placeholder="Masukkan Nomor Urut" value="{{$data->nomor_urut or ''}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-4">Satuan</label>
                                <div class="col-md-8">
                                    <select class="form-control input-circle select2" name="satuan" style="width: 100%;">
                                        <option value="">Pilih Satuan</option>                                            
                                        <option value="Unit">Unit</option>                                            
                                        <option value="Buah">Buah</option>                                            
                                        <option value="Paket">Paket</option>                                            
                                        <option value="Rol">Rol</option>                                            
                                        <option value="Bidang">Bidang</option>                                            
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Longitude</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control input-circle" name="lon" placeholder="Masukkan Longitude" value="{{$data->lon or ''}}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Instalasi</label>
                        <div class="col-lg-10">
                            <select class="form-control select2" name="instalasi" style="width: 100%;">
                                <option value="">Pilih Instalasi</option>
                                @foreach($instalasi as $row)
                                <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Lokasi</label>
                        <div class="col-lg-10">
                            <select class="form-control select2" name="lokasi" style="width: 100%;">
                                <option value="">Pilih Lokasi</option>
                                @if($data!='')
                                @foreach($lokasi as $row)
                                <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Ruangan</label>
                        <div class="col-lg-10">
                            <select class="form-control select2"  name="ruangan" style="width: 100%;">
                                <option value="">Pilih Ruangan</option> 
                                @if($data!='')
                                @foreach($ruangan as $row)
                                <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Masa Pemeliharaan</label>
                        <div class="col-lg-10">
                            <input type="text" name="masapemeliharaan" class="form-control daterange-basic" value="{{ $pemeliharaan }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Tanggal Pasang</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control input-circle date-picker" readonly name="tgl_pasang" placeholder="Masukkan Tanggal Pasang" value="{{(@$data->tgl_pasang)? date('m/d/Y',strtotime($data->tgl_pasang)) : ''}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Tanggal Operasi</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control input-circle date-picker" readonly name="tgl_operasi" placeholder="Masukkan Tanggal Operasi" value="{{(@$data->tgl_operasi) ? date('m/d/Y',strtotime($data->tgl_operasi)) : ''}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Tanggal Perkiraan Sisa Umur Teknis</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control input-circle date-picker" readonly name="tgl_perkiraan" placeholder="Masukkan Tanggal Operasi" value="{{(@$data->tgl_perkiraan_sut) ? date('m/d/Y',strtotime($data->tgl_perkiraan_sut)) : ''}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-4">Kondisi</label>
                                <div class="col-md-8">
                                    <select class="form-control input-circle select2" name="kondisi" style="width: 100%;">
                                        <option value="">Pilih Kondisi</option>   
                                        @foreach($kondisi as $row)
                                        <option value="{{$row->id}}">{{$row->name}}</option>                                                       
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-4">Keterangan</label>
                                <div class="col-md-8">
                                    <textarea name="keterangan" class="form-control" rows="2">{{$data->keterangan or ''}}</textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-4">Foto</label>
                                <div class="col-md-8">
                                    @if (!empty($data->foto))
                                        <?php 
                                        $foto = str_replace("/files", "", $data->foto->foto_file);
                                        ?>
                                        <img src="{{ url('pic-api/gambar/'.str_replace('/', '&', $foto), 'sftp-aset-img') }}" width="300px" height="300px">
                                    @endif
                                    <input type="file" name="image" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Tahun Pasang</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control input-circle" readonly name="tahun_pasang" placeholder="Masukkan Tahun Pasang" value="{{$data->tahun_pasang or ''}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Nilai Perolehan</label>
                                <?php 
                                $np = !empty($data->harga)?rupiah($data->harga, 0, '.', ',', false):''; 
                                ?>
                                <div class="col-md-8">
                                    <input type="text" class="form-control input-circle numberkey" name="harga" placeholder="Masukkan Nilai Perolehan" value="{{ $np }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Total Umur Perkiraan</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control input-circle" name="a" placeholder="Masukkan Total Umur Perkiraan" value="{{''}}">
                                </div>
                            </div>
                            <!-- <div class="form-group row">
                                <label class="col-md-12 col-form-label"><input type="checkbox" class="form-control icheck" name="sukucadang" value="0" data-checkbox="icheckbox_square-grey"> Suku Cadang</label>
                                <div class="col-md-8">
                                    <div class="icheck-inline">
                                        
                                    </div>
                                </div>
                            </div> -->
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Kode Form Monitoring</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control input-circle" name="kode_fm" placeholder="Kode Form Monitoring" value="{{$data->kode_fm or ''}}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Bagian</label>
                                <div class="col-md-8">
                                    {!! Form::select("bagian", $bagian, !empty($data->bagian)?$data->bagian:'', ['class'=>'form-control select2', 'style'=>'width:100%']) !!}
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Nama Penyedia</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control input-circle" name="penyedia" placeholder="Nama Penyedia" value="{{$data->penyedia or ''}}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Nama PPK</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control input-circle" name="ppk" placeholder="Nama PPK" value="{{$data->ppk or ''}}">
                                </div>
                            </div>
                        </div>                                        
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card card-collapsed">
                                <div class="card-header bg-primary text-white header-elements-inline">
                                    <h5 class="card-title">Spesifikasi Aset</h5>
                                    <div class="header-elements">
                                        <div class="list-icons">
                                            <a class="list-icons-item" data-action="collapse"></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">   
                                        <div class="col-md-12">
                                            <?php $n = 1; ?>
                                            @foreach($spesifikasi as $row)
                                            <div class="card card-collapsed">
                                                <div class="card-header bg-secondary text-white header-elements-inline">
                                                    <h5 class="card-title">{{$row->name}}</h5>
                                                    <div class="header-elements">
                                                        <div class="list-icons">
                                                            <a class="list-icons-item" data-action="collapse"></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">   
                                                        <div class="col-md-12">
                                                            @foreach($row->item as $baris)
                                                            <div class="form-group row">
                                                                <label class="col-md-3 col-form-label">{{$baris->name}}</label>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="hidden" name="kode_spek[]" value="{{$row->id.'#'.$baris->id}}">
                                                                    <input type="text" class="hidden" name="nama_spek[]" value="{{trim($row->name).' | '.trim($baris->name)}}">
                                                                    <input type="text" class="form-control input-circle" name="nilai_spek[]" placeholder="Masukkan Nilai Perolehan" value="{{(@$spekdata[$row->id][$baris->id])?$spekdata[$row->id][$baris->id]['val']:''}}">
                                                                </div>
                                                                <label class="col-md-3 col-form-label">Satuan</label>
                                                                <?php $temp = explode(',', $baris->satuan); ?>
                                                                <div class="col-md-3">
                                                                    <select class="form-control input-circle select2" name="satuan_spek[]" style="width: 100%;">
                                                                        @foreach($temp as $t)
                                                                        @if(trim(@$spekdata[$row->id][$baris->id]['unit'])==trim($t))
                                                                        <option selected value="{{trim($t)}}">{{trim($t)}}</option>                                                       
                                                                        @else
                                                                        <option value="{{trim($t)}}">{{trim($t)}}</option>                                                       
                                                                        @endif
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php $n++; ?>
                                            @endforeach
                                            <?php /*
                                            <div class="portlet-body portlet-collapsed">
                                                <div class="panel-group accordion" id="accordion3">
                                                    <?php $n = 1; ?>
                                                    @foreach($spesifikasi as $row)
                                                    <div class="panel panel-default">
                                                        <div class="panel-heading">
                                                            <h4 class="panel-title">
                                                                <a class="text-default collapsed" data-toggle="collapse" data-parent="#accordion3" href="#collapse_{{$n}}"> {{$row->name}} </a>
                                                            </h4>
                                                        </div>
                                                        <div id="collapse_{{$n}}" class="collapse">
                                                            <div class="panel-body">
                                                                @foreach($row->item as $baris)
                                                                <div class="form-group row">
                                                                    <label class="col-md-3 col-form-label">{{$baris->name}}</label>
                                                                    <div class="col-md-3">
                                                                        <input type="text" class="hidden" name="kode_spek[]" value="{{$row->id.'#'.$baris->id}}">
                                                                        <input type="text" class="hidden" name="nama_spek[]" value="{{trim($row->name).' | '.trim($baris->name)}}">
                                                                        <input type="text" class="form-control input-circle" name="nilai_spek[]" placeholder="Masukkan Nilai Perolehan" value="{{(@$spekdata[$row->id][$baris->id])?$spekdata[$row->id][$baris->id]['val']:''}}">
                                                                    </div>
                                                                    <label class="col-md-3 col-form-label">Satuan</label>
                                                                    <?php $temp = explode(',', $baris->satuan); ?>
                                                                    <div class="col-md-3">
                                                                        <select class="form-control input-circle select2" name="satuan_spek[]" style="width: 100%;">
                                                                            @foreach($temp as $t)
                                                                            @if(trim(@$spekdata[$row->id][$baris->id]['unit'])==trim($t))
                                                                            <option selected value="{{trim($t)}}">{{trim($t)}}</option>                                                       
                                                                            @else
                                                                            <option value="{{trim($t)}}">{{trim($t)}}</option>                                                       
                                                                            @endif
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php $n++; ?>
                                                    @endforeach
                                                </div>
                                            </div>
                                            */ ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <div class="text-right">
                    <button type="submit" class="btn btn-primary">Submit <i class="icon-paperplane ml-2"></i></button>
                </div>
            </form>
        </div>
    </div>
    <!-- /form inputs -->
@endsection

@section('js')
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js"></script>

<script src="{{ url('global_assets/js/plugins/ui/moment/moment.min.js') }}"></script>
<script src="{{ url('global_assets/js/plugins/pickers/daterangepicker.js') }}"></script>
<script type="text/javascript">
    var cekData = "{{!empty($data)}}";
    //alert(data);
    if (cekData) {
//alert(data);
        $("#kat").val("{{@$data->kategori_id}}").trigger('change');
        $("[name=subkategori]").val("{{@$data->sub_kategori_id}}").change();
        $("[name=subsubkategori]").val("{{@$data->sub_sub_kategori_id}}").change();
        $("[name=instalasi]").val("{{@$data->instalasi_id}}").trigger('change');
        $("[name=lokasi]").val("{{@$data->lokasi_id}}").change();
        $("[name=ruangan]").val("{{@$data->ruang_id}}").change();
        $("[name=kondisi]").val("{{@$data->kondisi_id}}").change();
        $("[name=satuan]").val("{{ucwords(@$data->satuan)}}").change();
        /*if ({{$data->is_sukucadang or '1'}} == 0){
            $("[name=sukucadang]").iCheck('check');
        }*/
//        $("[name=kategori]").change();
    }
    $(".select2").select2();
    $(".date-picker").datepicker({
//        format: 'dd/mm/yyyy',
        autoclose: !0,
    });

    /*Daterange masa pemeliharaan*/

    $('.daterange-basic').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD',
                cancelLabel: 'Clear'
            },
            autoUpdateInput: false
        }, function(chosen_date) {
            $('.daterange-basic').val(chosen_date.format('YYYY-MM-DD'));
        });

    $('.daterange-basic').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
    });

    $('.daterange-basic').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    /*End Daterange Pemeliharaan*/

    $("[name=tahun_pasang]").datepicker({
        format: 'yyyy',
        autoclose: !0,
        maxViewMode: 2,
        minViewMode: 2,
        clearBtn: true
    });
    $('#kat').change(function () {
        $('[name=subkategori]').empty();
        $('[name=subkategori]').append('<option value="">Pilih Sub Kategori</option>');
        $('[name=subsubkategori]').empty();
        $('[name=subsubkategori]').append('<option value="">Pilih Sub Sub Kategori</option>');
        if ($(this).val() == '') {

        } else {
            $.ajax({
                type: "get",
                url: "{{url('master/SubKategoriSelect')}}/" + $(this).val(),
//                data: {recid: $(this).val()},
                success: function (result) {
                    $('[name=subkategori]').append(result.data);
                }
            });
        }
    });
    $('[name=subkategori]').change(function () {
        $('[name=subsubkategori]').empty();
        $('[name=subsubkategori]').append('<option value="">Pilih Sub Sub Kategori</option>');
        if ($(this).val() == '') {

        } else {
            $.ajax({
                type: "get",
                url: "{{url('master/SubSubKategoriSelect')}}/" + $(this).val(),
//                data: {recid: $(this).val()},
                success: function (result) {
                    $('[name=subsubkategori]').append(result.data);
                }
            });
        }
    });
    $('[name=instalasi]').change(function () {
        $('[name=lokasi]').empty();
        $('[name=lokasi]').append('<option value="">Pilih Lokasi</option>');
        $('[name=ruangan]').empty();
        $('[name=ruangan]').append('<option value="">Pilih Ruangan</option>');
        if ($(this).val() == '') {

        } else {
            $.ajax({
                type: "get",
                url: "{{url('master/LokasiSelect')}}/" + $(this).val(),
//                data: {recid: $(this).val()},
                success: function (result) {
                    $('[name=lokasi]').append(result.data);
                }
            });
        }
    });
    /*$('[name=ruangan]').change(function () {
        $('[name=ruangan]').empty();
        $('[name=ruangan]').append('<option value="">Pilih Ruangan</option>');
        if ($(this).val() == '') {

        } else {
            $.ajax({
                type: "get",
                url: "{{url('master/RuanganSelect')}}/" + $(this).val(),
//                data: {recid: $(this).val()},
                success: function (result) {
                    $('[name=ruangan]').append(result.data);
                }
            });
        }
    });*/

    $('[name=lokasi]').change(function () {
        $('[name=ruangan]').empty();
        $('[name=ruangan]').append('<option value="">Pilih Ruangan</option>');

        var instalasi = $('[name=instalasi]').val();
        console.log(instalasi);

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('api-general/master/combo-ruang')}}/" + $(this).val() + "?instalasi=" + instalasi,
                success: function(result) {
                    $('[name=ruangan]').append(result.data);
                }
            })
        }
    });

    $('.numberkey').keyup(function(event) {
        // skip for arrow keys
        if(event.which >= 37 && event.which <= 40) return;

        // format number
        $(this).val(function(index, value) {
            return value
            .replace(/\D/g, "")
            .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        });
    });
</script>
@stop