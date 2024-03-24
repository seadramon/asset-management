@extends('layouts.main')

@section('title', 'Lampiran Perhitungan Availability - Asset Management')

@section('pagetitle', 'Lampiran Perhitungan Availability')

@section('content') 
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="card {{($data=='')?'':'hidden'}}" id="instalasi-data">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">List Lampiran Perhitungan Availability</h5>
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
                    <label class="col-form-label col-lg-2">Pilih Periode</label>
                    <div class="col-lg-10">
                        {!! Form::text('periode', null, ['class'=>'form-control', 'id'=>'monthpicker', 'required']) !!}
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
                            <th>Nama Aset</th>
                            <th>Kode Aset</th>
                            <th>Tanggal Awal</th>
                            <th>Tanggal Akhir</th>
                            <th>Unavailable</th>
                            <th>Urutan Jumlah tidak beroperasi</th>
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

<script src="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.js')}}"></script>
<link href="{{url('global_assets/plugins/datepicker/bootstrap-datepicker.css')}}" rel="stylesheet"/>

<script src="{{asset('global_assets/plugins/datatables/datatables.min.js')}}" type="text/javascript"></script>
<!-- <script src="{{asset('global_assets/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js')}}" type="text/javascript"></script> -->
<script>
$(document).ready(function () {
//    alert('aa');
    $(".select2").select2();

    $("#monthpicker").datepicker({
        format: "MM-yyyy",
        viewMode: "months", 
        minViewMode: "months"
    });

    $('#tabel').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('lampiranev::data-available') }}",
        "columns": [    
            {data: 'nama_aset', name: 'aset.nama_aset', defaultContent: '-'},
            {data: 'kode_aset', name: 'aset.kode_aset', defaultContent: '-'},
            {data: 'tgl_start', name: 'prb_data.tgl_start', defaultContent: '-'},
            {data: 'tgl_finish', name: 'prb_data.tgl_finish', defaultContent: '-'},
            {data: 'unavailable', name: 'unavailable', defaultContent: '-', orderable: false, searchable: false},
            {data: 'seq_numb', name: 'seq_numb', defaultContent: '-', orderable: false, searchable: false},
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

    $('#tampil').click(function() {
        var instalasi = $("#instalasi").val();
        var bagian = $("#bagian").val();
        var periode = $("#monthpicker").val();

        var url = "{{ url('lampiranev/data-available') }}?instalasi=" + instalasi + '&bagian=' + bagian + '&periode=' + periode;

        console.log(url);
        $('#tabel').DataTable().ajax.url(url).load();
    });
});
</script>
@endsection