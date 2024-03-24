@if (count($arrdata) > 0)
    <?php $i = 0; ?>
    @foreach($arrdata as $data)
        
        <tr>
            <td>
                <label>{{ $data['perawatan'] }}</label>
                <!-- perawatan -->
                {!! Form::hidden("periksa[$i][perawatan]", $data['perawatan'], ['class'=>'form-control', 'id'=>'perawatan']) !!}
                <!-- ms_52w_id -->
                {!! Form::hidden("periksa[$i][id]", $data['id'], ['class'=>'form-control', 'id'=>'id']) !!}
            </td>
            <td>
                <label id="frekuensi">
                    {{ $data['frekuensi'] }}
                </label>
            </td>
            <td>
                {!! Form::number("periksa[$i][minggu_mulai]", 
                        $data['minggu_mulai'], 
                        ['class'=>'form-control mm', 'id'=>'minggu_mulai', 'required', 'data-frekuensi' => $data['frekuensi'], 'min' => '1']) !!}
            </td>
            <td>
                {!! Form::number("periksa[$i][jumlah_orang]", 
                        $data['jumlah_orang'], 
                        ['class'=>'form-control', 'id'=>'jumlah_orang']) !!}
            </td>
            <td>
                {!! Form::number("periksa[$i][total_durasi]", 
                        $data['total_durasi'] , 
                        ['class'=>'form-control', 'id'=>'total_durasi', 'min'=>'0', 'step'=>'.01', 'style' => 'width:70%']) !!}
            </td>
            
        </tr>
        <?php $i++; ?>
    @endforeach
@else
    <tr>
        <td colspan="4" align="center">Data Kosong</td>
    </tr>
@endif

<script type="text/javascript">
    $('.mm').keypress(function(e) {        
        // var str = $('#frekuensi').text();
        var str = $(this).data("frekuensi");
        // var str = {{}} ;
        var frek = str.replace("W", "");
        var code = e.charCode;

        var number = parseInt(String.fromCharCode(code));
        
        if (Number.isInteger(number)) {
            var cur = $(this).val()+number;

            console.log(cur);
            if (cur.toString().length == 1 && cur == 0) {
                return false;
            }

            if (parseInt(number) > parseInt(frek)) {
                return false;
            }

            if (parseInt(cur) > parseInt(frek)) {
                console.log('kkk');
                $(this).val('');
                if (number == 0) {
                    return false;
                }
            }
        }
    });

    $('.mm').on('input', function () {
        // var str = $('#frekuensi').text();
    	var str = $(this).data("frekuensi");
        var frek = str.replace("W", "");
        var value = $(this).val();

	    if ((value !== '') && (value.indexOf('.') === -1)) {	        
	        $(this).val(Math.max(Math.min(value, parseInt(frek)), 0));
	    }
    })
</script>