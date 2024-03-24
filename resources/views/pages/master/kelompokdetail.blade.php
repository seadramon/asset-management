@extends('layouts.main')

@section('title', 'Master Kelompok Detail - Asset Management')

@section('pagetitle', 'Master Kelompok Detail')

@section('content') 
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card {{($data=='')?'hidden':''}}" id="kelompokdetail-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Kelompok Detail Form</h5>
			</div>

            <div class="card-body">
                <!-- BEGIN FORM-->
                <form action="{{route('master::kelompokdetail-simpan')}}" method="post" class="form-horizontal">
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="text" class="hidden" name="tipe" value="{{($data=='')?'0':'1'}}">
                        <input type="text" class="hidden" name="kode" value="{{($data=='')?'':$data->id }}">
                            
	                    <div class="form-group row">
							<label class="col-form-label col-lg-2">Kelompok</label>
							<div class="col-lg-10">
								<select class="form-control select2" name="kelompok" style="width: 100%;">
                                    @foreach($kelompok as $row)
                                    <option value="{{$row->id}}">{{$row->nama}}</option>
                                    @endforeach
                                </select>
							</div>
						</div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Nama Kelompok Detail</label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control input-circle" name="nama" placeholder="Masukkan Nama Kelompok Detail" value="{{$data->nama or ''}}">
                            </div>
                        </div>
                	</fieldset>

                	<div class="text-right">
						<button type="submit" class="btn btn-primary legitRipple">Simpan</button> 
	                    <a href="{{route('master::kelompokdetail-link')}}">
	                    	<button type="button" class="btn btn-light legitRipple">Kembali</button></a>
					</div>
                </form>
                <!-- END FORM-->
            </div>
        </div>

        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="card {{($data=='')?'':'hidden'}}" id="kelompokdetail-data">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Kelompok Detail List</h5>
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
                            <th>Kelompok Detail</th>
                            <th>Kelompok</th>
                            <th>Template</th>
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
<script src="{{asset('global_assets/plugins/select2/js/select2.full.min.js')}}" type="text/javascript"></script>
<script>
$(document).ready(function () {
//    alert('aa');
    var data = "{{!empty($data)?1:0}}";
    //alert(data);
    if (data > 0) {
        //alert(data);
        $("[name=kelompok]").val("{{@$data->ms_kelompok_id}}").change();
    }
    $('#tabel').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('master::kelompokdetail-data') }}",
        "columns": [
            {data: 'nama'},
            {data: 'kelompok.nama', name: 'kelompok.nama'},
            {data: 'kelompok.template.nama', name: 'kelompok.template.nama'},
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
        $("#kelompokdetail-form").removeClass('hidden');
        $("#kelompokdetail-data").addClass('hidden');
    });
});
</script>
@endsection