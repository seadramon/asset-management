<h5>Biaya Pemeliharaan</h5>

<!-- <div class="table-responsive"> -->
    <table class="table table-bordered" id="pemeliharaan" width="100%">
        <thead>
            <tr>
                <th>WO ID</th>
                <th>Tanggal Kegiatan</th>
                <th>Dokumen SPK</th>
                <th>Dokumen<br> Berita Acara Hasil <br>Pekerjaan (Penjamin Kualitas)</th>
                <!-- <th colspan="2">Aktivitas Pekerjaan</th> -->
                <th>Jenis Work Order</th>
                <th>Pelaksana Pekerjaan</th>
                <!-- <th>Jumlah</th>
                <th>Satuan</th>
                <th>Biaya Satuan</th>
                <th>Jumlah Biaya</th> -->
                <th>Action</th>
            </tr>
            <!-- <tr>
                <th>Servis</th>
                <th>Suku Cadang</th>
            </tr> -->
        </thead>
        <tbody>
            
        </tbody>
    </table>
<!-- </div> -->

<div id="new_pemeliharaan" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Biaya Pemeliharaan</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            {!! Form::open(['url' => route('lcca::pemeliharaan-simpan'), 'class' => 'form-horizontal', 'id' => 'f_newPemeliharaan']) !!}
                {!! Form::hidden('aset_id', $aset->id) !!}
                {!! Form::hidden('wo', null, ['id' => 'newWo']) !!}
                {!! Form::hidden('wo_id', null, ['id' => 'newWo_id']) !!}

                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Dokumen SPK</label>
                        <div class="col-sm-9">
                            {!! Form::textarea('spk', null, ['class'=>'form-control', 'id'=>'newSpk', 'required', 'rows' => '3']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Dokumen Berita Acara Hasil Pekerjaan</label>
                        <div class="col-sm-9">
                            {!! Form::textarea('berita_acara', null, ['class'=>'form-control', 'id'=>'newBerita_acara', 'required', 'rows' => '3']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Jumlah</label>
                        <div class="col-sm-9">
                            {!! Form::number("jumlah[0]", null, ['class'=>'form-control', 'id'=>'newJumlah', 'required']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Satuan</label>
                        <div class="col-sm-9">
                            {!! Form::text("satuan[0]", null, ['class'=>'form-control', 'id'=>'newSatuan', 'required']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Biaya Satuan</label>
                        <div class="col-sm-9">
                            {!! Form::number("biaya[0]", null, ['class'=>'form-control', 'id'=>'newBiaya', 'required']) !!}
                        </div>
                    </div>

                    <div class="table-responsive">
                        <button type="button" class="btn bg-success" id="addSukucadang">Tambah</button>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Biaya</th>
                                </tr>
                            </thead>
                            <tbody id="partNewSukucadang">
                                <tr>
                                    <td colspan="4">Data Kosong</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn bg-primary">Submit</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>

<div id="edit_pemeliharaan" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Biaya Pemeliharaan</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            {!! Form::open(['url' => route('lcca::pemeliharaan-simpan'), 'class' => 'form-horizontal', 'id' => 'f_editPemeliharaan']) !!}
                {!! Form::hidden('id', null, ['id' => 'recid']) !!}
                {!! Form::hidden('aset_id', $aset->id) !!}

                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Dokumen SPK</label>
                        <div class="col-sm-9">
                            {!! Form::textarea('spk', null, ['class'=>'form-control', 'id'=>'spk', 'required', 'rows' => '3']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Dokumen Berita Acara Hasil Pekerjaan</label>
                        <div class="col-sm-9">
                            {!! Form::textarea('berita_acara', null, ['class'=>'form-control', 'id'=>'berita_acara', 'required', 'rows' => '3']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Jumlah</label>
                        <div class="col-sm-9">
                            {!! Form::number('jumlah', null, ['class'=>'form-control', 'id'=>'jumlah', 'required']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label co1
                        l-sm-3">Satuan</label>
                        <div class="col-sm-9">
                            {!! Form::text('satuan', null, ['class'=>'form-control', 'id'=>'satuan', 'required']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-3">Biaya Satuan</label>
                        <div class="col-sm-9">
                            {!! Form::number('biaya', null, ['class'=>'form-control', 'id'=>'biaya', 'required']) !!}
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn bg-primary">Submit</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $.fn.modal.Constructor.prototype.enforceFocus = $.noop;
    var table = $('#pemeliharaan').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('lcca::mc-data') }}?komponen={{ $aset->id }}",
        "columns": [    
            {data: 'wo_id', name: 'wo_id', defaultContent: '-'},
            {data: 'tanggal', name: 'tanggal', defaultContent: '-'},
            {data: 'spk', name: 'spk', defaultContent: '-'},
            {data: 'berita_acara', name: 'berita_acara', defaultContent: '-'},
            // {data: 'jenis_penanganan', name: 'jenis_penanganan', defaultContent: '-'},
            // {data: 'group_nama_barang', name: 'group_nama_barang', defaultContent: '-'},
            {data: 'wo', name: 'wo', defaultContent: '-'},
            {data: 'metode', name: 'metode', defaultContent: '-'},
            /*{data: 'jumlah', name: 'jumlah', defaultContent: '-'},
            {data: 'satuan', name: 'satuan', defaultContent: '-'},
            {data: 'biaya', name: 'biaya', defaultContent: '-'},
            {data: 'jumlah_biaya', name: 'jumlah_biaya', defaultContent: '-'},*/
            {data: 'menu', orderable: false, searchable: false},
            // {data: 'group_kode_alias', name: 'group_kode_alias', orderable: false, searchable: false, visible: false},
            {data: 'recid', name: 'recid', orderable: false, searchable: false, visible: false},
        ],
        "order": [[0, 'asc']],
        "dom": '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
        "pagingType": "simple_numbers"
    }); 

    $('#pemeliharaan').on('click', ' tbody td .newPemeliharaan', function(){
        // console.log($(this).parents('tr')[0]);
        var nTr = $(this).parents('tr')[0];
        var dt = table.row(nTr);
        var sData = dt.data();

        $("#partNewSukucadang").html('<tr><td colspan="4">Data Kosong</td></tr>');
        
        $("#newWo").val('');
        $("#newWo_id").val('');
        if (sData) {
            $("#newWo").val(sData.wo);
            $("#newWo_id").val(sData.wo_id);

            if (sData.group_kode_alias) {
                $("#partNewSukucadang").html("");
                var kodeAlias = sData.group_kode_alias;

                $.ajax({
                    type: "get",
                    url: "{{url('lcca/sukucadang')}}?kodealias=" + kodeAlias,
                    success: function(result) {
                        console.log(result);
                        $("#partNewSukucadang").html(result);
                    }
                })
            }
        }
    });

    $("#addSukucadang").click(function() {
        $('#tabelSc > tbody').find('tr:last').removeClass('hidden');
    });

    $('#pemeliharaan').on('click', '.editPemeliharaan', function(){
       var data = table.row($(this).data('row')).data();

       $("#recid").val('');
       $("#spk").val('');
       $("#berita_acara").val('');
       $("#jumlah").val('');
       $("#satuan").val('');
       $("#biaya").val('');

       if (data) {
           $("#recid").val(data.recid);
           $("#spk").val(data.spk);
           $("#berita_acara").val(data.berita_acara);
           $("#jumlah").val(data.jumlah);
           $("#satuan").val(data.satuan);
           $("#biaya").val(data.biaya);
       }
    });
});
</script>