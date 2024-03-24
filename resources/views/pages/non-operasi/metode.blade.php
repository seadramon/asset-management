@extends('layouts.main')

@section('title', 'Input Metode ' . ucwords($tipe) . ' Non Operasi - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Input Metode  ' . ucwords($tipe) . '  Non Operasi')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">

        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Input Metode {{ ucwords($tipe) }} Non Operasi Form</h5>
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
                    @if ($tipe=='aduan')
                        {!! Form::model($data, ['route' => ['non-operasi::aduan-metode-simpan'], 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data']) !!}
                        {!! Form::hidden('id', null, ['id' => 'id_wo']) !!}
                        {!! Form::hidden('wo', 'aduan_non_op_id', ['id' => 'wo']) !!}
                        {!! Form::hidden('status', null, ['id' => 'statuswo']) !!}
                    @else
                        {!! Form::model($data, ['route' => ['non-operasi::usulan-metode-simpan'], 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data']) !!}
                        {!! Form::hidden('id', null, ['id' => 'id_wo']) !!}
                        {!! Form::hidden('wo', 'usulan_id', ['id' => 'wo']) !!}
                        {!! Form::hidden('status', null, ['id' => 'statuswo']) !!}
                    @endif
            		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">

                    <input type="hidden" name="fkey_proposal" id="fkey_proposal" value="{{ $f_key_proposal }}">
                    
                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Foto Investigasi</label>
                        <div class="col-lg-10">
                            <img src="{{ url('pic-api/gambar/non-operasi&'.$tipe.'&'.$data->id.'&'.$data->foto_investigasi) }}" width="400px" height="400px">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Foto Investigasi 2</label>
                        <div class="col-lg-10">
                            @if (!empty($data->foto_investigasi2))
                                <img src="{{ url('pic-api/gambar/non-operasi&'.$tipe.'&'.$data->id.'&'.$data->foto_investigasi2) }}" width="400px" height="400px">
                            @endif
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Penyebab</label>
                        <div class="col-lg-10">
                            {!! Form::textarea("penyebab", null, ['class'=>'form-control', 'id'=>'penyebab', 'readonly']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Kelayakan Operasional</label>
                        <div class="col-lg-10">
                            {!! Form::select('kondisi', $kondisi, null, ['class'=>'form-control select2', 'id'=>'kondisi', 'required']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Jenis Penanganan</label>
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

                    @if ($tipe == 'aduan')
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Tingkat Perbaikan</label>
                            <div class="col-lg-10">
                                {!! Form::select('tingkat', $tingkat, null, ['class'=>'form-control select2', 'id'=>'tingkat', 'required']) !!}
                            </div>
                        </div>
                    @endif

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

                    <br><br>
                    <h3>Sukucadang</h3>
                    @include('pages.sukucadang.partsukucadang')

                    <div style="margin-top: 20px;margin-bottom: 20px;"></div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary legitRipple">Simpan</button>
                        <a href="{{route('perawatan::perawatan-index')}}">
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