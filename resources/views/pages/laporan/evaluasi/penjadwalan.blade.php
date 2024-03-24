<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Efektifitas Penjadwalan</title>

    <style type="text/css">
        @page {
            margin: 0px;
        }
        header { position: fixed;margin-top: 5px; }
        footer { position: fixed; }
        body {
            margin: 0px;
        }
        main {
            margin-top: 55px;
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
            padding: 0;
        }
        .information {
            background-color: #fff;
            color: #000;
        }

        .information .logo {
            margin: 3px;
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
          margin-bottom: 5px;
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

<header>
    <div class="information" style="margin-top: 2px;">
        <table style="width:100%;border-bottom:none;">
            <tr>
                <td class="tengah">
                    <span class="text13">PERHITUNGAN EFEKTIVITAS PENJADWALAN</span><br>
                    <span class="text13n">Periode : {{$periode or ''}}</span>
                </td>
            </tr>
        </table>     
    </div>
</header>

<main>
<div class="invoice">
    <h5>Lokasi : {{ $lokasi->name }}</h5>

    <?php $no = 1; ?>
    @foreach($bagian as $bag)
        <table class="blueTable">
            <tr>
                <td class="tengahb">No.</td>
                <td class="tengahb">Peralatan</td>
                <td class="tengahb">Minggu ke-</td>
                <td class="tengahb">#Jadwal Monitoring</td>
                <td class="tengahb">#WO Monitoring Aktual</td>
                <td class="tengahb">%</td>
                <td class="tengahb">#Jadwal Perawatan Rutin</td>
                <td class="tengahb">#WO Perawatan Rutin Aktual</td>
                <td class="tengahb">%</td>
            </tr>

            <?php 
            $i = 1; 
            $rowpan = count($weeks) + 2;
            $arrTemp = [];
            $total = 0;
            ?>
            @foreach($weeks as $week)
                <?php
                $strPercent = ''; 
                if ($arrData[$bag->name][$week]['monitoring']['jadwal_monitoring'] > 0) {
                    $arrTemp[] = $arrData[$bag->name][$week]['monitoring']['presentase']*100;
                    $strPercent = '%';
                }
                ?>
                @if ($i==1)
                    <tr>
                        <td class="tengah" valign="top" rowspan="{{$rowpan}}">{{$no}}</td>
                        <td>{{ ucwords(strtolower($bag->name)) }}</td>
                        <td class="tengah">{{ $week }}</td>

                        <td class="kanan">{{ $arrData[$bag->name][$week]['monitoring']['jadwal_monitoring'] }}</td>
                        <td class="kanan">{{ $arrData[$bag->name][$week]['monitoring']['jadwal_monitoring_akt'] }}</td>
                        @if ($arrData[$bag->name][$week]['monitoring']['jadwal_monitoring'] > 0)
                            <td class="kanan">{{ $arrData[$bag->name][$week]['monitoring']['presentase']*100 }}%</td>
                        @else
                            <td class="kanan">-</td>
                        @endif
                        
                        <td class="kanan">{{ $arrData[$bag->name][$week]['prw']['jadwal_prw'] }}</td>
                        <td class="kanan">{{ $arrData[$bag->name][$week]['prw']['jadwal_prw_akt'] }}</td>
                        @if ($arrData[$bag->name][$week]['prw']['jadwal_prw'] > 0)
                            <td class="kanan">{{ $arrData[$bag->name][$week]['prw']['presentase']*100 }}%</td>
                        @else
                            <td class="kanan">-</td>
                        @endif
                    </tr>
                @else
                    <tr>
                        <td>&nbsp;</td>
                        <td class="tengah">{{ $week }}</td>
                        <td class="kanan">{{ $arrData[$bag->name][$week]['monitoring']['jadwal_monitoring'] }}</td>
                        <td class="kanan">{{ $arrData[$bag->name][$week]['monitoring']['jadwal_monitoring_akt'] }}</td>
                        @if ($arrData[$bag->name][$week]['monitoring']['jadwal_monitoring'] > 0)
                            <td class="kanan">{{ $arrData[$bag->name][$week]['monitoring']['presentase']*100 }}%</td>
                        @else
                            <td class="kanan">-</td>
                        @endif
                        <td class="kanan">{{ $arrData[$bag->name][$week]['prw']['jadwal_prw'] }}</td>
                        <td class="kanan">{{ $arrData[$bag->name][$week]['prw']['jadwal_prw_akt'] }}</td>
                        @if ($arrData[$bag->name][$week]['prw']['jadwal_prw'] > 0)
                            <td class="kanan">{{ $arrData[$bag->name][$week]['prw']['presentase']*100 }}%</td>
                        @else
                            <td class="kanan">-</td>
                        @endif
                    </tr>
                @endif
                <?php $i++; ?>
            @endforeach
            <?php 
            // $arrTemp = array_filter($arrTemp);
            if (count($arrTemp)) {
                $total = '0%';
                if (array_sum($arrTemp) > 0) {
                    $total = round(array_sum($arrTemp)/count($arrTemp), 2).'%';
                }
            } else {
                $total = "-";
            }
            ?>
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
                <td class="tengah">Total</td>
                <td class="kanan">{{ $arrData[$bag->name]['total']['monitoring']['total_jadwal'] }}</td>
                <td class="kanan">{{ $arrData[$bag->name]['total']['monitoring']['total_aktual'] }}</td>
                @if ($arrData[$bag->name]['total']['monitoring']['presentase'] > 0)
                    <td class="kanan">{{ $arrData[$bag->name]['total']['monitoring']['presentase'] }}%</td>
                @else
                    <td class="kanan">-</td>
                @endif
                <td class="kanan">{{ $arrData[$bag->name]['total']['prw']['total_jadwal'] }}</td>
                <td class="kanan">{{ $arrData[$bag->name]['total']['prw']['total_aktual'] }}</td>
                @if ($arrData[$bag->name]['total']['prw']['presentase'] > 0)
                    <td class="kanan">{{ $arrData[$bag->name]['total']['prw']['presentase'] }}%</td>
                @else
                    <td class="kanan">-</td>
                @endif
            </tr>
        </table>
        <?php 
        $no++;
        ?>
    @endforeach
</div>
</main>

</body>
</html>