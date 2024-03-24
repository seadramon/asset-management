@extends('layouts.main')

@section('title', 'Monitoring Aset - Asset Management')

@section('pagetitle', 'Data Menu Per Role')

@section('content') 
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="card" id="instalasi-data">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Data Menu Per Role</h5>
            </div>

            <div class="card-body">
                <!-- Table -->
                <table class="table datatable-basic" id="tabel">
                    <thead>
                        <tr>
							<th>Role</th>
                            <th>Menu Aplikasi</th>
							<th class="text-center">Menu</th>
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
<script>
$(document).ready(function () {
//    alert('aa');
    // $.fn.dataTable.ext.errMode = 'throw';
    $('#tabel').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('datarolemenus-json') }}",
        "columns": [    
            {data: 'name', searchable: true, orderable:false, class:'valign-top'},
            {data: 'menus', searchable: false, orderable:false},
	        {data: 'action', orderable: false, searchable: false, class: 'text-center valign-top'}
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
});
</script>
@endsection