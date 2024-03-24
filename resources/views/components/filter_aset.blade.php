<div class="form-group row">
    <label class="col-form-label col-lg-2">Instalasi</label>
    <div class="col-lg-10">
        {!! Form::select('instalasi_id', $instalasi, null, ['class'=>'form-control select2', 'id'=>'instalasi', 'style' => 'width:100%']) !!}
    </div>
</div>

<div class="form-group row">
    <label class="col-form-label col-lg-2">Lokasi</label>
    <div class="col-lg-10">
        {!! Form::select('lokasi', $lokasi, null, ['class'=>'form-control select2', 'id'=>'lokasi', 'style' => 'width:100%']) !!}
    </div>
</div>

<div class="form-group row">
    <label class="col-form-label col-lg-2">Ruang</label>
    <div class="col-lg-10">
        {!! Form::select('ruang', $ruang, null, ['class'=>'form-control select2', 'id'=>'ruang', 'style' => 'width:100%']) !!}
    </div>
</div>

<div class="form-group row">
    <label class="col-form-label col-lg-2">Bagian</label>
    <div class="col-lg-10">
        {!! Form::select('bagian', $bagian, null, ['class'=>'form-control select2', 'id'=>'bagian', 'style' => 'width:100%']) !!}
    </div>
</div>

<div class="form-group row">
    <label class="col-form-label col-lg-2">Kategori</label>
    <div class="col-lg-10">
        {!! Form::select('kategori', $kategori, null, ['class'=>'form-control select2', 'id'=>'kategori', 'style' => 'width:100%']) !!}
    </div>
</div>

<div class="form-group row">
    <label class="col-form-label col-lg-2">Sub Kategori</label>
    <div class="col-lg-10">
        {!! Form::select('subkategori', $subkategori, null, ['class'=>'form-control select2', 'id'=>'subkategori', 'style' => 'width:100%']) !!}
    </div>
</div>

<div class="form-group row">
    <label class="col-form-label col-lg-2">Sub Sub Kategori</label>
    <div class="col-lg-10">
        {!! Form::select('subsubkategori', $subsubkategori, null, ['class'=>'form-control select2', 'id'=>'subsubkategori', 'style' => 'width:100%']) !!}
    </div>
</div>

<div class="form-group row">
    <label class="col-form-label col-lg-2">Status</label>
    <div class="col-lg-10">
        {!! Form::select('kondisi', $kondisi, null, ['class'=>'form-control select2', 'id'=>'kondisi', 'style' => 'width:100%']) !!}
    </div>
</div>

<div class="form-group row">
    <label class="col-form-label col-lg-2">Nama Aset</label>
    <div class="col-lg-10">
        {!! Form::text('nama_aset', null, ['class'=>'form-control', 'id'=>'nama_aset']) !!}
    </div>
</div>

<div class="form-group row">
    <label class="col-form-label col-lg-2">Kode Aset</label>
    <div class="col-lg-10">
        {!! Form::text('kode_aset', null, ['class'=>'form-control', 'id'=>'kode_aset']) !!}
    </div>
</div>

<div class="form-group row">
    <label class="col-form-label col-lg-2">Tahun</label>
    <div class="col-lg-10">
        {!! Form::text('tahun', null, ['class'=>'form-control', 'id'=>'year']) !!}
    </div>
</div>

<div class="form-group row">
    <label class="col-form-label col-lg-2">Spesifikasi</label>
    <div class="col-lg-10">
        {!! Form::text('spesifikasi', null, ['class'=>'form-control', 'id'=>'spesifikasi']) !!}
    </div>
</div>

<div class="form-group row">
    <label class="col-form-label col-lg-2">No SPK</label>
    <div class="col-lg-10">
        {!! Form::text('no_spk', null, ['class'=>'form-control', 'id'=>'no_spk']) !!}
    </div>
</div>
