@extends('layouts.main')

@section('title', 'Management Roles - Asset Management')

@section('pagetitle', 'Management Roles')

@section('content') 
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="kondisi-form">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Form Role</h5>
            </div>

            <div class="card-body">
                <!-- BEGIN FORM-->
                @if (isset($data))
                    {!! Form::model($data, ['route' => ['mnjrole::mnjrole-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('mnjrole::mnjrole-simpan'), 'class' => 'form-horizontal']) !!}
                @endif
                    <fieldset class="mb-3">
                        <input type="hidden" class="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="hidden" class="hidden" name="tipe" value="{{($data=='')?'0':'1'}}">
                        <input type="hidden" class="hidden" name="kode" value="{{($data=='')?'':$data->id }}">

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Nama Role</label>
                            <div class="col-lg-10">
                                {!! Form::text('name', null, ['class'=>'form-control', 'id'=>'name']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Fitur</label>

                            <div class="col-form-label col-lg-10">
                                {!! Form::checkbox('can_add', 'Y') !!} Tambah <br>
                                {!! Form::checkbox('can_edit', 'Y') !!} Edit <br>
                                {!! Form::checkbox('can_delete', 'Y') !!} Delete <br>
                            </div>
                        </div>
                    </fieldset>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary legitRipple">Simpan</button> 
                        <a href="{{route('mnjrole::mnjrole-index')}}">
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