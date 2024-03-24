@extends('layouts.main')

@section('title', 'Perbaikan Aset - Asset Management')

@section('css')
<style type="text/css">
    .dropdown-menu {
    overflow: overlay !important; 
    overflow-x: overlay !important; 
    overflow-y: overlay !important; 
} 
</style>
@endsection

@section('pagetitle', 'Perbaikan Aset')

@section('content') 
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="card {{($data=='')?'':'hidden'}}" id="instalasi-data">
            @if (!in_array(namaRole(), ['SPV PENGOLAHAN', 'MANAJER PRODUKSI']))
                <div class="card-header header-elements-inline">
                    <h5 class="card-title">List Perbaikan Aset dari Monitoring</h5>
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
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                    <!-- ./table -->
    			</div>
            @endif

            <div class="card-header header-elements-inline">
                <h5 class="card-title">List Perbaikan Aset dari Aduan</h5>
            </div>

            <div class="card-body">
                <!-- Notifikasi -->
                @if (Session::has('errorAduan'))
                    <div class="alert alert-danger alert-styled-right alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        {{ Session::get('errorAduan', 'Error') }}
                    </div>
                @endif
                @if (Session::has('successAduan'))
                    <div class="alert alert-success alert-styled-right alert-arrow-right alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        {{ Session::get('successAduan', 'Success') }}
                    </div>
                @endif
                <!-- ./notifikasi -->

                @if (in_array(namaRole(), ['SPV PENGOLAHAN', 'MANAJER PRODUKSI']))
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
                @endif

                <!-- Table -->
                <table class="table datatable-basic" id="tabelAduan">
                    <thead>
                        <tr>
                            <th>Recid</th>     
                            <th>Judul</th>     
                            <th>Pelapor</th>     
                            <!-- <th>Spv Pemeliharaan</th>      -->
                            <th>Aset</th>
                            <th>Kode Aset</th>
                            <th>Lokasi</th>
                            <th>Instalasi</th>
                            <th>Bagian</th>
                            <th>Tanggal WO Terbit</th>
                            <th>Kondisi</th>
                            <th>Petugas</th>
                            <th>Menu</th>
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
<!-- <script src="{{asset('global_assets/scripts/datatable.js')}}" type="text/javascript"></script> -->
<script src="{{asset('global_assets/plugins/datatables/datatables.min.js')}}" type="text/javascript"></script>
<!-- <script src="{{asset('global_assets/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js')}}" type="text/javascript"></script> -->
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>

<script src="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.js')}}"></script>
<link href="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.css')}}" rel="stylesheet"/>
<script>
$(document).ready(function () {
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

//    alert('aa');
    $('#tabel').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('perbaikan::perbaikan-data') }}",
        "columns": [    
            {data: 'id', name: 'id', defaultContent:'-'},        
            {data: 'komponen.nama_aset', name: 'komponen.nama_aset', defaultContent: '-'},
            {data: 'komponen.kode_aset', name: 'komponen.kode_aset', defaultContent: '-'},
            {data: 'bagian.name', name: 'bagian.name', defaultContent: '-'},
            {data: 'instalasi.name', name: 'instalasi.name', defaultContent: '-'},
            {data: 'ms4w.hari', name: 'ms4w.hari', defaultContent: '-'},
            {data: 'petugas_id', name: 'petugas_id', defaultContent: '-'},
            {data: 'tanggal', name: 'tanggal', defaultContent: '-'},        
            {data: 'kondisi', name: 'kondisi', defaultContent: '-'},        
            {data: 'menu', orderable: false, searchable: false},
            {data: 'statusmsg', orderable: false, searchable: false}
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

    $('#tabelAduan').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('perbaikan::perbaikan-dataAduan') }}",
        "columns": [    
            {data: 'id', name: 'prb_data.id', defaultContent: '-'}, 
            {data: 'aduan_judul', name: 'prb_data.aduan_judul', defaultContent: '-'}, 
            {data: 'pelapor.nama', name: 'pelapor', defaultContent: '-', searchable: false, orderable: false},
            {data: 'nama_aset', name: 'aset.nama_aset', defaultContent: '-'},
            {data: 'kode_aset', name: 'aset.kode_aset', defaultContent: '-'},
            {data: 'lokasinm', name: 'lokasi.name', defaultContent: '-', orderable: false},
            {data: 'instalasi', name: 'instalasi.name', defaultContent: '-', orderable: false},
            {data: 'bagian', name: 'bagian', defaultContent: '-', searchable: false},
            {data: 'tanggal', name: 'prb_data.tanggal', defaultContent: '-'},
            {data: 'kondisi', name: 'prb_data.kondisi', defaultContent: '-'},

            {data: 'petugas_id', name: 'prb_data.petugas_id', defaultContent: '-'},
            {data: 'menu', orderable: false, searchable: false},
            {data: 'statusmsg', name: 'statusmsg', orderable: false, searchable: false}
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

    /*$(t.table().container()).on('keyup', 'thead input', function (event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);

        if (this.value == "") {
            t.column($(this).parent().index() + ':visible').search( this.value ).draw();
        } else {
            if(keycode == '13'){
                t.column($(this).parent().index() + ':visible').search( this.value ).draw();
            }
        }
        
        event.stopPropagation();
    });*/

    $('#year').change(function() {
        if ($(this).val() != '') {
            $('#tabel').DataTable().ajax.url("{{ url('perrbaikan/data') }}?period=" + $(this).val() + '&status=' + $("#status").val()).load();
            $('#tabelAduan').DataTable().ajax.url("{{ url('perrbaikan/dataaduan') }}?period=" + $(this).val() + '&status=' + $("#status").val()).load();
        } /*else {
            $('#tabel').DataTable().ajax.url("{{ route('perawatan::perawatan-data') }}").load();
        }*/
    });

    $('#status').change(function() {
        if ($(this).val() != '') {
            $('#tabel').DataTable().ajax.url("{{ url('perrbaikan/data') }}?period=" + $("#year").val() + '&status=' + $(this).val()).load();
            $('#tabelAduan').DataTable().ajax.url("{{ url('perrbaikan/dataaduan') }}?period=" + $("#year").val() + '&status=' + $(this).val()).load();
        }
    });
});
</script>
@endsection