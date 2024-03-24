@extends('layouts.main')

@section('title', 'Create Table Form Monitoring - Asset Management')

@section('css')
<link rel="stylesheet" type="text/css" href="{{url('global_assets/plugins/select2/css/select2-bootstrap.min.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
@stop

@section('pagetitle', 'Create Table Form Monitoring')

@section('content') 
</style>
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="card" id="mstrategi-form">
        	<div class="card-header header-elements-inline">
				<h5 class="card-title">Create Table Form Monitoring</h5>
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
                {!! Form::open(['url' => route('temp::temp-simpan'), 'class' => 'form-horizontal']) !!}
                	<fieldset class="mb-3">
                		<input type="text" class="hidden" name="_token" value="{{csrf_token()}}">

	                    <div class="form-group row">
							<label class="col-form-label col-lg-2">Form</label>
							<div class="col-lg-10">
                                {!! Form::select('selform', $forms, null, ['class'=>'form-control select2', 'id'=>'equipment']) !!}
							</div>
						</div>
                	</fieldset>

                	<div class="text-right">
						<button type="submit" class="btn btn-primary legitRipple">Run</button>
					</div>
                {!! Form::close() !!}
                <!-- END FORM-->

                <?php /*
                <!-- Table -->
                <table class="table datatable-basic" id="tabel">
                    <thead>
                        <tr>
                            <th>id</th>
                            <th>Kode Form</th>     
                            <th>Pengukuran</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
                <!-- ./table -->
                */ ?>
            </div>
        </div>
    <!-- /form inputs -->
@endsection
@section('js')
<script type="text/javascript" src="{{url('global_assets/plugins/select2/js/select2.full.min.js')}}"></script>
<script src="{{asset('global_assets/plugins/datatables/datatables.min.js')}}" type="text/javascript"></script>
<script>
$(document).ready(function () {
    $(".select2").select2();

    $('#tabel').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('temp::temp-data') }}",
        "columns": [    
            {data: 'recid'},        
            {data: 'kode_fm', name: 'kode_fm', defaultContent: '-'},
            {data: 'pengukuran', name: 'pengukuran', defaultContent: '-', orderable: false, searchable: false},
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
});
</script>
@endsection