@extends('layouts.main')

@section('title', 'Depresiasi - Asset Management')

@section('pagetitle', 'Depresiasi - Add/Update')

@section('content') 
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="kondisi-form">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Depresiasi</h5>
            </div>

            <div class="card-body">
                <!-- BEGIN FORM-->
                @if (isset($data))
                    {!! Form::model($data, ['route' => ['depresiasi::simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('depresiasi::simpan'), 'class' => 'form-horizontal', 'autocomplete' => 'off']) !!}
                @endif
                    <fieldset class="mb-3">
                        <input type="hidden" class="hidden" name="_token" value="{{csrf_token()}}">

                        @include('components.select_aset')

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Periode</label>
                            <div class="col-lg-10">
                                {!! Form::text('periode', $arrdata['periode'], ['class'=>'form-control', 'id'=>'monthpicker', 'required']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Depresiasi Bulanan</label>
                            <div class="col-lg-10">
                                {!! Form::number('depresiasi_bulanan', null, ['class'=>'form-control', 'id'=>'depresiasi_bulanan', 'required']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Depresiasi tahunan</label>
                            <div class="col-lg-10">
                                {!! Form::number('depresiasi_tahunan', null, ['class'=>'form-control', 'id'=>'depresiasi_tahunan', 'required']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Akumulasi Depresiasi</label>
                            <div class="col-lg-10">
                                {!! Form::number('akumulasi_depresiasi', null, ['class'=>'form-control', 'id'=>'akumulasi_depresiasi', 'required']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Nilai Aset Terakhir</label>
                            <div class="col-lg-10">
                                {!! Form::number('nilai_aset', null, ['class'=>'form-control', 'id'=>'nilai_aset', 'required']) !!}
                            </div>
                        </div>
                    </fieldset>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary legitRipple">Simpan</button>
                        <a href="{{route('depresiasi::index')}}" class="btn btn-light legitRipple">Kembali</a>
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

    $('#instalasi').change(function () {
        $('#lokasi').empty();
        $('#lokasi').append('<option value="">- Pilih Lokasi -</option>');

        $('#ruang').empty();
        $('#ruang').append('<option value="">- Pilih Ruang -</option>');

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('api-general/master/combo-lokasi')}}/" + $(this).val(),
                success: function(result) {
                    $('#lokasi').append(result.data);
                }
            })

            selectAset();
        }
    });

    $('#lokasi').change(function () {
        $('#ruang').empty();
        $('#ruang').append('<option value="">- Pilih Ruang -</option>');

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('api-general/master/combo-ruang')}}/" + $(this).val(),
                success: function(result) {
                    $('#ruang').append(result.data);
                }
            })

            selectAset();
        }
    });

    $('#ruang').change(function () {
        if ($(this).val() != '') {
            selectAset();
        }
    });

    function selectAset()
    {
        $('#aset').empty();
        $('#aset').append('<option value="">- Pilih Aset -</option>');

        var instalasi = $("#instalasi").val();
        var lokasi = $("#lokasi").val();
        var ruang = $("#ruang").val();

        var query = new URLSearchParams({
          instalasi : instalasi, 
          lokasi : lokasi,
          ruang : ruang,
        });

        console.log(query.toString());

        $.ajax({
            type: "get",
            url: "{{url('api-general/master/combo-aset')}}?" + query,
            success: function(result) {
                $('#aset').append(result.data);
            }
        })
    }
});
</script>
@endsection