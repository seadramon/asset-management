@extends('layouts.main')

@section('title', 'Usulan Non Operasi - Asset Management')

@section('pagetitle', 'Usulan Non Operasi')

@section('content') 
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="kondisi-form">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Form Usulan Non Operasi</h5>
            </div>
<?php //dd($data); ?>
            <div class="card-body">
                <!-- BEGIN FORM-->
                @if (isset($data))
                    {!! Form::model($data, ['route' => ['non-operasi::usulan-simpan'], 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('non-operasi::usulan-simpan'), 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data']) !!}
                @endif
                    <fieldset class="mb-3">
                        <input type="hidden" class="hidden" name="_token" value="{{csrf_token()}}">

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">PIC</label>
                            <div class="col-lg-10">
                                <div class="form-control-plaintext">{{(\Auth::check()) ? \Auth::user()->username : ''}}</div>
                                {!! Form::hidden('pic', (\Auth::check()) ? trim(\Auth::user()->userid) : '', ['class'=>'form-control', 'id'=>'pic']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Nama / Usulan Pekerjaan</label>
                            <div class="col-lg-10">
                                {!! Form::text('nama', null, ['class'=>'form-control', 'id'=>'nama']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Instalasi</label>
                            <div class="col-lg-10">
                                {!! Form::select('instalasi_id', $instalasi, null, ['class'=>'form-control select2', 'id'=>'instalasi_id']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Lokasi</label>
                            <div class="col-lg-10">
                                {!! Form::select('lokasi_id', $lokasi, null, ['class'=>'form-control select2', 'id'=>'lokasi_id']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">SPV Pemeliharaan yang dituju</label>
                            <div class="col-lg-10">
                                {!! Form::select('spv', $jabatan, null, ['class'=>'form-control select2', 'id'=>'spv']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Penggunaan Anggaran</label>
                            <div class="col-lg-10">
                                {!! Form::select('penggunaan_anggaran', $penggunaanAngg, null, ['class'=>'form-control select2', 'id'=>'penggunaan_anggaran']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Lingkup Kerja</label>
                            <div class="col-lg-10">
                                {!! Form::select('lingkup_kerja', $lingkupKerja, null, ['class'=>'form-control select2', 'id'=>'lingkup_kerja']) !!}
                            </div>
                        </div>

                        <div class="form-group row" id="asetfield">
                            <label class="col-form-label col-lg-2">Aset</label>
                            <div class="col-lg-10">
                                {!! Form::select('aset_id', $aset, null, ['class'=>'form-control select2', 'id'=>'aset_id']) !!}
                            </div>
                        </div>


                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Foto Kondisi Aktual</label>
                            <div class="col-lg-10">
                                @if (!empty($data->foto_kondisi))
                                    <?php $dir = $data->id; ?>
                                    <img src="{{ url('pic-api/gambar/non-operasi&usulan&'.$dir.'&'.$data->foto_kondisi) }}" width="200" height="200">
                                @endif
                                {!! Form::file('foto_kondisi') !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Tujuan Pekerjaan</label>
                            <div class="col-lg-10">
                                {!! Form::textarea('tujuan', null, ['class'=>'form-control', 'id'=>'tujuan', 'placeholder' => 'Tujuan Pekerjaan']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Keterangan Lainnya / Manfaat</label>
                            <div class="col-lg-10">
                                {!! Form::textarea('keterangan', null, ['class'=>'form-control', 'id'=>'keterangan', 'placeholder' => 'Keterangan Lainnya / Manfaat']) !!}
                            </div>
                        </div>
                    </fieldset>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary legitRipple">Simpan</button> 
                        <a href="{{route('non-operasi::aduan-index')}}">
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
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>
<script>
$(document).ready(function () {
    $(".select2").select2();

    $("#asetfield").hide();

    $("#lingkup_kerja").change(function() {
        $("#asetfield").hide();

        if ($(this).val() == "Perbaikan/Overhoul/ Penggantian Aset Operasi") {
            $("#asetfield").show();
        }
    });

    $('#lokasi_id').val({{ !empty($data->lokasi_id)?$data->lokasi_id:"" }});
    $('#lokasi_id').trigger('change');

    $('#instalasi_id').change(function () {
        $('#lokasi_id').empty();
        $('#lokasi_id').append('<option value="">-Pilih Lokasi-</option>');

        $('#spv').empty();
        $('#spv').append('<option value="">-Pilih SPV-</option>');

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('NonOperasi/LokasiSelect')}}/" + $(this).val(),
                success: function(result) {
                    $('#lokasi_id').append(result.template);
                }
            })

            $.ajax({
                url: "{{url('NonOperasi/jabselect')}}/" + $(this).val() + "/usulan",
                type: "get",
                success: function(result) {
                    $('#spv').append(result.template);
                }
            })
            
        }
    });

    $('#lokasi_id').change(function () {
        $('#aset_id').empty();
        $('#aset_id').append('<option value="">Pilih Aset</option>');
        var instalasi = $('#instalasi_id').val();
        console.log(instalasi);

        if ($(this).val() != '') {
            var url = "{{url('NonOperasi/AsetSelect')}}/" + $(this).val() + "?instalasi=" + instalasi;
            console.log(url);
            $.ajax({
                type: "get",
                url: url,
                success: function(result) {
                    console.log(result.template);
                    // $('#asset').append(result.data);
                    $('#aset_id').append(result.template);
                }
            })
        }
    });
});
</script>
@endsection