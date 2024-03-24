<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kesesuaian Pelaksanaan Perawatan dan Perbaikan</title>

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

        .customers {
          font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
          border-collapse: collapse;
          width: 100%;
          margin-bottom: 15px;
        }

        .customers td, .customers th {
          border: 1px solid #000;
          padding: 8px;
        }

        .customers tr:nth-child(even){background-color: #f2f2f2;}

        .customers tr:hover {background-color: #ddd;}

        .customers th {
          padding-top: 12px;
          padding-bottom: 12px;
          text-align: left;
          color: black;
    </style>

</head>
<body>

<!-- <header> -->
    <div class="information" style="margin-top: 10px;">
        <table style="width:100%;border-bottom:none;">
            <tr>
                <td class="tengah">
                    <span class="text13">KESESUAIAN PELAKSANAAN PERAWATAN DAN PERBAIKAN</span><br>
                    <span class="text13n">Periode : {{$periode or ''}}</span>
                </td>
            </tr>
        </table> 

        <table style="width:100%;">
            <tr>
                <td width="75%">&nbsp;</td>
                <td width="25%" class="kiri">
                    <span class="text13n">No. Dokumen:</span><br>
                    <span class="text13n">Tanggal:</span><br>
                    <span class="text13n">Rev:</span>
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
    <h5>Lokasi : {{ $lokasi->name }}</h5>
    <table class="customers">
        <thead>
            <tr>
                <th class="tengahb">No.</th>
                <th class="tengahb">Peralatan</th>
                <th class="tengahb"># WO Perawatan Rutin</th>
                <th class="tengahb"># WO Menunggu Material</th>
                <th class="tengahb">Rasio WO</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>            
            @foreach($bagian as $key => $bag)
                @if (!in_array($key, ['3', '4']))
                    <?php 
                    $bag = strtolower($bag);
                    $sc = 'sc_'.$bag;
                    ?>
                    <tr>
                        <td class="tengah">{{$i}}</td>
                        <td class="kiri">{{ ucwords(strtolower($bag)) }}</td>
                        <td class="kanan">{{ !empty($arrPrwRutin->$bag)?$arrPrwRutin->$bag:0 }}</td>
                        <td class="kanan">{{ !empty($arrPrwRutin->$sc)?$arrPrwRutin->$sc:0 }}</td>
                        @if (!is_null($arrPrwRutin->$sc) && !is_null($arrPrwRutin->$bag))
                            @if ($arrPrwRutin->$bag == 0)
                                <td class="kanan">0%</td>
                            @else
                                <td class="kanan">{{ round( pembagian( $arrPrwRutin->$sc, $arrPrwRutin->$bag ), 3 ) * 100 }}%</td>
                            @endif
                        @else
                            <td class="kanan">-</td>
                        @endif
                    </tr>
                    <?php $i++; ?>
                @endif
            @endforeach
        </tbody>
    </table>

    <table class="customers">
        <thead>
            <tr>
                <th class="tengahb">No.</th>
                <th class="tengahb">Peralatan</th>
                <th class="tengahb"># WO Perawatan Non Rutin</th>
                <th class="tengahb"># WO Menunggu Material</th>
                <th class="tengahb">Rasio WO</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            @foreach($bagian as $key => $bag)
                @if (!in_array($key, ['3']))
                    <?php 
                    $bag = strtolower($bag);
                    $sc = 'sc_'.$bag;
                    ?>
                    <tr>
                        <td class="tengah">{{$i}}</td>
                        <td class="kiri">{{ ucwords(strtolower($bag)) }}</td>
                        <td class="kanan">{{ !empty($arrPrw->$bag)?$arrPrw->$bag:0 }}</td>
                        <td class="kanan">{{ !empty($arrPrw->$sc)?$arrPrw->$sc:0 }}</td>
                        @if (!is_null($arrPrw->$sc) && !is_null($arrPrw->$bag))
                            @if ($arrPrw->$bag == 0)
                                <td class="kanan">0%</td>
                            @else
                                <td class="kanan">{{ round( pembagian( $arrPrw->$sc, $arrPrw->$bag ), 3 ) * 100 }}%</td>
                            @endif
                        @else
                            <td class="kanan">-</td>
                        @endif
                    </tr>
                    <?php $i++; ?>
                @endif
            @endforeach
        </tbody>
    </table>

    <table class="customers">
        <thead>
            <tr>
                <th class="tengahb">No.</th>
                <th class="tengahb">Peralatan</th>
                <th class="tengahb"># WO Perbaikan</th>
                <th class="tengahb"># WO Menunggu Material</th>
                <th class="tengahb">Rasio WO</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            @foreach($bagian as $key => $bag)
                @if (!in_array($key, []))
                    <?php 
                    $bag = strtolower($bag);
                    $sc = 'sc_'.$bag;
                    ?>
                    <tr>
                        <td class="tengah">{{$i}}</td>
                        <td class="kiri">{{ ucwords(strtolower($bag)) }}</td>
                        <td class="kanan">{{ !empty($arrPrb->$bag)?$arrPrb->$bag:0 }}</td>
                        <td class="kanan">{{ !empty($arrPrb->$sc)?$arrPrb->$sc:0 }}</td>
                        @if (!is_null($arrPrb->$sc) && !is_null($arrPrb->$bag))
                            @if ($arrPrb->$bag == 0)
                                <td class="kanan">-</td>
                            @else
                                <td class="kanan">{{ round( pembagian( $arrPrb->$sc, $arrPrb->$bag ), 3 ) * 100 }}%</td>
                            @endif
                        @else
                            <td class="kanan">-</td>
                        @endif
                    </tr>
                    <?php $i++; ?>
                @endif
            @endforeach
        </tbody>
</div>
<!-- </main> -->

</body>
</html>