@extends('layouts.main')

@section('title', 'Depresiasi - Asset Management')

@section('pagetitle', 'Daftar Depresiasi')

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
                        @include('components.select_aset')

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Tahun</label>
                            <div class="col-lg-10">
                                {!! Form::text('tahun', date('Y'), ['class'=>'form-control', 'id'=>'year']) !!}
                            </div>
                        </div>
                    </fieldset>
                    <div>
                        <button type="submit" class="btn btn-primary legitRipple">Cari</button>
                        <a href="#" class="btn btn-light legitRipple" id="reset">Reset</a>
                    </div>
                </form>
            </div>
        </div>
        <!-- ./Filter -->

        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="card" id="instalasi-data">
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
                <a href="{{ route('depresiasi::entri') }}" id="tambah-btn" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Tambah Baru
                    </a>

                <!-- Table -->
                <table class="table datatable-basic" id="tabel">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Aset</th>
                            <th>Instalasi</th>
                            <th>Lokasi</th>
                            <th>Ruang</th>
                            <th>Bulan</th>
                            <th>Tahun</th>
                            <th>Depresiasi Bulanan</th>
                            <th>Depresiasi Tahunan</th>
                            <th>Akumulasi Depresiasi</th>
                            <th>Nilai Aset Terakhir</th>
                            <th>Menu</th>
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
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>
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
        "ajax": "{{ route('depresiasi::data') }}",
        "columns": [    
            {data: 'id', name: 'depresiasi.id'},   
            {data: 'asetnya.nama_aset', name: 'asetnya.nama_aset', defaultContent: '-', orderable: false, searchable: false},
            {data: 'instalasi', name: 'instalasi', defaultContent: '-', orderable: false, searchable: false},
            {data: 'lokasi', name: 'lokasi', defaultContent: '-', orderable: false, searchable: false},
            {data: 'ruangan', name: 'ruangan', defaultContent: '-', orderable: false, searchable: false},
            {data: 'bulan.name', name: 'bulan.name', defaultContent: '-', orderable: false, searchable: false},
            {data: 'tahun', name: 'tahun', defaultContent: '-'},
            {data: 'depresiasi_bulanan', name: 'depresiasi_bulanan', defaultContent: '-'},
            {data: 'depresiasi_tahunan', name: 'depresiasi_tahunan', defaultContent: '-'},
            {data: 'akumulasi_depresiasi', name: 'akumulasi_depresiasi', defaultContent: '-'},
            {data: 'nilai_aset', name: 'nilai_aset', defaultContent: '-'},
            {data: 'menu', orderable: false, searchable: false} 
        ],
        "order": [[0, 'desc']],
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

    $("#monthpicker").datepicker({
        format: "MM-yyyy",
        viewMode: "months", 
        minViewMode: "months"
    });

    $("#filterDep").on("submit", function(event){
        event.preventDefault();
 // console.log("test filter");
        var formValues= $(this).serialize();

        if (formValues) {
            $('#tabel').DataTable().ajax.url("{{ url('depresiasi/data') }}?" + formValues).load();
        }
    });

    $("#reset").on("click", function(e) {
        e.preventDefault();

        $(".select2").val("");
        $(".select2").trigger("change");
        
        $('#tabel').DataTable().ajax.url("{{ url('depresiasi/data') }}").load();
    })

    $('#instalasi').change(function () {
        $('#lokasi').empty();
        $('#lokasi').append('<option value="">- Pilih Lokasi -</option>');

        $('#ruang').empty();
        $('#ruang').append('<option value="">- Pilih Ruang -</option>');

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('api-general/master/combo-lokasi')}}/" + $(this).val(),
                success: function(result) {
                    $('#lokasi').append(result.data);
                }
            })

            selectAset();
        }
    });

    $('#lokasi').change(function () {
        $('#ruang').empty();
        $('#ruang').append('<option value="">- Pilih Ruang -</option>');

        if ($(this).val() != '') {
            $.ajax({
                type: "get",
                url: "{{url('api-general/master/combo-ruang')}}/" + $(this).val(),
                success: function(result) {
                    $('#ruang').append(result.data);
                }
            })

            selectAset();
        }
    });

    $('#ruang').change(function () {
        if ($(this).val() != '') {
            selectAset();
        }
    });

    function selectAset()
    {
        $('#aset').empty();
        $('#aset').append('<option value="">- Pilih Aset -</option>');

        var instalasi = $("#instalasi").val();
        var lokasi = $("#lokasi").val();
        var ruang = $("#ruang").val();

        var query = new URLSearchParams({
          instalasi : instalasi, 
          lokasi : lokasi,
          ruang : ruang,
        });

        console.log(query.toString());

        $.ajax({
            type: "get",
            url: "{{url('api-general/master/combo-aset')}}?" + query,
            success: function(result) {
                $('#aset').append(result.data);
            }
        })
    }
});
</script>
@endsection