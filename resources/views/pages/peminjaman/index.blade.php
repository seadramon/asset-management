@extends('layouts.main')

@section('title', 'Peminjaman Aset - Asset Management')

@section('pagetitle', 'Peminjaman Aset')

@section('content') 
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="card {{($data=='')?'':'hidden'}}" id="kondisi-data">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">List Peminjaman</h5>
            </div>

            <div class="card-body">
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
                <a href="{{ route('peminjaman::peminjaman-entri') }}" id="tambah-btn" class="btn btn-success legitRipple"><i class="fa fa-plus"></i> Tambah Baru</a>

                <table class="table datatable-basic" id="tabel">
                    <thead>
                        <tr>         
                            <th rowspan="2">Kode</th>
                            <th rowspan="2">Aset</th>
                            <th colspan="5">Tanggal</th>
                            <th rowspan="2">Menu</th>
                        </tr>
                        <tr>
                            <th>Pengajuan</th>
                            <th>Renc Pinjam</th>
                            <th>Pinjam</th>
                            <th>Renc Kembali</th>
                            <th>Kembali</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
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
<!-- <script src="{{asset('global_assets/js/demo_pages/datatables_basic.js')}}"></script> -->
<script>
$(document).ready(function () {
//    alert('aa');
    var tabel = $('#tabel').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": "{{ route('peminjaman::peminjaman-data') }}",
            "columns": [
                {data: 'id'},
                {data: 'aset.nama_aset', name: 'aset.nama_aset', defaultContent: '-'},
                {data: 'pengajuan', name: 'pengajuan', defaultContent: '-'},
                {data: 'rencana_dipinjam', name: 'rencana_dipinjam', defaultContent: '-'},
                {data: 'dipinjam', name: 'dipinjam', defaultContent: '-'},
                {data: 'renc_kembali', name: 'renc_kembali', defaultContent: '-'},
                {data: 'dikembalikan', name: 'dikembalikan', defaultContent: '-'},
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

    $("#tambah-btn").click(function () {
        $("#kondisi-form").removeClass('hidden');
        $("#kondisi-data").addClass('hidden');
    });
});
</script>
@endsection