@extends('layouts.main')

@section('title', 'NonAktif Aset - Asset Management')

@section('pagetitle', 'NonAktif Aset')

@section('content') 
	<div class="card {{($data=='')?'':'hidden'}}" id="instalasi-data">
		<div class="card-header header-elements-inline">
			<h5 class="card-title">List NonAktif Asset</h5>
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
			<!-- ./Notifikasi -->

			<a href="{{ route('nonaktif::nonaktif-entri') }}" id="tambah-btn" class="btn btn-success legitRipple"><i class="fa fa-plus"></i> Tambah Baru</a>

			<!-- Table -->
            <table class="table datatable-basic" id="tabel">
                <thead>
                    <tr>
                        <!-- <th>Kode</th>      -->
                        <th>Aset</th>
                        <th>Instalasi</th>
                        <!-- <th>Kondisi</th> -->
                        <th>Menu</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
            <!-- ./table -->
		</div>
	</div>	

@endsection
@section('js')

<script src="{{asset('global_assets/plugins/datatables/datatables.min.js')}}" type="text/javascript"></script>
<script>
$(document).ready(function () {
//    alert('aa');
    $('#tabel').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('nonaktif::nonaktif-data') }}",
        "columns": [    
            // {data: 'id'},        
            {data: 'nama_aset', name: 'nama_aset', defaultContent: '-'},
            {data: 'instalasi.name', name: 'instalasi.name', defaultContent: '-'},
            // {data: 'kondisi.name', name: 'kondisi.name', defaultContent: '-'},
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