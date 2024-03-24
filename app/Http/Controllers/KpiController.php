<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

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

use Asset\Libraries\Kpi;

use Datatables;
use DateTime;
use DB;
use PDF;
use Session;
use Validator;
use Storage;
use Response;

class KpiController extends Controller
{
    protected $arrJab = ['81', '83', '80', '82', '84', '85', '86', '87', '218', '231'];

    static $closed = false;
    static $start = "";
    static $end = "";

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // dd(\Auth::user()->role()->find(\Auth::user()->role->recidroleuser)->jabatan->kodejabatan);
        /*$jabatan = Jabatan::whereIn('recidjabatan', $this->arrJab)->get()->pluck('namajabatan', 'recidjabatan')->toArray();
        $labelJabatan = ["" => "-             Pilih SPV             -"];
        $jabatan = $labelJabatan + $jabatan;*/
        $allManajer = implode(",", config('custom.kodejabatanManajer'));
        // dd($allManajer);

        if (namaRole() == 'Super Administrator') {
            $jabatan = DB::connection('oracleaplikasi_dbout')->select("select recidjabatan,kodejabatan, namajabatan, parentjab, prior namajabatan, leveljab from tu_jabatan connect by prior kodejabatan = parentjab start with kodejabatan in ($allManajer)");
        } else {
            $jabatan = DB::connection('oracleaplikasi_dbout')->select("select recidjabatan,kodejabatan, namajabatan, parentjab, prior namajabatan, leveljab from tu_jabatan connect by prior kodejabatan = parentjab start with kodejabatan =:id", ["id" => \Auth::user()->role()->find(\Auth::user()->role->recidroleuser)->jabatan->kodejabatan]);
        }

        $rekap = [
            '' => '- Pilih Rekap -',
            'respon-time' => 'Respon Time',
            'penyelesaian' => 'Penyelesaian'
        ];

        return view('pages.kpi.index', [
            'jabatan' => $jabatan,
            'rekap' => $rekap
        ]);
    }

    public function cetak(Request $request)
    {
        $start = null;
        $end = null;
        $tmpBag = explode("#", $request->bagian);
        $period = date("Ym", strtotime($request->periode));
        $setting = KpiSetting::wherePeriode($period)->first();
        if ( $setting ) {
            if ($setting->closed == '1') {
                self::$closed = true;
            }
        }

        $filename = sprintf('%d-%s.pdf', (int)$tmpBag[0], $period);
        // for debug purpose
        $setting = KpiSetting::wherePeriode($period)->first();
        if ( $setting ) {
            if ($setting->closed == '1') {
                $start = changeDateFormat($setting->start, 'Y-m-d H:i:s', 'Ymd');
                $end = changeDateFormat($setting->end, 'Y-m-d H:i:s', 'Ymd');
            }
        }
        $arrData = Kpi::cetak($request->periode, $request->bagian, $start, $end);
        $pdf = PDF::loadView('pages.laporan.kpi.reportPdf', $arrData)->setPaper('a4', 'landscape');

        return $pdf->stream($filename);
        // end:for debug purpose

        // Production
        /*if (self::$closed == true) {
            $dir = 'kpi/'.$period;
            cekDir($dir);

            $cekFile = Storage::disk('sftp-doc')->has($dir.'/'.$filename);

            if ( $cekFile ) {
                $doc = Storage::disk('sftp-doc')->get($dir.'/'.$filename);
                
                return Response::make($doc, 200, ['Content-Type' => 'application/pdf']);
            }
        } else {
            $arrData = Kpi::cetak($request->periode, $request->bagian);
            $pdf = PDF::loadView('pages.laporan.kpi.reportPdf', $arrData)->setPaper('a4', 'landscape');

            return $pdf->stream($filename);
        }*/
        // end:production
    }

    public function cetakBackup(Request $request)
    {
        $period = date("Ym", strtotime($request->periode));
        $period2 = date("Y-m", strtotime($request->periode));
        $tmpBag = explode("#", $request->bagian);
        $arrData = [];
        $lokasi = config('custom.'.(int)$tmpBag[0].'.lokasi');
        $bagian = config('custom.'.(int)$tmpBag[0].'.bagian');

        // Cek closing periode
        $setting = KpiSetting::wherePeriode($period)->first();
        if ( $setting ) {
            if ($setting->closed == '1') {
                self::$closed = true;
                self::$start = changeDateFormat($setting->start, 'Y-m-d H:i:s', 'Ymd');
                self::$end = changeDateFormat($setting->end, 'Y-m-d H:i:s', 'Ymd');
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
        $rel = self::leading($period, $lokasi, $bagian, 'beroperasi', $period2);
        

        $pdf = PDF::loadView('pages.laporan.kpi.reportPdf', [
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
            'periode' => isset($request->periode)?getPeriode($request->periode):'',
            'bag' => $tmpBag[1]
        ])->setPaper('a4', 'landscape');

// dd($lokasi);
        $filename = sprintf('%d-%s.pdf', (int)$tmpBag[0], $period);

        if (self::$closed == true) {
            $dir = 'kpi/'.$period;

            Storage::disk('sftp-doc')->put($dir.'/'.$filename, $pdf->output());
        }

        return $pdf->stream($filename);
    }

    public function listSetting()
    {
        $data = null;
        return view('pages.kpi.index-setting', [
            'data' => $data
        ]);
    }

    public function dataSetting(Request $request)
    {
        $query = KpiSetting::select('*');

        return Datatables::of($query)
                ->addColumn('period', function ($model) {
                    
                    return changeDateFormat($model->periode, 'Ym', 'F-Y');
                })
                ->addColumn('menu', function ($model) {
                    $edit = '<a href="' . route('kpi::kpi-setting', ['id' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                    // $close = '<a href="' . route('kpi::kpi-closing', ['id' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-close"></i> Closing </a>';
                    return $edit;
                })
                ->make(true);
    }

    public function setting()
    {
        $data = KpiSetting::wherePeriode(date('Ym'))->first();

        return view('pages.kpi.setting', [
            'data' => $data
        ]);
    }

    public function settingSimpan(Request $request)
    {
        try{
            DB::beginTransaction();

            if ($request->id) {
                $data = KpiSetting::find($request->id);
            } else {
                $data = new KpiSetting();
            }

            $data->periode = changeDateFormat($request->periode, 'F-Y', 'Ym');
            $data->start = $request->start;
            $data->end = $request->end.' 23:59:59';

            $data->save();

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('kpi::kpi-index-setting');
    }

    public function closing(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = KpiSetting::find($request->id);
            $data->closed = 1;

            $data->save();
            
            DB::commit();

            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('kpi::kpi-index-setting');
    }
}
