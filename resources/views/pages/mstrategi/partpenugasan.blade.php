<?php 
$i = 0;
?>
@foreach($komponen as $row)
	<div class="form-group row">
	    <label class="col-form-label col-lg-3">{{$row->ms52w->komponen->nama_aset}} # {{$row->ms52w->komponen->instalasi->name}}</label>
	    <div class="col-lg-9">
	    	<input type="hidden" name="ms4w[{{$i}}][id]" value="{{ $row->id }}">
                <input type="text" class="form-control input-circle" name="ms4w[{{$i}}][petugas]" placeholder="Masukkan Nama Petugas" value="">
	    </div>
	</div>
	<?php $i++; ?>
@endforeach

<script type="text/javascript">
    $(document).ready(function () {
        $(".select2").select2();
    });
</script>