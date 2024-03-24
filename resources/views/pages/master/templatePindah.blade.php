@extends('layouts.main')

@section('title', 'Master Equipment - Asset Management')

@section('pagetitle', 'Master Equipment')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card {{($data=='')?'hidden':''}}" id="template-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Pindah Komponen ke Equipment Baru</h5>
			</div>

            <div class="card-body">
                <!-- BEGIN FORM-->
                @if ($data!='')
                    {!! Form::model($data, ['route' => ['master::template-pindah-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('master::template-pindah-simpan'), 'class' => 'form-horizontal']) !!}
                @endif
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="text" class="hidden" name="tipe" value="{{($data=='')?'0':'1'}}">
                        <input type="text" class="hidden" name="kode" value="{{($data=='')?'':$data->id }}">

	                    <div class="form-group row">
							<label class="col-form-label col-lg-2">Equipment Lama</label>
							<div class="col-lg-10">
                                {!! Form::text("asetlama", $data->asetlama, ['class'=>'form-control', 'id'=>'asetlama', 'readonly']) !!}
							</div>
						</div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Equipment Baru</label>
                            <div class="col-lg-10">
                                {!! Form::select('asetbaru', $aset, null, ['class'=>'form-control select2', 'id'=>'asetbaru', 'style' => 'width:100%', 'required']) !!}
                            </div>
                        </div>
                	</fieldset>

                	<div class="text-right">
						<button type="submit" class="btn btn-primary legitRipple">Simpan</button> 
	                    <a href="{{route('master::template-link')}}">
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
//    alert('aa');
    $(".select2").select2();
});
</script>
@endsection