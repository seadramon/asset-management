@extends('layouts.main')

@section('title', 'Monitoring Aset - Asset Management')

@section('pagetitle', 'Monitoring Aset - '.$nama_aset)

@section('content') 
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="kondisi-form">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">{{ 'Form '.$id }}</h5>
            </div>

            <div class="card-body">
                @if ( $id != $kodefmOrigin )
                    <div style="text-align: right;margin-bottom: 10px;">
                        <a href="{{ route('monitoring::monitoring-entri', ['id' => $kodefmOrigin, 'id4w' => $ms_4w_id]) }}"> Lihat History Isian Kode Form Sebelumnya >>> </a>
                    </div>
                @endif

                <!-- BEGIN FORM-->
                @if (isset($data))
                    {!! Form::model($data, ['route' => ['monitoring::monitoring-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('monitoring::monitoring-simpan'), 'class' => 'form-horizontal', 'autocomplete' => 'off']) !!}
                @endif
                    <fieldset class="mb-3">
                        <input type="hidden" class="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="hidden" class="hidden" name="kode_fm" value="{{$id}}">
                        <input type="hidden" class="hidden" name="ms_4w_id" value="{{$ms_4w_id}}">

                        @foreach($forms as $form)
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">{{$form->pengukuran}}</label>
                                <div class="col-lg-8">
                                    @if ($form->dropdown)
                                        <?php 
                                        $temps = explode("#", $form->dropdown);
                                        $opt = ["" => "- Pilih -"];

                                        foreach ($temps as $row) {
                                            $opt[$row] = ucfirst(strtolower($row));
                                        }
                                        ?>
                                        
                                        @if (namaRole() == 'Super Administrator')
                                            {!! Form::select($form->nama_field, $opt, null, ['class'=>'form-control select2', 'id'=>$form->nama_field]) !!}
                                        @else
                                            {!! Form::select($form->nama_field, $opt, null, ['class'=>'form-control select2', 'id'=>$form->nama_field, 'disabled']) !!}
                                        @endif
                                    @else
                                        <?php 
                                        $a = $form->nama_field; 
                                        ?>
                                        @if (namaRole() == 'Super Administrator')
                                            {!! Form::text($form->nama_field, null, ['class'=>'form-control lama', 'id'=>$form->nama_field]) !!}
                                        @else
                                            {!! Form::text($form->nama_field, null, ['class'=>'form-control lama', 'id'=>$form->nama_field, 'disabled']) !!}
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </fieldset>

                    <div class="text-right">
                        @if (namaRole() == 'Super Administrator')
                            <button type="submit" class="btn btn-primary legitRipple">Simpan</button>
                        @endif
                        <a href="{{route('todolist::todolist-index')}}">
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
});
</script>
@endsection