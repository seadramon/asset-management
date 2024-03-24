@if (count($komponens) > 0)
    <?php $i = 0; ?>
    @foreach($komponens as $komponen)
        <?php 
            // dd($komponens);
            $ms52relation = false;

            if (count($komponen->ms52w_komponen) > 0) {
                $ms52relation = true;    
            }
        ?>
        <tr>
            <td>
                <label>{{ $komponen->nama_aset }}</label>
                {!! Form::hidden("periksa[$i][komponen_id]", $komponen->id, ['class'=>'form-control', 'id'=>'komponen_id']) !!}

                <!-- ms_52w_id -->
                {!! Form::hidden("periksa[$i][52w_id]", ($ms52relation)?$komponen->ms52w_komponen[0]['id']:"", ['class'=>'form-control', 'id'=>'52w_id']) !!}
            </td>
            <td>
                <label id="frekuensi">
                    <?php 
                    $dataFrekuensi = null;
                    ?>
                    @if (sizeof($komponen->pdm) > 0) 
                        {{ $komponen->pdm[0]['nilai'] }}
                        <?php 
                            $dataFrekuensi = $komponen->pdm[0]['nilai'];
                        ?>
                    @else
                        {{ '-' }}
                    @endif
                </label>
            </td>
            <td>
                {!! Form::number("periksa[$i][minggu_mulai]", 
                        ($ms52relation)?$komponen->ms52w_komponen[0]['minggu_mulai']:"", 
                        ['class'=>'form-control mm', 'id'=>'minggu_mulai', 'required', 'data-frekuensi' => $dataFrekuensi, 'min' => '1']) !!}
            </td>
            <td>
                {!! Form::number("periksa[$i][jumlah_orang]", 
                        ($ms52relation)?$komponen->ms52w_komponen[0]['jumlah_orang']:"", 
                        ['class'=>'form-control', 'id'=>'jumlah_orang']) !!}
            </td>
            <td>
                {!! Form::number("periksa[$i][total_durasi]", 
                        ($ms52relation)?$komponen->ms52w_komponen[0]['total_durasi']:"", 
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
        // var str = $('#frekuensi').text();
    	var str = $(this).data("frekuensi");
        var frek = str.replace("W", "");
        var value = $(this).val();

	    if ((value !== '') && (value.indexOf('.') === -1)) {	        
	        $(this).val(Math.max(Math.min(value, parseInt(frek)), 0));
	    }
    })
</script>