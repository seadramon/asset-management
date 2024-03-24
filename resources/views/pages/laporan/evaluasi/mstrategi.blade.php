<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Efektivitas Maintenance Strategy</title>

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
        .kirib{text-align: left; font-weight: bold;}
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
                    <span class="text13">PERHITUNGAN EFEKTIVITAS MAINTENANCE STRATEGY</span><br>
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
        @foreach($data as $row)
            <table class="blueTable">
                <tr>
                    <th class="tengahb">No.</th>
                    <th class="tengahb">Peralatan</th>
                    <th class="tengahb"># WO Perbaikan</th>
                    <th class="tengahb"># WO Perawatan Rutin</th>
                    <th class="tengahb"># WO Perawatan Non Rutin</th>
                    <th class="tengahb"># WO Monitoring</th>
                    <th class="tengahb"># Total WO</th>
                    <th class="tengahb">Rasio WO</th>
                </tr>

                <tr>
                    <td class="tengah" rowspan="2" valign="top">{{ $row['no'] }}</td>
                    <td class="kirib">{{ $row['bagian'] }}</td>
                    <td class="kanan">{{ $row['perbaikan'] }}</td>
                    <td class="kanan">{{ $row['prwrutin'] }}</td>
                    <td class="kanan">{{ $row['perawatan'] }}</td>
                    <td class="kanan">{{ $row['monitoring'] }}</td>
                    <td class="kanan">{{ $row['total'] }}</td>
                    <?php /* <td class="kanan">{{ $row['rasio'] }}</td> */ ?>
                    <td class="kanan"></td>
                </tr>
                <tr>
                    <td class="tengah">Prosentase (%)</td>
                    <td class="kanan">{{ $row['perbaikanPr'] }}%</td>
                    <td class="kanan">{{ $row['prwrutinPr'] }}%</td>
                    <td class="kanan">{{ $row['perawatanPr'] }}%</td>
                    <td class="kanan">{{ $row['monitoringPr'] }}%</td>
                    <td class="kanan">{{ $row['totalPr'] }}%</td>
                    <td class="kanan">{{ $row['rasioPr'] }}%</td>
                </tr>

                <!-- Detail -->
                @if ( in_array($row['bagian_id'], ['1', '2', '4']) )
                    <tr>
                        <td class="tengah" rowspan="2" valign="top">a.</td>
                        <td class="kirib">{{ $row['bagian'].' Utama' }}</td>
                        <td class="kanan">{{ $row['perbaikanUtama'] }}</td>
                        <td class="kanan">{{ $row['prwrutinUtama'] }}</td>
                        <td class="kanan">{{ $row['perawatanUtama'] }}</td>
                        <td class="kanan">{{ $row['monitoringUtama'] }}</td>
                        <td class="kanan">{{ $row['totalUtama'] }}</td>
                        <?php /*<td class="kanan">{{ $row['rasioUtama'] }}</td>*/?>
                        <td class="kanan"></td>
                    </tr>
                    <tr>
                        <td class="tengah">Prosentase (%)</td>
                        <td class="kanan">{{ $row['perbaikanPrUtama'] }}%</td>
                        <td class="kanan">{{ $row['prwrutinPrUtama'] }}%</td>
                        <td class="kanan">{{ $row['perawatanPrUtama'] }}%</td>
                        <td class="kanan">{{ $row['monitoringPrUtama'] }}%</td>
                        <td class="kanan">{{ $row['totalPrUtama'] }}%</td>
                        <td class="kanan">{{ $row['rasioPrUtama'] }}%</td>
                    </tr>

                    <tr>
                        <td class="tengah" rowspan="2" valign="top">b.</td>
                        <td class="kirib">{{ $row['bagian'].' Pendukung' }}</td>
                        <td class="kanan">{{ $row['perbaikanPendukung'] }}</td>
                        <td class="kanan">{{ $row['prwrutinPendukung'] }}</td>
                        <td class="kanan">{{ $row['perawatanPendukung'] }}</td>
                        <td class="kanan">{{ $row['monitoringPendukung'] }}</td>
                        <td class="kanan">{{ $row['totalPendukung'] }}</td>
                        <?php /* <td class="kanan">{{ $row['rasioPendukung'] }}</td> */ ?>
                        <td class="kanan">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="tengah">Prosentase (%)</td>
                        <td class="kanan">{{ $row['perbaikanPrPendukung'] }}%</td>
                        <td class="kanan">{{ $row['prwrutinPrPendukung'] }}%</td>
                        <td class="kanan">{{ $row['perawatanPrPendukung'] }}%</td>
                        <td class="kanan">{{ $row['monitoringPrPendukung'] }}%</td>
                        <td class="kanan">{{ $row['totalPrPendukung'] }}%</td>
                        <td class="kanan">{{ $row['rasioPrPendukung'] }}%</td>
                    </tr>
                @endif
                <!-- end:Detail -->
            </table>
            <br><br>
        @endforeach
</div>
<!-- </main> -->

</body>
</html>