@extends('layouts.main')

@section('title', 'NonAktif Aset - Asset Management')

@section('pagetitle', 'NonAktif Aset')

@section('content') 
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="kondisi-form">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Form NonAktif Aset</h5>
            </div>

            <div class="card-body">
                <!-- BEGIN FORM-->
                @if (isset($data))
                    {!! Form::model($data, ['route' => ['nonaktif::nonaktif-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('nonaktif::nonaktif-simpan'), 'class' => 'form-horizontal']) !!}
                @endif
                    <fieldset class="mb-3">
                        <input type="hidden" class="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="hidden" class="hidden" name="tipe" value="{{($data=='')?'0':'1'}}">
                        <input type="hidden" class="hidden" name="kode" value="{{($data=='')?'':$data->id }}">

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Aset</label>
                            <div class="col-lg-10">
                                @if($data=='')
                                    {!! Form::select('aset_id', $aset, null, ['class'=>'form-control select2', 'id'=>'aset']) !!}
                                @else
                                    {!! Form::text('nama_aset', ($data=='')?'':$data->nama_aset, ['class'=>'form-control', 'id'=>'nama_aset', 'disabled']) !!}

                                    {!! Form::hidden('aset_id', null) !!}
                                @endif
                            </div>
                        </div>

                        @if ($data != '')
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Kondisi</label>
                                <div class="col-lg-10">
                                    {!! Form::select('kondisi_id', $kondisi, null, ['class'=>'form-control select2', 'id'=>'aset']) !!}
                                </div>
                            </div>
                        @endif
                    </fieldset>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary legitRipple">Simpan</button> 
                        <a href="{{route('nonaktif::nonaktif-index')}}">
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
<script type="text/javascript" src="{{url('global_assets/plugins/pickers/pickadate/picker.js')}}"></script>
<script type="text/javascript" src="{{url('global_assets/plugins/pickers/pickadate/picker.date.js')}}"></script>
<script>
$(document).ready(function () {
    $(".select2").select2();

    $('.pickadate').pickadate({
        format:'yyyy-mm-dd',
        formatSubmit: 'yyyy-mm-dd',
    });

    $('#aset').change(function () {
        $('.lama').empty();

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('nonaktif/AsetSelect')}}/" + $(this).val(),
                success: function(result) {
                    var res = result.data;
                    // console.log(res.instalasi.name)
                    $("#instalasi_lama").val(res.instalasi.name);
                    $("#instalasi_lama_id").val(res.instalasi.id);
                    $("#lokasi_lama").val(res.lokasi.name);
                    $("#lokasi_lama_id").val(res.lokasi.id);
                    $("#ruang_lama").val(res.ruangan.name);
                    $("#ruang_lama_id").val(res.ruangan.id);
                },
                error: function(aa, bb, cc) {
                    console.log(aa);
                }
            })
        }
    });

    $("#instalasi_baru").change(function() {
        $('[name=lokasi_baru_id]').empty();
        $('[name=lokasi_baru_id]').append('<option value="">Pilih Lokasi Baru</option>');

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('master/LokasiSelect')}}/" + $(this).val(),
                success: function(result) {
                    $('[name=lokasi_baru_id]').append(result.data);
                },
                error: function(aa, bb, cc) {
                    console.log(aa);
                }
            })
        }
    })

    $("#lokasi_baru").change(function() {
        $('[name=ruang_baru_id]').empty();
        $('[name=ruang_baru_id]').append('<option value="">Pilih Ruang Baru</option>');

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('master/RuanganSelect')}}/" + $(this).val(),
                success: function(result) {
                    $('[name=ruang_baru_id]').append(result.data);
                },
                error: function(aa, bb, cc) {
                    console.log(aa);
                }
            })
        }
    })
});
</script>
@endsection