<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Form Prioritas Perbaikan dan Penggantian Aset Produksi dan Distribusi</title>

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
                    <span class="text13">PRIORITAS PERBAIKAN DAN PENGGANTIAN ASET PRODUKSI DAN DISTRIBUSI</span><br>
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
                <th" class="tengahb">No. Usulan Investasi</th>
                <th" class="tengahb">Nama Komponen</th>
                <th" class="tengahb">Replace / Refurbish</th>
                <th" class="tengahb">Strategi yang dipilih</th>
                <th" class="tengahb">Nilai Investasi/RAB</th>
                <th" class="tengahb">Kelayakan Operasional</th>
                <th" class="tengahb">Bobot</th>
                <th" class="tengahb">Kelayakan Keuangan</th>
                <th" class="tengahb">Bobot</th>
                <th" class="tengahb">Waktu Kebutuhan</th>
                <th" class="tengahb">Bobot</th>
                <th" class="tengahb">Kombinasi Bobot</th>
        </thead>
        <tbody>
            <?php 
            $i = 1;
            ?>
            @foreach ($result as $row)
                <tr>
                    <td class="kiri">{{ $i }}</td>
                    <td class="kiri">{{ $row['komponen'] }}</td>
                    <td class="tengah">{{ $row['rr'] }}</td>
                    <td class="tengah">{{ $row['intervensi'] }}</td>
                    <td class="tengah">{{ $row['angka_rab'] }}</td>
                    <td class="tengah">2010</td>
                    <td class="tengah">Pemasangan Awal</td>
                    <td class="tengah">&nbsp;</td>
                    <td class="tengah">&nbsp;</td>
                    <td class="tengah">&nbsp;</td>
                    <td class="tengah">&nbsp;</td>
                    <td class="tengah">&nbsp;</td>
                </tr>
                <?php $i++; ?>
            @endforeach
        </tbody>
    </table>
</div>
<!-- </main> -->

</body>
</html>