@extends('layouts.main')

@section('title', 'Master Kondisi - Asset Management')

@section('pagetitle', 'Master Kondisi')

@section('content') 
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card {{($data=='')?'hidden':''}}" id="kondisi-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Form Kondisi</h5>
			</div>

            <div class="card-body">
                <!-- BEGIN FORM-->
                <form action="{{route('master::kondisi-simpan')}}" method="post" class="form-horizontal">
                	<fieldset class="mb-3">
                		<input type="hidden" class="hidden" name="_token" value="{{csrf_token()}}">
	                    <input type="hidden" class="hidden" name="tipe" value="{{($data=='')?'0':'1'}}">
	                    <input type="hidden" class="hidden" name="kode" value="{{($data=='')?'':$data->id }}">

	                    <div class="form-group row">
							<label class="col-form-label col-lg-2">Kode</label>
							<div class="col-lg-10">
								<input type="text" class="form-control input-circle" name="nomor" placeholder="Masukkan Kode" value="{{$data->kode or ''}}">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-form-label col-lg-2">Nama</label>
							<div class="col-lg-10">
								<input type="text" class="form-control input-circle" name="nama" placeholder="Masukkan Nama" value="{{$data->name or ''}}">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-form-label col-lg-2">Nilai Level</label>
							<div class="col-lg-10">
								<input type="number" class="form-control input-circle" name="level" placeholder="Masukkan Nilai Level" value="{{$data->nilai_level or ''}}">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-form-label col-lg-2">Tingkat Pemeliharaan</label>
							<div class="col-lg-10">
								<input type="text" class="form-control input-circle" name="pemeliharaan" placeholder="Masukkan Tingkat Pemeliharaan" value="{{$data->tingkat_pemeliharaan or ''}}">
							</div>
						</div>
                	</fieldset>

                	<div class="text-right">
						<button type="submit" class="btn btn-primary legitRipple">Simpan</button> 
	                    <a href="{{route('master::kondisi-link')}}">
	                    	<button type="button" class="btn btn-light legitRipple">Kembali</button></a>
					</div>
                </form>
                <!-- END FORM-->
            </div>
        </div>

        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="card {{($data=='')?'':'hidden'}}" id="kondisi-data">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">List Kondisi</h5>
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
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Level</th>
                            <th>Tingkat Pemeliharaan</th>
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
<!-- <script src="{{asset('global_assets/scripts/datatable.js')}}" type="text/javascript"></script> -->
<script src="{{asset('global_assets/plugins/datatables/datatables.min.js')}}" type="text/javascript"></script>
<!-- <script src="{{asset('global_assets/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js')}}" type="text/javascript"></script> -->
<!-- <script src="{{asset('global_assets/js/demo_pages/datatables_basic.js')}}"></script> -->
<script>
$(document).ready(function () {
//    alert('aa');
    var tabel = $('#tabel').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": "{{ route('master::kondisi-data') }}",
            "columns": [
                {data: 'kode'},
                {data: 'name'},
                {data: 'nilai_level'},
                {data: 'tingkat_pemeliharaan'},
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
        $("#kondisi-form").removeClass('hidden');
        $("#kondisi-data").addClass('hidden');
    });
});
</script>
@endsection