<?php
namespace Asset\Libraries;

use Asset\Models\Aset,
    Asset\Models\Instalasi,
    Asset\Models\Ms52w,
    Asset\Models\Ms4w,
    Asset\Models\Prw4w,
    Asset\Models\Master,
    Asset\Models\MasterFm,
    Asset\Models\MasterJab,
    Asset\Models\Perawatan,
    Asset\Models\Perbaikan,
    Asset\Models\PrbDetail,
    Asset\Models\PrwDetail,
    Asset\Models\PmlKeluhan,
    Asset\Models\PmlKeluhanDev,
    Asset\Role as tuRoleUser,
    Asset\Jabatan,
    Asset\Models\KpiSetting;

use DateTime;
use DB;
use PDF;
use Session;
use Validator;
use Storage;

class Kpi
{
	protected $arrJab = ['81', '83', '80', '82', '84', '85', '86', '87', '218', '231'];

    static $closed = false;
    static $start = "";
    static $end = "";

    public static function cetak($periode, $bagian, $start = null, $end = null)
    {
    	$arrData = [];

        $period = date("Ym", strtotime($periode));
        $period2 = date("Y-m", strtotime($periode));
        $tmpBag = explode("#", $bagian);
        $arrData = [];
        $lokasi = config('custom.'.(int)$tmpBag[0].'.lokasi');
        $bagian = config('custom.'.(int)$tmpBag[0].'.bagian');
// dd($bagian);
        // Cek closing periode
        $setting = KpiSetting::wherePeriode($period)->first();
        if ( $setting ) {
            if ($setting->closed == '1') {
                self::$closed = true;
                self::$start = $start;
                self::$end = $end;
            }
        }

        // ------------ Preventif----------------------------
        // Monitoring
        $monitoring = self::getMonitoring($period, $lokasi, $bagian);

        // Perawatan Rutin
        $prwRutin = self::getPrwRutin($period, $lokasi, $bagian);

        // ------------ Corrective----------------------------
        // Perawatan
        $prwCorrective = self::getPrw($period, $lokasi, $bagian, 'corrective');
        $prwClosing = self::getPrw($period, $lokasi, $bagian, 'closing');

        // Perbaikan Monitoring
        $prbCorrective = self::getPrb($period, $lokasi, $bagian, 'monitoring', 'corrective');
        $prbClosing = self::getPrb($period, $lokasi, $bagian, 'monitoring', 'closing');

        // Perbaikan Aduan
        $prbAduanCorrective = self::getPrb($period, $lokasi, $bagian, 'aduan', 'corrective');
        $prbAduanClosing = self::getPrb($period, $lokasi, $bagian, 'aduan', 'closing');

        // ------------ end:Corrective----------------------------

        // Leading
        $avail = self::leading($period, $lokasi, $bagian, 'tidak beroperasi', $period2);
        $rel = self::leading($period, $lokasi, $bagian, '', $period2);

        $arrData = [
            'data' => $arrData, 
            'monitoring' => $monitoring, 
            'prwRutin' => $prwRutin, 
            'prwCorrective' => $prwCorrective, 
            'prwClosing' => $prwClosing,
            'prbCorrective' => $prbCorrective, 
            'prbClosing' => $prbClosing,
            'prbAduanCorrective' => $prbAduanCorrective, 
            'prbAduanClosing' => $prbAduanClosing,
            'avail' => $avail,
            'rel' => $rel,
            'periode' => isset($periode)?getPeriode($periode):'',
            'bag' => $tmpBag[1]
        ];

        return $arrData;
    }

    protected static function sqlMonitoring($period, $lokasi, $bagian)
    {
        $monitoring = [];
        $curYear = date('Y');

        if (self::$closed == true) {
            $start = self::$start;
            $end = self::$end;

            $sqlMonitoring = Ms4w::whereRaw("TO_CHAR(TANGGAL_MONITORING, 'YYYYMMDD') >= $start AND TO_CHAR(TANGGAL_MONITORING, 'YYYYMMDD') <= $end")
                ->select(DB::raw("count(ms_4w.id) monitoring,
                sum(
                    CASE WHEN
                        (to_number(to_char(tanggal_selesai,'IW')) = urutan_minggu) AND tanggal_selesai IS NOT NULL THEN 1
                    ELSE 0
                    END
                ) AS selesai,
                sum(
                    CASE WHEN
                        tanggal_selesai IS NULL THEN 1
                        WHEN (tanggal_selesai IS NOT NULL AND to_number(to_char(tanggal_selesai,'IW')) > urutan_minggu) THEN 1
                        WHEN tanggal_selesai IS NULL AND TO_CHAR(TANGGAL_MONITORING, 'YYYY') < $curYear THEN 1
                    ELSE 0
                    END
                ) AS tidak_selesai"))
                ->join('ms_52w', 'ms_4w.ms_52w_id', '=', 'ms_52w.id')
                ->join('aset', 'ms_52w.komponen_id', '=', 'aset.id')
                ->whereIn('aset.instalasi_id', $lokasi)
                ->whereIn('aset.bagian', $bagian)
                ->where('ms_4w.status', '<>', '99')
                ->where('kondisi_id', '<>', '12')
                ->where('ms_52w.tahun', substr($period, 0, 4))
                ->first();

            $monitoring['belum_selesai']= '-';
        } else {
            $sqlMonitoring = Ms4w::whereRaw("TO_CHAR(TANGGAL_MONITORING, 'YYYYMM') = $period")
                ->select(DB::raw("count(ms_4w.id) monitoring,
                sum(
                    CASE WHEN
                        (to_number(to_char(tanggal_selesai,'IW')) = urutan_minggu) AND tanggal_selesai IS NOT NULL THEN 1
                    ELSE 0
                    END
                ) AS selesai,
                sum(
                    CASE WHEN
                        (to_number(to_char(SYSDATE,'IW')) <= urutan_minggu) AND (tanggal_selesai IS NULL AND foto_lokasi IS NULL) AND TO_CHAR(TANGGAL_MONITORING, 'YYYY') = $curYear THEN 1
                    ELSE 0
                    END
                ) AS belum_selesai,
                sum(
                    CASE WHEN
                        (tanggal_selesai IS NULL AND to_number(to_char(SYSDATE,'IW')) > urutan_minggu) THEN 1
                        WHEN (tanggal_selesai IS NOT NULL AND to_number(to_char(tanggal_selesai,'IW')) > urutan_minggu) THEN 1
                        WHEN tanggal_selesai IS NULL AND TO_CHAR(TANGGAL_MONITORING, 'YYYY') < $curYear THEN 1
                    ELSE 0
                    END
                ) AS tidak_selesai"))
                ->join('ms_52w', 'ms_4w.ms_52w_id', '=', 'ms_52w.id')
                ->join('aset', 'ms_52w.komponen_id', '=', 'aset.id')
                ->whereIn('aset.instalasi_id', $lokasi)
                ->whereIn('aset.bagian', $bagian)
                ->where('ms_4w.status', '<>', '99')
                ->where('kondisi_id', '<>', '12')
                ->where('ms_52w.tahun', substr($period, 0, 4))
                ->first();

            $monitoring['belum_selesai']= !empty($sqlMonitoring->belum_selesai)?$sqlMonitoring->belum_selesai:0;
        }

        $monitoring['total'] = !empty($sqlMonitoring->monitoring)?$sqlMonitoring->monitoring:0;
        $monitoring['selesai']= !empty($sqlMonitoring->selesai)?$sqlMonitoring->selesai:0;
        $monitoring['tidak_selesai']= !empty($sqlMonitoring->tidak_selesai)?$sqlMonitoring->tidak_selesai:0;

        return $monitoring;
    }

    protected static function getMonitoring($period, $lokasi, $bagian)
    {
        $target = 90;        
        $monitoring = self::sqlMonitoring($period, $lokasi, $bagian);

        if ($monitoring['selesai'] > 0) {
            if (self::$closed == true) {
                $monitoring['persentase'] = round( ($monitoring['selesai'] / $monitoring['total'] ), 3 ) * 100;
            } else {
                $monitoring['persentase'] = round( ($monitoring['selesai'] / ($monitoring['total'] - $monitoring['belum_selesai']) ), 3 ) * 100;
            }
        } else {
            if ($monitoring['total'] > 0) {
                $monitoring['persentase'] = "0";
            } else {
                $monitoring['persentase'] = "-";
            }
        }

        if (is_numeric($monitoring['persentase'])) {
            if ($monitoring['persentase'] < $target) {
                $monitoring['status'] = "Tidak Tercapai";
            } else {
                $monitoring['status'] = "Tercapai";
            }
        } else {
            $monitoring['status'] = "-";
        }

        return $monitoring;
    }

    protected static function sqlPrwRutin($period, $lokasi, $bagian)
    {
        $prwRutin = [];
        $curYear = date('Y');

        if (self::$closed == true) {
            $start = self::$start;
            $end = self::$end;

            $sqlRutin = Prw4w::whereRaw("TO_CHAR(TANGGAL_MONITORING, 'YYYYMMDD') >= $start AND TO_CHAR(TANGGAL_MONITORING, 'YYYYMMDD') <= $end")
                ->select(DB::raw("count(prw_4w.id) monitoring,
                sum(
                    CASE WHEN
                        (to_number(to_char(tanggal_selesai,'IW')) = urutan_minggu) AND tanggal_selesai IS NOT NULL THEN 1
                    ELSE 0
                    END
                ) AS selesai,
                sum(
                    CASE 
                    WHEN
                        tanggal_selesai IS NULL THEN 1
                    WHEN
                        (to_number(to_char(tanggal_selesai,'IW')) > urutan_minggu) THEN 1
                    WHEN 
                        tanggal_selesai IS NULL AND TO_CHAR(TANGGAL_MONITORING, 'YYYY') < $curYear THEN 1
                    ELSE 0
                    END
                ) AS tidak_selesai"))
                ->join('prw_52w', 'prw_4w.prw_52w_id', '=', 'prw_52w.id')
                ->join('aset', 'prw_52w.komponen_id', '=', 'aset.id')
                ->where('prw_4w.status', '<>', '99')
                ->whereIn('aset.instalasi_id', $lokasi)
                ->whereIn('aset.bagian', $bagian)
                ->where('kondisi_id', '<>', '12')
                ->first();

            $prwRutin['belum_selesai'] = "-";
        } else {
            $sqlRutin = Prw4w::whereRaw("TO_CHAR(TANGGAL_MONITORING, 'YYYYMM') = $period")
                ->select(DB::raw("count(prw_4w.id) monitoring,
                sum(
                    CASE WHEN
                        (to_number(to_char(tanggal_selesai,'IW')) = urutan_minggu) AND tanggal_selesai IS NOT NULL THEN 1
                    ELSE 0
                    END
                ) AS selesai,
                sum(
                    CASE WHEN
                        (to_number(to_char(SYSDATE,'IW')) <= urutan_minggu) AND (tanggal_selesai IS NULL AND foto IS NULL) AND TO_CHAR(TANGGAL_MONITORING, 'YYYY') = $curYear THEN 1
                    ELSE 0
                    END
                ) AS belum_selesai,
                sum(
                    CASE WHEN
                        (tanggal_selesai IS NULL AND to_number(to_char(SYSDATE,'IW')) > urutan_minggu) OR (to_number(to_char(tanggal_selesai,'IW')) > urutan_minggu) THEN 1
                        WHEN tanggal_selesai IS NULL AND TO_CHAR(TANGGAL_MONITORING, 'YYYY') < $curYear THEN 1
                    ELSE 0
                    END
                ) AS tidak_selesai"))
                ->join('prw_52w', 'prw_4w.prw_52w_id', '=', 'prw_52w.id')
                ->join('aset', 'prw_52w.komponen_id', '=', 'aset.id')
                ->where('prw_4w.status', '<>', '99')
                ->whereIn('aset.instalasi_id', $lokasi)
                ->whereIn('aset.bagian', $bagian)
                ->where('kondisi_id', '<>', '12')
                ->first();

            $prwRutin['belum_selesai'] = !empty($sqlRutin->belum_selesai)?$sqlRutin->belum_selesai:0;
        }

        $prwRutin['total'] = !empty($sqlRutin->monitoring)?$sqlRutin->monitoring:0;
        $prwRutin['selesai'] = !empty($sqlRutin->selesai)?$sqlRutin->selesai:0;
        $prwRutin['tidak_selesai'] = !empty($sqlRutin->tidak_selesai)?$sqlRutin->tidak_selesai:0;

        return $prwRutin;
    }

    protected static function getPrwRutin($period, $lokasi, $bagian)
    {
        $target = 90;
        $prwRutin = self::sqlPrwRutin($period, $lokasi, $bagian);
        
        if ($prwRutin['selesai'] > 0) {
            if (self::$closed == true) {
                $prwRutin['persentase'] = round( ($prwRutin['selesai'] / $prwRutin['total'] ), 3 ) * 100;
            } else {
                $prwRutin['persentase']=round( ($prwRutin['selesai'] / ($prwRutin['total'] - $prwRutin['belum_selesai']) ), 3 ) * 100;
            }
        } else {
            if ($prwRutin['total'] > 0) {
                $prwRutin['persentase'] = "0";
            } else {
                $prwRutin['persentase'] = "-";
            }
        }

        if (is_numeric($prwRutin['persentase'])) {
            if ($prwRutin['persentase'] < $target) {
                $prwRutin['status'] = "Tidak Tercapai";
            } else {
                $prwRutin['status'] = "Tercapai";
            }
        } else {
            $prwRutin['status'] = "-";
        }

        return $prwRutin;
    }

    protected static function getPrw($period, $lokasi, $bagian, $tipe = "corrective")
    {
        $prw = [];
        DB::setDateFormat('DD-MON-YYYY');
        $prevPeriod = self::getPrevMonth($period);

        $dateStringOldFormat = $period;
        $firstDate = DateTime::createFromFormat('Ym', $dateStringOldFormat)->format('01-m-Y');
        $lastDate = DateTime::createFromFormat('Ym', $dateStringOldFormat)->format('t-m-Y 23:59:59');
// dd($lastDate)s
        $sqlPrw = Perawatan::join('aset', 'prw_data.komponen_id', '=', 'aset.id')
            ->whereIn('aset.instalasi_id', $lokasi)
            ->whereIn('aset.bagian', $bagian)
            // ->where('prw_data.metode', '<>', 'masa garansi investasi')
            ->whereNotIn('status', ['99', '98']);

        if ($period == "202012") {
            $sqlPrw = $sqlPrw->whereNull('petugas_catatan')
                ->whereNull('m_catatan');
        }
// dd($prevPeriod);
        if ($tipe == "corrective") {
            $target = 95;

            $tmp = $sqlPrw->whereRaw("(
    ( TO_CHAR(TANGGAL, 'YYYYMM') = $period AND TGL_INPUT_METODE IS NULL) OR
    ( (TO_CHAR(TANGGAL, 'YYYYMM') = $period AND TGL_INPUT_METODE IS NOT NULL and prw_data.metode <> 'masa garansi investasi' ) OR 
        (
            ( TO_CHAR(TANGGAL, 'YYYYMM') = $prevPeriod AND ( abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
- to_timestamp_tz(to_date('$firstDate','dd-mm-yyyy'), 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 48 ) AND TGL_INPUT_METODE IS NULL )
            OR
            (TO_CHAR(TANGGAL, 'YYYYMM') = $prevPeriod AND ( abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
- to_timestamp_tz(TGL_INPUT_METODE, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 48 ) AND TO_CHAR(TGL_INPUT_METODE, 'YYYYMM') = $period and prw_data.metode <> 'masa garansi investasi')
        )
    )
)")
            ->select(
                DB::raw("SUM(
                    CASE WHEN TO_CHAR(TANGGAL, 'YYYYMM') = $period THEN 1
                    ELSE 0
                    END
                ) AS jumlah,
                SUM(
                    CASE WHEN TO_CHAR(TANGGAL, 'YYYYMM') = $prevPeriod THEN 1
                    ELSE 0
                    END
                ) AS jumlahberlalu,
                SUM(
                    CASE WHEN abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   tgl_input_metode, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 48 THEN 1 
                    ELSE 0
                    END
                ) AS selesai,
                SUM(
                    CASE WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   tgl_input_metode, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > 48) AND TGL_INPUT_METODE IS NOT NULL THEN 1 
                    WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > 48) AND TGL_INPUT_METODE IS NULL THEN 1 
                    ELSE 0
                    END
                ) AS tidak_selesai,
                SUM(
                    CASE WHEN abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   sysdate, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 48 AND TGL_INPUT_METODE IS NULL THEN 1 
                    ELSE 0
                    END
                ) AS belum_selesai"))->first();

// dd($tmp);
            $prw['total'] = !empty($tmp->jumlah)?$tmp->jumlah:0;
            $prw['totalBerlalu'] = !empty($tmp->jumlahberlalu)?$tmp->jumlahberlalu:0;
            $allTotal = $prw['total'] + $prw['totalBerlalu'];

            $prw['selesai'] = !empty($tmp->selesai)?$tmp->selesai:0;
            $prw['tidak_selesai'] = !empty($tmp->tidak_selesai)?$tmp->tidak_selesai:0;
            $prw['belum_selesai'] = !empty($tmp->belum_selesai)?$tmp->belum_selesai:0;
        } else {
            $target = 90;

            $tmp = $sqlPrw->whereRaw("(
    ( TO_CHAR(TANGGAL, 'YYYYMM') = $period AND TGL_INPUT_METODE IS NULL) OR
    ( (TO_CHAR(TANGGAL, 'YYYYMM') = $period AND TGL_INPUT_METODE IS NOT NULL and prw_data.metode <> 'masa garansi investasi' ) OR 
        (
            ( TO_CHAR(TANGGAL, 'YYYYMM') = $prevPeriod AND ( abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
- to_timestamp_tz(to_date('$firstDate','dd-mm-yyyy'), 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 48 ) AND TGL_INPUT_METODE IS NULL )
            OR
            (TO_CHAR(TANGGAL, 'YYYYMM') = $prevPeriod AND ( abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
- to_timestamp_tz(TGL_INPUT_METODE, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 48 ) AND TO_CHAR(TGL_INPUT_METODE, 'YYYYMM') = $period and prw_data.metode <> 'masa garansi investasi')
        ) OR
        ( 
            TO_CHAR(TANGGAL, 'YYYYMM') <= $period AND TGL_FOTO_ANALISA IS NULL AND
            (
            (TO_CHAR(PERKIRAAN, 'YYYYMM') >= $period AND METODE = 'internal')
            OR (TO_CHAR(PERKIRAAN, 'YYYYMM') >= $period AND METODE NOT IN ('internal', 'masa garansi investasi') AND PERKIRAAN_REVISI IS NULL)
            OR (TO_CHAR(PERKIRAAN_REVISI, 'YYYYMM') >= $period AND METODE NOT IN ('internal', 'masa garansi investasi') AND PERKIRAAN_REVISI IS NOT NULL)
            )
        )  OR ( TO_CHAR(TANGGAL, 'YYYYMM') <= $period AND TO_CHAR(TGL_FOTO_ANALISA, 'YYYYMM') = $period AND metode <> 'masa garansi investasi' AND
                (TO_CHAR(PERKIRAAN, 'YYYYMM') >= $period OR TO_CHAR(PERKIRAAN_REVISI, 'YYYYMM') >= $period )      
            )
    )
)")
            ->select(
            DB::raw("SUM(
                CASE WHEN TO_CHAR(TANGGAL, 'YYYYMM') = $period THEN 1
                ELSE 0
                END
            ) AS jumlah,
            SUM(
                CASE 
                WHEN (TO_CHAR(TANGGAL, 'YYYYMM') < $period AND TO_CHAR(perkiraan, 'YYYYMM') >= $period) AND perkiraan_revisi is null AND METODE <> 'internal' THEN 1
                WHEN (TO_CHAR(TANGGAL, 'YYYYMM') < $period AND TO_CHAR(perkiraan_revisi, 'YYYYMM') >= $period) AND perkiraan_revisi is not null AND METODE <> 'internal' THEN 1
                WHEN (TO_CHAR(TANGGAL, 'YYYYMM') < $period AND TO_CHAR(perkiraan, 'YYYYMM') >= $period) AND METODE='internal' THEN 1
                WHEN TO_CHAR(TANGGAL, 'YYYYMM') < $period AND TO_CHAR(TGL_FOTO_ANALISA, 'YYYYMM') = $period THEN 1
                ELSE 0
                END
            ) AS jumlahberlalu,
            SUM(
                CASE WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   TGL_FOTO_ANALISA, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 192) AND METODE = 'internal' THEN 1
                    WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   TGL_FOTO_ANALISA, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   perkiraan, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND perkiraan_revisi IS NULL THEN 1
                    WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   TGL_FOTO_ANALISA, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   perkiraan_revisi, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND perkiraan_revisi IS NOT NULL THEN 1
                ELSE 0
                END
            ) AS selesai,
            SUM(
                CASE WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   TGL_FOTO_ANALISA, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > 192) AND METODE = 'internal' THEN 1
                WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > 192) AND METODE = 'internal' AND TGL_FOTO_ANALISA IS NULL THEN 1
                WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   TGL_FOTO_ANALISA, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   perkiraan, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND perkiraan_revisi IS NULL THEN 1
                WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   TGL_FOTO_ANALISA, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   perkiraan_revisi, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND perkiraan_revisi IS NOT NULL THEN 1
                WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   perkiraan, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND TGL_FOTO_ANALISA IS NULL AND perkiraan_revisi IS NULL THEN 1
                WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   perkiraan_revisi, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND TGL_FOTO_ANALISA IS NULL AND perkiraan_revisi IS NOT NULL THEN 1  
                ELSE 0
                END
            ) AS tidak_selesai,
            SUM(
                CASE WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 192) AND METODE = 'internal' AND TGL_FOTO_ANALISA IS NULL THEN 1
                    WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( perkiraan, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND TGL_FOTO_ANALISA IS NULL AND perkiraan_revisi IS NULL THEN 1
                    WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( perkiraan_revisi, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND TGL_FOTO_ANALISA IS NULL AND perkiraan_revisi IS NOT NULL THEN 1
                    WHEN METODE IS NULL AND perkiraan is null and TO_CHAR(TANGGAL, 'YYYYMM') = $period THEN 1
                ELSE 0
                END
            ) AS belum_selesai"))
            // ->whereNotNull('perkiraan')
            ->first();

            $prw['total'] = !empty($tmp->jumlah)?$tmp->jumlah:0;
            $prw['totalBerlalu'] = !empty($tmp->jumlahberlalu)?$tmp->jumlahberlalu:0;
            $allTotal = $prw['total'] + $prw['totalBerlalu'];

            $prw['selesai'] = !empty($tmp->selesai)?$tmp->selesai:0;
            $prw['tidak_selesai'] = !empty($tmp->tidak_selesai)?$tmp->tidak_selesai:0;
            $prw['belum_selesai'] = !empty($tmp->belum_selesai)?$tmp->belum_selesai:0;
        }

        if ($prw['selesai'] > 0) {
            // dd($prw['selesai']);
            // dd(round( ($prw['selesai'] / ($prw['total'] - $prw['belum_selesai']) ), 3 ));
            $prw['persentase'] = round( ($prw['selesai'] / ($allTotal - $prw['belum_selesai']) ), 3 ) * 100;
        } else {
            if ($allTotal > 0) {
                if ($prw['belum_selesai'] > 0) {
                    $prw['persentase'] = "Progress";  
                } elseif ($prw['belum_selesai'] > 0 && $prw['tidak_selesai'] > 0) {
                    $prw['persentase'] = "Tdktercapai-Progress";  
                } else {
                    $prw['persentase'] = "0";
                }
            } else {
                $prw['persentase'] = "-";
            }
        }

        if (is_numeric($prw['persentase'])) {
            if ($prw['persentase'] < $target) {
                $prw['status'] = "Tidak Tercapai";
            } else {
                $prw['status'] = "Tercapai";
            }
        } elseif ($prw['persentase'] == '-') {
            $prw['status'] = "Tercapai";
        } else {
            if ($prw['persentase'] == "Progress") {
                $prw['status'] = "Tercapai";
                $prw['persentase'] = "-";
            } elseif ($prw['persentase'] == "Tdktercapai-Progress") {
                $prw['status'] = "Tidak Tercapai";
                $prw['persentase'] = "0";
            } else {
                $prw['status'] = "-";
            }
        }

        return $prw;
    }

    protected static function getPrb($period, $lokasi, $bagian, $jenis = "monitoring", $tipe = "corrective")
    {
        $prb = [];
// dd($bagian);
        DB::setDateFormat('DD-MON-YYYY');
        $prevPeriod = self::getPrevMonth($period);

        $dateStringOldFormat = $period;
        $firstDate = DateTime::createFromFormat('Ym', $dateStringOldFormat)->format('01-m-Y');

DB::connection()->enableQueryLog();

        $sqlPrb = Perbaikan::where('tipe', $jenis)
            ->join('aset', 'prb_data.komponen_id', '=', 'aset.id')
            ->whereIn('aset.instalasi_id', $lokasi)
            ->whereIn('aset.bagian', $bagian)
            ->whereNotIn('status', ['99', '98']);
        
        // $prb['total'] = !empty($sqlPrb->first()->jumlah)?$sqlPrb->first()->jumlah:0;

        if ($tipe == "corrective") {
            $target = 95;
            $tmp = $sqlPrb->whereRaw("(
    ( TO_CHAR(TANGGAL, 'YYYYMM') = $period AND TGL_INPUT_METODE IS NULL) OR
    ( (TO_CHAR(TANGGAL, 'YYYYMM') = $period AND TGL_INPUT_METODE IS NOT NULL and prb_data.metode <> 'masa garansi investasi' ) OR 
        (
            ( TO_CHAR(TANGGAL, 'YYYYMM') = $prevPeriod AND ( abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
- to_timestamp_tz(to_date('$firstDate','dd-mm-yyyy'), 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 48 ) AND TGL_INPUT_METODE IS NULL )
            OR
            (TO_CHAR(TANGGAL, 'YYYYMM') = $prevPeriod AND ( abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
- to_timestamp_tz(TGL_INPUT_METODE, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 48 ) AND TO_CHAR(TGL_INPUT_METODE, 'YYYYMM') = $period and prb_data.metode <> 'masa garansi investasi')
        )
    )
)")
            ->select(
                DB::raw("SUM(
                    CASE WHEN TO_CHAR(TANGGAL, 'YYYYMM') = $period THEN 1
                    ELSE 0
                    END
                ) AS jumlah,
                SUM(
                    CASE WHEN TO_CHAR(TANGGAL, 'YYYYMM') = $prevPeriod THEN 1
                    ELSE 0
                    END
                ) AS jumlahberlalu,
                SUM(
                    CASE WHEN abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   tgl_input_metode, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 48 THEN 1 
                    ELSE 0
                    END
                ) AS selesai,
                SUM(
                    CASE 
                    WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   tgl_input_metode, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > 48) AND TGL_INPUT_METODE IS NOT NULL THEN 1 
                    WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > 48) AND TGL_INPUT_METODE IS NULL THEN 1 
                    ELSE 0
                    END
                ) AS tidak_selesai,
                SUM(
                    CASE WHEN abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   sysdate, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 48 AND TGL_INPUT_METODE IS NULL THEN 1 
                    ELSE 0
                    END
                ) AS belum_selesai"))->first();

            $prb['total'] = !empty($tmp->jumlah)?$tmp->jumlah:0;
            $prb['totalBerlalu'] = !empty($tmp->jumlahberlalu)?$tmp->jumlahberlalu:0;
            $allTotal = $prb['total'] + $prb['totalBerlalu'];

            $prb['selesai'] = !empty($tmp->selesai)?$tmp->selesai:0;
            $prb['tidak_selesai'] = !empty($tmp->tidak_selesai)?$tmp->tidak_selesai:0;
            $prb['belum_selesai'] = !empty($tmp->belum_selesai)?$tmp->belum_selesai:0;
        } else {
            $target = 90;

            $tmp = $sqlPrb->whereRaw("(
    ( TO_CHAR(TANGGAL, 'YYYYMM') = $period AND TGL_INPUT_METODE IS NULL) OR
    ( (TO_CHAR(TANGGAL, 'YYYYMM') = $period AND TGL_INPUT_METODE IS NOT NULL and prb_data.metode <> 'masa garansi investasi' ) OR 
        (
            ( TO_CHAR(TANGGAL, 'YYYYMM') = $prevPeriod AND ( abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
- to_timestamp_tz(to_date('$firstDate','dd-mm-yyyy'), 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 48 ) AND TGL_INPUT_METODE IS NULL )
            OR
            (TO_CHAR(TANGGAL, 'YYYYMM') = $prevPeriod AND ( abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
- to_timestamp_tz(TGL_INPUT_METODE, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 48 ) AND TO_CHAR(TGL_INPUT_METODE, 'YYYYMM') = $period and prb_data.metode <> 'masa garansi investasi')
        ) OR
        ( 
            TO_CHAR(TANGGAL, 'YYYYMM') <= $period AND TGL_FOTO_ANALISA IS NULL AND
            (
            (TO_CHAR(PERKIRAAN, 'YYYYMM') >= $period AND METODE = 'internal')
            OR (TO_CHAR(PERKIRAAN, 'YYYYMM') >= $period AND METODE NOT IN ('internal', 'masa garansi investasi') AND PERKIRAAN_REVISI IS NULL)
            OR (TO_CHAR(PERKIRAAN_REVISI, 'YYYYMM') >= $period AND METODE NOT IN ('internal', 'masa garansi investasi') AND PERKIRAAN_REVISI IS NOT NULL)
            )
        )  OR ( TO_CHAR(TANGGAL, 'YYYYMM') <= $period AND TO_CHAR(TGL_FOTO_ANALISA, 'YYYYMM') = $period AND metode <> 'masa garansi investasi' AND
                (TO_CHAR(PERKIRAAN, 'YYYYMM') >= $period OR TO_CHAR(PERKIRAAN_REVISI, 'YYYYMM') >= $period )      
            )
    )
)")
            // ->where('prb_data.metode', '<>', 'masa garansi investasi')
            ->select(
            DB::raw("SUM(
                CASE WHEN TO_CHAR(TANGGAL, 'YYYYMM') = $period THEN 1
                ELSE 0
                END
            ) AS jumlah,
            SUM(
                CASE 
                WHEN (TO_CHAR(TANGGAL, 'YYYYMM') < $period AND TO_CHAR(perkiraan, 'YYYYMM') >= $period) AND perkiraan_revisi is null AND METODE <> 'internal' THEN 1
                WHEN (TO_CHAR(TANGGAL, 'YYYYMM') < $period AND TO_CHAR(perkiraan_revisi, 'YYYYMM') >= $period) AND perkiraan_revisi is not null AND METODE <> 'internal' THEN 1
                WHEN (TO_CHAR(TANGGAL, 'YYYYMM') < $period AND TO_CHAR(perkiraan, 'YYYYMM') >= $period) AND METODE='internal' THEN 1
                WHEN TO_CHAR(TANGGAL, 'YYYYMM') < $period AND TO_CHAR(TGL_FOTO_ANALISA, 'YYYYMM') = $period THEN 1
                ELSE 0
                END
            ) AS jumlahberlalu,
            SUM(
                CASE WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   TGL_FOTO_ANALISA, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 192) AND METODE = 'internal' THEN 1
                    WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   TGL_FOTO_ANALISA, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   perkiraan, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND perkiraan_revisi IS NULL THEN 1
                    WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   TGL_FOTO_ANALISA, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   perkiraan_revisi, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND perkiraan_revisi IS NOT NULL THEN 1
                ELSE 0
                END
            ) AS selesai,
            SUM(
                CASE WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   TGL_FOTO_ANALISA, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > 192) AND METODE = 'internal' THEN 1
                WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > 192) AND METODE = 'internal' AND TGL_FOTO_ANALISA IS NULL THEN 1
                WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   TGL_FOTO_ANALISA, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   perkiraan, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND perkiraan_revisi IS NULL THEN 1
                WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   TGL_FOTO_ANALISA, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   perkiraan_revisi, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND perkiraan_revisi IS NOT NULL THEN 1
                WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   perkiraan, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND TGL_FOTO_ANALISA IS NULL AND perkiraan_revisi IS NULL THEN 1
                WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   perkiraan_revisi, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND TGL_FOTO_ANALISA IS NULL AND perkiraan_revisi IS NOT NULL THEN 1 
                ELSE 0
                END
            ) AS tidak_selesai,
            SUM(
                CASE WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 192) AND METODE = 'internal' AND TGL_FOTO_ANALISA IS NULL THEN 1
                    WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( perkiraan, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND TGL_FOTO_ANALISA IS NULL AND perkiraan_revisi IS NULL THEN 1
                    WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( perkiraan_revisi, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND TGL_FOTO_ANALISA IS NULL AND perkiraan_revisi IS NOT NULL THEN 1
                    WHEN METODE IS NULL AND perkiraan is null and TO_CHAR(TANGGAL, 'YYYYMM') = $period THEN 1
                ELSE 0
                END
            ) AS belum_selesai"))
            // ->whereNotNull('perkiraan')
            ->first();

            $prb['total'] = !empty($tmp->jumlah)?$tmp->jumlah:0;
            $prb['totalBerlalu'] = !empty($tmp->jumlahberlalu)?$tmp->jumlahberlalu:0;
            $allTotal = $prb['total'] + $prb['totalBerlalu'];

            $prb['selesai'] = !empty($tmp->selesai)?$tmp->selesai:0;
            $prb['tidak_selesai'] = !empty($tmp->tidak_selesai)?$tmp->tidak_selesai:0;
            $prb['belum_selesai'] = !empty($tmp->belum_selesai)?$tmp->belum_selesai:0;
        }

        if ($prb['selesai'] > 0) {
            /*if ($jenis == 'aduan') {
                $raw = $prb['selesai'] / ($prb['total'] - $prb['belum_selesai']);
                dd(round($raw, 3) * 100);
            }*/
            if (($allTotal - $prb['belum_selesai']) > 0) {
                $prb['persentase'] = round( ($prb['selesai'] / ($allTotal - $prb['belum_selesai'])), 3 ) * 100;
            } else {
                $prb['persentase'] = 0;
            }
        } else {
            if ($allTotal > 0) {
                if ($prb['belum_selesai'] > 0 && $prb['tidak_selesai'] == 0) {
                    $prb['persentase'] = "Progress";
                } elseif ($prb['belum_selesai'] > 0 && $prb['tidak_selesai'] > 0) {
                    $prb['persentase'] = "Tdktercapai-Progress";
                } else {
                    $prb['persentase'] = "0";
                }
            } else {
                $prb['persentase'] = "-";
            }            
        } 

        if (is_numeric($prb['persentase'])) {
            if ($prb['persentase'] < $target) {
                $prb['status'] = "Tidak Tercapai";
            } else {
                $prb['status'] = "Tercapai";
            }
        } elseif ($prb['persentase'] == '-') {
            $prb['status'] = "Tercapai";
        } else {
            if ($prb['persentase'] == "Progress") {
                $prb['status'] = "Tercapai";
                $prb['persentase'] = "-";
            } elseif ($prb['persentase'] == "Tdktercapai-Progress") {
                $prb['status'] = "Tidak Tercapai";
                $prb['persentase'] = "0";
            } else {
                $prb['status'] = "-";
            }
        }

        return $prb;
    }

    protected static function leading($period, $lokasi, $bagian, $kondisi = "", $period2)
    {
        $arrPeriod = explode("-", $period2);
        $jmlJam = cal_days_in_month(CAL_GREGORIAN, $arrPeriod[1], $arrPeriod[0]) * 24;
        $getHours = (int)hoursInMonth($period);
// dd($getHours);
        foreach ($bagian as $row) {
        	$sql = Aset::select(DB::raw('count(aset.id) jumlah'))
	            ->whereIn('aset.instalasi_id', $lokasi)
	            ->where('kondisi_id', '<>', '12')
                ->where('bagian', $row)
                ->where('kategori_id', '1')
                ->first();

            $data[$row]['total'] = $sql->jumlah;
            $data[$row]['target'] = $data[$row]['total'] * $jmlJam;
// dd($kondisi);
            $unSql = Aset::leftjoin(DB::raw("(SELECT 
                x.komponen_id AS komponen_id, x.tanggal AS tanggal, x.kondisi AS kondisi, x.tgl_finish AS tgl_finish
            FROM
                (
                SELECT id, komponen_id, tanggal, kondisi, tgl_finish, RANK() OVER (partition by KOMPONEN_ID order by id desc) as rnk
                FROM PRB_DATA
                where status <> '99' AND KONDISI IS NOT NULL and TO_CHAR(TANGGAL, 'YYYYMM') = $period) x
            WHERE
                x.rnk = 1) prb_data"), 'aset.id', '=', 'prb_data.komponen_id')
                ->whereIn('aset.instalasi_id', $lokasi)
                ->where('kondisi_id', '<>', '12')
                ->where('aset.kategori_id', '1')
                ->where('aset.bagian', $row);
                // ->whereRaw("TO_CHAR(prb_data.TANGGAL, 'YYYYMM') = $period")
                // ->where('prb_data.status', '<>', '99')

            if ($kondisi == "") {
                $unSql->select(DB::raw("SUM
                    (CASE 
                        WHEN tgl_finish IS NOT null and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') = $period THEN 
                            abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                            - to_timestamp_tz( tgl_finish, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))
                        WHEN tgl_finish IS NULL and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') = $period THEN
                            abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                            - to_timestamp_tz( LAST_DAY(tanggal), 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))
                        WHEN (tgl_finish IS NULL) and TO_CHAR( tanggal, 'YYYYMM') < $period THEN
                            $getHours
                        ELSE 0
                        END
                    ) AS jumlah"));                
            } else {
                $unSql->where('prb_data.kondisi', $kondisi)
                ->select(DB::raw("SUM
                (CASE 
                    WHEN 
                    (tgl_finish IS NOT NULL AND kondisi = 'tidak beroperasi') and TO_CHAR(prb_data.TANGGAL, 'YYYYMM')=$period THEN
                        abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                        - to_timestamp_tz( tgl_finish, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))
                    WHEN 
                    (tgl_finish IS NULL AND kondisi = 'tidak beroperasi') and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') = $period THEN
                        abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                        - to_timestamp_tz( LAST_DAY(tanggal), 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))
                    WHEN 
                    (tgl_finish IS NULL AND kondisi = 'tidak beroperasi') and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') < $period THEN
                        $getHours
                    ELSE 0
                    END
                ) AS jumlah"));
            }

            $sql = $unSql->first();
            $data[$row]['un'] = !empty($sql->jumlah)?$sql->jumlah:0;
            $data[$row]['av_rel'] = $data[$row]['target'] - $data[$row]['un'];

            if ($data[$row]['total'] > 0) {
                // dd();
                // $data[$row]['persentase'] = round($data[$row]['av_rel'] / $data[$row]['target'], 3) * 100;
                $data[$row]['persentase'] = round(($data[$row]['av_rel'] / $data[$row]['target']) * 100, 2);

                if ($data[$row]['persentase'] >= 90) {
                    $data[$row]['status'] = "Tercapai";    
                } else {
                    $data[$row]['status'] = "Tidak Tercapai";
                }
            } else {
                $data[$row]['persentase'] = "-";
                $data[$row]['status'] = "-";
            }
        }

        return $data;
    }

    public static function getPrevMonth($period)
    {
        $dateStringOldFormat = $period;
        $dateStringNewFormat = DateTime::createFromFormat('Ym', $dateStringOldFormat)->format('d-m-Y');
        
        $date = new DateTime($dateStringNewFormat);

        $date->modify('-1 month');
        $prevPeriod = $date->format('Ym');

        return $prevPeriod;
    }

    public static function getNextMonth($period)
    {
        $dateStringOldFormat = $period;
        $dateStringNewFormat = DateTime::createFromFormat('Ym', $dateStringOldFormat)->format('d-m-Y');
        
        $date = new DateTime($dateStringNewFormat);

        $date->modify('+1 month');
        $nextPeriod = $date->format('Ym');

        return $nextPeriod;
    }
}