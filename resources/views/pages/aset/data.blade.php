@extends('layouts.main')

@section('title', 'Home - Asset Management')

@section('content')
    <!-- Filter -->
    <div class="card" id="filter-data">
        <div class="card-header header-elements-inline">
            <h5 class="card-title">Filter/Pencarian</h5>
            <div class="header-elements">
                <div class="list-icons">
                    <a class="list-icons-item rotate-180" data-action="collapse"></a>
                </div>
            </div>
        </div>

        <div class="card-body" style="display: none;">

            <form id="filterDep" method="get">
                <fieldset class="mb-3">
                    @include('components.filter_aset')
                </fieldset>
                <div>
                    <button type="submit" class="btn btn-primary legitRipple">Cari</button>
                    <a href="#" class="btn btn-light legitRipple" id="reset">Reset</a>
                </div>
            </form>
        </div>
    </div>
    <!-- ./Filter -->

    <!-- Form inputs -->
    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title">Data Asset</h5>
            <div class="header-elements">
                <div class="list-icons">
                    <a class="list-icons-item" data-action="collapse"></a>
                    <a class="list-icons-item" data-action="reload"></a>
                    <!-- <a class="list-icons-item" data-action="remove"></a> -->
                </div>
            </div>
        </div>

        <div class="card-body">
        	<div class="row" style="overflow-x: scroll;">          
            	<div class="col-md-12">
                    {!! addMenu('Tambah Baru', 'tambah-btn') !!}
            		
            	</div>
            	<br>
            	<br>
            	<div class="col-xs-12">
            		<!-- <div class="table-responsive"> -->
	            		<table class="table table-hover" id="tabel" style="width: 100%;">
	                        <thead>
	                            <tr>                                    
	                                <th>Menu</th>
                                    <th>Nama Aset</th>
	                                <th>Jenis</th>
	                                <th>Kategori</th>
	                                <th>Sub Kategori</th>
	                                <th>Sub Sub Kategori</th>
	                                <th>Kode Aset</th>
	                                <th>Instalasi</th>
	                                <th>Lokasi</th>
	                                <th>Ruangan</th>
	                                <th>Status</th>
                                    <th>Tanggal Upload</th>
                                    <th>Update Terakhir</th>
                                    <th>Tanggal Pemindahan</th>
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

<script src="{{asset('global_assets/plugins/datatables/datatables.min.js')}}" type="text/javascript"></script>
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>
<script src="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.js')}}"></script>
<link href="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.css')}}" rel="stylesheet"/>

<script type="text/javascript">
	$('#tabel').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ url('aset/data/data') }}",
        "scrollX": true,
        "columns": [
            {data: 'menu', orderable: false, searchable: false},
            {data: 'nama_aset', defaultContent: '-'},
            {data: 'type.name', name: 'type.name', defaultContent: '-'},
            {data: 'kategori.name', name: 'kategori.name', defaultContent: '-'},
            {data: 'subkategori.name', defaultContent: '-'},
            {data: 'subsubkategori_name', name: 'subsubkategori.name', defaultContent: '-'},
            {data: 'kode_aset', defaultContent: '-'},
            {data: 'instalasi.name', defaultContent: '-'},
            {data: 'lokasi.name', defaultContent: '-'},
            {data: 'ruangan_name', name: 'ruangan.name', defaultContent: '-'},
            {data: 'kondisi.name', defaultContent: '-'},
            {data: 'ts_create', defaultContent: '-'},
            {data: 'ts_update', defaultContent: '-'},
            {data: 'pindah_tgl_pindah', name: 'pemindahan.tgl_pindah', defaultContent: '-'}
        ],
        "order": [[6, 'asc']]
    });

    // SUbmit filter
    $("#filterDep").on("submit", function(event){
        event.preventDefault();

        var formValues= $(this).serialize();

        if (formValues) {
            $('#tabel').DataTable().ajax.url("/aset/data/data?" + formValues).load();
        }
    });
    // end:submit filter
</script>
<script src="{{url('assets/js/filteraset.js')}}"></script>
@stop