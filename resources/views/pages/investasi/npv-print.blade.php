<?php
set_time_limit(1000);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Form perhitungan NPV untuk Perencanaan Investasi</title>

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
        .text14n {font-size: 14px;}
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
                    <span class="text18">Form perhitungan NPV untuk Perencanaan Investasi</span><br>
                </td>
            </tr>
        </table> 
              
    </div>
<!-- </header> -->

<!-- <main> -->
<div class="invoice"  style="margin-bottom:20px;margin-top: 30px;">
    <table>
        <tr>
            <td class="text14">Judul</td>
            <td class="text14">:</td>
            <td class="text14n">{{ $judul }}</td>
        </tr>
        <tr>
            <td class="text14">Tahun</td>
            <td class="text14">:</td>
            <td class="text14n">{{ $tahun }}</td>
        </tr>
        <tr>
            <td class="text14">Umur Ekonomis</td>
            <td class="text14">:</td>
            <td class="text14n">{{ $umur_ekonomis }}</td>
        </tr>
        <tr>
            <td class="text14">Lokasi</td>
            <td class="text14">:</td>
            <td class="text14n">{{ $lokasi }}</td>
        </tr>
        <tr>
            <td class="text14">Nilai investasi / RAB</td>
            <td class="text14">:</td>
            <td class="text14n">{{ rupiah($rab, 2, ".", ",") }}</td>
        </tr>
        <tr>
            <td class="text14">Discount Rate</td>
            <td class="text14">:</td>
            <td class="text14n">{{ $discount_rate }}</td>
        </tr>
        <tr>
            <td class="text14">Cash In Flow</td>
            <td class="text14">:</td>
            <td class="text14n">{{ rupiah($cash_in, 2, ".", ",") }}</td>
        </tr>
        <tr>
            <td class="text14">Cash Out Flow</td>
            <td class="text14">:</td>
            <td class="text14n">{{ rupiah($cash_out, 2, ".", ",") }}</td>
        </tr>
    </table>

    <br>

    <table class="blueTable">
        <tr style="text-align: center;font-weight: bolder;background-color: #dfdddd">
            <td>Tahun</td>
            <td>Cash In Flows</td>
            <td>Cash Out Flows</td>
            <td>Net</td>
        </tr>
        <?php
            $tahunSerial = $tahun; 
        ?>
        @for( $i = 1; $i <= $umur_ekonomis; $i++ )
            <?php 
                $tahunSerial += 1;
            ?>
            <tr>
                <td class="tengah">{{ $tahunSerial }}</td>
                <td class="tengah">{{ rupiah($cash_in, 2, ".", ",") }}</td>
                <td class="tengah">{{ rupiah($cash_out, 2, ".", ",") }}</td>
                <td class="tengah">{{ rupiah($cash_in - $cash_out, 2, ".", ",") }}</td>
            </tr>
        @endfor
        <tr style="text-align: center;font-weight: bolder;background-color: #dfdddd">
            <td colspan="4">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2">NPV</td>
            <td class="tengah">{{ rupiah($npv, 2, ".", ",") }}</td>
            <td class="tengah">{{ $npv_presentase }}%</td>
        </tr>

    </table>
</div>
<!-- </main> -->

</body>
</html>