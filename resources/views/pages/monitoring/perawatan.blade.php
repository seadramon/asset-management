@extends('layouts.main')

@section('title', 'Perawatan Aset - Asset Management')

@section('pagetitle', 'Perawatan Aset')

@section('content') 
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="card {{($data=='')?'':'hidden'}}" id="instalasi-data">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">List Perawatan Aset</h5>
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

                <?php /*
                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Pilih Periode</label>
                    <div class="col-lg-10">
                        {!! Form::text('period', null, ['class'=>'form-control', 'id'=>'monthpicker']) !!}
                    </div>
                </div>
                */?>
                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Pilih Tahun</label>
                    <div class="col-lg-10">
                        {!! Form::text('tahun', date('Y'), ['class'=>'form-control', 'id'=>'year']) !!}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Status</label>
                    <div class="col-lg-10">
                        {!! Form::select('status', $status, null, ['class'=>'form-control select2', 'id'=>'status']) !!}
                    </div>
                </div>

                <!-- Table -->
                <table class="table datatable-basic" id="tabel">
                    <thead>
                        <tr>
                            <th>Kode</th>     
                            <th>Aset</th>
                            <th>Kode Aset</th>
                            <th>Bagian</th>
                            <th>Instalasi</th>
                            <th>Hari / Minggu ke</th>
                            <th>Petugas</th>
                            <th>Tanggal WO Terbit</th>
                            <th>Kondisi</th>
                            <th>Menu</th>
                            <th>Status</th>
                            <!-- <th>Form</th> -->
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
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>

<script src="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.js')}}"></script>
<link href="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.css')}}" rel="stylesheet"/>
<script>
$(document).ready(function () {
//    alert('aa');

    $("#monthpicker").datepicker({
        format: "MM-yyyy",
        viewMode: "months", 
        minViewMode: "months"
    });
    $("#year").datepicker({
        format: "yyyy",
        viewMode: "years", 
        minViewMode: "years"
    });

    $(".select2").select2();

    $('#tabel').DataTable({
        "dom": 'Bfrtip',
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('perawatan::perawatan-data') }}",
        "columns": [    
            {data: 'id'},        
            {data: 'komponen.nama_aset', name: 'komponen.nama_aset', defaultContent: '-'},
            {data: 'komponen.kode_aset', name: 'komponen.kode_aset', defaultContent: '-'},
            {data: 'bagian.name', name: 'bagian.name', defaultContent: '-'},
            {data: 'instalasi.name', name: 'instalasi.name', defaultContent: '-'},
            {data: 'ms4w.hari', name: 'ms4w.hari', defaultContent: '-'},
            {data: 'petugas_id', name: 'petugas_id', defaultContent: '-'},
            {data: 'tanggal', name: 'tanggal', defaultContent: '-'},
            {data: 'kondisi', name: 'kondisi', defaultContent: '-'},
            {data: 'menu', orderable: false, searchable: false},
            {data: 'statusmsg', name: 'statusmsg', orderable: false, searchable: false}
            // {data: 'monitoringform', name: 'monitoringform', orderable: false, searchable: false}
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

    $('#year').change(function() {
        if ($(this).val() != '') {
            $('#tabel').DataTable().ajax.url("{{ url('perawatan/data') }}?period=" + $(this).val() + '&status=' + $("#status").val()).load();
        } /*else {
            $('#tabel').DataTable().ajax.url("{{ route('perawatan::perawatan-data') }}").load();
        }*/
    });

    $('#status').change(function() {
        if ($(this).val() != '') {
            $('#tabel').DataTable().ajax.url("{{ url('perawatan/data') }}?period=" + $("#year").val() + '&status=' + $(this).val()).load();
        }
    });
});
</script>
@endsection