@extends('layouts.main')

@section('title', 'Master Equipment - Asset Management')

@section('pagetitle', 'Master Equipment')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card {{($data=='')?'hidden':''}}" id="template-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Equipment Form</h5>
			</div>

            <div class="card-body">
                <!-- BEGIN FORM-->
                @if ($data!='')
                    {!! Form::model($data, ['route' => ['master::template-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('master::template-simpan'), 'class' => 'form-horizontal']) !!}
                @endif
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="text" class="hidden" name="tipe" value="{{($data=='')?'0':'1'}}">
                        <input type="text" class="hidden" name="kode" value="{{($data=='')?'':$data->id }}">

	                    <div class="form-group row">
							<label class="col-form-label col-lg-2">Nama Aset</label>
							<div class="col-lg-10">
                                <?php 
                                $checked = false;
                                ?>
                                @if($data!='')                                    
                                    {!! Form::text("namaaset", $namaaset, ['class'=>'form-control', 'id'=>'namaaset', 'disabled']) !!} 

                                    @if ($data->availability > 0)
                                        <?php $checked = true; ?>
                                    @endif
                                @else
                                    {!! Form::select('id_aset[]', $aset, null, ['class'=>'form-control select2', 'id'=>'aset', 'style' => 'width:100%', 'multiple']) !!}
                                @endif
							</div>
						</div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Sistem</label>
                            <div class="col-lg-10">
                                {!! Form::select('sistem_id', $sistem, null, ['class'=>'form-control select2', 'id'=>'sistem', 'style' => 'width:100%']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Bagian</label>
                            <div class="col-lg-10">
                                {!! Form::select('bagian', $bagian, null, ['class'=>'form-control select2', 'id'=>'bagian', 'style' => 'width:100%']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Kode Form Monitoring</label>
                            <div class="col-lg-10">
                                {!! Form::text("kode_fm", null, ['class'=>'form-control', 'id'=>'kode_fm']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    {!! Form::checkbox('availability', '1', $checked, ['id' => 'availability', 'class' => 'form-check-input']); !!}
                                    Availaibility
                                </label>
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

        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="card {{($data=='')?'':'hidden'}}" id="template-data">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">List equipment</h5>
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
				<button type="button" id="tambah-btn" class="btn btn-success legitRipple"> 
                    <i class="fa fa-plus"></i> Tambah Baru
                </button>

                <table class="table datatable-basic" id="tabel">
                    <thead>
                        <tr>                                    
                            <th>ID</th>
                            <th>Sistem</th>
                            <th>Kode Aset</th>
                            <th>Nama</th>
                            <th>Instalasi</th>
                            <th>Menu</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
			</div>
        </div>
        <!-- END EXAMPLE TABLE PORTLET-->
        <!-- END PAGE BASE CONTENT -->
    <!-- /form inputs -->
@endsection
@section('js')
<script src="{{asset('global_assets/plugins/datatables/datatables.min.js')}}" type="text/javascript"></script>
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>
<script>
$(document).ready(function () {
//    alert('aa');
    $(".select2").select2();

    $('#tabel').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('master::template-data') }}",
        "columns": [
            {data: 'id', defaultContent: '-'},
            {data: 'sistem.name', name: 'sistem.name', defaultContent: '-'},
            {data: 'kode_aset', name: 'kode_aset', defaultContent: '-'},
            {data: 'nama_aset', defaultContent: '-'},
            {data: 'instalasi.name', name: 'instalasi.name', defaultContent: '-'},
            {data: 'menu', orderable: false, searchable: false}
        ],
        "order": [[0, 'asc']],
        "autoWidth": false,
        "dom": '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
        "pagingType": "simple_numbers",
        "language": {
            search: '<span>Filter:</span> _INPUT_',
            searchPlaceholder: 'Type to filter...',
            lengthMenu: '<span>Show:</span> _MENU_',
            paginate: { 'first': 'First', 'last': 'Last', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
        }
    });
    $("#tambah-btn").click(function () {
        $("#template-form").removeClass('hidden');
        $("#template-data").addClass('hidden');
    });
});
</script>
@endsection