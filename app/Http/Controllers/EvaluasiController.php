<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Libraries\EvaluasiAset;

use Asset\Models\Aset,
    Asset\Models\Instalasi,
    Asset\Models\MasterJab,
    Asset\Models\Master,
    Asset\Models\Ms4w,
    Asset\Models\Prw4w,
    Asset\Models\Perawatan,
    Asset\Models\Perbaikan,
    Asset\Models\PmlKeluhan,
    Asset\Models\UsulanInvestasi,
    Asset\Role;

use DB;
use Datatables;
use Session;
use Validator;
use PDF;
use DateTime;

class EvaluasiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function getAvailable()
    {
        $instalasi = Instalasi::whereIn('id', lokasi())->get()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Semua Lokasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        $bagian = [
            "all" => 'Semua Bagian',
            "1" => 'Mekanikal',
            "2" => 'Elektrikal',
            "3" => 'Instrumentasi',
            "4" => 'Sipil',
        ];

        return view('pages.flaporan.evaluasi.available', [
            'instalasi' => $instalasi,
            'bagian' => $bagian
        ]);
    }

    public function laporanAvailable(Request $request)
    {
        $arrPeriod = date('Y-m', strtotime($request->periode));
        $arrPeriod = explode("-", $arrPeriod);
        $month = $arrPeriod[1];
        $year = $arrPeriod[0];
        $now = date('Ym');

        
        if ($request->bagian == "all") {
            $arrBagian = ['1', '2', '3', '4'];    
        } else {
            $arrBagian = [$request->bagian];
        }

        $period = date("Ym", strtotime($request->periode));
        $lastDate = DateTime::createFromFormat('Ym', $period)->format('t-m-Y');
        $firstDate = DateTime::createFromFormat('Ym', $period)->format('01-m-Y');

        DB::setDateFormat('DD-MON-YYYY');

        $getHours = (int)hoursInMonth($period);
        
        $equipment = Aset::select(DB::raw("aset.ID, max(x.NAME) AS SISTEM, max(NAMA_ASET) AS NAMA_ASET, max(aset.EQUIPMENT_ID) AS equipment_id, max(y.name) as bagiann, 
            SUM
            (CASE 
                WHEN ((tgl_finish IS NOT NULL and TO_CHAR(tgl_finish, 'YYYYMM') > $period) AND kondisi = 'tidak beroperasi') and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') = $period THEN 
                    abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( to_date('$lastDate','dd-mm-yyyy'), 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))
                WHEN (tgl_finish IS NOT NULL AND kondisi = 'tidak beroperasi') and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') = $period and TO_CHAR(tgl_finish, 'YYYYMM') = $period THEN 
                    abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( tgl_finish, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))
                WHEN (tgl_finish IS NULL AND kondisi = 'tidak beroperasi') and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') = $period THEN
                    abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( to_date('$lastDate','dd-mm-yyyy'), 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))
                WHEN (tgl_finish IS NULL AND kondisi = 'tidak beroperasi') and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') < $period THEN
                    $getHours
                ELSE 0
                END
            ) AS unavailable, 
            SUM
            (CASE 
                WHEN (tgl_finish IS NOT NULL and TO_CHAR(tgl_finish, 'YYYYMM') > $period) and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') = $period THEN 
                    abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( to_date('$lastDate','dd-mm-yyyy'), 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))
                WHEN (tgl_finish IS NOT NULL and TO_CHAR(tgl_finish, 'YYYYMM') = $period) and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') = $period THEN 
                    abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( tgl_finish, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))
                WHEN (tgl_finish IS NULL) and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') = $period and status <> '99' THEN
                    abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( to_date('$lastDate','dd-mm-yyyy'), 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))
                WHEN (tgl_finish IS NULL) and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') < $period and status <> '99' THEN
                    $getHours
                ELSE 0
                END
            ) AS unreliable"))
            ->leftjoin(DB::raw("(SELECT 
                x.komponen_id AS komponen_id, x.tanggal AS tanggal, x.kondisi AS kondisi, x.tgl_finish AS tgl_finish, x.status
            FROM
                (
                SELECT id, komponen_id, tanggal, kondisi, tgl_finish, status, RANK() OVER (partition by KOMPONEN_ID order by id desc) as rnk
                FROM PRB_DATA
                where status <> '99' AND KONDISI IS NOT NULL and TO_CHAR(TANGGAL, 'YYYYMM') = $period) x
            WHERE
                x.rnk = 1) prb_data"), 'aset.id', '=', 'prb_data.komponen_id')
            // ->leftjoin('aset asetb', 'aset.equipment_id', '=', 'asetb.id')
            ->leftJoin('master x', 'aset.sistem_id', '=', 'x.id')
            ->join('master y', 'aset.bagian', '=', 'y.id')
            ->where('aset.instalasi_id', $request->instalasi)
            ->where('aset.kondisi_id', '<>', '12')
            ->where('aset.kategori_id', '1')
            ->whereIn('aset.bagian', $arrBagian)
            ->whereNull('aset.equipment_id')
            ->groupBy('aset.id')
            ->orderBy(DB::raw("max(x.name)"))
            ->get();

        $komponen = Aset::select(DB::raw("aset.ID, max(x.NAME) AS SISTEM, max(NAMA_ASET) AS NAMA_ASET, max(aset.EQUIPMENT_ID) AS equipment_id, max(y.name) as bagiann, 
            SUM
            (CASE 
                WHEN ((tgl_finish IS NOT NULL and TO_CHAR(tgl_finish, 'YYYYMM') > $period) AND kondisi = 'tidak beroperasi') and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') = $period THEN 
                    abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( to_date('$lastDate','dd-mm-yyyy'), 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))
                WHEN (tgl_finish IS NOT NULL AND kondisi = 'tidak beroperasi') and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') = $period and TO_CHAR(tgl_finish, 'YYYYMM') = $period THEN 
                    abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( tgl_finish, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))
                WHEN (tgl_finish IS NULL AND kondisi = 'tidak beroperasi') and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') = $period THEN
                    abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( sysdate, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))
                WHEN (tgl_finish IS NULL AND kondisi = 'tidak beroperasi') and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') < $period THEN
                    $getHours
                ELSE 0
                END
            ) AS unavailable, 
            SUM
            (CASE 
                WHEN (tgl_finish IS NOT NULL and TO_CHAR(tgl_finish, 'YYYYMM') > $period) and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') = $period THEN 
                    abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( to_date('$lastDate','dd-mm-yyyy'), 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))
                WHEN (tgl_finish IS NOT NULL and TO_CHAR(tgl_finish, 'YYYYMM') = $period) and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') = $period THEN 
                    abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( tgl_finish, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))
                WHEN (tgl_finish IS NULL) and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') = $period THEN
                    abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( sysdate, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))
                WHEN (tgl_finish IS NULL) and TO_CHAR(prb_data.TANGGAL, 'YYYYMM') < $period THEN
                    $getHours
                ELSE 0
                END
            ) AS unreliable"))
            ->leftjoin(DB::raw("(SELECT 
                x.komponen_id AS komponen_id, x.tanggal AS tanggal, x.kondisi AS kondisi, x.tgl_finish AS tgl_finish
            FROM
                (
                SELECT id, komponen_id, tanggal, kondisi, tgl_finish, RANK() OVER (partition by KOMPONEN_ID order by id desc) as rnk
                FROM PRB_DATA
                where status <> '99' AND KONDISI IS NOT NULL) x
            WHERE
                x.rnk = 1) prb_data"), 'aset.id', '=', 'prb_data.komponen_id')
            // ->leftjoin('aset asetb', 'aset.equipment_id', '=', 'asetb.id')
            ->leftJoin('master x', 'aset.sistem_id', '=', 'x.id')
            ->join('master y', 'aset.bagian', '=', 'y.id')
            ->where('aset.instalasi_id', $request->instalasi)
            ->where('aset.kondisi_id', '<>', '12')
            ->where('aset.kategori_id', '1')
            ->whereIn('aset.bagian', $arrBagian)
            ->whereNotNull('aset.equipment_id')
            ->groupBy('aset.id')
            ->get();
            // dd($komponen);
        $arrData = [];
        $arrAvail = [];
        $arrRel = [];
        $avg = 0;
        $avgRel = 0;
        $i = 1;

        $jmlJamMonth = cal_days_in_month(CAL_GREGORIAN,$month,$year) * 24;
        if ($now==$period) {
            $datenow = (int)date('d');
            $jmlJamMonth = $datenow * 24;
        }

        foreach ($equipment as $row) {
            $unavailable = $row->unavailable;
            $unreliable = $row->unreliable;
            $available = $jmlJamMonth - $unavailable;
            $reliable = $jmlJamMonth - $unreliable;

            $arrAvail[] = round($available / $jmlJamMonth, 3) * 100;
            $arrRel[] = round($reliable / $jmlJamMonth, 3) * 100;

            $arrData[] = [
                // 'no' => $i,
                'no' => $row->id,
                'sistem' => ucwords(strtolower($row->sistem)),
                'bagian' => ucwords(strtolower($row->bagiann)),
                'equipment' => $row->nama_aset,
                'komponen' => $row->nama_aset,
                'total' => $jmlJamMonth,
                'available' => $available,
                'reliable' => $reliable,
                'unavailable' => $unavailable,
                'unreliable' => $unreliable,
                'availability' => round($available / $jmlJamMonth, 3) * 100,
                'reliability' => round($reliable / $jmlJamMonth, 3) * 100
            ];
            $i++;
            foreach ($komponen as $komp) {
                if ($komp->equipment_id == $row->id) {
                    $unavailable = isset($komp->unavailable)?$komp->unavailable:0;
                    $unreliable = isset($komp->unreliable)?$komp->unreliable:0;
                    $available = $jmlJamMonth - $unavailable;
                    $reliable = $jmlJamMonth - $unreliable;

                    $arrAvail[] = round($available / $jmlJamMonth, 3) * 100;
                    $arrRel[] = round($reliable / $jmlJamMonth, 3) * 100;

                    $arrData[] = [
                        // 'no' => $i,
                        'no' => $komp->id,
                        'sistem' => ucwords(strtolower($komp->sistem)),
                        'bagian' => ucwords(strtolower($komp->bagiann)),
                        'equipment' => '',
                        'komponen' => $komp->nama_aset,
                        'total' => $jmlJamMonth,
                        'available' => $available,
                        'reliable' => $reliable,
                        'unavailable' => $unavailable,
                        'unreliable' => $unreliable,
                        'availability' => round($available / $jmlJamMonth, 3) * 100,
                        'reliability' => round($reliable / $jmlJamMonth, 3) * 100
                    ];

                    $i++;
                }
            }
        }
        
        if (array_sum($arrAvail) > 0) $avg = round(array_sum($arrAvail) / count($arrAvail), 2);
        if (array_sum($arrRel) > 0) $avgRel = round(array_sum($arrRel) / count($arrRel), 2);

        $lokasi = Instalasi::find($request->instalasi);

        $pdf = PDF::loadView('pages.laporan.evaluasi.available', ['data' => $arrData, 
            'periode' => isset($request->periode)?str_replace("-", " ", $request->periode):'',
            'lokasi' => $lokasi,
            'avg' => $avg,
            'avgRel' => $avgRel
        ]);

        return $pdf->stream(sprintf('Perhitungan-Availability-Aset-%s.pdf', date('Y-m')));
    }

    public function getPenjadwalan()
    {
        $instalasi = Instalasi::whereIn('id', lokasi())->get()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Semua Lokasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        return view('pages.flaporan.evaluasi.penjadwalan', [
            'instalasi' => $instalasi
        ]);
    }

    public function laporanPenjadwalan(Request $request)
    {
        //mei 18-22
        $period = date("Ym", strtotime($request->periode));
        $tahun = date("Y", strtotime($request->periode));
        $bagian = Master::where('kelompok', 'BAGIAN')->get();
        $instalasi = $request->instalasi;
        $arrData = [];
        $arrTemp = [];
        $arrTempPrw = [];
        $arrPrw = [];

        // Perawatan Rutin
        $arrTempPrw = Prw4w::select(DB::raw("urutan_minggu,
            SUM (CASE WHEN ASET.BAGIAN = '1' THEN 1 ELSE 0 END) as prw_mekanikal,
            SUM (CASE WHEN ASET.BAGIAN = '2' THEN 1 ELSE 0 END) as prw_elektrikal,
            SUM (CASE WHEN ASET.BAGIAN = '3' THEN 1 ELSE 0 END) as prw_instrumentasi,
            SUM (CASE WHEN ASET.BAGIAN = '4' THEN 1 ELSE 0 END) as prw_sipil,
            SUM (CASE WHEN ASET.BAGIAN = '1' AND TANGGAL_SELESAI IS NOT NULL THEN 1 ELSE 0 END) as prwaktual_mekanikal,
            SUM (CASE WHEN ASET.BAGIAN = '2' AND TANGGAL_SELESAI IS NOT NULL THEN 1 ELSE 0 END) as prwaktual_elektrikal,
            SUM (CASE WHEN ASET.BAGIAN = '3' AND TANGGAL_SELESAI IS NOT NULL THEN 1 ELSE 0 END) as prwaktual_instrumentasi,
            SUM (CASE WHEN ASET.BAGIAN = '4' AND TANGGAL_SELESAI IS NOT NULL THEN 1 ELSE 0 END) as prwaktual_sipil"
        ))
        ->join('PRW_52W', 'prw_4w.prw_52w_id', '=', 'prw_52w.id')
        ->join('aset', 'prw_52w.komponen_id', '=', 'aset.id')
        ->whereRaw("TO_CHAR(TANGGAL_MONITORING, 'YYYYMM') = $period")
        ->where('aset.instalasi_id', $instalasi)
        // ->where('prw_52w.tahun', $tahun)
        ->where('kondisi_id', '<>', '12')
        ->whereNotIn('prw_4w.status', config('custom.hideStatus'))
        ->groupBy('urutan_minggu')
        ->get();
        // end:Perawatan Rutin

        // Monitoring        
        $arrTemp = Ms4w::select(DB::raw("urutan_minggu,
            SUM (CASE WHEN ASET.BAGIAN = '1' THEN 1 ELSE 0 END) as monitoring_mekanikal,
            SUM (CASE WHEN ASET.BAGIAN = '2' THEN 1 ELSE 0 END) as monitoring_elektrikal,
            SUM (CASE WHEN ASET.BAGIAN = '3' THEN 1 ELSE 0 END) as monitoring_instrumentasi,
            SUM (CASE WHEN ASET.BAGIAN = '4' THEN 1 ELSE 0 END) as monitoring_sipil,
            SUM (CASE WHEN ASET.BAGIAN = '1' AND TANGGAL_SELESAI IS NOT NULL THEN 1 ELSE 0 END) as monitoringaktual_mekanikal,
            SUM (CASE WHEN ASET.BAGIAN = '2' AND TANGGAL_SELESAI IS NOT NULL THEN 1 ELSE 0 END) as monitoringaktual_elektrikal,
            SUM (CASE WHEN ASET.BAGIAN = '3' AND TANGGAL_SELESAI IS NOT NULL THEN 1 ELSE 0 END) as monitoringaktual_instrumentasi,
            SUM (CASE WHEN ASET.BAGIAN = '4' AND TANGGAL_SELESAI IS NOT NULL THEN 1 ELSE 0 END) as monitoringaktual_sipil"
        ))
        ->join('MS_52W', 'MS_4W.MS_52W_ID', '=', 'MS_52W.ID')
        ->join('ASET', 'MS_52W.KOMPONEN_ID', '=', 'ASET.ID')
        ->whereRaw("TO_CHAR(TANGGAL_MONITORING, 'YYYYMM') = $period")
        ->where('aset.instalasi_id', $instalasi)
        ->where('ms_52w.tahun', $tahun)
        ->where('kondisi_id', '<>', '12')
        ->whereNotIn('ms_4w.status', config('custom.hideStatus'))
        ->groupBy('urutan_minggu')
        ->get();
        // dd($arrTemp);
        // end:Monitoring

        $date = date('Y-m-d', strtotime($request->periode));
        $weeks = weekInMonth($date);
        // dd($date);
        // dd($arrTemp);

        // Bundle di urutan minggu
        foreach ($bagian as $bag) {
            $arrPrwJadwal = [];
            $arrPrwJadwalAkt = [];

            foreach ($arrTempPrw as $tempPrw) {
                if (in_array($tempPrw->urutan_minggu, $weeks)) {
                    $presentase = 0;
                    $varPrw = 'prw_'.strtolower($bag->name);
                    $varPrwAktual = 'prwaktual_'.strtolower($bag->name);
                    
                    if ($tempPrw->$varPrw > 0) {
                        $presentase = round($tempPrw->$varPrwAktual / $tempPrw->$varPrw, 2);
                    }

                    $arrPrwJadwal[] = $tempPrw->$varPrw;
                    $arrPrwJadwalAkt[] = $tempPrw->$varPrwAktual;

                    $arrData[$bag->name][$tempPrw->urutan_minggu]['prw'] = [
                        'peralatan' => $bag->name,
                        'minggu_ke' => $tempPrw->urutan_minggu,
                        'jadwal_prw' => $tempPrw->$varPrw,
                        'jadwal_prw_akt' => $tempPrw->$varPrwAktual,
                        'presentase' => $presentase
                    ];
                }
            }
            // dd($arrPrwJadwal);
            $totalPrwJadwal = array_sum($arrPrwJadwal);
            $totalPrwJadwalAkt = array_sum($arrPrwJadwalAkt);
            $totalPresentase = 0;
            if ($totalPrwJadwal > 0) {
                $totalPresentase = round($totalPrwJadwalAkt / $totalPrwJadwal, 2) * 100;
            }

            $arrData[$bag->name]['total']['prw'] = [
                'total_jadwal' => $totalPrwJadwal,
                'total_aktual' => $totalPrwJadwalAkt,
                'presentase' => $totalPresentase
            ];

            $arrJadwal = [];
            $arrJadwalAkt = [];
            foreach ($arrTemp as $temp) {
                if (in_array($temp->urutan_minggu, $weeks)) {
                    $presentase = 0;
                    $varMonitoring = 'monitoring_'.strtolower($bag->name);
                    $varMonitoringAktual = 'monitoringaktual_'.strtolower($bag->name);
                    
                    if ($temp->$varMonitoring > 0) {
                        $presentase = round($temp->$varMonitoringAktual / $temp->$varMonitoring, 2);
                    }

                    $arrJadwal[] = $temp->$varMonitoring;
                    $arrJadwalAkt[] = $temp->$varMonitoringAktual;

                    $arrData[$bag->name][$temp->urutan_minggu]['monitoring'] = [
                        'peralatan' => $bag->name,
                        'minggu_ke' => $temp->urutan_minggu,
                        'jadwal_monitoring' => $temp->$varMonitoring,
                        'jadwal_monitoring_akt' => $temp->$varMonitoringAktual,
                        'presentase' => $presentase
                    ];
                }
            }
// dd($arrTemp);
            $totalJadwal = array_sum($arrJadwal);
            $totalJadwalAkt = array_sum($arrJadwalAkt);
            $totalPresentase = 0;
            if ($totalJadwal > 0) {
                $totalPresentase = round($totalJadwalAkt / $totalJadwal, 2) * 100;
            }

            $arrData[$bag->name]['total']['monitoring'] = [
                'total_jadwal' => $totalJadwal,
                'total_aktual' => $totalJadwalAkt,
                'presentase' => $totalPresentase
            ];
        }
        // ./Bundle di urutan minggu

        // Bundle in case minggu yang kosong
        foreach ($bagian as $bag) {
            foreach ($weeks as $week) {
                if ( !isset($arrData[$bag->name][$week]) ) {
                    if (!isset($arrData[$bag->name][$week]['monitoring'])) {
                        $arrData[$bag->name][$week]['monitoring'] = self::penjadwalanDefault($bag->name, $week, 'monitoring');
                    }

                    if (!isset($arrData[$bag->name][$week]['prw'])) {
                        $arrData[$bag->name][$week]['prw'] = self::penjadwalanDefault($bag->name, $week, 'prw');
                    }
                } else {
                    if (!isset($arrData[$bag->name][$week]['monitoring'])) {
                        $arrData[$bag->name][$week]['monitoring'] = self::penjadwalanDefault($bag->name, $week, 'monitoring');
                    }

                    if (!isset($arrData[$bag->name][$week]['prw'])) {
                        $arrData[$bag->name][$week]['prw'] = self::penjadwalanDefault($bag->name, $week, 'prw'); 
                    }
                }
            }
        }
        // ./Bundle in case minggu yang kosong
// dd($arrData);
        $lokasi = Instalasi::find($request->instalasi);

        $pdf = PDF::loadView('pages.laporan.evaluasi.penjadwalan', ['arrData' => $arrData, 
            'periode' => isset($request->periode)?str_replace("-", " ", $request->periode):'',
            'lokasi' => $lokasi,
            'weeks' => $weeks,
            'bagian' => $bagian,
        ]);

        return $pdf->stream(sprintf('Perhitungan-Efektivitas-Penjadwalan-%s.pdf', date('Y-m')));
    }

    private static function penjadwalanDefault($bag, $week, $wo)
    {
        return [
            'peralatan' => $bag,
            'minggu_ke' => $week,
            'jadwal_'.$wo => 0,
            'jadwal_'.$wo.'_akt' => 0,
            'presentase' => 0
        ];    
        
    }

    public function getMstrategi()
    {
        $instalasi = Instalasi::whereIn('id', lokasi())->get()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Semua Lokasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        return view('pages.flaporan.evaluasi.mstrategi', [
            'instalasi' => $instalasi
        ]);
    }

    public function laporanMstrategi(Request $request)
    {
        $period = date("Ym", strtotime($request->periode));
        $tahun = date("Y", strtotime($request->periode));
        $bagian = Master::where('kelompok', 'BAGIAN')->get();
        $instalasi = $request->instalasi;
        $arrData = [];
        $arrTemp = [];
        $arrTempPrw = [];
        $arrPrw = [];

        $spv = self::getSpv($instalasi, 3);;
        foreach ($bagian as $bag) {
        	// Monitoring
            $arrTemp[$bag->name]['monitoring'] = Ms4w::whereHas('ms52w', function($query) use($bag, $instalasi, $tahun){
                    $query->where('tahun', $tahun);
                    $query->whereHas('komponen', function($query2) use($bag, $instalasi){
                        $query2->where('bagian', $bag->id);
                        $query2->where('instalasi_id', $instalasi);
                    });
                })
                ->where('status', '1')
                ->whereRaw("TO_CHAR(TANGGAL_MONITORING, 'YYYYMM') = $period")
                ->select(DB::raw('count(*) monitoring'))
                ->first();

            // Perawatan Rutin
            $arrTemp[$bag->name]['prwrutin'] = Prw4w::whereHas('prw52w', function($query) use($bag, $instalasi, $tahun){
                    $query->where('tahun', $tahun);
                    $query->whereHas('komponen', function($query2) use($bag, $instalasi){
                        $query2->where('bagian', $bag->id);
                        $query2->where('instalasi_id', $instalasi);
                    });
                })
                ->where('status', '1')
                ->whereRaw("TO_CHAR(TANGGAL_MONITORING, 'YYYYMM') = $period")
                ->select(DB::raw('count(*) prwrutin'))
                ->first();

            // Perbaikan
            $arrTemp[$bag->name]['perbaikan'] = Perbaikan::whereHas('komponen', function($query2) use($bag, $instalasi){
                    $query2->where('bagian', $bag->id);
                    $query2->where('instalasi_id', $instalasi);
                })
                ->where('status', '<>', '99')
                ->whereRaw("TO_CHAR(TANGGAL, 'YYYYMM') = $period")
                ->select(DB::raw('count(*) perbaikan'))
                ->first();

            // Perawatan
            $arrTemp[$bag->name]['perawatan'] = Perawatan::whereHas('komponen', function($query2) use($bag, $instalasi){
                    $query2->where('bagian', $bag->id);
                    $query2->where('instalasi_id', $instalasi);
                })
                ->where('status', '<>', '99')
                ->whereRaw("TO_CHAR(TANGGAL, 'YYYYMM') = $period")
                ->select(DB::raw('count(*) perawatan'))
                ->first();

            if (in_array($bag->id, ['1', '2', '4'])) {
                $arrTemp[$bag->name]['monitoring-detail']=self::utamaPendukung('monitoring', $bag->id, $instalasi, $period, $tahun);
                $arrTemp[$bag->name]['prwrutin-detail']=self::utamaPendukung('prwrutin', $bag->id, $instalasi, $period, $tahun);
                $arrTemp[$bag->name]['perbaikan-detail'] = self::utamaPendukung('perbaikan', $bag->id, $instalasi, $period);
                $arrTemp[$bag->name]['perawatan-detail'] = self::utamaPendukung('perawatan', $bag->id, $instalasi, $period);
            }
        }
// dd($arrTemp);
        $i = 1;
        foreach ($bagian as $bag) {
            // $temp['perbaikan'] = $arrTemp[$bag->name]['perbaikan']->perbaikan + $arrTemp[$bag->name]['aduan']->aduan;
        	$temp['perbaikan'] = $arrTemp[$bag->name]['perbaikan']->perbaikan;
        	$temp['perawatan'] = $arrTemp[$bag->name]['perawatan']->perawatan;
            $temp['monitoring'] = $arrTemp[$bag->name]['monitoring']->monitoring;
        	$temp['prwrutin'] = $arrTemp[$bag->name]['prwrutin']->prwrutin;
        	$total = array_sum($temp); 
        	$rasio = $total-$temp['perbaikan'];

        	$tempPrs['perbaikanPr'] = round(pembagian($temp['perbaikan'],$total), 3) * 100;
        	$tempPrs['perawatanPr'] = round(pembagian($temp['perawatan'],$total), 3) * 100;
            $tempPrs['monitoringPr'] = round(pembagian($temp['monitoring'],$total), 3) * 100;
        	$tempPrs['prwrutinPr'] = round(pembagian($temp['prwrutin'],$total), 3) * 100;
        	$totalPrs = round(array_sum($tempPrs), 2); 
        	$rasioPrs = round($totalPrs-$tempPrs['perbaikanPr'], 2);

            // Detail
            $tempMain = [];
            $tempPrsMain = [];
            $totalPrsMain = "";
            $rasioPrsMain = "";

            $tempSupport = [];
            $tempPrsSupport = [];
            $totalPrsSupport = "";
            $rasioPrsSupport = "";
            if (in_array($bag->id, ['1', '2', '4'])) {
                // Main
                $tempMain['perbaikan'] = $arrTemp[$bag->name]['perbaikan-detail']->utama;
                $tempMain['perawatan'] = $arrTemp[$bag->name]['perawatan-detail']->utama;
                $tempMain['monitoring'] = $arrTemp[$bag->name]['monitoring-detail']->utama;
                $tempMain['prwrutin'] = $arrTemp[$bag->name]['prwrutin-detail']->utama;
                $totalMain = array_sum($tempMain); 
                $rasioMain = $totalMain-$tempMain['perbaikan'];

                $tempPrsMain['perbaikanPr'] = round(pembagian($tempMain['perbaikan'],$totalMain), 3) * 100;
                $tempPrsMain['perawatanPr'] = round(pembagian($tempMain['perawatan'],$totalMain), 3) * 100;
                $tempPrsMain['monitoringPr'] = round(pembagian($tempMain['monitoring'],$totalMain), 3) * 100;
                $tempPrsMain['prwrutinPr'] = round(pembagian($tempMain['prwrutin'],$totalMain), 3) * 100;
                $totalPrsMain = round(array_sum($tempPrsMain), 2); 
                $rasioPrsMain = round($totalPrsMain-$tempPrsMain['perbaikanPr'], 2);
                // end:Main

                // Support
                $tempSupport['perbaikan'] = $arrTemp[$bag->name]['perbaikan-detail']->pendukung;
                $tempSupport['perawatan'] = $arrTemp[$bag->name]['perawatan-detail']->pendukung;
                $tempSupport['monitoring'] = $arrTemp[$bag->name]['monitoring-detail']->pendukung;
                $tempSupport['prwrutin'] = $arrTemp[$bag->name]['prwrutin-detail']->pendukung;
                $totalSupport = array_sum($tempSupport); 
                $rasioSupport = $totalSupport-$tempSupport['perbaikan'];

                $tempPrsSupport['perbaikanPr'] = round(pembagian($tempSupport['perbaikan'],$totalSupport), 3) * 100;
                $tempPrsSupport['perawatanPr'] = round(pembagian($tempSupport['perawatan'],$totalSupport), 3) * 100;
                $tempPrsSupport['monitoringPr'] = round(pembagian($tempSupport['monitoring'],$totalSupport), 3) * 100;
                $tempPrsSupport['prwrutinPr'] = round(pembagian($tempSupport['prwrutin'],$totalSupport), 3) * 100;
                $totalPrsSupport = round(array_sum($tempPrsSupport), 2); 
                $rasioPrsSupport = round($totalPrsSupport-$tempPrsSupport['perbaikanPr'], 2);
                // end:Support
            }
            // end:Detail

            $arrData[$bag->name] = [
            	'no' => $i,
                'bagian' => ucfirst(strtolower($bag->name)),
            	'bagian_id' => $bag->id,

                'perbaikan' => $temp['perbaikan'],
                'perawatan' => $temp['perawatan'],
                'monitoring' => $temp['monitoring'],
                'prwrutin' => $temp['prwrutin'],
                'total' => $total,
                'rasio' => $rasio,
                // Detail
                'perbaikanUtama' => isset($tempMain['perbaikan'])?$tempMain['perbaikan']:0,
                'perawatanUtama' => isset($tempMain['perawatan'])?$tempMain['perawatan']:0,
                'monitoringUtama' => isset($tempMain['monitoring'])?$tempMain['monitoring']:0,
                'prwrutinUtama' => isset($tempMain['prwrutin'])?$tempMain['prwrutin']:0,
                'totalUtama' => !empty($totalMain)?$totalMain:0,
                'rasioUtama' => !empty($rasioMain)?$rasioMain:0,

                'perbaikanPendukung' => isset($tempSupport['perbaikan'])?$tempSupport['perbaikan']:0,
                'perawatanPendukung' => isset($tempSupport['perawatan'])?$tempSupport['perawatan']:0,
                'monitoringPendukung' => isset($tempSupport['monitoring'])?$tempSupport['monitoring']:0,
                'prwrutinPendukung' => isset($tempSupport['prwrutin'])?$tempSupport['prwrutin']:0,
                'totalPendukung' => !empty($totalSupport)?$totalSupport:0,
                'rasioPendukung' => !empty($rasioSupport)?$rasioSupport:0,
                // end:Detail

                'perbaikanPr' => $tempPrs['perbaikanPr'],
                'perawatanPr' => $tempPrs['perawatanPr'],
                'monitoringPr' => $tempPrs['monitoringPr'],
                'prwrutinPr' => $tempPrs['prwrutinPr'],
                'totalPr' => $totalPrs,
                'rasioPr' => $rasioPrs,
                // Detail
                'perbaikanPrUtama' => isset($tempPrsMain['perbaikanPr'])?$tempPrsMain['perbaikanPr']:0,
                'perawatanPrUtama' => isset($tempPrsMain['perawatanPr'])?$tempPrsMain['perawatanPr']:0,
                'monitoringPrUtama' => isset($tempPrsMain['monitoringPr'])?$tempPrsMain['monitoringPr']:0,
                'prwrutinPrUtama' => isset($tempPrsMain['prwrutinPr'])?$tempPrsMain['prwrutinPr']:0,
                'totalPrUtama' => !empty($totalPrsMain)?$totalPrsMain:0,
                'rasioPrUtama' => !empty($rasioPrsMain)?$rasioPrsMain:0,

                'perbaikanPrPendukung' => isset($tempPrsSupport['perbaikanPr'])?$tempPrsSupport['perbaikanPr']:0,
                'perawatanPrPendukung' => isset($tempPrsSupport['perawatanPr'])?$tempPrsSupport['perawatanPr']:0,
                'monitoringPrPendukung' => isset($tempPrsSupport['monitoringPr'])?$tempPrsSupport['monitoringPr']:0,
                'prwrutinPrPendukung' => isset($tempPrsSupport['prwrutinPr'])?$tempPrsSupport['prwrutinPr']:0,
                'totalPrPendukung' => !empty($totalPrsSupport)?$totalPrsSupport:0,
                'rasioPrPendukung' => !empty($rasioPrsSupport)?$rasioPrsSupport:0
                // end:Detail
            ];
            $i++;     
        }

        $lokasi = Instalasi::find($request->instalasi);
// dd($arrData);
        $pdf = PDF::loadView('pages.laporan.evaluasi.mstrategi', ['data' => $arrData, 
            'periode' => isset($request->periode)?str_replace("-", " ", $request->periode):'',
            'lokasi' => $lokasi
        ]);

        return $pdf->stream(sprintf('Perhitungan-Efektivitas-Manajemen-Strategi-%s.pdf', date('Y-m')));
    }

    private static function utamaPendukung($wo, $bag, $instalasi, $period, $tahun = null)
    {
        switch ($wo) {
            case 'perbaikan':
                $data = Perbaikan::join('aset', 'prb_data.komponen_id', '=', 'aset.id')
                    ->where('status', '<>', '99')
                    ->whereRaw("TO_CHAR(TANGGAL, 'YYYYMM') = $period");
                break;
            case 'perawatan':
                $data = Perawatan::join('aset', 'prw_data.komponen_id', '=', 'aset.id')
                    ->where('status', '<>', '99')
                    ->whereRaw("TO_CHAR(TANGGAL, 'YYYYMM') = $period");
                break;
            case 'monitoring':
                $data = Ms4w::join('MS_52W', 'ms_4w.ms_52w_id', '=', 'ms_52w.id')
                    ->join('aset', 'ms_52w.komponen_id', '=', 'aset.id')
                    ->where('status', '1')
                    ->whereRaw("TO_CHAR(TANGGAL_MONITORING, 'YYYYMM') = $period")
                    ->where('ms_52w.tahun', $tahun);
                break;
            case 'prwrutin':
                $data = Prw4w::join('PRW_52W', 'prw_4w.prw_52w_id', '=', 'prw_52w.id')
                    ->join('aset', 'prw_52w.komponen_id', '=', 'aset.id')
                    ->where('status', '1')
                    ->whereRaw("TO_CHAR(TANGGAL_MONITORING, 'YYYYMM') = $period")
                    ->where('prw_52w.tahun', $tahun);
                break;
        }

        $data = $data->where('aset.bagian', $bag)
            ->where('aset.instalasi_id', $instalasi)
            ->select(DB::raw("SUM
            (CASE 
                WHEN aset.kategori_id = '1' THEN 1
                ELSE 0
                END
            ) AS utama, 
            SUM
            (CASE 
                WHEN aset.kategori_id <> '1' THEN 1
                ELSE 0
                END
            ) AS pendukung"))
            ->first();

        return $data;
    }

    private static function getSpv($instalasi, $bagian)
    {
        $mbagian = "030";

        if ($bagian == "4") $mbagian = "031";

        $masterjab = Masterjab::where('lokasi', 'like', '%'.$instalasi.'%')
             ->where('bagian', 'like', '%'.$bagian.'%')
             ->whereNotIn('nip', ['10801498                      ', '10901554                      '])
             ->get();
         
        $arrNip = [];
        if (!empty($masterjab)) {
            foreach ($masterjab as $row) {
                $arrNip[] = $row->nip;
            }

            $dataSpv = Role::join('tu_jabatan', 'tu_roleuser.recidrole', '=', 'tu_jabatan.recidjabatan')
                ->select('tu_roleuser.nip', 'tu_roleuser.nama', 'tu_jabatan.kodejabatan', 'tu_jabatan.namajabatan', 'tu_jabatan.parentjab')
                ->whereIn('tu_roleuser.nip', $arrNip)
                ->where('tu_roleuser.is_manajer', '1')
                ->where('trim(tu_jabatan.parentjab)', trim($mbagian))
                ->first();

            if (!empty($dataSpv)) {
                return $dataSpv->nip;
            } else {
                return null;
            }
        }

        return null;
    }

    public function getTindakan()
    {
        $instalasi = Instalasi::whereIn('id', lokasi())->get()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Semua Lokasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        return view('pages.flaporan.evaluasi.tindakan', [
            'instalasi' => $instalasi
        ]);
    }

    public function laporanTindakan(Request $request)
    {
        $period = date("Ym", strtotime($request->periode));
        $bagian = Master::where('kelompok', 'BAGIAN')->get();
        $instalasi = $request->instalasi;
        $arrData = [];
        $arrTemp = [];
        $arrTempPrw = [];
        $arrPrw = [];
        $date = date('Y-m-d', strtotime($request->periode));
        $weeks = weekInMonth($date);

        $arrBagian = [];
        foreach ($bagian as $bag) {
            $arrBagian[$bag->id] = $bag->name;
        }

        // Perbaikan
        $arrPrb = Perbaikan::select(DB::raw("SUM(
            CASE
                WHEN aset.BAGIAN = '1' THEN 1
                ELSE 0
                END
            ) AS mekanikal,
            SUM(
                CASE
                    WHEN aset.BAGIAN = '2' THEN 1
                    ELSE 0
                    END
            ) AS elektrikal,
            SUM(
                CASE
                    WHEN aset.BAGIAN = '3' THEN 1
                    ELSE 0
                    END
            ) AS instrumentasi,
            SUM(
                CASE
                    WHEN aset.BAGIAN = '4' THEN 1
                    ELSE 0
                    END
            ) AS sipil,
            SUM(
                CASE
                    WHEN aset.BAGIAN = '1' AND (permohonan_sc.ID IS NOT NULL AND permohonan_Sc.STATUS = 'waiting-list') THEN 1
                    ELSE 0
                    END
            ) AS sc_mekanikal,
            SUM(
                CASE
                    WHEN aset.BAGIAN = '2' AND (permohonan_sc.ID IS NOT NULL AND permohonan_Sc.STATUS = 'waiting-list') THEN 1
                    ELSE 0
                    END
            ) AS sc_elektrikal,
            SUM(
                CASE
                    WHEN aset.BAGIAN = '3' AND (permohonan_sc.ID IS NOT NULL AND permohonan_Sc.STATUS = 'waiting-list') THEN 1
                    ELSE 0
                    END
            ) AS sc_instrumentasi,
            SUM(
                CASE
                    WHEN aset.BAGIAN = '4' AND (permohonan_sc.ID IS NOT NULL AND permohonan_Sc.STATUS = 'waiting-list') THEN 1
                    ELSE 0
                    END
            ) AS sc_sipil"))
            ->join('aset', 'prb_data.komponen_id', '=', 'aset.id')
            ->leftJoin('permohonan_sc', 'prb_data.id', '=', 'permohonan_sc.prb_data_id')
            ->whereRaw("TO_CHAR(prb_data.TANGGAL, 'YYYYMM') = $period")
            ->where('aset.instalasi_id', $instalasi)
            ->where('prb_data.status', '<>', '99')
            ->first();
// dd($arrPrb);
        // Perawatan
        $arrPrw = Perawatan::select(DB::raw("SUM(
            CASE
                WHEN aset.BAGIAN = '1' THEN 1
                ELSE 0
                END
            ) AS mekanikal,
            SUM(
                CASE
                    WHEN aset.BAGIAN = '2' THEN 1
                    ELSE 0
                    END
            ) AS elektrikal,
            SUM(
                CASE
                    WHEN aset.BAGIAN = '4' THEN 1
                    ELSE 0
                    END
            ) AS sipil,
            SUM(
                CASE
                    WHEN aset.BAGIAN = '1' AND (permohonan_sc.ID IS NOT NULL AND permohonan_Sc.STATUS = 'waiting-list') THEN 1
                    ELSE 0
                    END
            ) AS sc_mekanikal,
            SUM(
                CASE
                    WHEN aset.BAGIAN = '2' AND (permohonan_sc.ID IS NOT NULL AND permohonan_Sc.STATUS = 'waiting-list') THEN 1
                    ELSE 0
                    END
            ) AS sc_elektrikal,
            SUM(
                CASE
                    WHEN aset.BAGIAN = '4' AND (permohonan_sc.ID IS NOT NULL AND permohonan_Sc.STATUS = 'waiting-list') THEN 1
                    ELSE 0
                    END
            ) AS sc_sipil"))
            ->join('aset', 'prw_data.komponen_id', '=', 'aset.id')
            ->leftJoin('permohonan_sc', 'prw_data.id', '=', 'permohonan_sc.prw_data_id')
            ->whereRaw("TO_CHAR(prw_data.TANGGAL, 'YYYYMM') = $period")
            ->where('aset.instalasi_id', $instalasi)
            ->where('prw_data.status', '<>', '99')
            ->first();        
        // dd($arrPrw);
        // Perawatan Rutin
        $arrPrwRutin = Prw4w::select(DB::raw("SUM(
            CASE
                WHEN aset.BAGIAN = '1' THEN 1
                ELSE 0
                END
            ) AS mekanikal,
            SUM(
                CASE
                    WHEN aset.BAGIAN = '2' THEN 1
                    ELSE 0
                    END
            ) AS elektrikal,
            SUM(
                CASE
                    WHEN aset.BAGIAN = '1' AND (permohonan_sc.ID IS NOT NULL AND permohonan_Sc.STATUS = 'waiting-list') THEN 1
                    ELSE 0
                    END
            ) AS sc_mekanikal,
            SUM(
                CASE
                    WHEN aset.BAGIAN = '2' AND (permohonan_sc.ID IS NOT NULL AND permohonan_Sc.STATUS = 'waiting-list') THEN 1
                    ELSE 0
                    END
            ) AS sc_elektrikal"))
            ->join('PRW_52W', 'prw_4w.PRW_52W_ID', '=', 'PRW_52W.ID')
            ->join('aset', 'PRW_52W.komponen_id', '=', 'aset.id')            
            ->leftJoin('permohonan_sc', 'PRW_4W.id', '=', 'permohonan_sc.prw_rutin_id')
            ->whereRaw("TO_CHAR(PRW_4W.TANGGAL_MONITORING, 'YYYYMM') = $period")
            ->where('aset.instalasi_id', $instalasi)
            ->where('PRW_4W.status', '<>', '99')
            ->first();

        $lokasi = Instalasi::find($request->instalasi);

        $pdf = PDF::loadView('pages.laporan.evaluasi.tindakan', ['arrPrw' => $arrPrw, 
            'arrPrb' => $arrPrb, 
            'arrPrw' => $arrPrw, 
            'arrPrwRutin' => $arrPrwRutin, 
            'periode' => isset($request->periode)?str_replace("-", " ", $request->periode):'',
            'lokasi' => $lokasi,
            'bagian' => $arrBagian,
        ]);

        return $pdf->stream(sprintf('Perhitungan-Pelaksanaan-Perawatan-Perbaikan-%s.pdf', date('Y-m')));
    }

    public function getEfektifitasJam()
    {
        $instalasi = Instalasi::whereIn('id', lokasi())->get()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Semua Lokasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        return view('pages.flaporan.evaluasi.effJam', [
            'instalasi' => $instalasi
        ]);
    }

    public function laporanEfektifitasJam(Request $request)
    {
        //mei 18-22
        $period = date("Ym", strtotime($request->periode));
        $tahun = date("Y", strtotime($request->periode));
        $bagian = Master::where('kelompok', 'BAGIAN')->get();
        $instalasi = $request->instalasi;
        $arrData = [];
        $arrTemp = [];
        $arrTempPrb = [];
        $arrPrb = [];

        foreach ($bagian as $bag) {
        	// Monitoring
            $arrTemp[$bag->name] = Ms4w::whereHas('ms52w', function($query) use($bag, $instalasi, $tahun){
                    $query->where('tahun', $tahun);
                    $query->whereHas('komponen', function($query2) use($bag, $instalasi){
                        $query2->where('bagian', $bag->id);
                        $query2->where('instalasi_id', $instalasi);
                    });
                })
                ->whereRaw("TO_CHAR(TANGGAL_MONITORING, 'YYYYMM') = $period")
                ->groupBy('urutan_minggu')
                ->select(DB::raw('urutan_minggu, count(*) monitoring,
            sum(CASE WHEN status = 1 THEN 1 ELSE 0 END) monitoring_aktual'))
                ->orderBy('urutan_minggu')
                ->get();
        }

        $date = date('Y-m-d', strtotime($request->periode));
        $weeks = weekInMonth($date);
// dd($arrTempPrb);
        // Bundle di urutan minggu
        foreach ($arrTemp as $key => $row) {
            foreach ($row as $value) {
                if (in_array($value->urutan_minggu, $weeks)) {
                    $arrData[$key][$value->urutan_minggu] = [
                        'peralatan' => $key,
                        'minggu_ke' => $value->urutan_minggu,
                        'jadwal_monitoring' => $value->monitoring,
                        'jadwal_monitoring_akt' => $value->monitoring_aktual,
                        'total' => $value->monitoring_aktual,
                        'presentase' => '100'
                    ];
                }
            }
        }
        // ./Bundle di urutan minggu

        // Bundle in case minggu yang kosong
        foreach ($bagian as $bag) {
            foreach ($weeks as $week) {
                if (!isset($arrData[$bag->name][$week])) {
                    $arrData[$bag->name][$week] = [
                        'peralatan' => $bag->name,
                        'minggu_ke' => $week,
                        'jadwal_monitoring' => 0,
                        'jadwal_monitoring_akt' => 0,
                        'total' => 0,
                        'presentase' => 0
                    ];    
                }
            }
        }
        // ./Bundle in case minggu yang kosong

        $lokasi = Instalasi::find($request->instalasi);
        $bagian = Master::bagian()->get();

        $date = date('Y-m-d', strtotime($request->periode));
        $weeks = weekInMonth($date);

        $pdf = PDF::loadView('pages.laporan.evaluasi.effJam', ['arrData' => $arrData, 
            'periode' => isset($request->periode)?str_replace("-", " ", $request->periode):'',
            'lokasi' => $lokasi,
            'weeks' => $weeks,
            'bagian' => $bagian,
        ]);

        return $pdf->stream(sprintf('Perhitungan-Efektivitas-Jam-Orang-%s.pdf', date('Y-m')));
    }

    public function getRealisasiPemeliharaan()
    {
        $instalasi = Instalasi::whereIn('id', lokasi())->get()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Semua Lokasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        return view('pages.flaporan.evaluasi.realPemeliharaan', [
            'instalasi' => $instalasi
        ]);
    }

    public function laporanRealisasiPemeliharaan(Request $request)
    {
        $period = date("Ym", strtotime($request->periode));
        $bagian = Master::where('kelompok', 'BAGIAN')->get();
        $instalasi = $request->instalasi;
        $arrData = [];
        $arrTemp = [];
        $arrTempPrw = [];
        $arrPrw = [];

        foreach ($bagian as $bag) {
            // Perbaikan
            $arrPrb[$bag->name] = Perbaikan::whereHas('komponen', function($query2) use($bag, $instalasi){
                        $query2->where('bagian', $bag->id);
                        $query2->where('instalasi_id', $instalasi);
                })
                // ->join('ms_4w', 'prb_data.ms_4w_id', '=', 'ms_4w.id')
                ->whereRaw("TO_CHAR(prb_data.TANGGAL, 'YYYYMM') = $period")
                ->where('prb_data.status', '<>', '99')
                ->select(DB::raw("to_char(prb_data.tanggal,'WW') as urutan_minggu, count(prb_data.id) perbaikan, sum(CASE WHEN prb_data.status = 0 THEN 0 ELSE 1 END) respon_perbaikan, sum(CASE WHEN prb_data.status = 10 THEN 1 ELSE 0 END) perbaikan_selesai"))
                ->groupBy(DB::raw("to_char(prb_data.tanggal,'WW')"))
                ->orderBy(DB::raw("to_char(prb_data.tanggal,'WW')"))
                ->get();
/*dd($arrTemp[$bag->name]['perbaikan']);*/
            // Perawatan
            $arrPrw[$bag->name] = Perawatan::whereHas('komponen', function($query2) use($bag, $instalasi){
                        $query2->where('bagian', $bag->id);
                        $query2->where('instalasi_id', $instalasi);
                })
                ->join('ms_4w', 'prw_data.ms_4w_id', '=', 'ms_4w.id')
                ->whereRaw("TO_CHAR(prw_data.TANGGAL, 'YYYYMM') = $period")
                ->where('prw_data.status', '<>', '99')
                ->select(DB::raw("to_char(prw_data.tanggal,'WW') as urutan_minggu, count(prw_data.id) perawatan, sum(CASE WHEN prw_data.status = 0 THEN 0 ELSE 1 END) respon_perawatan, sum(CASE WHEN prw_data.status = 10 THEN 1 ELSE 0 END) perawatan_selesai"))
                /*->groupBy('ms_4w.urutan_minggu')
                ->orderBy('ms_4w.urutan_minggu')*/
                ->groupBy(DB::raw("to_char(prw_data.tanggal,'WW')"))
                ->orderBy(DB::raw("to_char(prw_data.tanggal,'WW')"))
                ->get();
        }
// dd($arrPrb);
        $date = date('Y-m-d', strtotime($request->periode));
        $weeks = weekInMonth($date);

        // Bundle di urutan minggu
        foreach ($arrPrw as $key => $row) {
            foreach ($row as $value) {
                if (in_array($value->urutan_minggu, $weeks)) {
                    $arrPrwData[$key][$value->urutan_minggu] = [
                        'peralatan' => $key,
                        'minggu_ke' => $value->urutan_minggu,
                        'perawatan' => $value->perawatan,
                        'respon_perawatan' => $value->respon_perawatan,
                        'perawatan_selesai' => $value->perawatan_selesai,
                        'respon' => ($value->respon_perawatan / $value->perawatan) * 100,
                        'penyelesaian' => ($value->perawatan_selesai / $value->perawatan) * 100,
                    ];
                }
            }
        }

        foreach ($arrPrb as $key => $row) {
            foreach ($row as $value) {
                if (in_array($value->urutan_minggu, $weeks)) {
                    $arrPrbData[$key][$value->urutan_minggu] = [
                        'peralatan' => $key,
                        'minggu_ke' => $value->urutan_minggu,
                        'perbaikan' => $value->perbaikan,
                        'respon_perbaikan' => $value->respon_perbaikan,
                        'perbaikan_selesai' => $value->perbaikan_selesai,
                        'respon' => ($value->respon_perbaikan / $value->perbaikan) * 100,
                        'penyelesaian' => ($value->perbaikan_selesai / $value->perbaikan) * 100,
                    ];
                }
            }
        }
        // ./Bundle di urutan minggu

        // Bundle in case minggu yang kosong
        foreach ($bagian as $bag) {
            foreach ($weeks as $week) {
                if (!isset($arrPrwData[$bag->name][$week])) {
                    $arrPrwData[$bag->name][$week] = [
                        'peralatan' => $bag->name,
                        'minggu_ke' => $week,
                        'perawatan' => 0,
                        'respon_perawatan' => 0,
                        'perawatan_selesai' => 0,
                        'respon' => 0,
                        'penyelesaian' => 0,
                    ];    
                }
                if (!isset($arrPrbData[$bag->name][$week])) {
                    $arrPrbData[$bag->name][$week] = [
                        'peralatan' => $bag->name,
                        'minggu_ke' => $week,
                        'perbaikan' => 0,
                        'respon_perbaikan' => 0,
                        'perbaikan_selesai' => 0,
                        'respon' => 0,
                        'penyelesaian' => 0,
                    ];    
                }
            }
        }
        // ./Bundle in case minggu yang kosong

        $lokasi = Instalasi::find($request->instalasi);
        $bagian = Master::bagian()->get();

        $date = date('Y-m-d', strtotime($request->periode));
        $weeks = weekInMonth($date);

        $pdf = PDF::loadView('pages.laporan.evaluasi.realPemeliharaan', ['arrPrb' => $arrPrbData,
            'arrPrw' => $arrPrwData,
            'periode' => isset($request->periode)?str_replace("-", " ", $request->periode):'',
            'lokasi' => $lokasi,
            'weeks' => $weeks,
            'bagian' => $bagian
        ])->setPaper('a4', 'landscape');

        return $pdf->stream(sprintf('Efektivitas-Realisasi-Pemeliharaan-%s.pdf', date('Y-m')));
    }

    public function getInvestasi()
    {
        // dd(date('Y'));
        $instalasi = Instalasi::whereIn('id', lokasi())->get()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Pilih Lokasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        $bagian = Master::bagian()->get()->pluck('name', 'kode')->toArray();
        $labelBagian = ["" => "-             Pilih Bagian             -"];
        $bagian = $labelBagian + $bagian;

        return view('pages.flaporan.evaluasi.investasi', [
            'instalasi' => $instalasi,
            'bagian' => $bagian
        ]);
    }

    public function laporanInvestasi(Request $request)
    {
        $equipment = Aset::whereNull('equipment_id')
            ->where('instalasi_id', $request->instalasi)
            ->where('bagian', $request->bagian)
            ->where('kondisi_id', '<>', '12')
            // ->limit(10)
            ->get();

        $komponen = Aset::whereNotNull('equipment_id')
            ->where('instalasi_id', $request->instalasi)
            ->where('kondisi_id', '<>', '12')
            ->where('bagian', $request->bagian)
            ->get();
// dd($equipment);
        $lokasi = Instalasi::find($request->instalasi);
        $bagian = Master::find($request->bagian);

        foreach ($equipment as $row) {
            $rr = $this->replaceRefurbish($row);
                if (empty($row->tgl_overhaul)) {
                    $intervensi = "Pemasangan Awal";
                    $tgl_intervensim = date('M Y', strtotime($row->tgl_pasang));
                } else {
                    $intervensi = "Overhaul";
                    $tgl_intervensim = date('M Y', strtotime($row->tgl_overhaul));
                }

                if (!empty($row->tgl_pasang)) {
                    $tglPasang = date('M Y', strtotime($row->tgl_pasang));
                } else {
                    $tglPasang = "-";
                }

                $result[] = [
                    'equipment' => $row->nama_aset,
                    'komponen' => $row->nama_aset,
                    'tgl_pasang' => $tglPasang,
                    'intervensi' => $intervensi,
                    'tgl_intervensim' => $tgl_intervensim,
                    'rr' => $rr,
                ];

                foreach ($komponen as $detail) {
                    if (empty($row->tgl_overhaul)) {
                        $intervensi = "Pemasangan Awal";
                        $tgl_intervensim = date('M Y', strtotime($row->tgl_pasang));
                    } else {
                        $intervensi = "Overhaul";
                        $tgl_intervensim = date('M Y', strtotime($row->tgl_overhaul));
                    }

                    if ($detail->equipment_id == $row->id) {
                        $rr = $this->replaceRefurbish($row);

                        if (!empty($detail->tgl_pasang)) {
                            $tglPasang = date('M Y', strtotime($detail->tgl_pasang));
                        } else {
                            $tglPasang = "-";
                        }

                        $result[] = [
                            'equipment' => "",
                            'komponen' => $detail->nama_aset,
                            'tgl_pasang' => $tglPasang,
                            'intervensi' => $intervensi,
                            'tgl_intervensim' => $tgl_intervensim,
                            'rr' => $rr,
                        ];
                    }
                }
            // }
        }
        // dd($result);

        $pdf = PDF::loadView('pages.laporan.evaluasi.investasi', ['equipment' => $equipment, 
            'periode' => date('Y'),
            'komponen' => $komponen,
            'lokasi' => $lokasi,
            'bagian' => $bagian,
            'result' => $result
        ])->setPaper('a4', 'landscape');

        return $pdf->stream(sprintf('Usulan-Perencanaan-Investasi-%s.pdf', date('Y-m')));
    }

    private function replaceRefurbish($row)
    {
        $end = date('Y-m-d');
        $start = strtotime($end.' -1 year');

        $dateVerify = date('Y', strtotime($row->verify_ts));
        $dateStart = date('Y', strtotime($end));

        $umur = $dateStart-$dateVerify;
        $param = EvaluasiAset::getParam($row->kode_fm);
        $kb = isset($param['kode_bobot'])?$param['kode_bobot']:"";
        $ue = isset($param['ue'])?$param['ue']:"";

        // rata-rata
        $rata = EvaluasiAset::getRata($kb,
                        $ue, 
                        $row->jmlperawatan, 
                        $row->jmlperbaikan);
        $rata = round($rata);

        // hasil akhir
        $ha = EvaluasiAset::getHasil($rata);
        /*if ($ha == "Normal") 
            $ha = "Pemasangan Awal";*/

        return $ha;
    }

    public function getPrioritas()
    {
        // dd(date('Y'));
        $instalasi = Instalasi::whereIn('id', lokasi())->get()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Pilih Lokasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        $bagian = Master::bagian()->get()->pluck('name', 'kode')->toArray();
        $labelBagian = ["" => "-             Pilih Bagian             -"];
        $bagian = $labelBagian + $bagian;

        return view('pages.flaporan.evaluasi.prioritas', [
            'instalasi' => $instalasi,
            'bagian' => $bagian
        ]);
    }

    public function laporanPrioritas(Request $request)
    {
        $equipment = Aset::whereNull('equipment_id')
            ->where('instalasi_id', $request->instalasi)
            ->where('bagian', $request->bagian)
            ->where('kondisi_id', '<>', '12')
            ->limit(10)
            ->get();

        $komponen = Aset::whereNotNull('equipment_id')
            ->where('instalasi_id', $request->instalasi)
            ->where('kondisi_id', '<>', '12')
            ->where('bagian', $request->bagian)
            ->get();

        $lokasi = Instalasi::find($request->instalasi);
        $bagian = Master::find($request->bagian);

        foreach ($equipment as $row) {
            // dd($row->tgl_pasang);
            $rr = $this->replaceRefurbish($row);
                if (empty($row->tgl_overhaul)) {
                    $intervensi = "Pemasangan Awal";
                    $tgl_intervensim = date('M Y', strtotime($row->tgl_pasang));
                } else {
                    $intervensi = "Overhaul";
                    $tgl_intervensim = date('M Y', strtotime($row->tgl_overhaul));
                }

                $result[] = [
                    'equipment' => $row->nama_aset,
                    'angka_rab' => $request->angka_rab,
                    'komponen' => $row->nama_aset,
                    'tgl_pasang' => date('M Y', strtotime($row->tgl_pasang)),
                    'intervensi' => $intervensi,
                    'tgl_intervensim' => $tgl_intervensim,
                    'rr' => $rr,
                ];

                foreach ($komponen as $detail) {
                    if (empty($row->tgl_overhaul)) {
                        $intervensi = "Pemasangan Awal";
                        $tgl_intervensim = date('M Y', strtotime($row->tgl_pasang));
                    } else {
                        $intervensi = "Overhaul";
                        $tgl_intervensim = date('M Y', strtotime($row->tgl_overhaul));
                    }

                    if ($detail->equipment_id == $row->id) {
                        $rr = $this->replaceRefurbish($row);

                        $result[] = [
                            'equipment' => "",
                            'angka_rab' => $request->angka_rab,
                            'komponen' => $detail->nama_aset,
                            'tgl_pasang' => $detail->tgl_pasang,
                            'intervensi' => $intervensi,
                            'tgl_intervensim' => $tgl_intervensim,
                            'rr' => $rr, //replace refurbish
                        ];
                    }
                }
            // }
        }
        // dd($result);

        $pdf = PDF::loadView('pages.laporan.evaluasi.prioritas', ['equipment' => $equipment, 
            'periode' => date('Y'),
            'komponen' => $komponen,
            'lokasi' => $lokasi,
            'bagian' => $bagian,
            'result' => $result
        ])->setPaper('a4', 'landscape');

        return $pdf->stream(sprintf('Form-Evaluasi-Usulan-Investasi-%s.pdf', date('Y-m')));
    }

    public function createPrioritas()
    {
        $instalasi = Instalasi::whereIn('id', lokasi())
            ->get()->pluck('name', 'id')->toArray(); 
        $labelInstalasi = ["" => "- Pilih Lokasi -"];
        $instalasi = $labelInstalasi + $instalasi;

        $asset = [];
        $template = [];

        $komponen = [];

        return view('pages.evaluasi.usulan',
            ['instalasi' => $instalasi,
            'asset' => $asset,
            'template' => $template,
            'komponen' => $komponen
        ]);
    }

    public function partPrioritas($id, $tahun = "")
    {
        DB::connection()->enableQueryLog();
        
        $komponen = Aset::with(['investasi'])
            ->where(function ($query) use ($id) {
                $query->where('equipment_id', $id)
                    ->where('equipment', 'no')
                    ->orWhere('id', $id);
            });
        $komponen = $komponen->orderBy('equipment', 'desc')->get();

        $ops = config('custom.kelayakanOperasional');
        $keuangan = config('custom.kelayakanKeuangan');
        $waktu = config('custom.waktuKebutuhan');
        $strategi = [
            '' => "- Pilih -",
            'replace' => "Replace",
            'refurbish' => "Refurbish"
        ];

        return view('pages.evaluasi.partPrioritas', [
            'komponens' => $komponen,
            'ops' => $ops,
            'keuangan' => $keuangan,
            'waktu' => $waktu,
            'strategi' => $strategi
        ])->render();
    }

    public function storePrioritas(Request $request)
    {
        DB::beginTransaction();

        try {
            if (count($request->periksa) > 0) {
                foreach ($request->periksa as $row) {
                    $tmp = UsulanInvestasi::where('komponen_id', $row['komponen_id'])->first();

                    if (!empty($tmp)) {
                        $data = UsulanInvestasi::find($tmp->id);
                    } else {
                        $data = new UsulanInvestasi;
                    }

                    $data->komponen_id = $row['komponen_id'];
                    $data->strategi = $row['strategi'];
                    $data->nilai_rab = $row['nilai_rab'];
                    $data->kelayakan_op = $row['kelayakan_op'];
                    $data->kelayakan_keuangan = $row['kelayakan_keuangan'];
                    $data->waktu = $row['waktu'];

                    $data->bobot_kelayakan_op = EvaluasiAset::bobotOperasional($row['kelayakan_op']);
                    $data->bobot_kelayakan_keuangan = EvaluasiAset::bobotKeuangan($row['kelayakan_keuangan']);
                    $data->bobot_waktu = EvaluasiAset::bobotWaktu($row['waktu']);

                    $data->save();

                    DB::commit();
                }
            }

            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('evaluasi::entry-prioritas');
    }
}
