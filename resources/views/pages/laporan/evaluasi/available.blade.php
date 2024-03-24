<?php
set_time_limit(1000);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Perhitungan Availability Aset</title>

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
        .text13n {font-size: 13px;}

        table.blueTable {
          font-family: Arial, Helvetica, sans-serif;
          border: 1px solid #1C6EA4;
          background-color: #FFFFFF;
          width: 100%;
          text-align: left;
          border-collapse: collapse;
        }
        table.blueTable td, table.blueTable th {
          border: 1px solid #AAAAAA;
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
                    <span class="text13">PERHITUNGAN AVAILABILITY ASET</span><br>
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
    <h5>Lokasi : {{ $lokasi->name }} </h5>
    <table class="blueTable">
        <!-- <thead> -->
            <tr>
                <th class="tengah">No</th>
                <th class="tengah">Sistem</th>
                <th class="tengah">Equipment</th>
                <th class="tengah">Komponen</th>
                <th class="tengah">Bagian</th>
                <th class="tengah">Available (Jam)</th>
                <th class="tengah">Unavailable (Jam)</th>
                <th class="tengah">Availability</th>
                <th class="tengah">Reliable</th>
                <th class="tengah">Unreliable (Jam)</th>
                <th class="tengah">Reliability</th>
            </tr>
        <!-- </thead> -->
        <!-- <tbody> -->
        	@if (count($data) > 0)
                @foreach($data as $row)
                    <tr>
                        <td align="center">{{ $row['no'] }}</td>
                        <td>{{ $row['sistem'] }}</td>
                        <td>{{ $row['equipment'] }}</td>
                        <td>{{ $row['komponen'] }}</td>
                        <td>{{ $row['bagian'] }}</td>
                        <td align="right">{{ $row['available'] }}</td>
                        <td align="right">{{ $row['unavailable'] }}</td>
                        <td align="right">{{ $row['availability'] }}%</td>
                        <td align="right">{{ $row['reliable'] }}</td>
                        <td align="right">{{ $row['unreliable'] }}</td>
                        <td align="right">{{ $row['reliability'] }}%</td>
                    </tr>
                @endforeach
            @else
            	<tr>
	                <td colspan="11">Data Kosong</td>
	            </tr>
            @endif
        <!-- </tbody> -->
        <tr>
            <td colspan="5">&nbsp;</td>
            <td colspan="3" class="tengah text13">
                Rata-rata Availability: {{$avg}}%
            </td>
            <td colspan="3" class="tengah text13">
                Rata-rata Reliability: {{$avgRel}}%
            </td>
        </tr>
    </table>

    <?php /*
    <table style="width:100%;margin-bottom: 10px;">
        <tr>
            <td colspan="4">&nbsp;</td>
            <td colspan="3" class="tengah">
                <span class="text13">Rata-rata: {{$avg}}%</span><br>
            </td>
            <td colspan="3" class="tengah">
                <span class="text13">Rata-rata: {{$avg}}%</span><br>
            </td>
        </tr>
    </table>
    */?>

    <table style="width:100%;margin-top: 20px">
        <tr>
            <td width="65%">&nbsp;</td>
            <td width="35%" class="tengah">
                <span class="text13n">Supervisor Pengendalian Aset</span><br>
                <span class="text13n">Operasi Produksi dan Distribusi</span><br>
            </td>
        </tr>
        <tr>
            <td width="100%" style="padding:20px">&nbsp;</td>
        </tr>
        <tr>
            <td width="65%">&nbsp;</td>
            <td width="35%" class="tengah">
                <span class="text13"><u>Azwar Anas Reza</u></span><br>
                <span class="text13n">NIp 1.06.01344</span><br>
            </td>
        </tr>
    </table>
</div>
<!-- </main> -->

</body>
</html>