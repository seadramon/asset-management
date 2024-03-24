<?php

namespace Asset\Http\Controllers\Api;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Libraries\ParamChecker;
use Asset\Libraries\ParamCheckerDev;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Asset\Models\Aset,
    Asset\Models\Instalasi,
    Asset\Models\Ms52w,
    Asset\Models\Ms4w,
    Asset\Models\Master,
    Asset\Models\MasterFm,
    Asset\Models\MasterJab,
    Asset\Models\Perawatan,
    Asset\Models\Perbaikan,
    Asset\Models\PrbDetail,
    Asset\Models\PrwDetail;

use DB;
use Html;
use Validator;
use Redirect;
use Session;
use Storage;

class MonitoringController extends Controller
{
    protected $disk = "sftp";

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $lokasi = $request->get('lokasi');
        $bagian = $request->get('bagian');
        $kodefm = $request->get('kodefm');

        $start = $request->get('start'); //offset
        $limit = $request->get('limit'); 

        try {
            $result = [];

            $data = Aset::select('aset.kode_aset', 'aset.kode_barcode', 'aset.nama_aset', 'aset.kode_fm', 'instalasi.name as instalasi_name', 'aset.instalasi_id', 'lokasi.name as lokasi_name', 'aset.lokasi_id')
                ->join('instalasi', 'aset.instalasi_id', '=', 'instalasi.id')
                ->join('lokasi', 'aset.lokasi_id', '=', 'lokasi.id')
                ->where('aset.kondisi_id', '<>', '12');

            if ($lokasi !== null) {
                $arrLokasi = explode("-", $lokasi);
                $data = $data->whereIn('aset.instalasi_id', $arrLokasi);
            }

            if ($bagian !== null) {
                $arrBagian = explode("-", $bagian);
                $data = $data->where('aset.bagian', $arrBagian);
            }

            if ($kodefm !== null) {
                $data = $data->where('kode_fm', strtoupper($kodefm));
            }

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            $data = $data->get()->toArray();
            
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $result[] = [
                        'kode_aset' => $row['kode_aset'],
                        'kode_barcode' => $row['kode_barcode'],
                        'nama_aset' => $row['nama_aset'],
                        'kode_fm' => $row['kode_fm'],
                        'instalasi_id' => $row['instalasi_id'],
                        'instalasi_name' => $row['instalasi_name'],
                        'lokasi_id' => $row['lokasi_id'],
                        'lokasi_name' => $row['lokasi_name'],
                    ];
                }
            }

            $return = ['data' => $result, 'next' => 'false'];

            if ($limit !== null) {
                $return = ['data' => array_slice($result, 0, $limit), 'next' => (count($result) > $limit)];    
            }

            return response()->json($return)->setStatusCode(200, 'OK');
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function todolist(Request $request)
    {        
        $nip = $request->header('nip');

        $lokasi = lokasi($nip);
        $bagian = bagian($nip);
// dd($lokasi);
        $start = $request->get('start'); //offset
        $limit = $request->get('limit'); 

        if (date('W') == '52') {
            $tahun = '2021';
        } else {
            $tahun = date('Y');
        }

        /*if ($nip == '11701790') {
            $tahun = '2020';
        }*/
// dd($tahun);
        try {
            $result = [];

            $data = Ms4w::select('ms_4w.*', 'ms_52w.instalasi_id', 'aset.id as id_aset', 'aset.kode_barcode', 'aset.kode_aset', 'aset.kode_fm', 'aset.nama_aset', 'aset.bagian', 'instalasi.name as instalasi', 'lokasi.name as lokasi')
                ->join('ms_52w', 'ms_4w.ms_52w_id', '=', 'ms_52w.id')
                ->join('aset', 'ms_52w.komponen_id', '=', 'aset.id')
                ->join('instalasi', 'ms_52w.instalasi_id', '=', 'instalasi.id')
                ->join('lokasi', 'aset.lokasi_id', '=', 'lokasi.id')
                // ->join(DB::connection('oraclesecman')->table('usrtab'), 'ms_4w.petugas', '=', 'usrtab.userid')
                ->whereIn('ms_52w.instalasi_id', $lokasi)
                ->whereIn('aset.bagian', $bagian)
                ->where('ms_4w.petugas', trim($nip))
                // ->whereNull('ms_4w.foto_lokasi')
                // ->where('ms_4w.urutan_minggu', '6')
                ->where('ms_4w.urutan_minggu', date('W'))
                ->whereRaw("TO_CHAR(ms_4w.TANGGAL_MONITORING, 'YYYY') = $tahun")
                ->where('aset.kondisi_id', '<>', '12')
                ->where('ms_4w.status', '<>', '99');

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            $data = $data->get()->toArray();
// dd($data);            
            /*foreach ($data as $row) {
                // dd($row);
                $tmp[] = $row['ms_52w_id'];
            }
            dd($tmp);*/
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $fm = MasterFm::where('kode_fm',$row['kode_fm'])
                        ->where('aktif', 'Y')
                        ->where('tipe', 1)
                        ->get();

                    if (Schema::hasTable('FM_'.$row['kode_fm'])) {
                        $dataFm = DB::table('FM_'.$row['kode_fm'])
                            ->where('ms_4w_id', $row['id'])
                            ->first();
                    } else {
                        $dataFm = null;
                    }

                    $result[] = [
                        'id4w' => $row['id'],
                        'instalasi_id' => $row['instalasi_id'],
                        'instalasi' => $row['instalasi'],
                        'lokasi' => $row['lokasi'],
                        'id_aset' => $row['id_aset'],
                        'kode_barcode' => $row['kode_barcode'],
                        'aset' => $row['nama_aset'],
                        'bagian' => $row['bagian'],
                        'urutan_minggu' => $row['urutan_minggu'],
                        'hari' => $row['hari'],
                        'petugas' => $row['petugas'],
                        'kode_fm' => $row['kode_fm'],
                        'urutan_minggu' => $row['urutan_minggu'],
                        'status' => $row['status'],
                        'tanggal_selesai' => $row['tanggal_selesai'],
                        'form' => $fm,
                        'dataValue' => $dataFm
                    ];
                }
            }

            $return = ['data' => $result, 'jumlah' => count($result), 'next' => 'false'];

            if ($limit !== null) {
                $return = ['data' => array_slice($result, 0, $limit), 'next' => (count($result) > $limit)];    
            }

            return response()->json($return)->setStatusCode(200, 'OK');
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function scan(Request $request)
    {
        $nip = $request->header('nip');
        $barcode = $request->get('barcode');
        $ms_4w_id = $request->get('ms_4w_id'); 

        if ($barcode) {
        	try {
        		$result = [];
        		
        		$aset = Aset::where('kode_barcode', $barcode)->first();
        		
        		$data = MasterFm::where('kode_fm', $aset->kode_fm)
        			->where('tipe', 1)
                    ->where('aktif', 'Y')
        			->get()->toArray();

        		if (count($data) > 0) {
	                foreach ($data as $row) {
	                    $result[] = [
	                        'recid' => $row['recid'],
	                        'ms_4w_id' => $ms_4w_id,
	                        'kode_fm' => $row['kode_fm'],
	                        'pengukuran' => $row['pengukuran'],
	                        'dropdown' => $row['dropdown'],
	                        'nama_field' => $row['nama_field']	                        
	                    ];
	                }
	            }
// dd('FM_'.$aset->kode_fm);
                if (Schema::hasTable('FM_'.$aset->kode_fm)) {
                    $arrValue = DB::table('FM_'.$aset->kode_fm)
                        ->where('ms_4w_id', $ms_4w_id)
                        ->first();
                } else {
                    $arrValue = null;
                }

	            $return = ['data' => $result, 'dataValue' => $arrValue];

	            return response()->json($return)->setStatusCode(200, 'OK');
        	}catch(Exception $e) {
        		return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        	}
        } else {
        	return response()->json(['error' => 'Barcode Kosong'])->setStatusCode(500, 'Error');
        }
    }

    public function simpan(Request $request)
    {
    	DB::beginTransaction();

        $nip = $request->header('nip');

        try {
            if (empty($request->foto_lokasi)) {
                return response()->json([
                        'result' => 'Error', 
                        'message' => 'Foto Lokasi tidak boleh kosong'
                ])->setStatusCode(500, 'Error');
            }

            // Write Log
            $dir = 'log_monitoring/'. date('Y-m');
            cekDir($dir);
            Storage::disk('sftp-doc')->put($dir.'/'. $request->ms_4w_id . '.json', response()->json($request->all()));
            // end:write Log

            $data = Ms4w::select('ms_4w.*')
                ->where('id', '=', $request->ms_4w_id)->first();

            //dd($data['urutan_minggu']);

            $dateW=date("W");
            //dd($dateW);

            if ($data['urutan_minggu'] != $dateW) {
                return response()->json([
                        'result' => 'Error', 
                        'message' => 'Tidak bisa menyimpan WO (sudah lewat minggu)'
                ])->setStatusCode(500, 'Error');
            }

            // mengembalikan data hasil hitungan waspada/bahaya + detail
            $result = ParamChecker::cekHitungan($request->except(['ms_4w_id']));

            $kodefm = $result['kode_fm'];
            unset($result['kode_fm']);
            $result['ms_4w_id'] = $request->ms_4w_id;
            $result['tanggal'] = date('Y-m-d');


            if ((strlen($result['waspada']) > 0) || (strlen($result['bahaya']) > 0)) {
                $arrTindakan = $this->tindakan($request->ms_4w_id);
                $arrTindakan['tanggal'] = DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')");

                if (strlen($result['waspada']) > 0) {
                    $cekPerawatan = self::cekPerawatan($arrTindakan['komponen_id']);

                    if ($cekPerawatan) {
                        // reset tabel perawatan dan detail
                        $clearDetail = PrwDetail::whereRaw('prw_data_id in (select id from prw_data where ms_4w_id = '.$result['ms_4w_id'].')')->delete();
                        Perawatan::where('ms_4w_id', $result['ms_4w_id'])->delete();
                        // end reset

                        // kode wo
                        $urutnya = prwUrutan();
                        $gen = generateKodeWo('prw',
                            $urutnya,
                            substr($kodefm, 0, 1),
                            $arrTindakan['instalasi_id'],
                            date('Y-m-d')
                        );
                        $arrTindakan['kode_wo'] = $gen;
                        $arrTindakan['urutan'] = $urutnya;
                        // kode wo

                        $prw = Perawatan::insertGetId($arrTindakan);

                        if (count($result['batasWaspada']) > 0) {
                            $prwDetail = [];

                            foreach ($result['batasWaspada'] as $key => $value) {
                                $prwDetail[] = [
                                    'prw_data_id' => $prw,
                                    'kode_fm' => $kodefm,
                                    'nama_field' => $key,
                                    'nilai_batas' => $value,
                                    'nilai_asli' => isset($result['nilaiOri'][$key])?$result['nilaiOri'][$key]:"0"
                                ];     
                            }
                            PrwDetail::insert($prwDetail);
                        }

                        // Notif
                        $prwNotif = Perawatan::where('ms_4w_id', $result['ms_4w_id'])->first();
                        $notif = kirimnotif(spv($nip),
                            [
                                'title' => 'Pemberitahuan WO Perawatan Baru',
                                'text' => sprintf('Pemberitahuan WO Perawatan Baru untuk %s', $prwNotif->komponen->nama_aset),
                                'sound' => 'default',
                                'click_action' => 'OPEN_ACTIVITY_NOTIF',
                                'tipe' => '231', 
                                'id' => $prw
                            ]
                            // ['tipe' => '1', 'id' => $request->id]
                        );
                        // End Notif    
                    }
                }

                if (strlen($result['bahaya']) > 0) {
                    $cekPerbaikan = self::cekPerbaikan($arrTindakan['komponen_id']);

                    if ($cekPerbaikan) {
                        // reset tabel perawatan dan detail
                        $clearDetail = PrbDetail::whereRaw('prb_data_id in (select id from prb_data where ms_4w_id = '.$result['ms_4w_id'].')')->delete();
                        Perbaikan::where('ms_4w_id', $result['ms_4w_id'])->delete();
                        // end reset

                        // kode wo
                        $urutnya = prbUrutan();
                        $gen = generateKodeWo('prb',
                            $urutnya,
                            substr($kodefm, 0, 1),
                            $arrTindakan['instalasi_id'],
                            date('Y-m-d')
                        );
                        $arrTindakan['kode_wo'] = $gen;
                        $arrTindakan['urutan'] = $urutnya;
                        // kode wo

                        $arrTindakan['tipe'] = 'monitoring';
                        $prb = Perbaikan::insertGetId($arrTindakan);
                        
                        if (count($result['batasBahaya']) > 0) {
                            $prbDetail = [];
                            $temp = [];
                            foreach ($result['batasBahaya'] as $key => $value) {
                                $prbDetail[] = [
                                    'prb_data_id' => $prb,
                                    'kode_fm' => $kodefm,
                                    'nama_field' => $key,
                                    'nilai_batas' => $value,
                                    'nilai_asli' => isset($result['nilaiOri'][$key])?$result['nilaiOri'][$key]:"0"
                                ];   
                                $temp[] = $prbDetail;
                            }          
                            PrbDetail::insert($prbDetail);
                            // dd('sukses');
                        }

                        // Notif
                        $prbNotif = Perbaikan::where('ms_4w_id', $result['ms_4w_id'])->first();
                        $notif = kirimnotif(spv($nip),
                            [
                                'title' => 'Pemberitahuan WO Perbaikan Baru',
                                'text' => sprintf('Pemberitahuan WO Perbaikan Baru untuk %s', $prbNotif->komponen->nama_aset),
                                'sound' => 'default',
                                'click_action' => 'OPEN_ACTIVITY_NOTIF',
                                'tipe' => '221', 
                                'id' => $prb
                            ]
                            // ['tipe' => '1', 'id' => $request->id]
                        );
                        // End Notif
                    }
                }

                // Lock Disposisi
                // self::lockDisposisi($arrTindakan);
            }

            $arrFm = [];
            $arrNotIn = ['nilaiOri', 'batasWaspada', 'batasBahaya', 'nip', 'foto_lokasi', 'foto_lokasi2'];
            foreach ($result as $key => $value) {
                if (!is_array($value) && !in_array($key, $arrNotIn)) {
                    $arrFm[$key] = $value;
                }
            }

            // reset table FM
            DB::table('FM_'.$request->kode_fm)
                ->where('ms_4w_id', $result['ms_4w_id'])
                ->delete();
            $data = DB::table('FM_'.$request->kode_fm)->insert($arrFm);
            // end reset

            // update ms4w
            $filename = "null";
            // dd($request->foto_lokasi);
            if (!empty($request->foto_lokasi)) {
                $str = explode(",", $request->foto_lokasi);
                $str = explode("/", str_replace(";base64", "", $str[0]));
                $ext = !empty($str[1])?$str[1]:"jpg";

                $filename = sprintf("%d-%d.%s", $result['ms_4w_id'], isset($result['nip'])?$result['nip']:"", $ext);
                // dd($filename);
                base64_to_jpeg($request->foto_lokasi, public_path('uploads/temp/'.$filename));

                $dir = 'monitoring/'.date('Y-m');
                cekDir($dir);

                \Storage::disk($this->disk)->put($dir.'/'.$filename, \File::get(public_path('uploads/temp/'.$filename)));
                unlink(public_path('uploads/temp/'.$filename));
            }

            $filename2 = "null";
            if (!empty($request->foto_lokasi2)) {
                $str = explode(",", $request->foto_lokasi2);
                $str = explode("/", str_replace(";base64", "", $str[0]));
                $ext = !empty($str[1])?$str[1]:"jpg";

                $filename2 = sprintf("%d-%d-2.%s", $result['ms_4w_id'], isset($result['nip'])?$result['nip']:"", $ext);
                base64_to_jpeg($request->foto_lokasi2, public_path('uploads/temp/'.$filename2));

                $dir = 'monitoring/'.date('Y-m');
                cekDir($dir);

                \Storage::disk($this->disk)->put($dir.'/'.$filename2, \File::get(public_path('uploads/temp/'.$filename2)));
                unlink(public_path('uploads/temp/'.$filename2));
            }
            
            Ms4w::where('id', $result['ms_4w_id'])
                ->update([
                    'status' => '1',
                    'tanggal_selesai' => DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')"),
                    'foto_lokasi' => $filename,
                    'foto_lokasi2' => $filename2
                ]);
// dd('halt');
            DB::commit();
            
            return response()->json(['message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    private static function cekPerawatan($komponen)
    {
        $ret = true;

        $prw = Perawatan::where('komponen_id', $komponen)
            ->whereNotIn('status', config('custom.skipStatus'))
            ->count();

        $prb = Perbaikan::where('komponen_id', $komponen)
            ->whereNotIn('status', config('custom.skipStatus'))
            ->count();

        if ($prw > 0 || $prb > 0) {
            $ret =  false;
        }

        return $ret;
    }

    private static function cekPerbaikan($komponen)
    {
        $ret = true;

        $prw = Perawatan::where('komponen_id', $komponen)
            ->whereNotIn('status', config('custom.skipStatus'))
            ->count();

        if ($prw > 0) {
            Perawatan::where('komponen_id', $komponen)
                ->whereNotIn('status', config('custom.skipStatus'))
                ->update([
                    'status' => '98',
                    'last_action' => 'Digantikan oleh wo perbaikan',
                    'updated_at' => getNow()
                ]);
        }

        $prb = Perbaikan::where('komponen_id', $komponen)
            ->whereNotIn('status', config('custom.skipStatus'))
            ->count();

        if ($prb > 0) {
            $ret =  false;
        }

        return $ret;
    }

    private static function lockDisposisi($param)
    {
        // dd($param);
        $data = Ms4w::whereIn('ms_52w_id', function($query) use($param){
            $query->select('id')
                ->from('MS_52W')    
                ->where('komponen_id', $param['komponen_id'])
                ->where('tahun', date('Y'));
        })
        ->where('urutan_minggu', '>', date('W'))
        ->update(['status' => '99']);
    }

    private function tindakan($id)
    {
        $arrTindakan = [];

        $arrData = Ms4w::with('Ms52w.komponen')
                ->where('id', $id)
                ->first();

        if ($arrData) {
            $arrTindakan = [
                'komponen_id' => $arrData->Ms52w->komponen->id,
                'ms_4w_id' => $id,
                'bagian_id' => $arrData->Ms52w->komponen->bagian,
                'instalasi_id' => $arrData->Ms52w->komponen->instalasi_id,
            ];
        }

        return $arrTindakan;
    }


    // METHODE FOR DEVELOPMENT PURPOSE
    public function simpandev(Request $request)
    {
        DB::beginTransaction();
dd($request->all());
        $nip = $request->header('nip');

        try {
            if (empty($request->foto_lokasi)) {
                return response()->json([
                        'result' => 'Error', 
                        'message' => 'Foto Lokasi tidak boleh kosong'
                ])->setStatusCode(500, 'Error');
            }

            $result = ParamChecker::cekHitungan($request->except(['ms_4w_id']));
            // dd($result);
            $kodefm = $result['kode_fm'];
            unset($result['kode_fm']);
            $result['ms_4w_id'] = $request->ms_4w_id;
            $result['tanggal'] = date('Y-m-d');

            if ((strlen($result['waspada']) > 0) || (strlen($result['bahaya']) > 0)) {
                $arrTindakan = $this->tindakan($request->ms_4w_id);
                $arrTindakan['tanggal'] = DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')");

                if (strlen($result['waspada']) > 0) {   
                    // reset tabel perawatan dan detail
                    $clearDetail = PrwDetail::whereRaw('prw_data_id in (select id from prw_data where ms_4w_id = '.$result['ms_4w_id'].')')->delete();
                    Perawatan::where('ms_4w_id', $result['ms_4w_id'])->delete();
                    // end reset

                    // kode wo
                    $urutnya = prwUrutan();
                    $gen = generateKodeWo('prw',
                        $urutnya,
                        substr($kodefm, 0, 1),
                        $arrTindakan['instalasi_id'],
                        date('Y-m-d')
                    );
                    $arrTindakan['kode_wo'] = $gen;
                    $arrTindakan['urutan'] = $urutnya;
                    // kode wo

                    $prw = Perawatan::insertGetId($arrTindakan);

                    if (count($result['batasWaspada']) > 0) {
                        $prwDetail = [];

                        foreach ($result['batasWaspada'] as $key => $value) {
                            $prwDetail[] = [
                                'prw_data_id' => $prw,
                                'kode_fm' => $kodefm,
                                'nama_field' => $key,
                                'nilai_batas' => $value,
                                'nilai_asli' => isset($result['nilaiOri'][$key])?$result['nilaiOri'][$key]:"0"
                            ];     
                        }
                        PrwDetail::insert($prwDetail);
                    }

                    // Notif
                    $prwNotif = Perawatan::where('ms_4w_id', $result['ms_4w_id'])->first();
                    $notif = kirimnotif(spv($nip),
                        [
                            'title' => 'Pemberitahuan WO Perawatan Baru',
                            'text' => sprintf('Pemberitahuan WO Perawatan Baru untuk %s', $prwNotif->komponen->nama_aset),
                            'sound' => 'default',
                            'click_action' => 'OPEN_ACTIVITY_NOTIF',
                            'tipe' => '231', 
                            'id' => $prw
                        ]
                        // ['tipe' => '1', 'id' => $request->id]
                    );
                    // End Notif
                }

                if (strlen($result['bahaya']) > 0) {         
                    // reset tabel perawatan dan detail
                    $clearDetail = PrbDetail::whereRaw('prb_data_id in (select id from prb_data where ms_4w_id = '.$result['ms_4w_id'].')')->delete();
                    Perbaikan::where('ms_4w_id', $result['ms_4w_id'])->delete();
                    // end reset

                    // kode wo
                    $urutnya = prbUrutan();
                    $gen = generateKodeWo('prb',
                        $urutnya,
                        substr($kodefm, 0, 1),
                        $arrTindakan['instalasi_id'],
                        date('Y-m-d')
                    );
                    $arrTindakan['kode_wo'] = $gen;
                    $arrTindakan['urutan'] = $urutnya;
                    // kode wo

                    $arrTindakan['tipe'] = 'monitoring';
                    $prb = Perbaikan::insertGetId($arrTindakan);
                    
                    if (count($result['batasBahaya']) > 0) {
                        $prbDetail = [];
                        $temp = [];
                        foreach ($result['batasBahaya'] as $key => $value) {
                            $prbDetail[] = [
                                'prb_data_id' => $prb,
                                'kode_fm' => $kodefm,
                                'nama_field' => $key,
                                'nilai_batas' => $value,
                                'nilai_asli' => isset($result['nilaiOri'][$key])?$result['nilaiOri'][$key]:"0"
                            ];   
                            $temp[] = $prbDetail;
                        }          
                        PrbDetail::insert($prbDetail);
                        // dd('sukses');
                    }

                    // Notif
                    $prbNotif = Perbaikan::where('ms_4w_id', $result['ms_4w_id'])->first();
                    $notif = kirimnotif(spv($nip),
                        [
                            'title' => 'Pemberitahuan WO Perbaikan Baru',
                            'text' => sprintf('Pemberitahuan WO Perbaikan Baru untuk %s', $prbNotif->komponen->nama_aset),
                            'sound' => 'default',
                            'click_action' => 'OPEN_ACTIVITY_NOTIF',
                            'tipe' => '221', 
                            'id' => $prb
                        ]
                        // ['tipe' => '1', 'id' => $request->id]
                    );
                    // End Notif
                }

                // Lock Disposisi
                // self::lockDisposisi($arrTindakan);
            }

            $arrFm = [];
            $arrNotIn = ['nilaiOri', 'batasWaspada', 'batasBahaya', 'nip', 'foto_lokasi', 'foto_lokasi2'];
            foreach ($result as $key => $value) {
                if (!is_array($value) && !in_array($key, $arrNotIn)) {
                    $arrFm[$key] = $value;
                }
            }

            // reset table FM
            DB::table('FM_'.$request->kode_fm)
                ->where('ms_4w_id', $result['ms_4w_id'])
                ->delete();
            $data = DB::table('FM_'.$request->kode_fm)->insert($arrFm);
            // end reset

            // update ms4w
            $filename = "null";
            // dd($request->foto_lokasi);
            if (!empty($request->foto_lokasi)) {
                $str = explode(",", $request->foto_lokasi);
                $str = explode("/", str_replace(";base64", "", $str[0]));
                $ext = !empty($str[1])?$str[1]:"jpg";

                $filename = sprintf("%d-%d.%s", $result['ms_4w_id'], isset($result['nip'])?$result['nip']:"", $ext);
                // dd($filename);
                base64_to_jpeg($request->foto_lokasi, public_path('uploads/temp/'.$filename));

                $dir = 'monitoring/'.date('Y-m');
                cekDir($dir);

                \Storage::disk($this->disk)->put($dir.'/'.$filename, \File::get(public_path('uploads/temp/'.$filename)));
                unlink(public_path('uploads/temp/'.$filename));
            }

            $filename2 = "null";
            if (!empty($request->foto_lokasi2)) {
                $str = explode(",", $request->foto_lokasi2);
                $str = explode("/", str_replace(";base64", "", $str[0]));
                $ext = !empty($str[1])?$str[1]:"jpg";

                $filename2 = sprintf("%d-%d-2.%s", $result['ms_4w_id'], isset($result['nip'])?$result['nip']:"", $ext);
                base64_to_jpeg($request->foto_lokasi2, public_path('uploads/temp/'.$filename2));

                $dir = 'monitoring/'.date('Y-m');
                cekDir($dir);

                \Storage::disk($this->disk)->put($dir.'/'.$filename2, \File::get(public_path('uploads/temp/'.$filename2)));
                unlink(public_path('uploads/temp/'.$filename2));
            }
            
            Ms4w::where('id', $result['ms_4w_id'])
                ->update([
                    'status' => '1',
                    'tanggal_selesai' => DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')"),
                    'foto_lokasi' => $filename,
                    'foto_lokasi2' => $filename2
                ]);
// dd('halt');
            DB::commit();
            
            return response()->json(['message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }


    public function testjson($kodefm)
    {
        $arrData = [];
        $data = MasterFm::where('kode_fm', $kodefm)->get();

        foreach ($data as $row) {
            $arrData[$row->nama_field] = "";
        }

        echo json_encode($arrData);
    }
    // END
}
