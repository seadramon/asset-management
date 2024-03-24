@extends('layouts.main')

@section('title', 'Manajemen Strategi - Asset Management')

@section('content')
    <!-- Form inputs -->
    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title">Manajemen Strategi List</h5>
            <div class="header-elements">
                <div class="list-icons">
                    <a class="list-icons-item" data-action="collapse"></a>
                    <a class="list-icons-item" data-action="reload"></a>
                    <a class="list-icons-item" data-action="remove"></a>
                </div>
            </div>
        </div>

        <div class="card-body">
        	<div class="row" style="overflow-x: scroll;">          
            	<div class="col-md-12">
            		<a href="{{route('mstrategi-entri::kategori-simpan')}}" id="tambah-btn" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Tambah Baru
                    </a>
            	</div>
            	<br>
            	<br>
            	<div class="col-xs-12">
            		<!-- <div class="table-responsive"> -->
	            		<table class="table table-hover" id="tabel" style="width: 100%;">
	                        <thead>
	                            <tr>                                    
	                                <th>Template</th>
	                                <th>Kelompok</th>
	                                <th>Komponen</th>
	                                <th>Nilai</th>
	                            </tr>
	                        </thead>
	                        <tbody>
	                        </tbody>
	                    </table>
	                <!-- </div> -->
            	</div>
            </div>
        </div>
    </div>
@stop

@section('css')
<!-- <link href="{{asset('global_assets/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" /> -->
<style type="text/css">
	table.dataTable thead th, table.dataTable thead td{
		border-bottom: 1px solid #a7a0a0;
	}
    .dataTables_length label{
        position: absolute;
        right: 0;
    }
</style>
@endsection
@section('js')
<!-- <script src="{{asset('global_assets/scripts/datatable.js')}}" type="text/javascript"></script> -->
<script src="{{asset('global_assets/plugins/datatables/datatables.min.js')}}" type="text/javascript"></script>
<!-- <script src="{{asset('global_assets/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js')}}" type="text/javascript"></script> -->
<!-- <script src="{{asset('global_assets/js/demo_pages/datatables_basic.js')}}"></script> -->

<script type="text/javascript">
	$('#tabel').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ url('aset/data/data') }}",
        "scrollX": true,
        "columns": [
            {data: 'menu', orderable: false, searchable: false},
            {data: 'type.name', name: 'type.name'},
            {data: 'kategori.name', name: 'kategori.name'},
            {data: 'subkategori.name'},
            {data: 'subsubkategori_name', name: 'subsubkategori.name'},
            {data: 'kode_aset'},
            {data: 'nama_aset'},
            {data: 'instalasi.name'},
            {data: 'lokasi.name'},
            {data: 'ruangan_name', name: 'ruangan.name'},
            {data: 'kondisi.name'}
        ],
        "order": [[6, 'asc']]
    });
</script>
@stop