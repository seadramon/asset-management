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
            <h5 class="card-title">View Asset</h5>
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
            <fieldset class="mb-3">
                <!-- <legend class="text-uppercase font-size-sm font-weight-bold">Basic inputs</legend> -->
                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Kategori</label>
                    <label class="col-form-label col-lg-10"> : {!! !empty($data->kategori)?$data->kategori->name:'' !!}</label>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Sub Kategori</label>
                    <label class="col-form-label col-lg-10"> : {!! !empty($data->subkategori)?$data->subkategori->name:'' !!}</label>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Sub Sub Kategori</label>
                    <label class="col-form-label col-lg-10"> : {!! !empty($data->subsubkategori)?$data->subsubkategori->name:'' !!}</label>
                </div>
                <div class="row">
                    <!--sisi kiri-->
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">No Aktiva</label>
                            <label class="col-form-label col-md-8"> : {!! $data->no_aktiva !!}</label>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">No SPK</label>
                            <label class="col-form-label col-md-8"> : {!! $data->no_spk !!}</label>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Nama Aset</label>
                            <label class="col-form-label col-md-8"> : {!! $data->nama_aset !!}</label>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Jumlah</label>
                            <label class="col-form-label col-md-8"> : {!! $data->jumlah !!}</label>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Latitude</label>
                            <label class="col-form-label col-md-8"> : {!! $data->lat !!}</label>
                        </div>
                    </div>
                    <!--sisi kanan-->
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">No SPMU</label>
                            <label class="col-form-label col-md-8"> : {!! $data->no_spmu !!}</label>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">No BK</label>
                            <label class="col-form-label col-md-8"> : {!! $data->no_bk !!}</label>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Nomor Urut</label>
                            <label class="col-form-label col-md-8"> : {!! $data->nomor_urut !!}</label>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-md-4">Satuan</label>
                            <label class="col-form-label col-md-8"> : {!! $data->satuan !!}</label>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Longitude</label>
                            <label class="col-form-label col-md-8"> : {!! $data->lon !!}</label>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Instalasi</label>
                    <label class="col-form-label col-lg-10"> : 
                        {{ $data->instalasi->name  ?? '-' }}
                    </label>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Lokasi</label>
                    <label class="col-form-label col-lg-10"> : 
                        {{ $data->lokasi->name  ?? '-' }}
                    </label>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Ruangan</label>
                    <label class="col-form-label col-lg-10"> : 
                        {{ $data->ruangan->name ?? '-' }}
                    </label>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Masa Pemeliharaan</label>
                    <label class="col-form-label col-lg-10"> : 
                        @if (!empty($data->pemeliharaan_start) || !empty($data->pemeliharaan_end))
                            {{ !empty($data->pemeliharaan_start)?date("Y-m-d", strtotime($data->pemeliharaan_start)):"" . "s/d" . !empty($data->pemeliharaan_end)?date("Y-m-d", strtotime($data->pemeliharaan_end)):"" }}
                        @endif
                    </label>
                </div>
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Tanggal Upload</label>
                            <label class="col-form-label col-md-8"> : 
                                {{date("Y-m-d", strtotime($data->ts_create))}}
                            </label>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Tanggal Pasang</label>
                            <label class="col-form-label col-md-8"> : 
                                {{(@$data->tgl_pasang)? date('m/d/Y',strtotime($data->tgl_pasang)) : ''}}
                            </label>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Tanggal Operasi</label>
                            <label class="col-form-label col-md-8"> : 
                                {{(@$data->tgl_operasi) ? date('m/d/Y',strtotime($data->tgl_operasi)) : ''}}
                            </label>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Tanggal Pemindahan</label>
                            <label class="col-form-label col-md-8"> : 
                                {{ !empty($data->pindah_tgl_pindah)?date("Y-m-d", strtotime($data->pindah_tgl_pindah)) : ""}}
                            </label>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Tanggal Perkiraan Sisa Umur Teknis</label>
                            <label class="col-form-label col-md-8"> : 
                                {{(@$data->tgl_perkiraan_sut) ? date('m/d/Y',strtotime($data->tgl_perkiraan_sut)) : ''}}
                            </label>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-md-4">Kondisi</label>
                            <label class="col-form-label col-md-8"> : 
                                {{ $data->kondisi->name }}
                            </label>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-md-4">Keterangan</label>
                            <label class="col-form-label col-md-8">: 
                                {!! $data->keterangan or '' !!}
                            </label>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-md-4">Foto</label>
                            <div class="col-md-8"> : 
                                @if (!empty($data->foto))
                                    <?php 
                                    $foto = str_replace("/files", "", $data->foto->foto_file);
                                    ?>
                                    <img src="{{ url('pic-api/gambar/'.str_replace('/', '&', $foto), 'sftp-aset-img') }}" width="300px" height="300px">
                                @endif
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Update Terakhir</label>
                            <label class="col-form-label col-md-8"> : 
                                {{date("Y-m-d H:i:s", strtotime($data->ts_update))}}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Tahun Pasang</label>
                            <label class=" col-form-label col-md-8"> : 
                                {{$data->tahun_pasang or ''}}
                            </label>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Nilai Perolehan</label>
                            <label class="col-form-label col-md-8"> : 
                                {{ $data->harga or '' }}
                            </label>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Total Umur Perkiraan</label>
                            <label class="col-form-label col-md-8"> : 
                                {{ '' }}
                            </label>
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
                            <label class="col-form-label col-md-8"> : 
                                {{$data->kode_fm or ''}}
                            </label>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Bagian</label>
                            <label class="col-form-label col-md-8"> : 
                                {{$data->bagiannya->name or ''}}
                            </label>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Nama Penyedia</label>
                            <label class="col-form-label col-md-8"> : 
                                {{$data->penyedia or ''}}
                            </label>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Nama PPK</label>
                            <label class="col-form-label col-md-8"> : 
                                {{$data->ppk or ''}}
                            </label>
                        </div>
                    </div>                                        
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
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
                                        {!! $spek !!}
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
</script>
@stop