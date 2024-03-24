<?php $i = 0; ?>
@if (count($data) > 0)
	@foreach($data as $row)
		<tr>
            <td>
            	<input type="hidden" name="komponen[{{$i}}][id]" value="{{ $row->id }}">
                <input type="text" class="form-control" name="komponen[{{$i}}][part]" value="{{ $row->part }}">
            </td>
            <td>
                <input type="text" class="form-control" name="komponen[{{$i}}][mode_gagal]" value="{{ $row->mode_gagal }}">
            </td>
            <td>
                <input type="text" class="form-control" name="komponen[{{$i}}][efek_gagal]" value="{{ $row->efek_gagal }}">
            </td>
            <td>
                <input type="text" class="form-control" name="komponen[{{$i}}][penyebab_gagal]" value="{{ $row->penyebab_gagal }}">
            </td>
            <td><button type="button" class="btn btn-danger btn-sm removeBtn" onClick="remBtn($(this))"><i class="fa fa-times"></i></button></td>
        </tr>
        <?php $i++; ?>
	@endforeach
@else
	<tr>
        <td>
            <input type="text" class="form-control" name="komponen[0][part]" value="">
        </td>
        <td>
            <input type="text" class="form-control" name="komponen[0][mode_gagal]" value="">
        </td>
        <td>
            <input type="text" class="form-control" name="komponen[0][efek_gagal]" value="">
        </td>
        <td>
            <input type="text" class="form-control" name="komponen[0][penyebab_gagal]" value="">
        </td>
        <td><button type="button" class="btn btn-danger btn-sm removeBtn" onClick="remBtn($(this))"><i class="fa fa-times"></i></button></td>
    </tr>
@endif