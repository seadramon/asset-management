<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Efektifitas Realisasi Pemeliharaan</title>

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
    </style>

</head>
<body>

<header>
    <div class="information" style="margin-top: 10px;">
        <table style="width:100%;border-bottom:none;">
            <tr>
                <td class="tengah">
                    <span class="text13">PERHITUNGAN EFEKTIVITAS REALISASI PEMELIHARAAN</span><br>
                    <span class="text13n">Periode : {{$periode or ''}}</span>
                </td>
            </tr>
        </table>    
    </div>
</header>

<main>
<div class="invoice"  style="margin-bottom:20px;">
    <h5>Lokasi : {{ $lokasi->name }}</h5>

    <?php $i = 1; ?>
    @foreach($bagian as $bag)
        <table id="customers">
            <tr>
                @if ($i == 1)
                    <td class="tengahb" rowspan="2">No.</td>
                @else
                    <td class="tengahb" rowspan="2"></td>
                @endif
                <td class="tengahb" rowspan="2">Peralatan</td>
                <td class="tengahb" rowspan="2">Minggu ke-</td>

                <td class="tengahb" colspan="5">Perawatan Non Rutin</td>
                <td class="tengahb" colspan="5">Perbaikan</td>
            </tr>
            <tr>
                <td class="tengahb"># WO Perawatan Non Rutin </td>
                <td class="tengahb"># Respon WO Perawatan Non Rutin</td>
                <td class="tengahb"># WO Perawatan Non Rutin Selesai</td>
                <td class="tengahb">% Respon</td>
                <td class="tengahb">% Penyelesaian</td>

                <td class="tengahb"># WO Perbaikan </td>
                <td class="tengahb"># Respon WO Perbaikan</td>
                <td class="tengahb"># WO Perbaikan Selesai</td>
                <td class="tengahb">% Respon</td>
                <td class="tengahb">% Penyelesaian</td>
            </tr>
            <?php $j = 0;  ?>
            @foreach($weeks as $week)
                <tr>
                    @if ($j == 0)
                        <td class="tengah" valign="top" rowspan="5">{{$i}}</td>
                        <td><b>{{ ucwords(strtolower($bag->name)) }}</b></td>
                    @else
                        <td>&nbsp;</td>
                    @endif
                    <td class="tengah">{{$week}}</td>
                    <!-- Pemeliharaan -->
                    <td class="kanan">{{ $arrPrw[$bag->name][$week]['perawatan'] }}</td>
                    <td class="kanan">{{ $arrPrw[$bag->name][$week]['respon_perawatan'] }}</td>
                    <td class="kanan">{{ $arrPrw[$bag->name][$week]['perawatan_selesai'] }}</td>
                    <td class="kanan">{{ $arrPrw[$bag->name][$week]['respon'] }}%</td>
                    <td class="kanan">{{ $arrPrw[$bag->name][$week]['penyelesaian'] }}%</td>

                    <!-- Perbaikan -->
                    <td class="kanan">{{ $arrPrb[$bag->name][$week]['perbaikan'] }}</td>
                    <td class="kanan">{{ $arrPrb[$bag->name][$week]['respon_perbaikan'] }}</td>
                    <td class="kanan">{{ $arrPrb[$bag->name][$week]['perbaikan_selesai'] }}</td>
                    <td class="kanan">{{ $arrPrb[$bag->name][$week]['respon'] }}%</td>
                    <td class="kanan">{{ $arrPrb[$bag->name][$week]['penyelesaian'] }}%</td>
                </tr>
                <?php $j++; ?>
            @endforeach
            <?php $i++; ?>
        </table><br><br><br>
    @endforeach
</div>
</main>

</body>
</html>