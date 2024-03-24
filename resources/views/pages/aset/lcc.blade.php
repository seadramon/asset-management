<h5>Life Cycle Cost</h5>

<div class="table-responsive">
    <!-- <a href="#" id="tambah-btn" class="btn btn-success legitRipple"><i class="fa fa-plus"></i> Tambah Baru</a><br><br> -->

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No. </th>
                <th>Elemen Biaya</th>
                <th>Biaya</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1.</td>
                <td>Biaya Akuisisi</td>
                <td>Rp {!! !empty($aset->harga)?number_format($aset->harga,0,",","."):"0" !!}</td>
            </tr>
            <tr>
                <td>2.</td>
                <td>Biaya Operasional</td>
                <td>Rp {!! !empty($totalOperasional)?number_format($totalOperasional,0,",","."):"0" !!}</td>
            </tr><tr>
                <td>3.</td>
                <td>Biaya Pemeliharaan</td>
                <td>Rp {!! !empty($totalPemeliharaan)?number_format($totalPemeliharaan,0,",","."):"0" !!}</td>
            </tr><tr>
                <td>4.</td>
                <td>Biaya Penghapusan</td>
                <td>Rp {!! !empty($aset->penghapusan_biaya)?number_format($aset->penghapusan_biaya,0,",","."):"0" !!}</td>
            </tr>
            <tr>
                <td></td>
                <td>TOTAL LIFE CYCLE COST</td>
                <td>Rp {!! !empty($totalLcc)?number_format($totalLcc,0,",","."):"0" !!}</td>
            </tr>
        </tbody>
    </table>
</div>