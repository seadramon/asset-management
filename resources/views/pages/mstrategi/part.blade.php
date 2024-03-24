<?php $i = 1; ?>
@foreach($parts as $part)
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-collapsed">
                <div class="card-header bg-primary text-white header-elements-inline">
                    <h5 class="card-title">{{ $part->nama }}</h5>
                    <div class="header-elements">
                        <div class="list-icons">
                            <a class="list-icons-item" data-action="collapse"></a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">   
                        <div class="col-md-12">
                            <?php $n = 1; ?>
                            @foreach($part->komponendetail as $row)
                            <div class="card card-collapsed">
                                <div class="card-header bg-secondary text-white header-elements-inline">
                                    <h5 class="card-title">{{$row->part}}</h5>

                                    <input type="hidden" class="form-control input-circle" name="msdata[{{$i}}][komponen]" value="{{$row->id}}">

                                    <div class="header-elements">
                                        <div class="list-icons">
                                            <a class="list-icons-item" data-action="collapse"></a>
                                        </div>
                                    </div>
                                </div>

                                <!-- example -->
                                <div class="card-body">
                                    <div class="row">   
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label">Kelompok</label>
                                                <div class="col-md-3">
                                                    <select class="form-control input-circle select2" name="msdata[{{$i}}][kelompok]" style="width: 100%;">
                                                        <option value="">-Pilih Kelompok-</option>
                                                        @foreach($kelompok as $parent)                                        
                                                            <optgroup label="{{ $parent->nama }}">
                                                                @foreach($kelompokDetail as $child)
                                                                    @if ($child->ms_kelompok_id == $parent->id)
                                                                        <option value="{{ $parent->id }}#{{ $child->id }}">
                                                                            {{ $child->nama }}
                                                                        </option>
                                                                    @endif
                                                                @endforeach
                                                            </optgroup>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                
                                                <label class="col-md-3 col-form-label">Nilai</label>
                                                <div class="col-md-3">
                                                    <input type="text" class="form-control input-circle" name="msdata[{{$i}}][nilai]" placeholder="Masukkan Nilai Perolehan">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php $n++; ?>
                            <?php $i++; ?>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>          
@endforeach
<script type="text/javascript">
    $(document).ready(function () {
        $(".select2").select2();
    });
</script>