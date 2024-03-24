@extends('layouts.main')

@section('title', 'Management Menus - Asset Management')

@section('pagetitle', 'Management Menus')

@section('content') 
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="kondisi-form">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Form Menu</h5>
            </div>

            <div class="card-body">
                <!-- BEGIN FORM-->
                @if (isset($data))
                    {!! Form::model($data, ['route' => ['mnjmenu::mnjmenu-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('mnjmenu::mnjmenu-simpan'), 'class' => 'form-horizontal']) !!}
                @endif
                    <fieldset class="mb-3">
                        <input type="hidden" class="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="hidden" class="hidden" name="tipeform" value="{{($data=='')?'0':'1'}}">
                        <input type="hidden" class="hidden" name="kode" value="{{($data=='')?'':$data->id }}">

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Nama</label>
                            <div class="col-lg-10">
                                {!! Form::text('nama', null, ['class'=>'form-control', 'id'=>'nama']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">URL</label>
                            <div class="col-lg-10">
                                {!! Form::text('url', null, ['class'=>'form-control', 'id'=>'url']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Icon</label>
                            <div class="col-lg-10">
                                {!! Form::text('icon', null, ['class'=>'form-control', 'id'=>'icon']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Tipe</label>
                            <div class="col-lg-10">
                                {!! Form::select('tipe', $tipe, null, ['class'=>'form-control select2', 'id'=>'tipe']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Urutan</label>
                            <div class="col-lg-10">
                                {!! Form::text('urut', null, ['class'=>'form-control', 'id'=>'urut']) !!}
                            </div>
                        </div>
                    </fieldset>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary legitRipple">Simpan</button> 
                        <a href="{{route('mnjmenu::mnjmenu-index')}}">
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