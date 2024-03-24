<?php $i = 1; ?>
@foreach($parts as $part)
    <?php $nilai = ""; ?>
    @foreach($val as $value)
        @if ($value['komponen_id']==$part->id)
            <?php $nilai = $value['nilai']; ?>
        @endif
    @endforeach
    <div class="form-group row">
        <label class="col-form-label col-lg-2">{{ $part->nama_aset }}</label>
        <div class="col-lg-10">
            <input type="hidden" name="msdatapdm[{{$i}}][part]" value="{{ $part->id }}">
            <input type="text" class="form-control input-circle" name="msdatapdm[{{$i}}][nilai]" placeholder="Masukkan Durasi" value="{{ $nilai }}">
        </div>
    </div>         
    <?php $i++; ?>
@endforeach