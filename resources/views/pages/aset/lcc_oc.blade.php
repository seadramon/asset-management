<h5>Biaya Operasional (Konsumsi Listrik)</h5>

<div class="table-responsive">
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#new_operasional">Tambah Baru <i class="fa fa-plus"></i></button>
    <br><br>

    <table class="table table-bordered" id="tblOp">
        <thead>
            <tr>
                <th>No. </th>
                <th>Tanggal Pengambilan Data</th>
                <th>Pemakaian kWh minggu</th>
                <th>Harga per kWh</th>
                <th>Biaya kWh per minggu</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            @if ( sizeof($aset->biayaOperasional) > 0 )
                @foreach( $aset->biayaOperasional as $row )
                    <tr>
                        <td>{{ $i }}</td>
                        <td>{{ date("d F Y", strtotime($row->tanggal)) }}</td>
                        <td>{{ !empty($row->pemakaian)?number_format($row->pemakaian,0,",","."):"0" }}</td>
                        <td>{{ !empty($row->harga)?number_format($row->harga,0,",","."):"0" }}</td>
                        <td>{{ !empty($row->biaya)?number_format($row->biaya,0,",","."):"0" }}</td>
                        <td>
                            <button type="button" class="btn btn-outline bg-primary border-primary text-primary-800 btn-icon editOp" data-toggle="modal" data-target="#edit_operasional" data-baris="{{ $row->toJson() }}"><i class="icon-pencil"></i></button>

                            <form style="float:right;" method="POST" action="{{route('lcca::operasional-delete', ['id' => $row->id])}}" onsubmit="return ConfirmDelete()">
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="aset_id" value="{{ $row->aset_id }}">
                                <button type="submit" class="btn btn-outline bg-danger border-danger text-danger-800 btn-icon"><i class="icon-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php $i++; ?>
                @endforeach
            @else
                <tr>
                    <td colspan="6" align="center">Data Kosong</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<div id="new_operasional" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Biaya Operasional</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            {!! Form::open(['url' => route('lcca::operasional-simpan'), 'class' => 'form-horizontal']) !!}
                {!! Form::hidden('aset_id', $aset->id) !!}
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Tanggal Pengambilan Data</label>
                        <div class="col-sm-9">
                            {!! Form::text('tanggal', null, ['class'=>'form-control datepicker', 'id'=>'tanggal', 'required']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Pemakaian kWh minggu</label>
                        <div class="col-sm-9">
                            {!! Form::number('pemakaian', null, ['class'=>'form-control', 'id'=>'pemakaian', 'required']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Harga per kWh</label>
                        <div class="col-sm-9">
                            {!! Form::number('harga', null, ['class'=>'form-control', 'id'=>'harga', 'required']) !!}
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn bg-primary">Submit form</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>

<div id="edit_operasional" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Biaya Operasional</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            {!! Form::open(['url' => route('lcca::operasional-simpan'), 'class' => 'form-horizontal']) !!}
                {!! Form::hidden('aset_id', $aset->id) !!}
                {!! Form::hidden('id', null, ['id' => 'id']) !!}
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Tanggal Pengambilan Data</label>
                        <div class="col-sm-9">
                            {!! Form::text('tanggal', null, ['class'=>'form-control datepicker', 'id'=>'tanggalEdit', 'required']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Pemakaian kWh minggu</label>
                        <div class="col-sm-9">
                            {!! Form::number('pemakaian', null, ['class'=>'form-control', 'id'=>'pemakaianEdit', 'required']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Harga per kWh</label>
                        <div class="col-sm-9">
                            {!! Form::number('harga', null, ['class'=>'form-control', 'id'=>'hargaEdit', 'required']) !!}
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn bg-primary">Submit form</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>

<script type="text/javascript">
$(function() {
    $(".editOp").click(function(event) {
        var data = $(this).data('baris');
        $("#tanggalEdit").val('');
        $("#pemakaianEdit").val('');
        $("#hargaEdit").val('');

        if (data) {
            $("#id").val(data.id);
            $("#tanggalEdit").val(data.tanggal);
            $("#pemakaianEdit").val(data.pemakaian);
            $("#hargaEdit").val(data.harga);
        }
    });
});
</script>