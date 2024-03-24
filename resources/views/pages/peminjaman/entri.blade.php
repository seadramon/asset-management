@extends('layouts.main')

@section('title', 'Peminjaman Aset - Asset Management')

@section('pagetitle', 'Peminjaman Aset')

@section('content') 
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="kondisi-form">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">{{ $title }}</h5>
            </div>

            <div class="card-body">
                <!-- BEGIN FORM-->
                @if (isset($data))
                    {!! Form::model($data, ['route' => ['peminjaman::peminjaman-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('peminjaman::peminjaman-simpan'), 'class' => 'form-horizontal']) !!}
                @endif
                    <fieldset class="mb-3">
                        <input type="hidden" class="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="hidden" class="hidden" name="tipe" value="{{($data=='')?'0':'1'}}">
                        <input type="hidden" class="hidden" name="kode" value="{{($data=='')?'':$data->id }}">

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Aset</label>
                            <div class="col-lg-10">
                                {!! Form::select('aset_id', $aset, null, ['class'=>'form-control select2', 'id'=>'aset']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Peminjam</label>
                            <div class="col-lg-10">
                                {!! Form::text('peminjam', null, ['class'=>'form-control', 'id'=>'peminjam']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Tanggal Rencana Pinjam</label>
                            <div class="col-lg-10">
                                {!! Form::text('tgl_rencana_dipinjam', null, ['class'=>'form-control pickadate', 'id'=>'rencana_pinjam']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Tanggal diPinjam</label>
                            <div class="col-lg-10">
                                {!! Form::text('tgl_dipinjam', null, ['class'=>'form-control pickadate', 'id'=>'tgl_dipinjam']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Tanggal Rencana Kembali</label>
                            <div class="col-lg-10">
                                {!! Form::text('tgl_renc_kembali', null, ['class'=>'form-control pickadate', 'id'=>'tgl_renc_kembali']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Tanggal Rencana Kembali extend</label>
                            <div class="col-lg-10">
                                {!! Form::text('tgl_renc_kembali_extend', null, ['class'=>'form-control pickadate', 'id'=>'tgl_renc_kembali_extend']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Catatan Pinjam</label>
                            <div class="col-lg-10">
                                {!! Form::textarea('catatan_pinjam', null, ['class'=>'form-control', 'id'=>'catatan_pinjam']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Tanggal Dikembalikan</label>
                            <div class="col-lg-10">
                                {!! Form::text('tgl_dikembalikan', null, ['class'=>'form-control pickadate', 'id'=>'tgl_dikembalikan']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Catatan Kembali</label>
                            <div class="col-lg-10">
                                {!! Form::textarea('catatan_kembali', null, ['class'=>'form-control', 'id'=>'catatan_kembali']) !!}
                            </div>
                        </div>
                    </fieldset>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary legitRipple">Simpan</button> 
                        <a href="{{route('peminjaman::peminjaman-index')}}">
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
});
</script>
@endsection