@extends('layouts.main')

@section('title', 'Monitoring Aset - Asset Management')

@section('pagetitle', 'Monitoring Aset')

@section('content') 
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="card {{($data=='')?'':'hidden'}}" id="instalasi-data">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">List Monitoring Aset</h5>
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
                @if (Session::has('warning'))
                    <div class="alert alert-warning alert-styled-right alert-arrow-right alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        {{ Session::get('warning', 'Warning') }}
                    </div>
                @endif

                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Pilih Lokasi</label>
                    <div class="col-lg-10">
                        {!! Form::select('instalasi', $instalasi, null, ['class'=>'form-control select2', 'id'=>'instalasi']) !!}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Pilih Tahun</label>
                    <div class="col-lg-10">
                        {!! Form::text('tahun', $tahun, ['class'=>'form-control', 'id'=>'year']) !!}
                    </div>
                </div>
                <!-- ./notifikasi -->
				
                <!-- <a href="{{ route('monitoring::monitoring-entri') }}" id="tambah-btn" class="btn btn-success legitRipple"><i class="fa fa-plus"></i> Tambah Baru</a> -->

                <!-- Table -->
                <table class="table datatable-basic" id="tabel">
                    <thead>
                        <tr>
                            <!-- <th>ID</th>      -->
                            <th>Lokasii</th>    
                            <th>Aset</th>
                            <th>Minggu</th>
                            <th>Hari</th>
                            <th>Petugas</th>
                            <th>Tahun</th>
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
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>
<!-- <script src="{{asset('global_assets/scripts/datatable.js')}}" type="text/javascript"></script> -->
<script src="{{asset('global_assets/plugins/datatables/datatables.min.js')}}" type="text/javascript"></script>
<!-- <script src="{{asset('global_assets/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js')}}" type="text/javascript"></script> -->
<script src="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.js')}}"></script>
<link href="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.css')}}" rel="stylesheet"/>
<script>
$(document).ready(function () {
//    alert('aa');
    $(".select2").select2();

    $("#year").datepicker({
        format: "yyyy",
        viewMode: "years", 
        minViewMode: "years"
    });

    $('#tabel').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('monitoring::monitoring-data') }}",
        "columns": [    
            // {data: 'id', defaultContent: '-'},        
            {data: 'lokasi', name: 'instalasi.name', defaultContent: '-'},
            {data: 'nama_aset', name: 'aset.nama_aset', defaultContent: '-'},
            {data: 'urutan_minggu', name: 'urutan_minggu', defaultContent: '-'},
            {data: 'hari', name: 'hari', defaultContent: '-'},
            {data: 'petugas', name: 'petugas', defaultContent: '-'},
            {data: 'tahun', name: 'tahun', defaultContent: '-', orderable: false, searchable: false},
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

    $('#instalasi').change(function() {
        // alert("{{ url('monitoring/data') }}/" + $(this).val());
        console.log("{{ url('monitoring/data') }}?idlok=" + $(this).val() + '&year=' + $("#year").val());

        if ($(this).val() != '') {
            $('#tabel').DataTable().ajax.url("{{ url('monitoring/data') }}?idlok=" + $(this).val() + '&year=' + $("#year").val()).load();
        } else {
            $('#tabel').DataTable().ajax.url("{{ route('monitoring::monitoring-data') }}").load();
        }
    });

    $('#year').change(function() {
        console.log("{{ url('monitoring/data') }}?idlok=" + $("#instalasi").val() + '&year=' + $(this).val());

        if ($(this).val() != '') {
            $('#tabel').DataTable().ajax.url("{{ url('monitoring/data') }}?idlok=" + $("#instalasi").val() + '&year=' + $(this).val()).load();
        } else {
            $('#tabel').DataTable().ajax.url("{{ route('monitoring::monitoring-data') }}").load();
        }
    });
});
</script>
@endsection