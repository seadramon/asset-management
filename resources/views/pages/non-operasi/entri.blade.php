@extends('layouts.main')

@section('title', 'Aduan Non Operasi - Asset Management')

@section('pagetitle', 'Aduan Non Operasi')

@section('content') 
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="kondisi-form">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Form Aduan Non Operasi</h5>
            </div>

            <div class="card-body">
                <!-- BEGIN FORM-->
                @if (isset($data))
                    {!! Form::model($data, ['route' => ['non-operasi::aduan-simpan'], 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('non-operasi::aduan-simpan'), 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data']) !!}
                @endif
                    <fieldset class="mb-3">
                        <input type="hidden" class="hidden" name="_token" value="{{csrf_token()}}">

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Judul Kerusakan</label>
                            <div class="col-lg-10">
                                {!! Form::text('judul', null, ['class'=>'form-control', 'id'=>'judul']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Lokasi Kerusakan</label>
                            <div class="col-lg-10">
                                {!! Form::text('lokasi', null, ['class'=>'form-control', 'id'=>'lokasi']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Sifat Kerusakan</label>
                            <div class="col-lg-10">
                                {!! Form::select('sifat', $sifat, null, ['class'=>'form-control select2', 'id'=>'sifat']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">SPV Pemeliharaan yang dituju</label>
                            <div class="col-lg-10">
                                {!! Form::select('spv', $jabatan, null, ['class'=>'form-control select2', 'id'=>'spv']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Foto Kondisi Aktual</label>
                            <div class="col-lg-10">
                                @if (!empty($data->foto))
                                    <?php $dir = date('Y-m', strtotime($data->created_at)); ?>
                                    <img src="{{ url('pic-api/gambar/non-operasi&'.$dir.'&'.$data->foto) }}" width="200" height="200">
                                @endif
                                {!! Form::file('foto') !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Catatan Kerusakan</label>
                            <div class="col-lg-10">
                                {!! Form::textarea('catatan', null, ['class'=>'form-control', 'id'=>'catatan', 'placeholder' => 'Catatan Kerusakan']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Indikasi Kerusakan</label>
                            <div class="col-lg-10">
                                {!! Form::textarea('indikasi', null, ['class'=>'form-control', 'id'=>'indikasi', 'placeholder' => 'Indikasi Kerusakan']) !!}
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

    $('.pickadate').pickadate({
        format:'yyyy-mm-dd',
        formatSubmit: 'yyyy-mm-dd',
    });
});
</script>
@endsection