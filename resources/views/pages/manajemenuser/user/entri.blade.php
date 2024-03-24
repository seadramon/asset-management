@extends('layouts.main')

@section('title', 'Management Users - Asset Management')

@section('pagetitle', 'Management Users')

@section('content') 
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="kondisi-form">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Form User</h5>
            </div>

            <div class="card-body">
                <!-- BEGIN FORM-->
                @if (isset($data))
                    {!! Form::model($data, ['route' => ['mnjuser::mnjuser-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('mnjuser::mnjuser-simpan'), 'class' => 'form-horizontal']) !!}
                @endif
                    <fieldset class="mb-3">
                        <input type="hidden" class="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="hidden" class="hidden" name="tipe" value="{{($data=='')?'0':'1'}}">
                        <input type="hidden" class="hidden" name="kode" value="{{($data=='')?'':$data->id }}">

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">NIP</label>
                            <div class="col-lg-10">
                                {!! Form::text('user_id', $user->userid, ['class'=>'form-control', 'id'=>'user_id', 'readonly']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Nama</label>
                            <div class="col-lg-10">
                                {!! Form::text('name', $user->username, ['class'=>'form-control', 'id'=>'name', 'disabled']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Role</label>
                            <div class="col-lg-10">
                                {!! Form::select('role_id', $role, null, ['class'=>'form-control select2', 'id'=>'role_id']) !!}
                            </div>
                        </div>
                    </fieldset>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary legitRipple">Simpan</button> 
                        <a href="{{route('mnjuser::mnjuser-index')}}">
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