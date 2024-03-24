<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rekomendasi Perbaikan dan Penggantian Aset Produksi dan Distribusi</title>

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
        .tengahb {text-align: center; font-weight: bold;}
        .kiri{text-align: left;}
        .kanan{text-align: right;}
        .batasBawah{border-bottom:1pt solid black;}
        .text14 {font-weight: bold; font-size: 14px;}
        .text13 {font-weight: bold; font-size: 13px;}
        .text13n {font-size: 13px;}

        #customers {
          font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
          border-collapse: collapse;
          width: 100%;
        }

        #customers td, #customers th {
          border: 1px solid #000;
          padding: 8px;
        }

        #customers tr:nth-child(even){background-color: #f2f2f2;}

        #customers tr:hover {background-color: #ddd;}

        #customers th {
          padding-top: 12px;
          padding-bottom: 12px;
          text-align: left;
          color: black;
        }
    </style>

</head>
<body>

<!-- <header> -->
    <div class="information" style="margin-top: 10px;">
        <table style="width:100%;border-bottom:none;">
            <tr>
                <td class="tengah">
                    <span class="text13">REKOMENDASI PERBAIKAN DAN PENGGANTIAN ASET PRODUKSI DAN DISTRIBUSI</span><br>
                    <span class="text13n">Periode : {{$periode or ''}}</span>
                </td>
            </tr>
        </table>    
    </div>
<!-- </header> -->

<!-- <main> -->
<div class="invoice"  style="margin-bottom:20px;">
    <h5>Lokasi : {{ $lokasi->name }}</h5>

    <?php 
    $i = 1; 
    $tahun = date('Y');
    ?>
    <table id="customers">
        <thead>
            <tr>
                <th style="background-color: #AAD4FF;" class="tengahb" rowspan="2">Equipment</th>
                <th style="background-color: #AAD4FF;" class="tengahb" rowspan="2">Komponen</th>
                <!-- <th style="background-color: #FF7FFF;" class="tengahb" rowspan="2">Frekuensi Siklus Replace/Refurbish(Thn)</th> -->
                <th style="background-color: #FFFF00;" class="tengahb" rowspan="2">Tanggal Pemasangan (Bln/Thn)</th>
                <th style="background-color: #FFFF00;" class="tengahb" rowspan="2">Intervensi</th>
                <th style="background-color: #AAFF7F;" class="tengahb" rowspan="2">Tanggal Intervensi Mayor Terakhir (Bln/Thn)</th>
                <th style="background-color: #AAFF7F;" class="tengahb" rowspan="2">Tipe kegiatan pada saat Intervensi Mayor Terakhir</th>
                <th style="background-color: #FF7F7F;" class="tengahb" rowspan="2">Replace / Refurbish</th>
                <?php 
                for ($i = 0; $i <= 1; $i++) {
                $tahun = $tahun + $i;
                ?>
                    <th style="background-color: #AA00FF;" class="tengahb">{{ $tahun }}</th>
                <?php 
                }
                ?>
            </tr>
            <tr>
                <?php 
                for ($i = 0; $i <= 1; $i++) {
                $tahun = $tahun + $i;
                ?>
                    <th style="background-color: #AA00FF;" class="tengahb">{{ $i+1 }}</th>
                <?php 
                }
                ?>
            </tr>
        </thead>
        <tbody>
            @foreach ($result as $row)
                <tr>
                    <td class="kiri">{{ $row['equipment'] }}</td>
                    <td class="kiri">{{ $row['komponen'] }}</td>
                    <td class="tengah">{{ $row['tgl_pasang'] }}</td>
                    <td class="tengah">{{ $row['intervensi'] }}</td>
                    <td class="tengah">{{ $row['tgl_intervensim'] }}</td>
                    <td class="tengah">2010</td>
                    <td class="tengah">{{ $row['rr'] }}</td>
                    <td class="tengah">50</td>
                    <td class="tengah">0</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- </main> -->

</body>
</html>