<?php
ini_set('max_execution_time', 3000); 
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembobotan</title>

    <style type="text/css">
        @page {
            margin: 0px;
        }
        header { position: fixed; }
        main{ margin-top: 15px; margin-bottom: 20px;  }
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
            margin: 15px;
        }
        .invoice h3 {
            margin-left: 15px;
        }
        .information {
            background-color: #60A7A6;
            color: #FFF;
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

<div class="information">
    <table style="width:100%;border-bottom:none;">
        <tr>
            <!-- <td class="kiri" width="5%">
                <img src="{{asset('global_assets/images/logo.png')}}" height="50" style="text-align:left;" />
            </td> -->
            <td class="tengah">
                <span class="text13">PEMERINTAH KOTA SURABAYA</span><br>
                <span class="text14">MANAJEMEN ASET PDAM SURYA SEMBADA <br></span>
                <span class="text13n">LAPORAN PEMBOBOTAN {{$tanggal}}</span>
            </td>
        </tr>
    </table>
</div>

<main>
    <div class="invoice">
        <!-- <h3>Invoice specification #123</h3> -->
        <table class="blueTable">
            <thead>
                <tr>
                <th>No</th>
                <th>Aset</th>
                <th>Kode Aset</th>
                <th>Umur Ekonomis</th>
                <th>Umur Berjalan</th>
                <th>Jumlah Perawatan</th>
                <th>Jumlah Perbaikan (Ringan)</th>
                <th>Jumlah Perbaikan (Berat)</th>
                <th>Bobot Akhir</th>
                <th>Hasil Akhir</th>
                </tr>
            </thead>
            <tbody>
            	@if (count($data) > 0)
	                @foreach($data as $row)
		                <tr>
                        <td>{{$row['no']}}</td>
                        <td>{{$row['nama_aset']}}</td>
		                <td>{{$row['kode_aset']}}</td>
                        <td align="center">{{$row['umurEkonomis']}}</td>
		                <td align="center">{{$row['umurBerjalan']}}</td>
		                <td align="center">{{$row['jmlPerawatan']}}</td>
		                <td align="center">{{$row['jmlPerbaikanRingan']}}</td>
                        <td align="center">{{$row['jmlPerbaikanBerat']}}</td>
		                <td align="center">{{$row['rata']}}</td>
                        <td align="center">{{$row['hasil']}}</td>
		                </tr>
	                @endforeach
	            @else
	            	<tr>
		                <td colspan="8">Data Kosong</td>
		            </tr>
	            @endif
            </tbody>
        </table>
    </div>
</main> 

</body>
</html>