@extends('layouts.main')

@section('title', 'Depresiasi - Asset Management')

@section('pagetitle', 'Depresiasi - View')

@section('content') 
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="kondisi-form">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Depresiasi</h5>
            </div>

            <div class="card-body">
                <!-- BEGIN FORM-->
                <fieldset class="mb-3">
                    <input type="hidden" class="hidden" name="_token" value="{{csrf_token()}}">

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Aset</label>
                        <div class="col-lg-10">
                            <div class="form-control-plaintext">{{ '['.$data->asetnya->kode_aset.'] '.$data->asetnya->nama_aset }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Bulan</label>
                        <div class="col-lg-10">
                            <div class="form-control-plaintext">{{ $data->bulan->name }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Tahun</label>
                        <div class="col-lg-10">
                            <div class="form-control-plaintext">{{ $data->tahun }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Depresiasi Bulanan</label>
                        <div class="col-lg-10">
                            <div class="form-control-plaintext">{{ $data->depresiasi_bulanan }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Depresiasi tahunan</label>
                        <div class="col-lg-10">
                            <div class="form-control-plaintext">{{ $data->depresiasi_tahunan }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Akumulasi Depresiasi</label>
                        <div class="col-lg-10">
                            <div class="form-control-plaintext">{{ $data->akumulasi_depresiasi }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Nilai Aset Terakhir</label>
                        <div class="col-lg-10">
                            <div class="form-control-plaintext">{{ $data->nilai_aset }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Pembuat</label>
                        <div class="col-lg-10">
                            <div class="form-control-plaintext">{{ 'System Administrator' }}</div>
                        </div>
                    </div><div class="form-group row">
                        <label class="col-form-label col-lg-2">Waktu Pembuatan</label>
                        <div class="col-lg-10">
                            <div class="form-control-plaintext">{{ $data->ts_create }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Pemutakhir</label>
                        <div class="col-lg-10">
                            <div class="form-control-plaintext">{{ 'System Administrator' }}</div>
                        </div>
                    </div><div class="form-group row">
                        <label class="col-form-label col-lg-2">Waktu Pemutakhiran</label>
                        <div class="col-lg-10">
                            <div class="form-control-plaintext">{{ $data->ts_update }}</div>
                        </div>
                    </div>
                </fieldset>

                <div class="text-right">
                    <a href="{{route('depresiasi::index')}}" class="btn btn-light legitRipple">Kembali</a>
                </div>
                <!-- END FORM-->
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    <!-- /form inputs -->
@endsection
@section('js')
@endsection