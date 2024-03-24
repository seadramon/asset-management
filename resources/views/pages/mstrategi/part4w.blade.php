<?php /*
@for ($i = 1; $i <= $jmlMinggu; $i++)
	<div class="form-group row">
	    <label class="col-form-label col-lg-2">Minggu ke {{ $urutanMinggu }}</label>
	    <div class="col-lg-10">
	    	{!! Form::hidden("week[$i][urutan_minggu]", $urutanMinggu, ['class'=>'form-control']) !!}

	        {!! Form::select("week[$i][hari]", $dayList, null, ['class'=>'form-control select2', 'id'=>'hari']) !!}
	    </div>
	</div>
	<?php $urutanMinggu++; ?>
@endfor
*/ ?>
<?php $i = 0; ?>
<?php /*dd($komponens);*/ ?>
@foreach($komponens as $komponen)
<?php /*dd(empty($komponen));*/ ?>
	@if (!empty($komponen))
		{!! Form::hidden("week[$i][id]", $komponen->ms_4w_id, ['class'=>'form-control', 'id'=>'id']) !!}
		{!! Form::hidden("week[$i][aset_id]", $komponen->aset_id, ['class'=>'form-control', 'id'=>'aset_id']) !!}

		{!! Form::hidden("week[$i][is_equipment]", $komponen->equipment, ['class'=>'form-control', 'id'=>'is_equipment']) !!}
		{!! Form::hidden("week[$i][equipment_id]", $komponen->equipment_id, ['class'=>'form-control', 'id'=>'equipment_id']) !!}

		{!! Form::hidden("week[$i][ms_52w_id]", $komponen->id, ['class'=>'form-control', 'id'=>'ms_52w_id']) !!}
		
		{!! Form::hidden("week[$i][ms_4w_id]", $komponen->ms_4w_id, ['class'=>'form-control', 'id'=>'ms_4w_id']) !!}

		{!! Form::hidden("week[$i][hari]", !empty($komponen->hari)?$komponen->hari:'senin', ['class'=>'form-control', 'id'=>'hari']) !!}
		<div class="form-group row">
		    <label class="col-form-label col-lg-2">{{ $komponen->nama_aset }}</label>
		    <div class="col-lg-10">
		    	{!! Form::select("week[$i][petugas]", $petugas, $komponen->petugas, ['class'=>'form-control select2', 'id'=>'petugas', 'required']) !!}
		    </div>
		</div>
		<?php $i++ ?>
	@endif
@endforeach


<div class="text-left" style="margin-top: 50px;">
	{{ 'Jumlah Entri : '.$jmlKomponen }}
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $(".select2").select2();
    });
</script>