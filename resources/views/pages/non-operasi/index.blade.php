@extends('layouts.main')

@section('title', 'Aduan Non Operasi - Asset Management')

@section('pagetitle', 'Aduan Non Operasi')

@section('content') 
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="card {{($data=='')?'':'hidden'}}" id="instalasi-data">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">List Aduan Non Operasi</h5>
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
                <!-- ./notifikasi -->

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
				
                @if (!in_array(namaRole(), config('custom.roleExceptionNoAdmin')))
                    <a href="{{ route('non-operasi::aduan-entri') }}" id="tambah-btn" class="btn btn-success legitRipple"><i class="fa fa-plus"></i> Tambah Baru</a>
                @endif

                <!-- Table -->
                <table class="table datatable-basic" id="tabel">
                    <thead>
                        <tr>
                            <th>ID</th>     
                            <th>Judul</th>    
                            <th>Pelapor</th>    
                            <th>Lokasi</th>
                            <th>Sifat</th>
                            <th>Bagian</th>
                            <th>Instalasi</th>
                            <th>Petugas</th>
                            <th>Tanggal WO Terbit</th>
                            <th>Tindakan</th>
                            <th>Status</th>
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
    $("#year").datepicker({
        format: "yyyy",
        viewMode: "years", 
        minViewMode: "years"
    });

    $(".select2").select2();

    $('#tabel').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('non-operasi::aduan-data') }}",
        "columns": [    
            {data: 'id', name: 'id', defaultContent: '-'},        
            {data: 'judul', name: 'judul', defaultContent: '-'},
            {data: 'pelapor.nama', name: 'pelapor', defaultContent: '-', orderable: false, searchable: false},
            {data: 'lokasi', name: 'lokasi', defaultContent: '-'},
            {data: 'sifat', name: 'sifat', defaultContent: '-'},
            {data: 'jabatan.namajabatan', name: 'bagian', defaultContent: '-', orderable: false, searchable: false},
            {data: 'instalasi.name', name: 'instalasi', defaultContent: '-', orderable: false, searchable: false},
            {data: 'petugas.nama', name: 'petugas', defaultContent: '-', orderable: false, searchable: false},
            {data: 'created_at', name: 'created_at', defaultContent: '-'},
            {data: 'menu', orderable: false, searchable: false},
            {data: 'status', orderable: false, searchable: false}
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
            console.log('test');
            $('#tabel').DataTable().ajax.url("{{ url('NonOperasi/aduan-data') }}?period=" + $(this).val() + '&status=' + $("#status").val()).load();
        } /*else {
            $('#tabel').DataTable().ajax.url("{{ route('perawatan::perawatan-data') }}").load();
        }*/
    });

    $('#status').change(function() {
        if ($(this).val() != '') {
            $('#tabel').DataTable().ajax.url("{{ url('NonOperasi/aduan-data') }}?period=" + $("#year").val() + '&status=' + $(this).val()).load();
        }
    });
});
</script>
@endsection