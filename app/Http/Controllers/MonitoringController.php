<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Libraries\ParamChecker;
use Asset\Libraries\ParamCheckerDev;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Asset\User;
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
    Asset\Models\PrwDetail,
    Asset\Models\RoleUser,
    Asset\Models\Role,
    Asset\Role as tuRoleUser;

use DB;
use Datatables;
use Session;
use Validator;
// use Hitung;

class MonitoringController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = '';
        
        $tahun = date('Y');
        $instalasi = Instalasi::whereIn('id', lokasi())->get()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Semua Lokasi             -"];
        $instalasi = $labelInstalasi + $instalasi;        

        return view('pages.monitoring.index', [
            'tahun' => $tahun,
            'data' => $data,
            'instalasi' => $instalasi
        ]);
    }

    public function monitoringData(Request $request)
    {
        DB::connection()->enableQueryLog();
        $idlok = $request->idlok;
        $tahun = !empty($request->year)?$request->year:date('Y');

        if ($idlok == "") {
            /*$query = Ms4w::with(['Ms52w' => function($query) use($arrLokasi) {
                        $query->whereIn('ms_52w.instalasi_id', $arrLokasi);
                    }])
                    ->with('Ms52w.komponen');*/
            $query = Ms4w::select('ms_4w.*', 'ms_52w.instalasi_id', 'aset.kode_aset', 'aset.nama_aset', 'instalasi.name as lokasi', 'aset.bagian', DB::raw("TO_CHAR(ms_4w.TANGGAL_MONITORING, 'YYYY') as tahun"), 'aset.kode_fm')
                ->join('ms_52w', 'ms_4w.ms_52w_id', '=', 'ms_52w.id')
                ->join('aset', 'ms_52w.komponen_id', '=', 'aset.id')
                ->join('instalasi', 'ms_52w.instalasi_id', '=', 'instalasi.id')
                /*->join(DB::connection('oraclesecman')->table('usrtab'), 'ms_4w.petugas', '=', 'usrtab.userid')*/
                ->whereIn('ms_52w.instalasi_id', lokasi())
                ->whereIn('aset.bagian', bagian())
                ->where('aset.kondisi_id', '<>', '12')
                ->whereRaw("TO_CHAR(ms_4w.TANGGAL_MONITORING, 'YYYY') = $tahun")
                ->where('ms_4w.status', '<>', '99');
        } else {
            $query = Ms4w::select('ms_4w.*', 'ms_52w.instalasi_id', 'aset.kode_aset', 'aset.nama_aset', 'instalasi.name as lokasi', 'aset.bagian', DB::raw("TO_CHAR(ms_4w.TANGGAL_MONITORING, 'YYYY') as tahun"), 'aset.kode_fm')
                ->join('ms_52w', 'ms_4w.ms_52w_id', '=', 'ms_52w.id')
                ->join('aset', 'ms_52w.komponen_id', '=', 'aset.id')
                ->join('instalasi', 'ms_52w.instalasi_id', '=', 'instalasi.id')
                /*->join(DB::connection('oraclesecman')->table('usrtab'), 'ms_4w.petugas', '=', 'usrtab.userid')*/
                ->where('ms_52w.instalasi_id', $idlok)
                ->whereIn('aset.bagian', bagian())
                ->where('aset.kondisi_id', '<>', '12')
                ->whereRaw("TO_CHAR(ms_4w.TANGGAL_MONITORING, 'YYYY') = $tahun")
                ->where('ms_4w.status', '<>', '99');
            /*$query->get();
            dd(DB::getQueryLog());*/
        }

        return Datatables::of($query)
                ->addColumn('menu', function ($model) {
                    // dd($model->ms52w->instalasi_id);
                    $edit = '<a href="' . route('monitoring::monitoring-penugasan', ['id' => $model->id, 'idlokasi' => $model->ms52w->instalasi_id, 'idbagian'=>$model->bagian]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Disposisi </a>';
                    return $edit;
                })
                ->make(true);
    }

    public function entri($id, $id4w) 
    {
        $form = MasterFm::where('kode_fm', $id)
                ->where('tipe', 1)
                ->where('AKTIF', 'Y')
                ->orderBy('recid', 'asc')
                ->get();

        if (Schema::hasTable('FM_'.$id)) {
            $data = DB::table('FM_'.$id)
                ->where('ms_4w_id', $id4w)
                ->first();
        } else {
            $data = null;
        }

        $temp = Ms4w::find($id4w);
        $nama_aset = $temp->ms52w->komponen->nama_aset;
        $kodefmOrigin = $temp->ms52w->komponen->kode_fm_pr;

        return view('pages.monitoring.entri', [
            'forms' => $form,
            'id' => $id,
            'ms_4w_id' => $id4w,
            'data' => $data,
            'nama_aset' => $nama_aset,
            'kodefmOrigin' => $kodefmOrigin
        ]);
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

    public function simpan(Request $request)
    {
        DB::beginTransaction();

        try {
            // dd($request->all());
            // dd(json_encode($request->all()));
            /*if (empty($request->foto_lokasi)) {
                return response()->json([
                        'result' => 'Error', 
                        'message' => 'Foto Lokasi tidak boleh kosong'
                ])->setStatusCode(500, 'Error');
            }*/

            /*if (namaRole() != "Super Administrator") {
                dd(json_encode($request->all()));
            }*/

            // Cek minggu
            $data = Ms4w::select('ms_4w.*')
                ->where('id', '=', $request->ms_4w_id)->first();

            $dateW=date("W");

            if ($data['urutan_minggu'] != $dateW) {
                return response()->json([
                        'result' => 'Error', 
                        'message' => 'Tidak bisa menyimpan WO (sudah lewat minggu)'
                ])->setStatusCode(500, 'Error');
            }
            // dd('test');
            // Lets Rock!
            $result = ParamChecker::cekHitungan($request->except(['ms_4w_id']));
            // dd($result);
            $kodefm = $result['kode_fm'];
            unset($result['kode_fm']);
            $result['ms_4w_id'] = $request->ms_4w_id;
            $result['tanggal'] = date('Y-m-d');

            //dd($result['urutan_minggu']);

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
            unset($arrFm['_token']);
            DB::table('FM_'.$kodefm)
                ->where('ms_4w_id', $result['ms_4w_id'])
                ->delete();
            $data = DB::table('FM_'.$kodefm)->insert($arrFm);
            // end reset

            // update ms4w
            $filename = "isi lewat web tanpa Upload foto";
            // Upload foto

            $filename2 = "isi lewat web tanpa Upload foto";
            // Upload foto 2
            
            Ms4w::where('id', $result['ms_4w_id'])
                ->update([
                    'status' => '1',
                    'tanggal_selesai' => getNow(),
                    'foto_lokasi' => $filename,
                    'foto_lokasi2' => $filename2
                ]);
// dd('halt');
            DB::commit();

            Session::flash('success', 'Data berhasil disimpan');
        } catch (Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan. '.$e->getMessage());
        }

        return redirect()->route('todolist::todolist-index');
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

    private static function resetTindakan($tindakan, $ms4w)
    {
    	if ($tindakan == 'prw') {
    		$clearDetail = PrwDetail::whereRaw('prw_data_id in (select id from prw_data where ms_4w_id = '.$ms4w.')')->delete();
    		Perawatan::where('ms_4w_id', $ms4w)->delete();
    	} else {
    		$clearDetail = PrbDetail::whereRaw('prb_data_id in (select id from prb_data where ms_4w_id = '.$ms4w.')')->delete();
    		Perbaikan::where('ms_4w_id', $ms4w)->delete();
    	}
    }

    public function update(Request $request)
    {
        DB::beginTransaction();

        try {
            $id = $request->id;

            // hitungan 
            $result = ParamChecker::cekHitungan($request->except(['_token', 'ms_4w_id']));
            unset($result['kode_fm']);
            $result['ms_4w_id'] = $request->ms_4w_id;
            $result['tanggal'] = date('Y-m-d');
            // hitungan

            if ((strlen($result['waspada']) > 0) || (strlen($result['bahaya']) > 0)) {
                $arrTindakan = $this->tindakan($request->ms_4w_id);
                // $arrTindakan['petugas_id'] = trim(\Auth::user()->userid); 
                $arrTindakan['tanggal'] = date('Y-m-d');

                if (strlen($result['waspada']) > 0) {
                    Perawatan::where('ms_4w_id', $request->ms_4w_id)
                        ->update($arrTindakan);
                }

                if (strlen($result['bahaya']) > 0) {
                    $arrTindakan['tipe'] = 'monitoring';
                    Perbaikan::where('ms_4w_id', $request->ms_4w_id)
                        ->update($arrTindakan);
                }
            }

            $data = DB::table('FM_'.$request->kode_fm)
                ->where('id', $id)
                ->update($request->except(['_token', 'kode_fm']));

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan. '.$e->getMessage());
        }

        return redirect()->route('todolist::todolist-index');
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

    public function penugasan($id, $idlokasi, $idbagian)
    {        
        DB::enableQueryLog();        
        /*$data = tuRoleUser::where('recidrole', \Auth::user()->role->jabatan->recidjabatan)
            ->get();
            
        $nip = MasterJab::where('master_jab.lokasi', lokasi(\Auth::user()->userid, 'str'))
            ->where('master_jab.bagian', 'like', '%'.$idbagian.'%')
            ->where('master_jab.bagian', 'like', bagian(\Auth::user()->userid, 'str'))
            ->whereNotIn('nip', function($query){ //not admin
                $query->select('user_id')
                    ->from('ru_role_user')->where('role_id', env('IDSUPERADMIN', '1'));
            }) 
            ->where('nip', '<>', \Auth::user()->userid) //not spv
            ->get();

        $arrNip = [];
        foreach ($nip as $row) {
            $arrNip[] = sprintf("'%s'", $row->nip);
        }
        $strNip = implode(',', $arrNip);*/

        $arrUser = ["" => "-             Pilih Petugas             -"];

        $arrUser = ["" => "-             Pilih Petugas             -"];
        $users = tuRoleUser::select('nip', 'nama')
            ->whereNull('is_manajer')
            ->where('recidrole', \Auth::user()->role->jabatan->recidjabatan)
            ->get();

            // dd($users);
        foreach ($users as $row) {
            $arrUser[trim($row->nip)] = trim($row->nama);
        }        

        return view('pages.monitoring.penugasan', [
            'petugas' => $arrUser,
            'id' => $id,
            'idlokasi' => $idlokasi
        ]);
    }

    public function penugasanSimpan(Request $request)
    {
        DB::beginTransaction();

        try {            
            $id = $request->id;

            // close wo monitoring jika masih ada perbaikan belum close
            $aset = Ms4w::select('ms_52w.komponen_id')
                ->join('ms_52w', 'ms_52w.id', '=', 'ms_4w.ms_52w_id')
                ->where('ms_4w.id', $id)->first();
            // dd();    
            $prbExist = Perbaikan::where('komponen_id', $aset->komponen_id)
                ->whereNotIn('status', ['10', '99'])
                ->whereNotNull('petugas_id')
                ->count();
// dd($id);
            if ($prbExist > 0) {
                $data = Ms4w::where('id', $id)
                    ->update(['status' => '99']);

                DB::commit();
                Session::flash('warning', 'Disposisi tidak dapat dilakukan, karena masih ada WO Perbaikan pada aset ini yang belum ditutup');
            } else {
                // Generate kode wo
                /*$arrKode = Ms4w::find($id);
                // $arrKode->ms52w->komponen
                $urutan = Ms4w::select(DB::raw('max(urutan) as urutan'))
                    ->whereRaw("TO_CHAR(TANGGAL, 'MM') = ".date('m'))
                    ->first();
                
                $gen = generateKodeWo('monitoring', 
                        !empty($urutan->urutan)?$urutan->urutan++:1,
                        $arrKode->ms52w->komponen->bagian,
                        $arrKode->ms52w->komponen->instalasi_id,
                        date('d-m-Y'));
                dd($request->all());*/
                // ./Generate kode wo

                $data = Ms4w::where('id', $id)
                    ->update($request->except(['_token', 'kode_fm']));
                
                DB::commit();
                Session::flash('success', 'Data berhasil disimpan');
            }
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan. '.$e->getMessage());
        }

        return redirect()->route('monitoring::monitoring-index');
    }


    /* METHOD FOR DEVELOPMENT PURPOSE */
    public function simpandev(Request $request)
    {
    	dd('dev');
        DB::beginTransaction();

        try {
            $result = ParamChecker::cekHitungan($request->except(['_token', 'ms_4w_id']));
            dd($result);
            $kodefm = $result['kode_fm'];
            unset($result['kode_fm']);
            $result['ms_4w_id'] = $request->ms_4w_id;
            $result['tanggal'] = date('Y-m-d');

            if ((strlen($result['waspada']) > 0) || (strlen($result['bahaya']) > 0)) {
                $arrTindakan = $this->tindakan($request->ms_4w_id);
                $arrTindakan['tanggal'] = date('Y-m-d');

                if (strlen($result['waspada']) > 0) {
                    // reset tabel perawatan dan detail
                    $clearDetail = PrwDetail::whereRaw('prw_data_id in (select id from prw_data where ms_4w_id = '.$result['ms_4w_id'].')')->delete();
                    Perawatan::where('ms_4w_id', $result['ms_4w_id'])->delete();
                    // end reset

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
                }

                if (strlen($result['bahaya']) > 0) {    
                    // reset tabel perawatan dan detail
                    $clearDetail = PrbDetail::whereRaw('prb_data_id in (select id from prb_data where ms_4w_id = '.$result['ms_4w_id'].')')->delete();
                    Perbaikan::where('ms_4w_id', $result['ms_4w_id'])->delete();
                    // end reset

                    $arrTindakan['tipe'] = 'monitoring';
                    $prb = Perbaikan::insertGetId($arrTindakan);
                    
                    if (count($result['batasBahaya']) > 0) {
                        $prbDetail = [];

                        foreach ($result['batasBahaya'] as $key => $value) {
                            $prbDetail[] = [
                                'prb_data_id' => $prb,
                                'kode_fm' => $kodefm,
                                'nama_field' => $key,
                                'nilai_batas' => $value,
                                'nilai_asli' => isset($result['nilaiOri'][$key])?$result['nilaiOri'][$key]:"0"
                            ];     
                        }          
                        PrbDetail::insert($prbDetail);
                        // dd('sukses');
                    }
                }
            }

            $arrFm = [];
            $arrNotIn = ['nilaiOri', 'batasWaspada', 'batasBahaya'];
            foreach ($result as $key => $value) {
                if (!is_array($value) && !in_array($key, $arrNotIn)) {
                    $arrFm[$key] = $value;
                }
            }

            // reset table FM
            DB::table('FM_'.$request->kode_fm)
                ->where('ms_4w_id', $result['ms_4w_id'])
                ->delete();
            // end reset
            $data = DB::table('FM_'.$request->kode_fm)->insert($arrFm);

            // update 4W
            Ms4w::where('id', $result['ms_4w_id'])
                ->update(['status' => '1']);

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch (Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan. '.$e->getMessage());
        }

        return redirect()->route('todolist::todolist-index');
    }
    /* END METHOD FOR DEVELOPMENT PURPOSE */
}
