@extends('layouts.main')

@section('title', 'Pemindaham Aset - Asset Management')

@section('pagetitle', 'Pemindaham Aset')

@section('content') 
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="card {{($data=='')?'':'hidden'}}" id="instalasi-data">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">List Pemindahan Aset</h5>
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
				
                <a href="{{ route('pemindahan::pemindahan-entri') }}" id="tambah-btn" class="btn btn-success legitRipple"><i class="fa fa-plus"></i> Tambah Baru</a>

                <!-- Table -->
                <table class="table datatable-basic" id="tabel">
                    <thead>
                        <tr>
                            <th>Kode</th>     
                            <th>Aset</th>
                            <th>Lokasi Baru</th>
                            <th>Ruang Baru</th>
                            <th>Lokasi Lama</th>
                            <th>Ruang Lama</th>
                            <th>Tanggal Pindah</th>
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
        "ajax": "{{ route('pemindahan::pemindahan-data') }}",
        "columns": [    
            {data: 'id'},        
            {data: 'aset.nama_aset', name: 'aset.nama_aset', defaultContent: '-'},
            {data: 'lokasi_baru.name', name: 'lokasi_baru.name', defaultContent: '-'},
            {data: 'ruangan_baru.name', name: 'ruangan_baru.name', defaultContent: '-'},
            {data: 'lokasi_lama.name', name: 'lokasi_lama.name', defaultContent: '-'},
            {data: 'ruangan_lama.name', name: 'ruangan_lama.name', defaultContent: '-'},
            {data: 'tgl_pindah', defaultContent: 'belum diset'},
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
});
</script>
@endsection