<?php
set_time_limit(1000);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Key Performance Indikator</title>

    <style type="text/css">
        @page {
            margin: 0px;
        }
        header { position: fixed;margin-top: 60px; }
        footer { position: fixed; }
        body {
            margin: 0px;
        }
        * {
            font-family: Verdana, Arial, sans-serif;
        }
        a {
            color: #fff;
            text-decoration: none;
        }
        table {
            font-size: x-small;
        }
        tfoot tr td {
            font-weight: bold;
            font-size: x-small;
        }
        .invoice table {
            margin-left: 10px;
            margin-right: 15px;
        }
        .invoice h3, h5 {
            margin-left: 17px;
        }
        .information {
            background-color: #fff;
            color: #000;
        }

        .information .logo {
            margin: 5px;
        }
        .information table {
            padding: 1px;
        }
        .tepi {border: 1px solid black;}
        .tengah {text-align: center;}
        .kiri{text-align: left;}
        .kanan{text-align: right;}
        .batasBawah{border-bottom:1pt solid black;}
        .text14 {font-weight: bold; font-size: 14px;}
        .text13 {font-weight: bold; font-size: 13px;}
        .text18 {font-weight: bold; font-size: 18px;}
        .text13n {font-size: 13px;}

        table.blueTable {
          font-family: Arial, Helvetica, sans-serif;
          border: 1px solid #000000;
          background-color: #FFFFFF;
          width: 100%;
          text-align: left;
          border-collapse: collapse;
        }
        table.blueTable td, table.blueTable th {
          border: 1px solid #000000;
          padding: 3px 3px;
        }
        table.blueTable tbody td {
          font-size: 13px;
        }
        table.blueTable thead {
          background: #BFBEBD;
          border-bottom: 1px solid #000000;
        }
        table.blueTable thead th {
          font-size: 12px;
          font-weight: bold;
          color: #000000;
          border-left: 1px solid #2B2F32;
        }
        table.blueTable thead th:first-child {
          border-left: none;
        }
    </style>

</head>
<body>

<!-- <header> -->
    <div class="information" style="margin-top: 10px;">
        <table style="width:100%;border-bottom:none;">
            <tr>
                <td class="tengah">
                    <span class="text18">Key Performance Indikator (KPI)</span><br>
                    <span class="text13n">Periode : {{$periode or ''}}</span>
                </td>
            </tr>
        </table> 
              
    </div>
<!-- </header> -->

<!-- <footer>
    <div class="information" style="position: absolute; bottom: 0;margin-top:100px;">
        <table width="100%">
            <tr>
                <td align="left" style="width: 50%;">
                    &copy; {{ date('Y') }} <a href="#">Asset Management</a> by <a href="#" target="_blank">PDAM Surya Sembada Kota Surabaya</a>
                </td>
                <td align="right" style="width: 50%;">
                    &nbsp;
                </td>
            </tr>

        </table>
    </div>
</footer> -->

<!-- <main> -->
<div class="invoice"  style="margin-bottom:20px;">
    <h5>Bagian : {{ $bag }} </h5>

    <table class="blueTable">
        <tr>
            <td colspan="10" style="font-weight: bolder;background-color: #dfdddd; padding-top: 10px; padding-bottom: 10px;" class="tengah text14">Lagging Indicator</td>
        </tr>
        <tr style="text-align: center;font-weight: bolder;">
            <td style="width: 5%;">No</td>
            <td style="width: 20%;">Jenis WO</td>
            <td style="width: 10%;">Target Penyelesaian</td>
            <td style="width: 10%;">Jumlah WO Bulan Berlalu</td>
            <td style="width: 10%;">Jumlah WO Bulan Ini</td>
            <td style="width: 10%;">Selesai</td>
            <td style="width: 10%;">Tidak Selesai</td>
            <td style="width: 10%;">Belum Selesai</td>
            <td style="width: 10%;">%</td>
            <td style="width: 15%;">Status</td>
        </tr>
        <tr>
            <td colspan="10" style="font-weight: bolder;background-color: #e5d9d9;">Preventive</td>
        </tr>
        <tr>
            <td class="tengah">1</td>
            <td>Monitoring</td>
            <td class="tengah">90%</td>
            <td class="tengah">-</td>
            <td class="tengah">{{ $monitoring['total'] }}</td>
            <td class="tengah">{{ $monitoring['selesai'] }}</td>
            <td class="tengah">{{ $monitoring['tidak_selesai'] }}</td>
            <td class="tengah">{{ $monitoring['belum_selesai'] }}</td>
            <td class="tengah">{{ is_numeric($monitoring['persentase'])?$monitoring['persentase'].'%':'-' }}</td>
            <td class="tengah">{{ $monitoring['status'] }}</td>
        </tr>
        <tr>
            <td class="tengah">2</td>
            <td>Perawatan Rutin</td>
            <td class="tengah">90%</td>
            <td class="tengah">-</td>
            <td class="tengah">{{ $prwRutin['total'] }}</td>
            <td class="tengah">{{ $prwRutin['selesai'] }}</td>
            <td class="tengah">{{ $prwRutin['tidak_selesai'] }}</td>
            <td class="tengah">{{ $prwRutin['belum_selesai'] }}</td>
            <td class="tengah">{{ is_numeric($prwRutin['persentase'])?$prwRutin['persentase'].'%':'-' }}</td>
            <td class="tengah">{{ $prwRutin['status'] }}</td>
        </tr>

        <tr>
            <td colspan="10" style="font-weight: bolder;background-color: #e5d9d9;">Corrective (Respons)</td>
        </tr>
        <tr>
            <td class="tengah">1</td>
            <td>Perawatan non Rutin</td>
            <td class="tengah">95%</td>
            <td class="tengah">{{ $prwCorrective['totalBerlalu'] }}</td>
            <td class="tengah">{{ $prwCorrective['total'] }}</td>
            <td class="tengah">{{ $prwCorrective['selesai'] }}</td>
            <td class="tengah">{{ $prwCorrective['tidak_selesai'] }}</td>
            <td class="tengah">{{ $prwCorrective['belum_selesai'] }}</td>
            <td class="tengah">{{ is_numeric($prwCorrective['persentase'])?$prwCorrective['persentase'].'%':'-' }}</td>
            <td class="tengah">{{ $prwCorrective['status'] }}</td>
        </tr>
        <tr>
            <td class="tengah">2</td>
            <td>Perbaikan dari Monitoring</td>
            <td class="tengah">95%</td>
            <td class="tengah">{{ $prbCorrective['totalBerlalu'] }}</td>
            <td class="tengah">{{ $prbCorrective['total'] }}</td>
            <td class="tengah">{{ $prbCorrective['selesai'] }}</td>
            <td class="tengah">{{ $prbCorrective['tidak_selesai'] }}</td>
            <td class="tengah">{{ $prbCorrective['belum_selesai'] }}</td>
            <td class="tengah">{{ is_numeric($prbCorrective['persentase'])?$prbCorrective['persentase'].'%':'-' }}</td>
            <td class="tengah">{{ $prbCorrective['status'] }}</td>
        </tr>
        <tr>
            <td class="tengah">3</td>
            <td>Perbaikan dari Aduan</td>
            <td class="tengah">95%</td>
            <td class="tengah">{{ $prbAduanCorrective['totalBerlalu'] }}</td>
            <td class="tengah">{{ $prbAduanCorrective['total'] }}</td>
            <td class="tengah">{{ $prbAduanCorrective['selesai'] }}</td>
            <td class="tengah">{{ $prbAduanCorrective['tidak_selesai'] }}</td>
            <td class="tengah">{{ $prbAduanCorrective['belum_selesai'] }}</td>
            <td class="tengah">{{ is_numeric($prbAduanCorrective['persentase'])?$prbAduanCorrective['persentase'].'%':'-' }}</td>
            <td class="tengah">{{ $prbAduanCorrective['status'] }}</td>
        </tr>

        <tr>
            <td colspan="10" style="font-weight: bolder;background-color: #e5d9d9;">Corrective (Closing)</td>
        </tr>
        <tr>
            <td class="tengah">1</td>
            <td>Perawatan non Rutin</td>
            <td class="tengah">90%</td>
            <td class="tengah">{{ $prwClosing['totalBerlalu'] }}</td>
            <td class="tengah">{{ $prwClosing['total'] }}</td>
            <td class="tengah">{{ $prwClosing['selesai'] }}</td>
            <td class="tengah">{{ $prwClosing['tidak_selesai'] }}</td>
            <td class="tengah">{{ $prwClosing['belum_selesai'] }}</td>
            <td class="tengah">{{ is_numeric($prwClosing['persentase'])?$prwClosing['persentase'].'%':'-' }}</td>
            <td class="tengah">{{ $prwClosing['status'] }}</td>
        </tr>
        <tr>
            <td class="tengah">2</td>
            <td>Perbaikan dari Monitoring</td>
            <td class="tengah">90%</td>
            <td class="tengah">{{ $prbClosing['totalBerlalu'] }}</td>
            <td class="tengah">{{ $prbClosing['total'] }}</td>
            <td class="tengah">{{ $prbClosing['selesai'] }}</td>
            <td class="tengah">{{ $prbClosing['tidak_selesai'] }}</td>
            <td class="tengah">{{ $prbClosing['belum_selesai'] }}</td>
            <td class="tengah">{{ is_numeric($prbClosing['persentase'])?$prbClosing['persentase'].'%':'-' }}</td>
            <td class="tengah">{{ $prbClosing['status'] }}</td>
        </tr>
        <tr>
            <td class="tengah">3</td>
            <td>Perbaikan dari Aduan</td>
            <td class="tengah">90%</td>
            <td class="tengah">{{ $prbAduanClosing['totalBerlalu'] }}</td>
            <td class="tengah">{{ $prbAduanClosing['total'] }}</td>
            <td class="tengah">{{ $prbAduanClosing['selesai'] }}</td>
            <td class="tengah">{{ $prbAduanClosing['tidak_selesai'] }}</td>
            <td class="tengah">{{ $prbAduanClosing['belum_selesai'] }}</td>
            <td class="tengah">{{ is_numeric($prbAduanClosing['persentase'])?$prbAduanClosing['persentase'].'%':'-' }}</td>
            <td class="tengah">{{ $prbAduanClosing['status'] }}</td>
        </tr>
    </table>
    <br>
    <table class="blueTable">
        <tr>
            <td colspan="9" style="font-weight: bolder;background-color: #dfdddd; padding-top: 10px; padding-bottom: 10px;" class="tengah text14">Leading Indicator</td>
        </tr>
        <tr style="text-align: center;font-weight: bolder;">
            <td style="width: 5%;">No</td>
            <td style="width: 20%;">Bagian</td>
            <td style="width: 10%;">Target Penyelesaian</td>
            <td style="width: 10%;">Jumlah Aset</td>
            <td style="width: 10%;">Total Target</td>
            <td style="width: 10%;">un Av/Rel (jam)</td>
            <td style="width: 10%;">Av/Rel (jam)</td>
            <td style="width: 10%;">%</td>
            <td style="width: 15%;">Status</td>
        </tr>
        <tr>
            <td colspan="9" style="font-weight: bolder;background-color: #e5d9d9;">Availability Asset</td>
        </tr>
        <?php $i = 1; ?>
        @foreach ($avail as $key => $row)
            <tr>
                <td class="tengah">{{$i}}</td>
                <td>{{ config('custom.bagian.'.(string)$key) }}</td>
                <td class="tengah">90%</td>
                <td class="tengah">{{ $row['total'] }}</td>
                <td class="tengah">{{ $row['target'] }}</td>
                <td class="tengah">{{ $row['un'] }}</td>
                <td class="tengah">{{ $row['av_rel'] }}</td>
                <td class="tengah">{{ $row['persentase'] }}%</td>
                <td class="tengah">{{ $row['status'] }}</td>
            </tr>
            <?php $i++; ?>
        @endforeach

        <tr>
            <td colspan="9" style="font-weight: bolder;background-color: #e5d9d9;">Reliability Asset</td>
        </tr>
        <?php $i = 1; ?>
        @foreach ($rel as $key => $row)
            <tr>
                <td class="tengah">{{$i}}</td>
                <td>{{ config('custom.bagian.'.(string)$key) }}</td>
                <td class="tengah">90%</td>
                <td class="tengah">{{ $row['total'] }}</td>
                <td class="tengah">{{ $row['target'] }}</td>
                <td class="tengah">{{ $row['un'] }}</td>
                <td class="tengah">{{ $row['av_rel'] }}</td>
                <td class="tengah">{{ $row['persentase'] }}%</td>
                <td class="tengah">{{ $row['status'] }}</td>
            </tr>
            <?php $i++; ?>
        @endforeach
    </table>
</div>
<!-- </main> -->

</body>
</html>