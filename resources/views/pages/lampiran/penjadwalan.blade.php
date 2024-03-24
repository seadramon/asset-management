@extends('layouts.main')

@section('title', 'Lampiran Efektifitas Penjadwalan - Asset Management')

@section('pagetitle', 'Lampiran Efektifitas Penjadwalan')

@section('content') 
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="card {{($data=='')?'':'hidden'}}" id="instalasi-data">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">List Lampiran Efektifitas Penjadwalan</h5>
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

                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Pilih Lokasi</label>
                    <div class="col-lg-10">
                        {!! Form::select('instalasi', $instalasi, null, ['class'=>'form-control select2', 'id'=>'instalasi']) !!}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Pilih Bagian</label>
                    <div class="col-lg-10">
                        {!! Form::select('bagian', $bagian, null, ['class'=>'form-control select2', 'id'=>'bagian']) !!}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Pilih Minggu</label>
                    <div class="col-lg-10">
                        {!! Form::select('minggu', $week, null, ['class'=>'form-control select2', 'id'=>'minggu']) !!}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Pilih Tahun</label>
                    <div class="col-lg-10">
                        {!! Form::text('tahun', $tahun, ['class'=>'form-control', 'id'=>'year']) !!}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Status</label>
                    <div class="col-lg-10">
                        {!! Form::select('status', $status, null, ['class'=>'form-control select2', 'id'=>'status']) !!}
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-lg-12">
                        <button id="tampil" class="btn btn-primary legitRipple">Tampilkan</button>
                    </div>
                </div>
                <!-- ./notifikasi -->

                <!-- Table -->
                <table class="table datatable-basic" id="tabel">
                    <thead>
                        <tr>
                            <th>Aset ID</th>
                            <th>52w ID</th>
                            <th>Urutan Minggu</th>
                            <th>Nama Aset</th>
                            <th>Kode Aset</th>
                            <th>Proses Disposisi</th>
                            <th>Pelaksanaan</th>
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
        "ajax": "{{ route('lampiranev::data-penjadwalan') }}",
        "columns": [    
            {data: 'aset_id', name: 'aset.id', defaultContent: '-'},
            {data: 'ms52wid', name: 'ms_52w.id', defaultContent: '-'},
            {data: 'urutan_minggu', name: 'urutan_minggu', defaultContent: '-'},
            {data: 'nama_aset', name: 'aset.nama_aset', defaultContent: '-'},
            {data: 'kode_aset', name: 'aset.kode_aset', defaultContent: '-'},
            {data: 'petugas', name: 'petugas', defaultContent: '-'},
            {data: 'statusmsg', orderable: false, searchable: false}
        ],
        "order": [[0, 'asc']],
        "autoWidth": false,
        "dom": '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
        "pagingType": "simple_numbers",
        "language": {
            search: '<span>Filter:</span> _INPUT_',
            sEmptyTable: 'Tidak ada data, Jadwal belum diisi',
            searchPlaceholder: 'Type to filter...',
            lengthMenu: '<span>Show:</span> _MENU_',
            paginate: { 'first': 'First', 'last': 'Last', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
        }
    });

    $('#tampil').click(function() {
        var instalasi = $("#instalasi").val();
        var bagian = $("#bagian").val();
        var minggu = $("#minggu").val();
        var year = $("#year").val();
        var status = $("#status").val();
        var url = "{{ url('lampiranev/data-penjadwalan') }}?instalasi=" + instalasi + '&bagian=' + bagian + '&minggu=' + minggu + '&year=' + year + '&status=' + status;

        // console.log(url);
        $('#tabel').DataTable().ajax.url(url).load();
    });
});
</script>
@endsection