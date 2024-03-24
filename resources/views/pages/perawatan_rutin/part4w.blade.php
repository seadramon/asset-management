<ul class="nav nav-tabs nav-tabs-highlight nav-justified">
    <li class="nav-item"><a href="#justified-left-tab1" class="nav-link active" data-toggle="tab"><i class="icon-user-check mr-2"></i> Disposisi</a></li>
    <li class="nav-item"><a href="#justified-left-tab2" class="nav-link" data-toggle="tab"><i class="icon-cogs mr-2"></i> Suku Cadang</a></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="justified-left-tab1">
        <table class="table table-hover" id="tabel" style="width: 100%;">
            <thead>
                <tr>                                    
                    <th>Nama Aset</th>
                    <th>Part</th>
                    <th>Perawatan</th>
                    <th>Disposisi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                	$i = 0; 
                	$arrAset = [];
                ?>
                @if (sizeof($arrkomponen) > 0)
                	@foreach($arrkomponen as $asetnya)
                		<?php 
                		$x = 0;
                		?>
                		@foreach($komponens as $komponen)
                			@if ($komponen->nama_aset == $asetnya)
                				<tr>
                					<td>
                						{!! Form::hidden("week[$i][aset_id]", $komponen->komponen->id, ['class'=>'form-control', 'id'=>'aset_id']) !!}
                						{!! Form::hidden("week[$i][part_id]", $komponen->part_id, ['class'=>'form-control', 'id'=>'part_id']) !!}

                						{!! Form::hidden("week[$i][is_equipment]", $komponen->komponen->equipment, ['class'=>'form-control', 'id'=>'is_equipment']) !!}
                						{!! Form::hidden("week[$i][equipment_id]", $komponen->komponen->equipment_id, ['class'=>'form-control', 'id'=>'equipment_id']) !!}

                						{!! Form::hidden("week[$i][id]", $komponen->prw_4w_id, ['class'=>'form-control', 'id'=>'id']) !!}
                						{!! Form::hidden("week[$i][prw_52w_id]", $komponen->id, ['class'=>'form-control', 'id'=>'prw_52w_id']) !!}
                						{!! Form::hidden("week[$i][wo_id]", $komponen->wo_id, ['class'=>'form-control', 'id'=>'wo_id']) !!}
                						{!! Form::hidden("week[$i][hari]", !empty($komponen->hari)?$komponen->hari:'senin', ['class'=>'form-control', 'id'=>'hari']) !!}
                						@if ($x == 0)
                							{{ $komponen->wo_id }} . {{ $komponen->nama_aset }}
                						@endif
                					</td>
                					<td>
                                        {{ $komponen->part }}
                					</td>
                					<td>
                						{{ $komponen->perawatan }}
                					</td>
                					<td>
                                        @if ($x == 0)
                						    {!! Form::select("week[$i][petugas]", $petugas, $komponen->petugas, ['class'=>'form-control select2', 'id'=>'petugas', 'required', 'width' => '100%']) !!}
                                        @endif
                					</td>
                				</tr>
                				<?php $i++; ?>
                				<?php $x++; ?>
                			@endif
                		@endforeach
                	@endforeach
                @else
                	<tr>
                        <td colspan="4" align="center">Data Kosong</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="tab-pane fade" id="justified-left-tab2">
        
        <fieldset class="mb-3">
            @if (!empty($arrkomponen))
            	<div class="form-group row">
                    <label class="col-form-label col-lg-2">Komponen</label>
                    <div class="col-lg-10">
                        {!! Form::select('komponensc', $arrkomponen, null, ['class'=>'form-control select2', 'id'=>'komponensc']) !!}
                    </div>
                </div>
            	<div class="form-group row">
                    <label class="col-form-label col-lg-2">Suku Cadang</label>
                    <div class="col-lg-10">
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
                        <button type="button" id="addClone" class="btn btn-success legitRipple"> 
                            <i class="fa fa-plus"></i> Tambah
                        </button>
                    </div>
                </div>
            @endif

            <div class="row">
                <?php //dd(count($dataSc)); ?>
            	<table class="table" id="tabelSc">
                    <thead>
                        <tr>                                    
                            <th>Nama Komponen</th>
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
                                        <label id="komponenval{{ $i }}" class="komponenval" data-tipe="labelkomponen">{{ $pairAset[$rowSc->prw_rutin_id] }}</label>
                                        <input type="hidden" class="form-control" data-tipe="inputkomponen" name="arrsukucadang[{{ $i }}][komponen_id]" value="{{ $arrPair[$rowSc->prw_rutin_id] }}">
                                    </td>
                                    <td>
                                        <label id="sukucadangval{{ $i }}" class="sukucadangval" data-tipe="labelsc">{{ $pairKodeAlias[$rowSc->kode_alias] }}</label>
                                        <input type="hidden" class="form-control" data-tipe="inputsc" name="arrsukucadang[{{ $i }}][kode_alias]" value="{{ $rowSc->kode_alias }}">
                                        <input type="hidden" class="form-control" data-tipe="inputscSaldo" name="arrsukucadang[{{ $i }}][saldo]" value="">
                                        <input type="hidden" class="form-control" data-tipe="inputscKelompok" name="arrsukucadang[{{ $i }}][kelompok_barang]" value="{{ $rowSc->kelompok_barang }}">
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
                    		        <label id="komponenval0" class="komponenval" data-tipe="labelkomponen"></label>
                    		        <input type="hidden" class="form-control" data-tipe="inputkomponen" name="arrsukucadang[0][komponen_id]" value="">
                    		    </td>
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
        </fieldset>
    </div>
</div>

<div class="text-left" style="margin-top: 50px;">
    {{ 'Jumlah Entri : '.$jmlKomponen }}
</div>

<script type="text/javascript">
    $(document).ready(function () {
        Noty.overrideDefaults({
            theme: 'limitless',
            layout: 'topRight',
            type: 'alert',
            timeout: 2500
        });

    	if ($("#sukucadang").val() == "") {
	        $("#addClone").hide();
	    } else {
	        $("#addClone").show();
	    }

        $(".select2").select2({ width: '100%' });

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
    $("#addClone").click(function() {
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

            $('#tabelSc > tbody').find('tr:last').find('input[type=number]').prop('name','arrsukucadang['+icl+'][jumlah]').prop('max', arrScId[1]).prop('id', 'jumlah' + icl).val('');
            $('#tabelSc > tbody').find('tr:last').find('select').prop('name','arrsukucadang['+icl+'][dibeli_by]');
            $('#tabelSc > tbody').find('tr:last').find('textarea').prop('name','arrsukucadang['+icl+'][keterangan]');

            icl++;
        }
    });

    var remBtn = function(e) {
        icl--;
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

    // Pe er gans
    /*$('input[type="number"]').on('keypress', function(e) {
        console.log('keypress');
        var frek = $(this).attr('max');
        var code = e.charCode;

        var number = parseInt(String.fromCharCode(code));
        
        if (Number.isInteger(number)) {
            var cur = $(this).val()+number;

            if (parseInt(number) > parseInt(frek)) {
                return false;
            }

            if (parseInt(cur) > parseInt(frek)) {
                console.log('kkk');
                $(this).val('');
            }
        }
    });*/

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

    /*$('.jumlah').on('input', function () {
        console.log('input');
        var frek = $(this).attr('max');
        var value = $(this).val();

        if ((value !== '') && (value.indexOf('.') === -1)) {            
            $(this).val(Math.max(Math.min(value, parseInt(frek)), 0));
        }
    })*/
</script>