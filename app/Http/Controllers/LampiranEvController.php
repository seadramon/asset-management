<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Aset,
    Asset\Models\Instalasi,
    Asset\Models\Ms52w,
    Asset\Models\Ms4w,
    Asset\Models\Master,
    Asset\Models\AduanNonOperasi,
    Asset\Models\Usulan,
    Asset\Models\Perawatan,
    Asset\Models\Perbaikan;

use Asset\Repositories\Interfaces\MasterRepositoryInterface;

use DB;
use Datatables;
use Session;
use Validator;

class LampiranEvController extends Controller
{
    private $masterRepository;

    public function __construct(MasterRepositoryInterface $masterRepository)
    {
        $this->masterRepository = $masterRepository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(MasterRepositoryInterface $masterRepository)
    {
        //
    }

    public function getPenjadwalan()
    {
        $data = '';
        
        $instalasi = Instalasi::all()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Semua Lokasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        $bagian = Master::bagian()->get()->pluck('name', 'id')->toArray();
        $labelBagian = ["" => "-             Semua Bagian             -"];
        $bagian = $labelBagian + $bagian;

        $status = [
            "" => "- Pilih Status -",
            1 => "Selesai",
            0 => "Belum Selesai",
        ];

        $week = ["" => "- Pilih Minggu-"];
        for ($i = 1; $i <= lastWeekNumber(); $i++) { 
            $week[$i] = "Minggu ke ".$i;
        }  

        $tahun = date('Y');   

        return view('pages.lampiran.penjadwalan', [
            'data' => $data,
            'instalasi' => $instalasi,
            'tahun' => $tahun,
            'bagian' => $bagian,
            'week' => $week,
            'status' => $status,
        ]);
    }

    public function dataPenjadwalan(Request $request)
    {
        DB::connection()->enableQueryLog();
        $tahun = !empty($request->year)?$request->year:date('Y');
        // dd($request->status);

    	$query = Ms4w::select('ms_4w.*', 'ms_52w.instalasi_id', 'ms_52w.id as ms52wid', 'aset.id aset_id', 'aset.kode_aset', 'aset.nama_aset', 'instalasi.name as lokasi', 'aset.bagian')
            ->join('ms_52w', 'ms_4w.ms_52w_id', '=', 'ms_52w.id')
            ->join('aset', 'ms_52w.komponen_id', '=', 'aset.id')
            ->join('instalasi', 'ms_52w.instalasi_id', '=', 'instalasi.id')
            ->where('aset.kondisi_id', '<>', '12')
            ->where('ms_4w.status', '<>', '99')
            ->whereRaw("TO_CHAR(ms_4w.TANGGAL_MONITORING, 'YYYY') = $tahun");

        if ($request->instalasi != "") {
        	$query = $query->where('ms_52w.instalasi_id', $request->instalasi);
        }

        if ($request->bagian != "") {
        	$query = $query->where('aset.bagian', $request->bagian);
        }

        if ($request->minggu != "") {
        	$query = $query->where('ms_4w.urutan_minggu', $request->minggu);
        }

        if ($request->status != "") {
        	if ($request->status == '1') {
        		$query = $query->whereNotNull('ms_4w.foto_lokasi');   
        	} else {
        		$query = $query->whereNull('ms_4w.foto_lokasi');   
        	}
        }

        /*$data = $query->get();
        foreach ($data as $row) {
            $res[] = [
                'id' => $row->id,
                'status' => $row->status,
                'urutan_minggu' => $row->urutan_minggu,
                '52w' => $row->ms52wid,
            ];
        }
        dd($res);*/

        return Datatables::of($query)
        	->addColumn('statusmsg', function ($model) {
        	    // dd(!empty($model->petugas_id));
        	    /*switch ($model->status) {
        	        case 0:
        	            $edit = '<a href="#" class="badge badge-secondary"> Tidak Dilaksanakan </a>';
        	            break;
        	        case 1:
        	            $edit = '<a href="#" class="badge badge-success"> Selesai </a>';
        	            break;
        	    }*/
        	    if ( !empty($model->foto_lokasi) ) {
        	    	$edit = '<a href="#" class="badge badge-success"> Selesai </a>';
        	    } else {
        	    	$edit = '<a href="#" class="badge badge-secondary"> Tidak Dilaksanakan </a>';
        	    }
        	    return $edit;
        	})
        	->make(true);
    }

    public function getRealisasiPemeliharaan()
    {
        $data = '';
        
        $instalasi = Instalasi::all()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Semua Lokasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        $bagian = Master::bagian()->get()->pluck('name', 'id')->toArray();
        $labelBagian = ["" => "-             Semua Bagian             -"];
        $bagian = $labelBagian + $bagian;

        $week = ["" => "- Pilih Minggu-"];
        for ($i = 1; $i <= 52; $i++) { 
            $week[$i] = "Minggu ke ".$i;
        }

        $tipe = [
            "monitoring" => "Monitoring",
            "aduan" => "Aduan"
        ];     

        return view('pages.lampiran.realisasi', [
            'data' => $data,
            'instalasi' => $instalasi,
            'bagian' => $bagian,
            'week' => $week,
            'tipe' => $tipe
        ]);
    }

    public function dataRealisasiPerawatan(Request $request)
    {
        DB::connection()->enableQueryLog();
// dd('aa');
        $query = Perawatan::select('prw_data.*', 'ms_4w.urutan_minggu', 'aset.kode_aset', 'aset.nama_aset')
            ->join('ms_4w', 'prw_data.ms_4w_id', '=', 'ms_4w.id')
            ->join('aset', 'prw_data.komponen_id', '=', 'aset.id')
            // ->where('aset.kondisi_id', '<>', '12')
            ->where('prw_data.status', '<>', '99');
            // ->where('ms_4w.status', '<>', '99');
// dd($query->get());
        if ($request->instalasi != "") {
            $query = $query->where('aset.instalasi_id', $request->instalasi);
        }

        if ($request->bagian != "") {
            $query = $query->where('aset.bagian', $request->bagian);
        }

        if ($request->minggu != "") {
            $query = $query->where('ms_4w.urutan_minggu', $request->minggu);
        }

        return Datatables::of($query)->make(true);
    }

    public function dataRealisasiPerbaikan(Request $request)
    {
        DB::connection()->enableQueryLog();

        $tipe = "monitoring";
        if (!empty($request->tipe)) {
            $tipe = $request->tipe;    
        }

        $query = Perbaikan::select('prb_data.*', 'aset.kode_aset', 'aset.nama_aset', 'instalasi.name as instalasi')
                ->join('aset', 'prb_data.komponen_id', '=', 'aset.id')
                ->join('instalasi', 'instalasi.id', '=', 'aset.instalasi_id')
                // ->where('aset.kondisi_id', '<>', '12')
                ->where('prb_data.tipe', $tipe);

        if ($tipe == "monitoring") {
            $query = $query->join('ms_4w', 'prb_data.ms_4w_id', '=', 'ms_4w.id')
                ->where('ms_4w.status', '<>', '99');
        }

        if ($request->instalasi != "") {
            $query = $query->where('aset.instalasi_id', $request->instalasi);
        }

        if ($request->bagian != "") {
            $query = $query->where('aset.bagian', $request->bagian);
        }

        if ($request->minggu != "" && $tipe == "monitoring") {
            $query = $query->where('ms_4w.urutan_minggu', $request->minggu);
        }

        return Datatables::of($query)->make(true);
    }

    public function dataRealisasiNonOperasi(Request $request)
    {
        DB::connection()->enableQueryLog();

        $query = AduanNonOperasi::select('aduan_non_operasi.*', 'instalasi.name as instalasi')
            ->join('instalasi', 'instalasi.id', '=', 'aduan_non_operasi.instalasi_id');

        if ($request->instalasi != "") {
            $query = $query->where('aduan_non_operasi.instalasi_id', $request->instalasi);
        }

        if ($request->minggu != "") {
            $query = $query->whereRaw("to_number(to_char(CREATED_AT,'IW')) = ?", [$request->minggu]);
        }

        return Datatables::of($query)->make(true);
    }

    public function dataRealisasiUsulan(Request $request)
    {
        DB::connection()->enableQueryLog();

        $query = Usulan::select('usulan_non_operasi.*', 'instalasi.name as instalasi')
            ->join('instalasi', 'instalasi.id', '=', 'usulan_non_operasi.instalasi_id');

        if ($request->instalasi != "") {
            $query = $query->where('usulan_non_operasi.instalasi_id', $request->instalasi);
        }

        if ($request->minggu != "") {
            $query = $query->whereRaw("to_number(to_char(CREATED_AT,'IW')) = ?", [$request->minggu]);
        }

        return Datatables::of($query)->make(true);
    }

    public function getAvailable()
    {
        $data = '';
        
        $instalasi = Instalasi::all()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Semua Lokasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        $bagian = Master::bagian()->get()->pluck('name', 'id')->toArray();
        $labelBagian = ["" => "-             Semua Bagian             -"];
        $bagian = $labelBagian + $bagian;

        return view('pages.lampiran.available', [
            'data' => $data,
            'instalasi' => $instalasi,
            'bagian' => $bagian
        ]);
    }

    public function dataAvailable(Request $request)
    {
        DB::connection()->enableQueryLog();
// dd($periode);
        $query = Aset::select(DB::raw("aset.kode_aset, aset.nama_aset, prb_data.tgl_start, prb_data.tgl_finish,
            (prb_data.tgl_finish-prb_data.tgl_start) * 24 AS unavailable, row_number() over(partition by ASET.NAMA_ASET order by ASET.NAMA_ASET) seq_numb"))
            ->leftjoin('prb_data', 'aset.id', '=', 'prb_data.komponen_id')
            ->where('aset.availability', '1');

        if ($request->instalasi != "") {
            $query = $query->where('aset.instalasi_id', $request->instalasi);
        }

        if ($request->bagian != "") {
            $query = $query->where('aset.bagian', $request->bagian);
        }

        if ($request->periode != "") {
            $period = date("Ym", strtotime($request->periode));
            // dd($period);
            $query = $query->where(DB::raw("TO_CHAR(prb_data.TANGGAL, 'YYYYMM')"), $period);
        } else {
        	$query = $query->where(DB::raw("TO_CHAR(prb_data.TANGGAL, 'YYYY')"), date('Y'));
        }

        return Datatables::of($query)->make(true);
    }

    public function getKesesuaian()
    {
        $data = '';
        
        $instalasi = Instalasi::all()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Semua Lokasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        $bagian = $this->masterRepository->getBagian()->pluck('name', 'id')->toArray();
        $labelBagian = ["" => "-             Semua Bagian             -"];
        $bagian = $labelBagian + $bagian;

        $onproses = ["" => "- Pilih Tampilkan - ",
        	'proses' => "On Proses",
    	];

        $tindakan = ["prw" => "Perawatan",
            "prb" => "Perbaikan"
        ];

        return view('pages.lampiran.kesesuaian', [
            'data' => $data,
            'instalasi' => $instalasi,
            'bagian' => $bagian,
            'tindakan' => $tindakan
        ]);
    }

    public function dataKesesuaian(Request $request)
    {
        DB::connection()->enableQueryLog();

        switch ($request->tindakan) {
            case 'prb':
                $query = Aset::leftjoin('prb_data', 'aset.id', '=', 'prb_data.komponen_id')
                    ->select(DB::raw("aset.kode_aset, aset.nama_aset, TO_CHAR(prb_data.TANGGAL, 'DD-MM-YYYY') as tanggal"))
                    ->where('prb_data.status', '1')
                    ->whereNotNull('prb_data.tgl_foto_investigasi');
                $wo = 'Perbaikan';
                break;
            case 'prw':
                $query = Aset::leftjoin('prw_data', 'aset.id', '=', 'prw_data.komponen_id')
                    ->select(DB::raw("aset.kode_aset, aset.nama_aset, TO_CHAR(prw_data.TANGGAL, 'DD-MM-YYYY') as tanggal"))
                    ->where('prw_data.status', '1')
                    ->whereNotNull('prw_data.tgl_foto_investigasi');
                $wo = 'Perawatan';
                break;
            default:
                $query = Aset::leftjoin('prw_data', 'aset.id', '=', 'prw_data.komponen_id')
                    ->select(DB::raw("aset.kode_aset, aset.nama_aset, TO_CHAR(prw_data.TANGGAL, 'DD-MM-YYYY') as tanggal"))
                    ->where('prw_data.status', '1')
                    ->whereNotNull('prw_data.tgl_foto_investigasi');
                $wo = 'Perawatan';
                break;
        }   
// dd($query->get());
        if ($request->instalasi != "") {
            $query = $query->where('aset.instalasi_id', $request->instalasi);
        }

        if ($request->bagian != "") {
            $query = $query->where('aset.bagian', $request->bagian);
        }

        return Datatables::of($query)
            ->addColumn('sukucadang', '-')
            ->addColumn('wo', $wo)
            ->make(true);
    }    
}
