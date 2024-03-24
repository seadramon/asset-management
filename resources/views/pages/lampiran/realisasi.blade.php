@extends('layouts.main')

@section('title', 'Lampiran Realisasi Pemeliharaan - Asset Management')

@section('pagetitle', 'Lampiran Realisasi Pemeliharaan')

@section('content') 
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="card {{($data=='')?'':'hidden'}}" id="instalasi-data">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">List Lampiran Realisasi Pemeliharaan</h5>
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
                    <div class="col-lg-12">
                        <button id="tampil" class="btn btn-primary legitRipple">Tampilkan</button>
                    </div>
                </div>
                <!-- ./notifikasi -->

                <!-- Table Perawatan -->
                <h3>List Perawatan</h3>
                <table class="table datatable-basic" id="tabel">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Aset</th>
                            <th>Kode Aset</th>
                            <th>Metode</th>
                            <th>Tanggal WO Perawatan Non Rutin</th>
                            <th>Tanggal Disposisi</th>
                            <th>Tanggal Investigasi</th>
                            <th>Tanggal Input Metode</th>
                            <th>Approve M.Pemeliharaan</th>
                            <th>Approve M.DalOps</th>
                            <th>Approve M.PPP</th>
                            <th>Tanggal DED Selesai</th>
                            <th>Tanggal Target Penyelesaian User</th>
                            <th>Tanggal Target Revisi Penyelesaian User</th>
                            <th>Tanggal Penanganan User</th>
                            <th>Tanggal Penyelesaian User</th>
                            <!-- <th>Kesimpulan</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
                <!-- ./table Perawatan -->

                <div style="margin-bottom: 40px;margin-top: 40px;"></div>

                <!-- Table Perbaikan -->
                <h3>List Perbaikan</h3>
                <div class="form-group row">
                    <label class="col-form-label col-lg-2">Pilih Tipe</label>
                    <div class="col-lg-10">
                        {!! Form::select('tipe', $tipe, null, ['class'=>'form-control select2', 'id'=>'tipe']) !!}
                    </div>
                </div>

                <table class="table datatable-basic" id="tabelPrb">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Aset</th>
                            <th>Kode Aset</th>
                            <th>Instalasi</th>
                            <th>Metode</th>                            
                            <th>Tanggal WO Perbaikan</th>
                            <th>Tanggal Disposisi</th>
                            <th>Tanggal Investigasi</th>
                            <th>Tanggal Input Metode</th>
                            <th>Approve M.Pemeliharaan</th>
                            <th>Approve M.DalOps</th>
                            <th>Approve M.PPP</th>
                            <th>Tanggal DED Selesai</th>
                            <th>Tanggal Target Penyelesaian</th>
                            <th>Tanggal Target Revisi Penyelesaian</th>
                            <th>Tanggal Penanganan</th>
                            <th>Tanggal Penyelesaian</th>
                            <!-- <th>Kesimpulan</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
                <!-- ./table Perbaikan -->

                <div style="margin-bottom: 40px;margin-top: 40px;"></div>

                <!-- Table Aduan Non Operasi -->
                <h3>List Aduan Non Operasi</h3>

                <table class="table datatable-basic" id="tabelNonOp">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Instalasi</th>
                            <th>Lokasi</th>
                            <th>Metode</th>
                            <th>Tanggal WO</th>
                            <th>Tanggal Disposisi</th>
                            <th>Tanggal Investigasi</th>
                            <th>Tanggal Input Metode</th>
                            <th>Approve M.Pemeliharaan</th>
                            <th>Approve M.DalOps</th>
                            <th>Approve M.PPP</th>
                            <th>Tanggal DED Selesai</th>
                            <th>Tanggal Target Penyelesaian</th>
                            <th>Tanggal Target Revisi Penyelesaian</th>
                            <th>Tanggal Penanganan</th>
                            <th>Tanggal Penyelesaian</th>
                            <!-- <th>Kesimpulan</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
                <!-- ./table Aduan Non Operasi -->

                <div style="margin-bottom: 40px;margin-top: 40px;"></div>

                <!-- Table Usulan Pekerjaan -->
                <h3>List Usulan Pekerjaan</h3>

                <table class="table datatable-basic" id="tabelUsulan">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Instalasi</th>
                            <th>Lokasi</th>
                            <th>Metode</th>
                            <th>Tanggal WO</th>
                            <th>Tanggal Disposisi</th>
                            <th>Tanggal Investigasi</th>
                            <th>Tanggal Input Metode</th>
                            <th>Approve M.Pemeliharaan</th>
                            <th>Approve M.DalOps</th>
                            <th>Approve M.PPP</th>
                            <th>Tanggal DED Selesai</th>
                            <th>Tanggal Target Penyelesaian</th>
                            <th>Tanggal Target Revisi Penyelesaian</th>
                            <th>Tanggal Penanganan</th>
                            <th>Tanggal Penyelesaian</th>
                            <!-- <th>Kesimpulan</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
                <!-- ./table Usulan Pekerjaan -->
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
<script>
$(document).ready(function () {
//    alert('aa');
    $(".select2").select2();

    $('#tabel').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('lampiranev::data-realisasi-perawatan') }}",
        "columns": [    
            {data: 'id', name: 'prw_data.id', defaultContent: '-'},
            {data: 'nama_aset', name: 'aset.nama_aset', defaultContent: '-'},
            {data: 'kode_aset', name: 'aset.kode_aset', defaultContent: '-'},
            {data: 'metode', name: 'metode', defaultContent: '-'},
            {data: 'tanggal', name: 'tanggal', defaultContent: '-'},
            {data: 'tgl_disposisi', name: 'tgl_disposisi', defaultContent: '-'},
            {data: 'tgl_foto_investigasi', name: 'tgl_foto_investigasi', defaultContent: '-'},
            {data: 'tgl_input_metode', name: 'tgl_input_metode', defaultContent: '-'},
            {data: 'approve_manajer', name: 'approve_manajer', defaultContent: '-'},
            {data: 'approve_dalops', name: 'approve_dalops', defaultContent: '-'},
            {data: 'approve_ms_ppp', name: 'approve_ms_ppp', defaultContent: '-'},
            {data: 'tgl_ded_selesai', name: 'tgl_ded_selesai', defaultContent: '-'},
            {data: 'perkiraan', name: 'perkiraan', defaultContent: '-'},
            {data: 'perkiraan_revisi', name: 'perkiraan_revisi', defaultContent: '-'},
            {data: 'tgl_foto_analisa', name: 'tgl_foto_analisa', defaultContent: '-'},
            {data: 'tgl_finish', name: 'tgl_finish', defaultContent: '-'},
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

    $('#tabelPrb').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('lampiranev::data-realisasi-perbaikan') }}",
        "columns": [    
            {data: 'id', name: 'prb_data.id', defaultContent: '-'},
            {data: 'nama_aset', name: 'aset.nama_aset', defaultContent: '-'},
            {data: 'kode_aset', name: 'aset.kode_aset', defaultContent: '-'},
            {data: 'instalasi', name: 'instalasi.name', defaultContent: '-'},
            {data: 'metode', name: 'metode', defaultContent: '-'},
            {data: 'tanggal', name: 'prb_data.tanggal', defaultContent: '-'},
            {data: 'tgl_disposisi', name: 'prb_data.tgl_disposisi', defaultContent: '-'},
            {data: 'tgl_foto_investigasi', name: 'prb_data.tgl_foto_investigasi', defaultContent: '-'},
            {data: 'tgl_input_metode', name: 'prb_data.tgl_input_metode', defaultContent: '-'},
            {data: 'approve_manajer', name: 'prb_data.approve_manajer', defaultContent: '-'},
            {data: 'approve_dalops', name: 'prb_data.approve_dalops', defaultContent: '-'},
            {data: 'approve_ms_ppp', name: 'prb_data.approve_ms_ppp', defaultContent: '-'},
            {data: 'tgl_ded_selesai', name: 'tgl_ded_selesai', defaultContent: '-'},
            {data: 'perkiraan', name: 'prb_data.perkiraan', defaultContent: '-'},
            {data: 'perkiraan_revisi', name: 'prb_data.perkiraan_revisi', defaultContent: '-'},
            {data: 'tgl_foto_analisa', name: 'tgl_foto_analisa', defaultContent: '-'},
            {data: 'tgl_finish', name: 'prb_data.tgl_finish', defaultContent: '-'},
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

    $('#tabelNonOp').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('lampiranev::data-realisasi-non-operasi') }}",
        "columns": [    
            {data: 'id', name: 'aduan_non_operasi.id', defaultContent: '-'},
            {data: 'judul', name: 'judul', defaultContent: '-'},
            {data: 'instalasi', name: 'instalasi.name', defaultContent: '-'},
            {data: 'lokasi', name: 'lokasi', defaultContent: '-'},
            {data: 'metode', name: 'metode', defaultContent: '-'},
            {data: 'created_at', name: 'created_at', defaultContent: '-'},
            {data: 'tgl_disposisi', name: 'tgl_disposisi', defaultContent: '-'},
            {data: 'tgl_foto_investigasi', name: 'tgl_foto_investigasi', defaultContent: '-'},
            {data: 'tgl_input_metode', name: 'tgl_input_metode', defaultContent: '-'},
            {data: 'approve_manajer', name: 'approve_manajer', defaultContent: '-'},
            {data: 'approve_dalops', name: 'approve_dalops', defaultContent: '-'},
            {data: 'approve_ms_ppp', name: 'approve_ms_ppp', defaultContent: '-'},
            {data: 'tgl_ded_selesai', name: 'tgl_ded_selesai', defaultContent: '-'},
            {data: 'perkiraan', name: 'perkiraan', defaultContent: '-'},
            {data: 'perkiraan_revisi', name: 'perkiraan_revisi', defaultContent: '-'},
            {data: 'tgl_foto_analisa', name: 'tgl_foto_analisa', defaultContent: '-'},
            {data: 'tgl_finish', name: 'tgl_finish', defaultContent: '-'},
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

    $('#tabelUsulan').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('lampiranev::data-realisasi-usulan') }}",
        "columns": [    
            {data: 'id', name: 'usulan_non_operasi.id', defaultContent: '-'},
            {data: 'nama', name: 'nama', defaultContent: '-'},
            {data: 'instalasi', name: 'instalasi.name', defaultContent: '-'},
            {data: 'lokasi', name: 'lokasi', defaultContent: '-'},
            {data: 'metode', name: 'metode', defaultContent: '-'},
            {data: 'created_at', name: 'created_at', defaultContent: '-'},
            {data: 'tgl_disposisi', name: 'tgl_disposisi', defaultContent: '-'},
            {data: 'tgl_foto_investigasi', name: 'tgl_foto_investigasi', defaultContent: '-'},
            {data: 'tgl_input_metode', name: 'tgl_input_metode', defaultContent: '-'},
            {data: 'approve_manajer', name: 'approve_manajer', defaultContent: '-'},
            {data: 'approve_dalops', name: 'approve_dalops', defaultContent: '-'},
            {data: 'approve_ms_ppp', name: 'approve_ms_ppp', defaultContent: '-'},
            {data: 'tgl_ded_selesai', name: 'tgl_ded_selesai', defaultContent: '-'},
            {data: 'perkiraan', name: 'perkiraan', defaultContent: '-'},
            {data: 'perkiraan_revisi', name: 'perkiraan_revisi', defaultContent: '-'},
            {data: 'tgl_foto_analisa', name: 'tgl_foto_analisa', defaultContent: '-'},
            {data: 'tgl_finish', name: 'tgl_finish', defaultContent: '-'},
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

    $('#tipe').change(function() {
        var instalasi = $("#instalasi").val();
        var bagian = $("#bagian").val();
        var minggu = $("#minggu").val();

        var tipe = $(this).val();

        var urlPrb = "{{ url('lampiranev/data-realisasi-perbaikan') }}?tipe=" + tipe + '&instalasi=' + instalasi + '&bagian=' + bagian + '&minggu=' + minggu;

        $('#tabelPrb').DataTable().ajax.url(urlPrb).load();
    });

    $('#tampil').click(function() {
        var instalasi = $("#instalasi").val();
        var bagian = $("#bagian").val();
        var minggu = $("#minggu").val();

        var url = "{{ url('lampiranev/data-realisasi-perawatan') }}?instalasi=" + instalasi + '&bagian=' + bagian + '&minggu=' + minggu;
        var urlPrb = "{{ url('lampiranev/data-realisasi-perbaikan') }}?instalasi=" + instalasi + '&bagian=' + bagian + '&minggu=' + minggu;

        var urlNonOp = "{{ url('lampiranev/data-realisasi-non-operasi') }}?instalasi=" + instalasi + '&minggu=' + minggu;
        var urlUsulan = "{{ url('lampiranev/data-realisasi-usulan') }}?instalasi=" + instalasi + '&minggu=' + minggu;

        // console.log(url);
        $('#tabel').DataTable().ajax.url(url).load();
        $('#tabelPrb').DataTable().ajax.url(urlPrb).load();

        $('#tabelNonOp').DataTable().ajax.url(urlNonOp).load();
        $('#tabelUsulan').DataTable().ajax.url(urlUsulan).load();
    });
});
</script>
@endsection