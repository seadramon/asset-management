@extends('layouts.main')

@section('title', 'Master Ruangan - Asset Management')

@section('pagetitle', 'Master Ruangan')

@section('content') 
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card {{($data=='')?'hidden':''}}" id="ruangan-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Form Ruangan</h5>
			</div>

            <div class="card-body">
                <!-- BEGIN FORM-->
                <form action="{{route('master::ruangan-simpan')}}" method="post" class="form-horizontal">
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="text" class="hidden" name="tipe" value="{{($data=='')?'0':'1'}}">
                        <input type="text" class="hidden" name="id" value="{{($data=='')?'':$data->id }}">

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Kode</label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control input-circle" name="kode" placeholder="Masukkan Kode" value="{{$data->kode or ''}}" 
                                <?php echo !empty($data)?"readonly":""; ?>>
                            </div>
                        </div>

	                    <div class="form-group row">
							<label class="col-form-label col-lg-2">Lokasi</label>
							<div class="col-lg-10">
								<select class="form-control select2" name="instalasi" style="width: 100%;">
                                    @foreach($instalasi as $row)
                                    <optgroup label="{{$row->name}}">
                                        @foreach($row->lokasi as $baris)
                                        <option value="{{$row->id.'#'.$baris->id}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$baris->name}}</option>
                                        @endforeach
                                    </optgroup>
                                    @endforeach
                                </select>
							</div>
						</div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Nama Ruangan</label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control input-circle" name="nama" placeholder="Masukkan Nama Ruangan" value="{{$data->name or ''}}">
                            </div>
                        </div>
                	</fieldset>

                	<div class="text-right">
						<button type="submit" class="btn btn-primary legitRipple">Simpan</button> 
	                    <a href="{{route('master::ruangan-link')}}">
	                    	<button type="button" class="btn btn-light legitRipple">Kembali</button></a>
					</div>
                </form>
                <!-- END FORM-->
            </div>
        </div>

        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="card {{($data=='')?'':'hidden'}}" id="ruangan-data">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">List Ruangan</h5>
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
                            <th>Ruangan</th>
                            <th>Kode Lokasi</th>
                            <th>Lokasi</th>
                            <th>Kode Instalasi</th>
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
<!-- <script src="{{asset('global_assets/plugins/datatables/datatable.js')}}" type="text/javascript"></script> -->
<script src="{{asset('global_assets/plugins/datatables/datatables.min.js')}}" type="text/javascript"></script>
<!-- <script src="{{asset('global_assets/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js')}}" type="text/javascript"></script> -->
<script src="{{asset('global_assets/plugins/select2/js/select2.full.min.js')}}" type="text/javascript"></script>
<script>
$(document).ready(function () {
//    alert('aa');
    var data = "{{!empty($data)?1:0}}";
    //alert(data);
    if (data > 0) {
        //alert(data);
        $("[name=instalasi]").val("{{@$data->instalasi_id.'#'.@$data->lokasi_id}}").change();
    }
    $('#tabel').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('master::ruangan-data') }}",
        "columns": [
            {data: 'kode'},
            {data: 'name'},
            {data: 'lokasi.kode', name: 'lokasi.kode'},
            {data: 'lokasi.name', name: 'lokasi.name'},
            {data: 'lokasi.instalasi.kode', name: 'lokasi.instalasi.kode', orderable: false},
            {data: 'lokasi.instalasi.name', name: 'lokasi.instalasi.name', orderable: false},
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
    $(".select2").select2();
    $("#tambah-btn").click(function () {
        $("#ruangan-form").removeClass('hidden');
        $("#ruangan-data").addClass('hidden');
    });
});
</script>
@endsection