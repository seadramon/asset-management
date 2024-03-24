@extends('layouts.main')

@section('title', 'Monitoring Aset - Asset Management')

@section('pagetitle', 'Data Menu Per Role')

@section('content') 
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
		<div class="row">
			<div class="col-md-6">
				<div class="card" id="instalasi-data">
					<div class="card-header header-elements-inline">
						<h5 class="card-title">Data Menu Per Role</h5>
					</div>

					<form class="m-form m-form--fit m-form--label-align-right" >
						<div class="card-body">
							@if(Session::has('status'))
							<div class="form-group m-form__group m--margin-top-10">
								<div class="alert alert-{{Session::get('alert')}}" role="alert">
								{{Session::get('status')}}
								</div>
							</div>
							@endif
							<div class="form-group m-form__group">
								<label>Role Petugas</label>
								<input class="form-control col-md-12 m-input--air" name="role" disabled="" value="{{$role->name}}">
							</div>

							<div class="form-group m-form__group">
								<label>Display Menu</label><br>
								<select class="form-control col-md-12 m-input--air" data-placeholder="Pilih Role Petugas" required="" name="menus" id="menus">
									<option value="">Pilih Menu</option>
									@foreach($single_menu as $row1)
										<option value="{{$row1->id}}||{{$row1->id}}">[&nbsp{{$row1->nama}}&nbsp]</option>
									@endforeach
									@foreach($head_menu as $row)
										<optgroup label="{{$row->nama}}">
									<!-- untuk menu tanpa submenu -->
										@foreach($sub_head as $sh)
											@if(substr($row->urut,0,1) == substr($sh->urut,0,1))
												<option value="{{$row->id}}||{{$sh->id}}">{{$sh->nama}}</option>
											@endif
										@endforeach
										<!-- end untuk menu tanpa submenu -->
										<!-- start head sub menu -->
										@foreach($subhead_menu as $subhead)
										<!-- <option value="{{$row->id}}||{{$subhead->id}}"><b>{{$subhead->nama}}</b></option> -->
											@if(substr($row->urut,0,1) == substr($subhead->urut,0,1))
											<!-- <option value="{{$row->id}}||{{$subhead->id}}">*{{$subhead->nama}}</option> -->
												<optgroup label="{{$row->nama}} - {{$subhead->nama}}">
												@foreach($sub_menu as $subrow)
													@if(substr($subhead->urut,0,2) == substr($subrow->urut,0,2))
														<option value="{{$subhead->id}}||{{$subrow->id}}">&nbsp&nbsp&nbsp{{$subrow->nama}}</option>
													@endif
												@endforeach
												</optgroup>
											@endif
										@endforeach
										<!-- end of head sub menu -->
										</optgroup>
									@endforeach
								</select>
								<span class="help-block">Ganti untuk menambah menu setiap role</span>
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="col-md-6">
				<div class="card" id="instalasi-data">
					<div class="card-header header-elements-inline">
						<h5 class="card-title">Data Menu Per Role</h5>
					</div>
					<form class="m-form m-form--fit m-form--label-align-right" method="post" action="{{route('datarolemenus-post')}}">


						<!-- additional -->
						<input type="hidden" name="_token" value="{{ csrf_token() }}">
						<input type="hidden" name="action_form" value="add">


						<div class="card-body">
							<div style="padding:15px">
							<p id="role_selected">Role Terpilih : {{$role->role}}</p>
							<input type="text" hidden name="role" id="role_selected_val" value="{{$role->id}}" />
								<table class="table table-bordered">
									<thead>
										<tr>
											<td align="center">Menu</td>
											<td align="center">Action</td>
										</tr>
									</thead>
									<tbody id="table_menu">
                                        @foreach($role->menus as $row)
                                            <tr>
                                                <td>{{$row->nama}}</td>
												@if($row->tipe == 1)
													<td align="center">
													<a class="btn btn-primary btn-sm" href="{{ route('dataroleheadmenus-del' , ['role' => $role->id, 'menu' => $row->id]) }}"
														onClick="return confirm(' Apakah anda yakin akan menghapus data ini ?? Menghapus menu header berarti anda akan menghapus semua sub menu nya !!')">
														Hapus Menu Utama</a>
													</td>
												@elseif($row->tipe == 3)
													<td align="center">
														<a class="btn btn-warning btn-sm m--font-light" href="{{route('datarolesubheadmenus-del' ,['role' => $role->id, 'menu' => $row->id] )}}" 
														onClick="return confirm(' Apakah anda yakin akan menghapus data ini ?? Menghapus menu header berarti anda akan menghapus semua sub menu nya !!')">
														Hapus Sub Menu Utama</a>
													</td>
												@else
													<td align="center"><a href="{{route('datarolemenus-del' , ['role' => $role->id, 'menu' => $row->id])}}"  class="btn btn-danger"
                                                    onClick="return confirm(\' Apakah anda yakin akan menghapus data ini ??\')"><i class="fa fa-times"></i></a></td>
												@endif
                                                
                                            </tr>
                                        @endforeach
									</tbody>
								</table>
								<button type="submit" class="btn btn-primary">Submit</button>
								<a href="{{URL::previous()}}" class="btn btn-secondary">Back</a>
							</div>
						</div>
					</form>
				</div>
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
    $('#menus').select2({
			placeholder: 'Tentukan menu setiap role...',
			width: 'resolve'
		});
});
$("#roles_select").change(function(){
		$("#role_selected").text("Role Terpilih : "+$("#roles_select option:selected").text());
		$("#role_selected_val").val($("#roles_select option:selected").val());
		$('#table_menu').empty();
		$('#menus').prop('disabled', false);

		if($("#roles_select option:selected").val() === ''){
			$('#menus').prop('disabled', true);
			$("#role_selected").text("Role Terpilih : -");
		}
	});


	$("#menus").change(function(){
		var menu_id = $("#menus option:selected").val();
		var menu_name = $("#menus option:selected").text();
		
		$("#table_menu").append('<tr>'+
				'<td>'+menu_name+' <input hidden type="text" value="'+ menu_id +'" name="menu[]"/></td>'+
				'<td align="center"><a href="javascript:void(0)" onclick="del($(this).parent().parent())"><i class="fa fa-times"></i> delete</a></td>'+
		'</tr>');
	});

	function del(rm){
		var aa = confirm('Yakin menghapus data ini ?');
		if(aa){
			$(rm).remove();
		}else{
			return false;
		}
	}
</script>
@endsection