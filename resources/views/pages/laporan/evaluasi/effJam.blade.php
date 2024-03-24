<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Efektifitas Jam Orang</title>

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
    </style>

</head>
<body>

<header>
    <div class="information" style="margin-top: 10px;">
        <table style="width:100%;border-bottom:none;">
            <tr>
                <td class="tengah">
                    <span class="text13">PERHITUNGAN EFEKTIVITAS JAM ORANG</span><br>
                    <span class="text13n">Periode : {{$periode or ''}}</span>
                </td>
            </tr>
        </table>     
    </div>
</header>

<main>
<div class="invoice"  style="margin-bottom:15px;">
    <h5>Lokasi : {{ $lokasi->name }}</h5>

    <?php $no = 1; ?>
    @foreach($bagian as $bag)
        <table id="customers">
            <tr>
                <td class="tengahb">No.</td>
                <td class="tengahb">Peralatan</td>
                <td class="tengahb">Minggu ke-</td>
                <td class="tengahb">#Jam Orang WO Monitoring Aktual </td>
                <td class="tengahb"># Jam Orang WO Perawatan Aktual</td>
                <td class="tengahb"># Jam Orang WO Perbaikan Aktual</td>
                <td class="tengahb">Total #Jam Orang Aktual</td>
                <td class="tengahb">#Jam Orang Tersedia</td>
                <td class="tengahb">%</td>
            </tr>

            <?php 
            $i = 1; 
            $rowpan = count($weeks) + 2;
            $arrTemp = [];
            $total = 0;
            ?>
            @foreach($weeks as $week)
                @if ($i==1)
                    <tr>
                        <td class="tengah" valign="top" rowspan="{{$rowpan}}">{{$no}}</td>
                        <td>{{ ucwords(strtolower($bag->name)) }}</td>
                        <td class="tengah">{{ $week }}</td>
                        <td class="kanan">{{ $arrData[$bag->name][$week]['jadwal_monitoring_akt'] }}</td>
                        <td class="kanan">0</td>
                        <td class="kanan">0</td>
                        <td class="kanan">{{ $arrData[$bag->name][$week]['total'] }}</td>
                        <td class="kanan">0</td>
                        <td class="kanan">{{ $arrData[$bag->name][$week]['presentase'] }}%</td>
                    </tr>
                @else
                    <tr>
                        <td>&nbsp;</td>
                        <td class="tengah">{{ $week }}</td>
                        <td class="kanan">{{ $arrData[$bag->name][$week]['jadwal_monitoring_akt'] }}</td>
                        <td class="kanan">0</td>
                        <td class="kanan">0</td>
                        <td class="kanan">{{ $arrData[$bag->name][$week]['total'] }}</td>
                        <td class="kanan">0</td>
                        <td class="kanan">{{ $arrData[$bag->name][$week]['presentase'] }}%</td>
                    </tr>
                @endif
                <?php $i++; ?>
            @endforeach
            <tr>
                <td>&nbsp;</td>
                <td class="tengah">&nbsp;</td>
                <td class="kanan">&nbsp;</td>
                <td class="kanan">&nbsp;</td>
                <td class="kanan">&nbsp;</td>
                <td class="kanan">&nbsp;</td>
                <td class="kanan">&nbsp;</td>
                <td class="kanan">&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td class="tengah"></td>
                <td class="kanan"></td>
                <td class="kanan"></td>
                <td class="kanan"></td>
                <td class="kanan"></td>
                <td class="kanan"></td>
                <td class="kanan">0%</td>
            </tr>
        </table>
        <br>    
        <?php 
        $no++;
        ?>
    @endforeach
</div>
</main>

</body>
</html>