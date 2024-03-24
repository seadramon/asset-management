<div class="form-group row">
    <label class="col-form-label col-lg-2">Instalasi</label>
    <div class="col-lg-10">
        {!! Form::select('instalasi_id', $instalasi, $arrdata['instalasi'], ['class'=>'form-control select2', 'id'=>'instalasi', 'style' => 'width:100%']) !!}
    </div>
</div>

<div class="form-group row">
    <label class="col-form-label col-lg-2">Lokasi</label>
    <div class="col-lg-10">
        {!! Form::select('lokasi', $lokasi, $arrdata['lokasi'], ['class'=>'form-control select2', 'id'=>'lokasi', 'style' => 'width:100%']) !!}
    </div>
</div>

<div class="form-group row">
    <label class="col-form-label col-lg-2">Ruang</label>
    <div class="col-lg-10">
        {!! Form::select('ruang', $ruang, $arrdata['ruang'], ['class'=>'form-control select2', 'id'=>'ruang', 'style' => 'width:100%']) !!}
    </div>
</div>

<div class="form-group row">
    <label class="col-form-label col-lg-2">Aset</label>
    <div class="col-lg-10">
        {!! Form::select('aset_id', $aset, null, ['class'=>'form-control select2', 'id'=>'aset', 'style' => 'width:100%']) !!}
    </div>
</div>