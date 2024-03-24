@extends('layouts.main')

@if (empty($data->aduan_id))
    @section('title', 'Hasil Analisa Perbaikan dari Monitoring - Asset Management')
@else
    @section('title', 'Hasil Analisa Perbaikan dari Aduan - Asset Management')
@endif

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
                @if (empty($data->aduan_id))
				    <h5 class="card-title">Input Metode Perbaikan dari Monitoring Form</h5>
                @else
                    <h5 class="card-title">Input Metode Perbaikan dari Aduan</h5>
                @endif
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
            	<fieldset class="mb-3">
                    <!-- BEGIN FORM-->
                    @if ($data!='')
                        {!! Form::model($data, ['route' => ['perbaikan::perbaikan-metode-simpan'], 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data']) !!}
                        {!! Form::hidden('id', null, ['id' => 'id_wo']) !!}
                        {!! Form::hidden('status', null, ['id' => 'statuswo']) !!}
                    @else
                        {!! Form::open(['url' => route('perbaikan::perbaikan-metode-simpan'), 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data']) !!}
                        {!! Form::hidden('id', null, ['id' => 'id_wo']) !!}
                    @endif
                    {!! Form::hidden('wo', 'prb_data_id', ['id' => 'wo']) !!}

            		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">

                    <input type="hidden" name="fkey_proposal" id="fkey_proposal" value="{{ $f_key_proposal }}">
                    
                    @if ($data->tipe == 'aduan')
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Judul Kerusakan</label>
                            <div class="col-lg-10">
                                <div class="form-control-plaintext">{{ $data->aduan_judul }}</div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Indikasi Kerusakan</label>
                            <div class="col-lg-10">
                                <div class="form-control-plaintext">{!! $data->aduan_indikasi !!}</div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Catatan Kerusakan</label>
                            <div class="col-lg-10">
                                <div class="form-control-plaintext">{!! $data->aduan_catatan !!}</div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Foto Kondisi</label>
                            <div class="col-lg-10">
                                @if (!empty($data->aduan_kondisi))
                                    <img src="{{ url('pic-api/gambar/aduan&'.$period.'&'.$data->aduan_kondisi) }}" width="300px" height="300px">
                                @endif
                            </div>
                        </div>

                        <?php /*
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Foto Kerusakan</label>
                            <div class="col-lg-10">
                                @if (!empty($keluhan->path_kerusakan))
                                    <img src="{{ url('pic-api/gambar/perbaikan&'.$keluhan->path_kerusakan) }}" width="300px" height="300px">
                                @endif
                            </div>
                        </div>
                        */?>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Foto Lokasi</label>
                            <div class="col-lg-10">
                                @if (!empty($data->aduan_lok_kerusakan))
                                    <img src="{{ url('pic-api/gambar/'.str_replace('/', '&', $data->aduan_lok_kerusakan)) }}" width="300px" height="300px">
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Foto Investigasi</label>
                        <div class="col-lg-10">
                            <img src="{{ url('pic-api/gambar/'.str_replace('/', '&', $data->foto_investigasi)) }}" width="300px" height="300px">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Foto Investigasi 2</label>
                        <div class="col-lg-10">
                            @if (!empty($data->foto_investigasi2))
                                <img src="{{ url('pic-api/gambar/'.str_replace('/', '&', $data->foto_investigasi2)) }}" width="300px" height="300px">
                            @endif
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Penyebab</label>
                        <label class="col-form-label col-lg-10">{!! $data->penyebab !!}</label>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Kelayakan Operasional</label>
                        <div class="col-lg-10">
                            {!! Form::select('kondisi', $kondisi, null, ['class'=>'form-control select2', 'id'=>'kondisi', 'required']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Jenis Penanganan Pekerjaan</label>
                        <div class="col-lg-10">
                            {!! Form::text("jenis_penanganan", null, ['class'=>'form-control', 'id'=>'jenis_penanganan', 'required']) !!}
                        </div>
                    </div>

                    @if ($data->status == "1")
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Target Penyelesaian</label>
                            <div class="col-lg-10">
                                {!! Form::text("perkiraan", null, ['class'=>'form-control datepicker', 'id'=>'perkiraan', 'required']) !!}
                            </div>
                        </div>
                    @else
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Target Penyelesaian</label>
                            <div class="col-lg-10">
                                {!! Form::text("perkiraan", null, ['class'=>'form-control datepicker', 'id'=>'perkiraan', 'readonly']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Target Penyelesaian Revisi</label>
                            <div class="col-lg-10">
                                {!! Form::text("perkiraan_revisi", null, ['class'=>'form-control datepicker', 'id'=>'perkiraan_revisi', 'required']) !!}
                            </div>
                        </div>
                    @endif

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Tingkat Perbaikan</label>
                        <div class="col-lg-10">
                            {!! Form::select('tingkat', $tingkat, null, ['class'=>'form-control select2', 'id'=>'tingkat', 'required']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Pelaksana Pekerjaan</label>
                        <div class="col-lg-10">
                            {!! Form::select('metode', $metode, null, ['class'=>'form-control select2', 'id'=>'metode', 'required']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Sifat Pekerjaan</label>
                        <div class="col-lg-10">
                            {!! Form::select('sifat', $sifat, null, ['class'=>'form-control select2', 'id'=>'sifat', 'required']) !!}
                        </div>
                    </div>

                    <div class="form-group row ekspp">
                        <label class="col-form-label col-lg-2">Tahun Anggaran</label>
                        <div class="col-lg-10">
                            {!! Form::text("tahun_anggaran", null, ['class'=>'form-control yearpicker', 'id'=>'tahun_anggaran']) !!}
                        </div>
                    </div>

                    <div class="form-group row ekspp">
                        <label class="col-form-label col-lg-2">Nomor Anggaran</label>
                        <div class="col-lg-10">
                            {!! Form::text("perkiraan_anggaran", null, ['class'=>'form-control', 'id'=>'perkiraan_anggaran']) !!}
                        </div>
                    </div>

                    <div class="form-group row ekspp">
                        <label class="col-form-label col-lg-2">Proposal</label>
                        <div class="col-lg-5">
                            {!! Form::select('c_proposal', $c_proposal, null, ['class'=>'form-control select2', 'id'=>'c_proposal', 'width' => '100%']) !!}
                        </div>
                        <div class="col-lg-1">
                            <a href="#" class="btn btn-success btn-sm" id="linkProposal">Lihat</a>
                        </div>
                        <div class="col-lg-1" id="modalNewProposal">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal_proposal">Buat Baru </button>
                        </div>
                    </div>

                    <?php /*
                    <div class="form-group row ekspp">
                        <label class="col-form-label col-lg-2">Proposal</label>
                        <div class="col-lg-10">
                            {!! Form::file('proposal') !!}
                        </div>
                    </div>
                    */?>

                    <br><br>
                    <h3>Sukucadang</h3>
                    @include('pages.sukucadang.partsukucadang')

                    <div style="margin-top: 20px;margin-bottom: 20px;"></div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary legitRipple">Submit</button>

                        <?php /*
                        <a href="{{route('perawatan::perawatan-sukucadang', ['id' => $data->id, 'aset' => $data->komponen_id])}}?wo=perbaikan&fkey=prb_data_id" class="btn btn-info legitRipple">Sukucadang</a>
                        */?>

                        <a href="{{route('perbaikan::perbaikan-index')}}">
                            <button type="button" class="btn btn-light legitRipple">Kembali</button></a>
                    </div>

                    {!! Form::close() !!}
                    <!-- END FORM-->
            	</fieldset>
            </div>
        </div>
    <!-- /form inputs -->

    <!-- Horizontal form modal -->
    <div id="modal_proposal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Proposal Pekerjaan</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                @include('pages.proposal.modal')
            </div>
        </div>
    </div>
    <!-- /horizontal form modal -->

@endsection
@section('js')
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>

<script src="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.js')}}"></script>
<link href="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.css')}}" rel="stylesheet"/>

<script src="{{url('assets/js/metode.js')}}"></script>
@endsection