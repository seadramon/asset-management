@extends('layouts.main')

@section('title', 'Penugasan Perbaikan - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Penugasan Perbaikan')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">List Parameter yang Masuk dalam Masalah</h5>
            </div>

            <div class="card-body">
                <!-- Table -->
                <table class="table datatable-basic" id="tabel">
                    <thead>
                        <tr>
                            <!-- <th>Kode</th>      -->
                            <th>Pengukuran</th>
                            <th>Nilai</th>
                            <th>Batas</th>
                            <th>Kode Form</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
                <!-- ./table -->
            </div>


        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Penugasan Perbaikan Form</h5>
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
                <!-- BEGIN FORM-->
                @if ($data!='')
                    {!! Form::model($data, ['route' => ['perbaikan::perbaikan-penugasan-simpan'], 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('id', null) !!}
                @else
                    {!! Form::open(['url' => route('perbaikan::perbaikan-penugasan-simpan'), 'class' => 'form-horizontal']) !!}
                @endif
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="text" class="hidden" name="tipe" value="{{($data=='')?'0':'1'}}">
                        {!! Form::hidden("id", $id, ['class'=>'form-control', 'id'=>'id']) !!}

                        <?php /*
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Kondisi</label>
                            <div class="col-lg-10">
                                {!! Form::select('kondisi', $kondisi, null, ['class'=>'form-control select2', 'id'=>'kondisi']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Metode</label>
                            <div class="col-lg-10">
                                {!! Form::select('metode', $metode, null, ['class'=>'form-control select2', 'id'=>'metode']) !!}
                            </div>
                        </div>
                        */?>

	                    <div class="form-group row" id="petugasField">
							<label class="col-form-label col-lg-2">Petugas</label>
							<div class="col-lg-10">
                                {!! Form::select('petugas', $petugas, null, ['class'=>'form-control select2', 'id'=>'petugas']) !!}
							</div>
						</div>

                        <div id="beforepart"></div>

                	</fieldset>

                	<div class="text-right">
						<button type="submit" class="btn btn-primary legitRipple">Simpan</button>
                        <a href="{{route('perbaikan::perbaikan-index')}}">
                            <button type="button" class="btn btn-light legitRipple">Kembali</button></a>
					</div>
                {!! Form::close() !!}
                <!-- END FORM-->
            </div>
        </div>
    <!-- /form inputs -->
@endsection
@section('js')
<script src="{{asset('global_assets/plugins/datatables/datatables.min.js')}}" type="text/javascript"></script>
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>
<script>
$(document).ready(function () {
    $('#tabel').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('perbaikan::perbaikan-data-detail', ['id' => $id]) }}",
        "columns": [    
            // {data: 'id'},        
            {data: 'pengukuran', name: 'pengukuran', defaultContent: '-'},
            {data: 'nilai_asli', name: 'nilai_asli', defaultContent: '-'},
            {data: 'nilai_batas', name: 'nilai_batas', defaultContent: '-'},
            {data: 'kode_fm', name: 'kode_fm', defaultContent: '-'},
            // {data: 'menu', orderable: false, searchable: false}
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

    $(".select2").select2();
    // document.getElementById("petugasField").style.display = "none";

    $("#metode").change(function() {
        var mtd = $(this).val();

        if (mtd == 'internal') {
            $("#petugasField").show();
        } else {
            document.getElementById("petugasField").style.display = "none";
        }
    });
});
</script>
@endsection