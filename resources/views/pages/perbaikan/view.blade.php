@extends('layouts.main')

@if (empty($data->aduan_id))
    @section('title', 'View Perbaikan dari Monitoring - Asset Management')
@else
    @section('title', 'View Perbaikan dari Aduan - Asset Management')
@endif

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'View Perbaikan')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">

        	<div class="card-header header-elements-inline">
                @if (empty($data->aduan_id))
				    <h5 class="card-title">View Perbaikan dari Monitoring</h5>
                @else
                    <h5 class="card-title">View Perbaikan dari Aduan</h5>
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
                <!-- BEGIN FORM-->
                @if ($data!='')
                    {!! Form::model($data, ['route' => ['perbaikan::perbaikan-metode-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('perbaikan::perbaikan-metode-simpan'), 'class' => 'form-horizontal']) !!}
                @endif
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">
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
                                <label class="col-form-label col-lg-2">Foto Kondisi/Kerusakan</label>
                                <div class="col-lg-10">
                                    <?php 
                                    $period = date('Y-m', strtotime($data->tanggal));
                                    ?>
                                    @if (!empty($data->aduan_kondisi))
                                        <img src="{{ url('pic-api/gambar/aduan&'.$period.'&'.$data->aduan_kondisi) }}" width="300px" height="300px">
                                    @endif
                                </div>
                            </div>

                            <?php /*
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Foto Kerusakan</label>
                                <div class="col-lg-10">
                                    @if (!empty($data->path_kerusakan))
                                        <img src="{{ url('pic-api/gambar/perbaikan&'.$data->path_kerusakan) }}" width="300px" height="300px">
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
                            <label class="col-form-label col-lg-10">{!! $data->kondisi !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Jenis Penanganan Pekerjaan</label>
                            <label class="col-form-label col-lg-10">{!! $data->jenis_penanganan !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Target Penyelesaian</label>
                            <label class="col-form-label col-lg-10">{!! $data->perkiraan !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Target Penyelesaian Revisi</label>
                            <label class="col-form-label col-lg-10">{!! $data->perkiraan_revisi !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Tingkat Perbaikan</label>
                            <label class="col-form-label col-lg-10">{!! $data->tingkat !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Pelaksana Pekerjaan</label>
                            <label class="col-form-label col-lg-10">{!! $data->metode !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Sifat Pekerjaan</label>
                            <label class="col-form-label col-lg-10">{!! $data->sifat !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Catatan Revisi Petugas</label>
                            <label class="col-form-label col-lg-10">{!! $data->petugas_catatan !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Catatan Revisi Manajer Pemeliharaan/Trandist</label>
                            <label class="col-form-label col-lg-10">{!! $data->m_catatan !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Catatan Manajer PPP</label>
                            <label class="col-form-label col-lg-10">{!! $data->ms_ppp_catatan !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Catatan Revisi Manajer Dalpro</label>
                            <label class="col-form-label col-lg-10">{!! $data->dalpro_catatan !!}</label>
                        </div>

                        @if ($cekMasukProposal)
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Proposal</label>
                                    <a class="col-form-label col-lg-10" href="{{ route('proposal::pekerjaan', ['wo' => 'prb_data_id', 'id' => $data->id, 'report' => 'true']) }}" target="_blank">Lihat Proposal</a>
                            </div>
                        @endif

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Tahun Anggaran</label>
                            <label class="col-form-label col-lg-10">{!! $data->tahun_anggaran !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Perkiraan Anggaran</label>
                            <label class="col-form-label col-lg-10">{!! $data->perkiraan_anggaran !!}</label>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Foto Penanganan</label>
                            <div class="col-lg-10">
                                @if (!empty($data->foto))
                                    <img src="{{ url('pic-api/gambar/'.str_replace('/', '&', $data->foto)) }}" width="300px" height="300px">
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Foto Penanganan 2</label>
                            <div class="col-lg-10">
                                @if (!empty($data->foto2))
                                    <img src="{{ url('pic-api/gambar/'.str_replace('/', '&', $data->foto2)) }}" width="300px" height="300px">
                                @endif
                            </div>
                        </div>

                	</fieldset>                    
                {!! Form::close() !!}

                <!-- Sukucadang -->
                @include('pages.sukucadang.show')

                @if ( in_array(namaRole(), config('custom.pko')) && in_array($data->status, config('custom.pko-statusdisplay')) )
                    {!! Form::model($data, ['route' => ['perbaikan::perbaikan-ded-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                        <fieldset class="mb-3">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Status Proses DED</label>
                                <div class="col-lg-10">
                                    {!! Form::select('status', $statusDed, null, ['class'=>'form-control select2', 'id'=>'status']) !!}
                                </div>
                            </div>

                            <div class="form-group row d-none" id="tolakded">
                                <label class="col-form-label col-lg-2">Catatan Tolak DED</label>
                                <div class="col-lg-10">
                                    {!! Form::textarea("pko_catatan", null, ['class'=>'form-control', 'id'=>'pko_catatan']) !!}
                                </div>
                            </div>
                        </fieldset>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary legitRipple">Simpan</button>
                            <a href="{{route('perbaikan::perbaikan-index')}}">
                                <button type="button" class="btn btn-light legitRipple">Kembali</button></a>
                        </div>
                    {!! Form::close() !!}
                @elseif ( (trim(\Auth::user()->userid) == trim(getMsPpp()->nip)) && $data->status == '1.3' )
                    <h3>Konfirmasi</h3>

                    {!! Form::model($data, ['route' => ['perbaikan::perbaikan-msppp-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                    {!! Form::hidden('wo', 'prb') !!}
                        <fieldset class="mb-3">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Konfirmasi</label>
                                <div class="col-lg-10">
                                    {!! Form::select('status', $statusMsppp, null, ['class'=>'form-control select2', 'id'=>'statusMsppp']) !!}
                                </div>
                            </div>

                            <div class="form-group row d-none" id="tolakmsppp">
                                <label class="col-form-label col-lg-2">Catatan</label>
                                <div class="col-lg-10">
                                    {!! Form::textarea("ms_ppp_catatan", null, ['class'=>'form-control', 'id'=>'ms_ppp_catatan']) !!}
                                </div>
                            </div>
                        </fieldset>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary legitRipple">Simpan</button>
                            <a href="{{route('perbaikan::perbaikan-index')}}">
                                <button type="button" class="btn btn-light legitRipple">Kembali</button></a>
                        </div>
                    {!! Form::close() !!}
                @else
                    <div class="text-right">
                        <a href="{{route('perbaikan::perbaikan-index')}}">
                            <button type="button" class="btn btn-light legitRipple">Kembali</button></a>
                    </div>
                @endif
                <!-- END FORM-->
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
    $(".select2").select2();

    $(".datepicker").datepicker({
        format:'yyyy-mm-dd',
        formatSubmit: 'yyyy-mm-dd'
    });
});

$("#status").on('change', function() {
    var status = $(this).val();

    if ($("#tolakded").hasClass('d-none') == false) {
        $("#tolakded").addClass('d-none');
    }

    if ($("#pko_catatan")[0].hasAttribute('required')) {
        $("#pko_catatan").prop('required',false);   
    }

    if (status == '99') {
        console.log('tolak');
        $("#tolakded").removeClass('d-none');

        $("#pko_catatan").prop('required',true);
    }
});

$("#statusMsppp").on('change', function() {
    var status = $(this).val();

    if ($("#tolakmsppp").hasClass('d-none') == false) {
        $("#tolakmsppp").addClass('d-none');
    }

    if ($("#ms_ppp_catatan")[0].hasAttribute('required')) {
        $("#ms_ppp_catatan").prop('required',false);   
    }

    if (status == '99' || status == '3.4') {
        console.log('tolak');
        $("#tolakmsppp").removeClass('d-none');

        $("#ms_ppp_catatan").prop('required',true);
    }
});
</script>
@endsection