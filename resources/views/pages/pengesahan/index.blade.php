@extends('layouts.main')

@section('title', 'Home - Asset Management')

@section('content')
    <!-- Form inputs -->
    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title">Pengesahan Data Asset</h5>
        </div>

        <div class="card-body">

            <div class="alert alert-danger alert-styled-right alert-dismissible">
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                Pilih Asset yang akan disahkan
            </div>

            <div class="alert alert-success alert-styled-right alert-dismissible">
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                Data Berhasil disahkan
            </div>

        	<div class="row" style="overflow-x: scroll;">          
            	<div class="col-md-12">
            		<button id="verify-btn" class="btn btn-success btn-sm"><i class="fa fa-check-circle"></i> Pengesahan
                    </button>
            	</div>
            	<br>
            	<br>
            	<div class="col-xs-12">
            		<!-- <div class="table-responsive"> -->
	            		<table class="table table-hover" id="tabel" style="width: 100%;">
	                        <thead>
	                            <tr>  
                                    <th>&nbsp;</th>                
                                    <th>Jenis</th>                  
                                    <th>Kategori</th>
                                    <th>Sub Kategori</th>
                                    <th>Sub Sub Kategori</th>
                                    <th>Kode Aset</th>
                                    <th>Nama Aset</th>
	                                <th>Instalasi</th>
	                                <th>Lokasi</th>
	                                <th>Ruangan</th>
	                                <th>Kondisi</th>
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
    $(document).ready(function () {
        $(".alert-danger").hide();
        $(".alert-success").hide();

        $('#tabel').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": "{{ route('pengesahan::data') }}",
            "scrollX": true,
            "columns": [
                {data: 'select_orders', orderable: false, searchable: false},
                {data: 'type.name', name: 'type.name', defaultContent: '-'},
                {data: 'kategori.name', name: 'kategori.name', defaultContent: '-'},
                {data: 'subkategori.name', defaultContent: '-'},
                {data: 'subsubkategori_name', name: 'subsubkategori.name', defaultContent: '-'},
                {data: 'kode_aset', defaultContent: '-'},
                {data: 'nama_aset', defaultContent: '-'},
                {data: 'instalasi.name', defaultContent: '-'},
                {data: 'lokasi.name', defaultContent: '-'},
                {data: 'ruangan_name', name: 'ruangan.name', defaultContent: '-'},
                {data: 'kondisi.name', defaultContent: '-'}
            ],
            "order": [[6, 'asc']]
        });

        $("#verify-btn").click(function(){
            // console.log('test');
            var verf = [];
            $.each($("input[name='pengesahan']:checked"), function(){
                verf.push($(this).val());
            });

            if ( verf.length > 0 ) {
                $.ajax({
                    type: "post",
                    data: {pengesahan:verf},
                    url: "{{ route('pengesahan::simpan') }}",
                    success: function(ret) {
                        if (ret.result == "success") {
                            $('#tabel').DataTable().ajax.reload();
                            $(".alert-success").show();
                        }
                    }
                })
            } else {
                $(".alert-danger").show();
            }
        });
    });
</script>
@stop