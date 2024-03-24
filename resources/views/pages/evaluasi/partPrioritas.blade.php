@if (count($komponens) > 0)
    <?php $i = 0; ?>
    @foreach($komponens as $komponen)
        <?php 
            // dd($komponen->investasi[0]);
            $investRelation = false;
            if (count($komponen->investasi) > 0) {
                $investRelation = true;    
            }
        ?>
        <tr>
            <td>
                <label>{{ $komponen->nama_aset }}</label>
                {!! Form::hidden("periksa[$i][komponen_id]", $komponen->id, ['class'=>'form-control', 'id'=>'komponen_id']) !!}
            </td>
            <td>
                {!! Form::select("periksa[$i][strategi]", $strategi, ($investRelation)?$komponen->investasi[0]->strategi:"", ['class'=>'form-control select2', 'id'=>'strategi']) !!}
            </td>
            <td>
                {!! Form::number("periksa[$i][nilai_rab]", ($investRelation)?$komponen->investasi[0]->nilai_rab:"", ['class'=>'form-control', 'id'=>'nilai_rab']) !!}
            </td>
            <td>
                {!! Form::select("periksa[$i][kelayakan_op]", $ops, ($investRelation)?$komponen->investasi[0]->kelayakan_op:"", ['class'=>'form-control select2', 'id'=>'ops']) !!}
            </td>
            <td>
                {!! Form::select("periksa[$i][kelayakan_keuangan]", $keuangan, ($investRelation)?$komponen->investasi[0]->kelayakan_keuangan:"", ['class'=>'form-control select2', 'id'=>'kelayakan_keuangan']) !!}
            </td>
            <td>
                {!! Form::select("periksa[$i][waktu]", $waktu, ($investRelation)?$komponen->investasi[0]->waktu:"", ['class'=>'form-control select2', 'id'=>'waktu']) !!}
            </td>
            
        </tr>
        <?php $i++; ?>
    @endforeach
@else
    <tr>
        <td colspan="6" align="center">Data Kosong</td>
    </tr>
@endif

<script type="text/javascript">
    $(".select2").select2();
    
    $('.mm').keypress(function(e) {
        var str = $('#frekuensi').text();
        var frek = str.replace("W", "");
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
    });

    $('.mm').on('input', function () {
    	var str = $('#frekuensi').text();
        var frek = str.replace("W", "");
        var value = $(this).val();

	    if ((value !== '') && (value.indexOf('.') === -1)) {	        
	        $(this).val(Math.max(Math.min(value, parseInt(frek)), 0));
	    }
    })
</script>