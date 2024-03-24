@extends('layouts.main')

@section('title', 'Proposal - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Input Metode Perbaikan')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">

            <div class="card-header header-elements-inline">
                <h5 class="card-title">Proposal Form</h5>
            </div>

            <div class="card-body">
                @if (count($errors) > 0)
                    <div class="alert alert-danger alert-styled-right alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        @foreach ($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                    </div>
                @endif
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
                
                <fieldset class="mb-3">
                    <!-- BEGIN FORM-->
                    @if ($data!='')
                        {!! Form::model($data, ['route' => ['proposal::simpan'], 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data']) !!}
                        {!! Form::hidden('id', null) !!}
                    @else
                        {!! Form::open(['url' => route('proposal::simpan'), 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data']) !!}
                    @endif

                    {!! Form::hidden($wo, $wo_id) !!}
                    {!! Form::hidden('wo', $wo) !!}

                    <input type="text" class="hidden" name="_token" value="{{csrf_token()}}">
                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Nama Pekerjaan</label>
                        <div class="col-sm-9">
                            {!! Form::textarea("nama", null, ['class'=>'form-control', 'rows' => '3', 'cols' => '3', 'id'=>'nama']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Lokasi</label>
                        <div class="col-sm-9">
                            {!! Form::textarea("lokasi", null, ['class'=>'form-control', 'rows' => '3', 'cols' => '3', 'id'=>'lokasi']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Gambaran Umum</label>
                        <div class="col-sm-9">
                            {!! Form::textarea("gambaran", null, ['class'=>'form-control', 'rows' => '3', 'cols' => '3', 'id'=>'gambaran']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Kondisi Saat Ini</label>
                        <div class="col-sm-9">
                            {!! Form::textarea("kondisi", null, ['class'=>'form-control', 'rows' => '3', 'cols' => '3', 'id'=>'kondisi']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Manfaat Secara Teknis</label>
                        <div class="col-sm-9">
                            {!! Form::textarea("manfaat_teknis", null, ['class'=>'form-control', 'rows' => '3', 'cols' => '3', 'id'=>'manfaat_teknis']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Manfaat Secara Ekonomis</label>
                        <div class="col-sm-9">
                            {!! Form::textarea("manfaat_ekonomis", null, ['class'=>'form-control', 'rows' => '3', 'cols' => '3', 'id'=>'manfaat_ekonomis']) !!}
                        </div>
                    </div>

                    <legend class="text-uppercase font-size-sm font-weight-bold">Biaya</legend>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Nomor Perkiraan</label>
                        <div class="col-sm-9">
                            <div class="form-control-plaintext">{{ $datawo->perkiraan_anggaran }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Tahun Anggaran</label>
                        <div class="col-sm-9">
                            <div class="form-control-plaintext">{{ $datawo->tahun_anggaran }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Tanggal Pekerjaan Mulai</label>
                        <div class="col-sm-9">
                            {!! Form::text("tgl_mulai", null, ['class'=>'form-control datepicker', 'id'=>'tgl_mulai']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Tanggal Pekerjaan Selesai</label>
                        <div class="col-sm-9">
                            <div class="form-control-plaintext">{{ changeDateFormat($datawo->perkiraan, 'Y-m-d H:i:s', 'Y-m-d') }}
                            {!! Form::hidden("perkiraan", changeDateFormat($datawo->perkiraan, 'Y-m-d H:i:s', 'Y-m-d'), ['class'=>'form-control', 'id'=>'perkiraan']) !!}
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Waktu</label>
                        <div class="col-sm-9">
                            {!! Form::text("waktu", null, ['class'=>'form-control', 'id'=>'waktu', 'readonly']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Spesifikasi</label>
                        <div class="col-sm-9">
                            {!! Form::text("spesifikasi", "seperti tersebut dalam PP", ['class'=>'form-control', 'id'=>'spesifikasi']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Kesimpulan</label>
                        <div class="col-sm-9">
                            {!! Form::textarea("kesimpulan", null, ['class'=>'form-control', 'rows' => '3', 'cols' => '3', 'id'=>'kesimpulan']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Foto 1</label>
                        <div class="col-sm-9">
                            @if (!empty($datawo->foto_investigasi))
                                <img src="{{ url('pic-api/gambar/perbaikan&'.$datawo->foto_investigasi) }}" width="300px" height="300px">
                            @endif
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Foto 2</label>
                        <div class="col-sm-9">
                            @if (!empty($data->foto))
                                <img src="{{ url('pic-api/gambar/'.str_replace('/', '&', $data->foto)) }}" width="300px" height="300px">
                            @endif
                            {!! Form::file('foto', ['class' => 'form-control-uniform-custom']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Deskripsi</label>
                        <div class="col-sm-9">
                            {!! Form::textarea("deskripsi", null, ['class'=>'form-control', 'rows' => '3', 'cols' => '3', 'id'=>'deskripsi']) !!}
                        </div>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary legitRipple">Submit</button>
                        <a href="{{route('perbaikan::perbaikan-index')}}">
                            <button type="button" class="btn btn-light legitRipple">Lewati</button></a>
                    </div>

                    {!! Form::close() !!}
                    <!-- END FORM-->
                </fieldset>
            </div>
        </div>
    <!-- /form inputs -->
@endsection
@section('js')
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>

<script src="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.js')}}"></script>
<link href="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.css')}}" rel="stylesheet"/>
<script>
$(document).ready(function () {

    $("#tgl_mulai").change(function(event) {
        var date = new Date($(this).val());
        var otherDate = new Date($("#perkiraan").val());

        if (date < otherDate) {
            var result = Math.ceil(Math.abs(date - otherDate) / (1000 * 60 * 60 * 24));

            $("#waktu").val(result);
        } else {
            return false;
        }
    });

    $(".ekspp").hide();

    $(".select2").select2();

    $(".datepicker").datepicker({
        format:'yyyy-mm-dd',
        formatSubmit: 'yyyy-mm-dd'
    });

    $(".yearpicker").datepicker({
        format: "yyyy",
        viewMode: "years", 
        minViewMode: "years"
    });
});
</script>
@endsection