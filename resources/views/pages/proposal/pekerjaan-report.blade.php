<?php
set_time_limit(1000);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Proposal Pekerjaan</title>

    <style type="text/css">
        @page {
            margin: 0px;
        }
        header { position: fixed;margin-top: 60px; }
        footer { position: fixed; }
        body {
            margin: 25px;
            border: 2px double #000;
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
        .text11 {font-weight: bold; font-size: 11px;}
        .text13n {font-size: 13px;}

        table.blueTable {
          font-family: Arial, Helvetica, sans-serif;
          border: 1.5px solid #000;
          background-color: #FFFFFF;
          width: 100%;
          text-align: left;
          border-collapse: collapse;
          margin: 0;
          border-left: none;
          border-right: none;
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

        table.commontbl {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #FFFFFF;
            width: 100%;
            text-align: left;
            border-collapse: collapse;
        }

        table.commontbl td {
            font-size: 11px;
            padding: 3px;
        }

        tr.border-bottom td {
            border-bottom: 1pt dotted #000;
        }
    </style>

</head>
<body>

<div id="content">
<!-- <header> -->
    <div class="information" style="margin-top: 10px;">
        <div class="kanan text13n" style="margin-right: 10px;">ID Proposal : {{ $data->id }}</div>
        <table style="width:100%;border-bottom:none;">
            <tr>
                <td class="tengah">
                    <span class="text13">PROPOSAL PEKERJAAN</span><br>
                    <span class="text13">PDAM SURYA SEMBADA KOTA SURABAYA</span>
                </td>
            </tr>
        </table> 
              
    </div>
<!-- </header> -->

<!-- <main> -->
<div class="invoice">
    <br>
    <table class="blueTable">
        <tr>
            <td class="tengah" style="font-weight: bold;">URAIAN</td>
        </tr>
    </table><br>

    <table class="commontbl" style="width:100%" style="margin-top: 20px;">
        <tr class="border-bottom">
            <td class="text11" width="30%">Nama Pekerjaan</td>
            <td class="text11" width="3%">:</td>
            <td width="67%">{{ $data->nama }}</td>
        </tr>
        <tr class="border-bottom">
            <td class="text11">Lokasi</td>
            <td class="text11">:</td>
            <td>{{ $data->lokasi }}</td>
        </tr>
        <tr class="border-bottom">
            <td class="text11">Gambaran Umum</td>
            <td class="text11">:</td>
            <td>{{ $data->gambaran }}</td>
        </tr>
        <tr class="border-bottom">
            <td class="text11">Kondisi saat ini</td>
            <td class="text11">:</td>
            <td>{{ $data->kondisi }}</td>
        </tr>
        <tr class="border-bottom">
            <td class="text11">Manfaat Teknis</td>
            <td class="text11">:</td>
            <td>{{ $data->manfaat_teknis }}</td>
        </tr>
        <tr class="border-bottom">
            <td class="text11">Manfaat Ekonomis</td>
            <td class="text11">:</td>
            <td>{{ $data->manfaat_ekonomis }}</td>
        </tr>
        <tr class="border-bottom">
            <td class="text11" colspan="3">Biaya</td>
        </tr>
        <tr class="border-bottom">
            <td class="text11">&nbsp;&nbsp;&nbsp;Nomor Perkiraan</td>
            <td class="text11">:</td>
            <td>{{ $data->perkiraan_anggaran }}</td>
        </tr>
        <tr class="border-bottom">
            <td class="text11">&nbsp;&nbsp;&nbsp;Tahun Anggaran</td>
            <td class="text11">:</td>
            <td>{{ $data->tahun_anggaran }}</td>
        </tr>
        <tr class="border-bottom">
            <td class="text11">Tanggal Pekerjaan Mulai</td>
            <td class="text11">:</td>
            <td>{{ changeDateFormat($data->tgl_mulai, 'Y-m-d H:i:s', 'Y-m-d') }}</td>
        </tr>
        <tr class="border-bottom">
            <td class="text11">Tanggal Pekerjaan Selesai</td>
            <td class="text11">:</td>
            <td>
                @if (!empty($data->perkiraan_revisi))
                    {{ changeDateFormat($data->perkiraan_revisi, 'Y-m-d H:i:s', 'Y-m-d') }}
                @else
                    {{ changeDateFormat($data->perkiraan, 'Y-m-d H:i:s', 'Y-m-d') }}
                @endif
            </td>
        </tr>
        <tr class="border-bottom">
            <td class="text11">Waktu</td>
            <td class="text11">:</td>
            <td>{{ $data->waktu }} Hari</td>
        </tr>
        <tr class="border-bottom">
            <td class="text11">Spesifikasi</td>
            <td class="text11">:</td>
            <td>{{ $data->spesifikasi }}</td>
        </tr>
        <tr class="border-bottom">
            <td class="text11">Kesimpulan</td>
            <td class="text11">:</td>
            <td>{{ $data->kesimpulan }}</td>
        </tr>
        <tr>
            <td colspan="3"></td>
        </tr>
    </table>

    <br>
    <table class="blueTable">
        <tr>
            <td class="tengah" style="font-weight: bold;">DOKUMENTASI</td>
        </tr>
    </table>
    <table class="commontbl" style="width:100%">
        <tr>
            <td class="text11 tengah" width="50%">
                @if (!empty($datawo->foto_investigasi))
                    @if (in_array($wo, ['aduan_non_op_id', 'usulan_id']))
                        <img src="{{ url('pic-api/gambar/non-operasi&'.captionWo($wo).'&'.$datawo->id.'&'.$datawo->foto_investigasi) }}" width="200px" height="200px">
                    @else
                        <img src="{{ url('pic-api/gambar/'.str_replace('/', '&', $datawo->foto_investigasi)) }}" width="200px" height="200px">
                    @endif
                @endif
            </td>
            <td class="text11 tengah" width="50%">
                @if (!empty($data->foto))
                    <img src="{{ url('pic-api/gambar/'.str_replace('/', '&', $data->foto)) }}" width="200px" height="200px">
                @endif
            </td>
        </tr>
    </table>
    
    <table class="commontbl" style="width:100%">
        <tr class="border-bottom">
            <td class="text11" width="30%">Deskripsi</td>
            <td class="text11" width="3%">:</td>
            <td width="67%">{{ $data->deskripsi }}</td>
        </tr>
    </table><br>
    

    <table style="width:100%;margin-top: 20px" border="0">
        <tr>
            <td width="50%" class="tengah">
                @if ($datawo->spv == '218')
                    <span class="text13n">{{ getJabatan($datawo->manajer)->namajabatan }}</span><br>
                @else
                    <span class="text13n">{{ getJabatan(manajer($datawo->manajer))->namajabatan }}</span><br>
                @endif
            </td>
            <td width="50%" class="tengah">
                <span class="text13n">{{ getJabatan($datawo->manajer)->namajabatan }}</span><br>
            </td>
        </tr>
        <tr>
            <td width="100%" colspan="2" style="padding:20px">&nbsp;</td>
        </tr>
        <tr>
            <td width="50%" class="tengah">
                @if ($datawo->spv == '218')
                    <span class="text13"><u>{{ getProfile($datawo->manajer)->nama}}</u></span><br>
                    <span class="text13n">NIP. {{ formatNip(getProfile($datawo->manajer)->nip) }}</span><br>
                @else
                    <span class="text13"><u>{{ getProfile(manajer($datawo->manajer))->nama}}</u></span><br>
                    <span class="text13n">NIP. {{ formatNip(getProfile(manajer($datawo->manajer))->nip) }}</span><br>
                @endif
            </td>
            <td width="50%" class="tengah">
                <span class="text13"><u>{{ getProfile($datawo->manajer)->nama }}</u></span><br>
                <span class="text13n">NIP. {{ formatNip(getProfile($datawo->manajer)->nip)}}</span><br>
            </td>
        </tr>
    </table>
</div>
<!-- </main> -->
</div>
</body>
</html>