<?php $i = 0; ?>
{!! Form::hidden('jmlSukucadang', $jmlSukucadang, ['id' => 'jmlSukucadang']) !!}

@foreach($arrData as $data)
	<tr>
		<td>{{ $data->nama }}</td>
		<td>
			{!! Form::number("jumlah[$i]", null, ['class'=>'form-control', 'id'=>'newJumlah', 'required']) !!}
		</td>
		<td>
			{!! Form::text("satuan[$i]", null, ['class'=>'form-control', 'id'=>'newSatuan', 'required']) !!}
		</td>
		<td>
			{!! Form::number("biaya[$i]", null, ['class'=>'form-control', 'id'=>'newBiaya', 'required']) !!}
		</td>
	</tr>

	<?php $i++; ?>
@endforeach