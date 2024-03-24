<?php 
$i = 0; 
$arrPrw = new stdClass();
?>
<?php //dd($parts); ?>
<input type="hidden" class="form-control" name="komponen_id" value="{{ $komponen_id }}">
@foreach( $parts as $key => $part)
    <?php 
    $tmp = explode("#", $key);
    ?>
    <tr>
        <td>
            <input type="hidden" class="form-control" name="komponen[{{$i}}][kode_part]" value="{{ $tmp[0] }}">
            <label id="komponenval{{$i}}" class="komponenval">{{ $tmp[1] }}</label>
        </td>

        <?php $j = 0; ?>
        @foreach($perawatan as $kolom)
            <?php $kolomName = $kolom->name; ?>
            <td>
                <input type="text" class="form-control" name="komponen[{{$i}}][{{ $kolomName }}]" value="<?php echo isset($part[$kolomName]['nilai'])?$part[$kolomName]['nilai']:""; ?>" placeholder="W(n)">
            </td>
        @endforeach
        <!-- <td><button type="button" class="btn btn-danger btn-sm removeBtn" onClick="remBtn($(this))"><i class="fa fa-times"></i></button></td> -->
    </tr>
    <?php $i++; ?>
@endforeach