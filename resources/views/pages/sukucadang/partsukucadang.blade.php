@if (in_array($wo, ['perawatan', 'perbaikan']))
    {!! Form::hidden('komponen_id', $aset->id) !!}

    <div class="form-group row">
        <label class="col-form-label col-lg-2">Nama Aset</label>
        <label class="col-form-label col-lg-10">{!! $aset->nama_aset !!}</label>
    </div>

    <div class="form-group row">
        <label class="col-form-label col-lg-2">Instalasi</label>
        <label class="col-form-label col-lg-10">{!! $aset->instalasi->name !!}</label>
    </div>

    <div class="form-group row">
        <label class="col-form-label col-lg-2">Bagian</label>
        <label class="col-form-label col-lg-10">{!! $aset->bagiannya->name !!}</label>
    </div>
@else
    <div class="form-group row">
        <label class="col-form-label col-lg-2">Unit Kerja</label>
        <div class="col-lg-10">
            {!! Form::select('unitkerja', $unitkerja, null, ['class'=>'form-control select2', 'id'=>'unitkerja']) !!}
        </div>
    </div>

    <div class="form-group row">
        <label class="col-form-label col-lg-2">Bagian</label>
        <div class="col-lg-10">
            {!! Form::select('bagian', $bagian, null, ['class'=>'form-control select2', 'id'=>'bagian']) !!}
        </div>
    </div>
@endif

<div class="form-group row">
    <label class="col-form-label col-lg-2">Suku Cadang</label>
    <div class="col-lg-10">
        {!! Form::hidden('wo_id', $woId) !!}
        {!! Form::hidden('wo', $wo) !!}
        {!! Form::hidden('fkey', $fkey) !!}

        {!! Form::hidden('dtlRemoved', null, ['id' => 'dtlRemoved']) !!}

        {!! Form::select('sukucadang', $cbSukucadang, null, ['class'=>'form-control select2', 'id'=>'sukucadang']) !!}
    </div>
</div>
<div class="form-group row">
    <label class="col-form-label col-lg-2">Stok</label>
    <div class="col-lg-8">
        {!! Form::text('stok', null, ['class'=>'form-control', 'id'=>'stok', 'readonly']) !!}
        {!! Form::hidden('kelompok_barang_g', null, ['class'=>'form-control', 'id'=>'kelompok_barang_g', 'readonly']) !!}
    </div>
    <div class="col-lg-2">
        <!-- <button type="button" id="addClone" class="btn btn-success legitRipple"> 
            <i class="fa fa-plus"></i> Tambah
        </button> -->
        <div class="dropdown">
          <button class="btn btn-success dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Tambah
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a class="dropdown-item" id="addClone">Sukucadang</a>
            <a class="dropdown-item" id="addWaiting">Waiting List</a>
          </div>
        </div>
    </div>
</div>
<div class="row">
    <?php //dd(count($dataSc)); ?>
    <table class="table" id="tabelSc">
        <thead>
            <tr>                                    
                <th>Suku Cadang</th>
                <th>Jumlah</th>
                <th>Dibeli Oleh</th>
                <th>Keterangan</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @if (sizeof($dataSc) > 0)
                <?php $i = 0; ?>
                @foreach($dataSc as $rowSc)
                    <tr>
                        <td>
                            <label id="sukucadangval{{ $i }}" class="sukucadangval" data-tipe="labelsc">{{ $pairKodeAlias[$rowSc->kode_alias] }}</label>

                            <input type="hidden" class="form-control" data-tipe="inputsc" name="arrsukucadang[{{ $i }}][kode_alias]" value="{{ $rowSc->kode_alias }}">
                            <input type="hidden" class="form-control" data-tipe="inputscSaldo" name="arrsukucadang[{{ $i }}][saldo]" value="">
                            <input type="hidden" class="form-control" data-tipe="inputscKelompok" name="arrsukucadang[{{ $i }}][kelompok_barang]" value="{{ $rowSc->kelompok_barang }}">

                            <input type="hidden" class="form-control" data-tipe="idSc" name="arrsukucadang[{{ $i }}][id]" value="{{ $rowSc->id }}">
                        </td>
                        <td>
                            <input type="number" id="jumlah{{ $i }}" class="form-control jumlah" onkeypress="javascript:valPress(event, $(this),$(this).attr('max'));" min="0" name="arrsukucadang[{{ $i }}][jumlah]" value="{{ $rowSc->jumlah }}">
                        </td>
                        <td>
                            <select name="arrsukucadang[{{ $i }}][dibeli_by]" class="form-control">
                                <option value="">-Pilih-</option>
                                <option value="gudang" {{ $rowSc->dibeli_by == 'gudang' ? ' selected="selected"' :'' }}>Gudang</option>
                                <option value="pemeliharaan"  {{ $rowSc->dibeli_by == 'pemeliharaan' ? ' selected="selected"' :'' }}>Pemeliharaan</option>
                            </select>
                        </td>
                        <td>
                            <textarea name="arrsukucadang[{{ $i }}][keterangan]">{{ $rowSc->keterangan }}</textarea>
                        </td>
                        <td><button type="button" class="btn btn-danger btn-sm removeBtn" onClick="remBtn($(this))"><i class="fa fa-times"></i></button></td>
                    </tr>

                    <?php $i++; ?>
                @endforeach
            @else
                <tr class="hidden">
                    <td>
                        <label id="sukucadangval0" class="sukucadangval" data-tipe="labelsc"></label>
                        <input type="hidden" class="form-control" data-tipe="inputsc" name="arrsukucadang[0][kode_alias]" value="">
                        <input type="hidden" class="form-control" data-tipe="inputscSaldo" name="arrsukucadang[0][saldo]" value="">
                        <input type="hidden" class="form-control" data-tipe="inputscKelompok" name="arrsukucadang[0][kelompok_barang]" value="">
                    </td>
                    <td>
                        <input type="number" id="jumlah0" class="form-control jumlah" onkeypress="javascript:valPress(event, $(this),$(this).attr('max'));" min="0" name="arrsukucadang[0][jumlah]">
                    </td>
                    <td>
                        <select name="arrsukucadang[0][dibeli_by]" class="form-control">
                            <option value="">-Pilih-</option>
                            <option value="gudang">Gudang</option>
                            <option value="pemeliharaan">Pemeliharaan</option>
                        </select>
                    </td>
                    <td>
                        <textarea name="arrsukucadang[0][keterangan]"></textarea>
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm removeBtn" onClick="remBtn($(this))"><i class="fa fa-times"></i></button></td>
                </tr>
            @endif
        </tbody>
    </table>
</div>


<!-- WAITING LIST -->
<div class="row" style="margin-top: 50px;">
    <?php //dd(count($dataSc)); ?>
    <h3>Waiting List</h3>
    <table class="table" id="tabelWait">
        <thead>
            <tr>                                    
                <th>Suku Cadang</th>
                <th>Jumlah</th>
                <th>Dibeli Oleh</th>
                <th>Keterangan</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @if (sizeof($dataScWait) > 0)
                <?php $i = 0; ?>
                @foreach($dataScWait as $rowSc)
                    <?php //dd($rowSc$rowSc->kode_alias); ?>
                    <tr>
                        <td>
                            <?php// dd($rowSc); ?>
                            <label id="sukucadangwaitval{{ $i }}" class="sukucadangwaitval" data-tipe="labelsc">{{ $pairKodeAlias[$rowSc->kode_alias] }}</label>
                            <input type="hidden" class="form-control" data-tipe="inputsc" name="arrsukucadangWaiting[{{ $i }}][kode_alias]" value="{{ $rowSc->kode_alias }}">
                            <input type="hidden" class="form-control" data-tipe="inputscSaldo" name="arrsukucadangWaiting[{{ $i }}][saldo]" value="">
                            <input type="hidden" class="form-control" data-tipe="inputscKelompok" name="arrsukucadangWaiting[{{ $i }}][kelompok_barang]" value="{{ $rowSc->kelompok_barang }}">

                            <input type="hidden" class="form-control" data-tipe="idSc" name="arrsukucadangWaiting[{{ $i }}][id]" value="{{ $rowSc->id }}">
                        </td>
                        <td>
                            <input type="number" id="jumlah{{ $i }}" class="form-control jumlah" onkeypress="javascript:valPress(event, $(this),$(this).attr('max'));" min="0" name="arrsukucadangWaiting[{{ $i }}][jumlah]" value="{{ $rowSc->jumlah }}">
                        </td>
                        <td>
                            <select name="arrsukucadangWaiting[{{ $i }}][dibeli_by]" class="form-control">
                                <option value="">-Pilih-</option>
                                <option value="gudang" {{ $rowSc->dibeli_by == 'gudang' ? ' selected="selected"' :'' }}>Gudang</option>
                                <option value="pemeliharaan"  {{ $rowSc->dibeli_by == 'pemeliharaan' ? ' selected="selected"' :'' }}>Pemeliharaan</option>
                            </select>
                        </td>
                        <td>
                            <textarea name="arrsukucadangWaiting[{{ $i }}][keterangan]">{{ $rowSc->keterangan }}</textarea>
                        </td>
                        <td><button type="button" class="btn btn-danger btn-sm removeBtnWait" onClick="remBtnWait($(this))"><i class="fa fa-times"></i></button></td>
                    </tr>

                    <?php $i++; ?>
                @endforeach
            @else
                <tr class="hidden">
                    <td>
                        <label id="sukucadangwaitval0" class="sukucadangwaitval" data-tipe="labelsc"></label>
                        <input type="hidden" class="form-control" data-tipe="inputsc" name="arrsukucadangWaiting[0][kode_alias]" value="">
                        <input type="hidden" class="form-control" data-tipe="inputscSaldo" name="arrsukucadangWaiting[0][saldo]" value="">
                        <input type="hidden" class="form-control" data-tipe="inputscKelompok" name="arrsukucadangWaiting[0][kelompok_barang]" value="">
                    </td>
                    <td>
                        <input type="number" id="jumlah0" class="form-control jumlah" min="1" name="arrsukucadangWaiting[0][jumlah]">
                    </td>
                    <td>
                        <select name="arrsukucadangWaiting[0][dibeli_by]" class="form-control">
                            <option value="">-Pilih-</option>
                            <option value="gudang">Gudang</option>
                            <option value="pemeliharaan">Pemeliharaan</option>
                        </select>
                    </td>
                    <td>
                        <textarea name="arrsukucadangWaiting[0][keterangan]"></textarea>
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm removeBtnWait" onClick="remBtnWait($(this))"><i class="fa fa-times"></i></button></td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<script type="text/javascript">
$(document).ready(function () {
    $("#dtlRemoved").val('');

    // Sukucadang
    if ($("#sukucadang").val() == "") {
        $("#addClone").hide();
    } else {
        $("#addClone").show();
    }

    $('#sukucadang').change(function () {
        $('#stok').empty();
        $("#addClone").hide();

        if ($(this).val() != '') {
            var str = $(this).val();
            var res = str.split("#");
            console.log(res);
            $("#stok").val(res[1]);
            $("#kelompok_barang_g").val(res[3]);

            if (res[1] > 0) {
                $("#addClone").show();
            }
        }
    });
});

var jmlDataSc = <?php echo count($dataSc) ?>;

if (jmlDataSc > 0) {
    var icl= jmlDataSc;
    console.log(jmlDataSc);
} else {
    var icl= 0;
}
$("#addClone").click(function($e) {
    console.log(icl);
    $('#tabelSc > tbody').find('tr:last').removeClass('hidden');

    var komponenid = $("#komponensc").val();
    var komponen = $("#komponensc").find('option:selected').text();
    
    var scId = $("#sukucadang").val();
    var scName = $("#sukucadang").find('option:selected').text();
    var arrScId = scId.split("#");
    /*
    0 - kode alias
    1 - saldo
    2 - id gudang
    */

    if (komponenid != "") {
        if (icl > 0) {
            $("#tabelSc > tbody > tr:last").clone().appendTo('#tabelSc > tbody');       
            $('#tabelSc > tbody').find('tr:last').find('.removeBtn').show();
        } else {
            $('#komponenval0').html(komponen);
        }

        // $('.komponenval').html(komponen);
        $('#tabelSc > tbody').find('tr:last').find('label[data-tipe=labelkomponen]').prop('id','komponenval'+icl).html(komponen);
        $('#tabelSc > tbody').find('tr:last').find('input[data-tipe=inputkomponen]').prop('name','arrsukucadang['+icl+'][komponen_id]').val(komponenid);

        $('#tabelSc > tbody').find('tr:last').find('label[data-tipe=labelsc]').prop('id','sukucadangval'+icl).html(scName);
        $('#tabelSc > tbody').find('tr:last').find('input[data-tipe=inputsc]').prop('name','arrsukucadang['+icl+'][kode_alias]').val(arrScId[0]);
        $('#tabelSc > tbody').find('tr:last').find('input[data-tipe=inputscSaldo]').prop('name','arrsukucadang['+icl+'][saldo]').val(arrScId[1]);
        $('#tabelSc > tbody').find('tr:last').find('input[data-tipe=inputscKelompok]').prop('name','arrsukucadang['+icl+'][kelompok_barang]').val(arrScId[3]);

        $('#tabelSc > tbody').find('tr:last').find('input[data-tipe=idSc]').prop('name','arrsukucadang['+icl+'][id]').val('');

        $('#tabelSc > tbody').find('tr:last').find('input[type=number]').prop('name','arrsukucadang['+icl+'][jumlah]').prop('max', arrScId[1]).prop('id', 'jumlah' + icl).val('');
        $('#tabelSc > tbody').find('tr:last').find('select').prop('name','arrsukucadang['+icl+'][dibeli_by]');
        $('#tabelSc > tbody').find('tr:last').find('textarea').prop('name','arrsukucadang['+icl+'][keterangan]');

        icl++;
    }
});

var remBtn = function(e) {
    icl--;
    
    var idSc = $(e).closest('tr').find('input[data-tipe=idSc]').val();
    $('#dtlRemoved').val($('#dtlRemoved').val() + ',' + idSc);

    if (icl == 0) {
        $('#tabelSc > tbody').find('tr:last').addClass('hidden');

        $('#tabelSc > tbody').find('tr:last').find('input[data-tipe=inputkomponen]').prop('name','arrsukucadang['+icl+'][komponen_id]').val('');

        $('#tabelSc > tbody').find('tr:last').find('input[data-tipe=inputsc]').prop('name','arrsukucadang['+icl+'][kode_alias]').val('');
        $('#tabelSc > tbody').find('tr:last').find('input[data-tipe=inputscSaldo]').prop('name','arrsukucadang['+icl+'][saldo]').val('');
        $('#tabelSc > tbody').find('tr:last').find('input[data-tipe=inputscKelompok]').prop('name','arrsukucadang['+icl+'][kelompok_barang]').val('');

        $('#tabelSc > tbody').find('tr:last').find('input[type=number]').prop('name','arrsukucadang['+icl+'][jumlah]').prop('max', arrScId[1]).prop('id', 'jumlah' + icl).val(0);
        $('#tabelSc > tbody').find('tr:last').find('select').prop('name','arrsukucadang['+icl+'][dibeli_by]').val('');
        $('#tabelSc > tbody').find('tr:last').find('textarea').prop('name','arrsukucadang['+icl+'][keterangan]').val('');
    } else {
        $(e).closest('tr').remove();
    }
}

var valPress = function(event, ini, max) {
    console.log('test');
    var frek = max;
    var code = event.charCode;

    var number = parseInt(String.fromCharCode(code));
    
    if (Number.isInteger(number)) {
        var cur = ini.val()+number;

        if (parseInt(number) > parseInt(frek)) {
            console.log(parseInt(number));
            console.log(parseInt(frek));
            new Noty({
                text: 'Jumlah yang Anda masukkan melebihi stok',
                type: 'error',
                modal: true
            }).show();
        }

        if (parseInt(cur) > parseInt(frek)) {
            ini.val('');
        }
    }
}

/*----------------------WAITING LIST*/
var jmlDataScWait = <?php echo count($dataScWait) ?>;

if (jmlDataScWait > 0) {
    var iclWait= jmlDataScWait;
    console.log(jmlDataScWait);
} else {
    var iclWait= 0;
}
$("#addWaiting").click(function($e) {
    console.log(iclWait);
    $('#tabelWait > tbody').find('tr:last').removeClass('hidden');

    var komponenid = $("#komponensc").val();
    var komponen = $("#komponensc").find('option:selected').text();
    
    var scId = $("#sukucadang").val();
    var scName = $("#sukucadang").find('option:selected').text();
    var arrScId = scId.split("#");
    /*
    0 - kode alias
    1 - saldo
    2 - id gudang
    */

    if (komponenid != "") {
        if (iclWait > 0) {
            $("#tabelWait > tbody > tr:last").clone().appendTo('#tabelWait > tbody');       
            $('#tabelWait > tbody').find('tr:last').find('.removeBtnWait').show();
        } else {
            $('#komponenval0').html(komponen);
        }

        // $('.komponenval').html(komponen);
        $('#tabelWait > tbody').find('tr:last').find('label[data-tipe=labelkomponen]').prop('id','komponenval'+iclWait).html(komponen);
        $('#tabelWait > tbody').find('tr:last').find('input[data-tipe=inputkomponen]').prop('name','arrsukucadangWaiting['+iclWait+'][komponen_id]').val(komponenid);

        $('#tabelWait > tbody').find('tr:last').find('label[data-tipe=labelsc]').prop('id','sukucadangwaitval'+iclWait).html(scName);
        $('#tabelWait > tbody').find('tr:last').find('input[data-tipe=inputsc]').prop('name','arrsukucadangWaiting['+iclWait+'][kode_alias]').val(arrScId[0]);
        $('#tabelWait > tbody').find('tr:last').find('input[data-tipe=inputscSaldo]').prop('name','arrsukucadangWaiting['+iclWait+'][saldo]').val(arrScId[1]);
        $('#tabelWait > tbody').find('tr:last').find('input[data-tipe=inputscKelompok]').prop('name','arrsukucadangWaiting['+iclWait+'][kelompok_barang]').val(arrScId[3]);

        $('#tabelWait > tbody').find('tr:last').find('input[type=number]').prop('name','arrsukucadangWaiting['+iclWait+'][jumlah]').prop('id', 'jumlah' + iclWait).val('');
        $('#tabelWait > tbody').find('tr:last').find('select').prop('name','arrsukucadangWaiting['+iclWait+'][dibeli_by]');
        $('#tabelWait > tbody').find('tr:last').find('textarea').prop('name','arrsukucadangWaiting['+iclWait+'][keterangan]');

        iclWait++;
    }
});

var remBtnWait = function(e) {
    iclWait--;

    var idSc = $('#tabelWait > tbody').find('tr:last').find('input[data-tipe=idSc]').val();
    console.log(idSc);
    $('#dtlRemoved').val($('#dtlRemoved').val() + ',' + idSc);
    
    if (iclWait == 0) {
        $('#tabelWait > tbody').find('tr:last').addClass('hidden');

        $('#tabelWait > tbody').find('tr:last').find('input[data-tipe=inputkomponen]').prop('name','arrsukucadangWaiting['+iclWait+'][komponen_id]').val('');

        $('#tabelWait > tbody').find('tr:last').find('input[data-tipe=inputsc]').prop('name','arrsukucadangWaiting['+iclWait+'][kode_alias]').val('');
        $('#tabelWait > tbody').find('tr:last').find('input[data-tipe=inputscSaldo]').prop('name','arrsukucadangWaiting['+iclWait+'][saldo]').val('');
        $('#tabelWait > tbody').find('tr:last').find('input[data-tipe=inputscKelompok]').prop('name','arrsukucadangWaiting['+iclWait+'][kelompok_barang]').val('');

        $('#tabelWait > tbody').find('tr:last').find('input[type=number]').prop('name','arrsukucadangWaiting['+iclWait+'][jumlah]').prop('id', 'jumlah' + iclWait).val(0);
        $('#tabelWait > tbody').find('tr:last').find('select').prop('name','arrsukucadangWaiting['+iclWait+'][dibeli_by]').val('');
        $('#tabelWait > tbody').find('tr:last').find('textarea').prop('name','arrsukucadangWaiting['+iclWait+'][keterangan]').val('');
    } else {
        $(e).closest('tr').remove();
    }
}
</script>