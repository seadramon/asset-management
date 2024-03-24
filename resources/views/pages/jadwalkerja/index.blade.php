@extends('layouts.main')

@section('title', 'Jadwal Kerja Pompa - Asset Management')

@section('pagetitle', 'Jadwal Kerja Pompa')

@section('content') 
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="card {{($data=='')?'':'hidden'}}" id="instalasi-data">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">List Jadwal Kerja Pompa</h5>
            </div>

            <div class="card-body">
                <!-- Notifikasi -->
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
                <!-- ./notifikasi -->
                <a href="{{ route('jadwalkerja::jadwalkerja-entri') }}" id="tambah-btn" class="btn btn-success legitRipple"><i class="fa fa-plus"></i> Tambah Baru</a>
                <!-- Table -->
                <table class="table datatable-basic" id="tabel">
                    <thead>
                        <tr>
                            <!-- <th>Kode</th>      -->
                            <th>Aset</th>
                            <th>Lokasi</th>
                            <th>Minggu</th>
                            <th>Menu</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
                <!-- ./table -->
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
<script>
$(document).ready(function () {
//    alert('aa');
    $('#tabel').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('jadwalkerja::jadwalkerja-data') }}",
        "columns": [    
            // {data: 'id'},        
            {data: 'nama_aset', name: 'nama_aset', defaultContent: '-'},
            {data: 'name', name: 'name', defaultContent: '-'},
            {data: 'minggu', name: 'minggu', defaultContent: '-'},
            {data: 'menu', orderable: false, searchable: false}
        ],
        "order": [[0, 'asc']],
        "autoWidth": false,
        "scrollX": true,
        "dom": '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
        "pagingType": "simple_numbers",
        "language": {
            search: '<span>Filter:</span> _INPUT_',
            searchPlaceholder: 'Type to filter...',
            lengthMenu: '<span>Show:</span> _MENU_',
            paginate: { 'first': 'First', 'last': 'Last', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
        }
    });
});
</script>
@endsection