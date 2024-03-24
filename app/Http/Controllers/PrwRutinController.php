<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Http\Controllers\Api\SukuCadangController;

use Asset\Models\Template,
    Asset\Models\Komponen,
    Asset\Models\KomponenDetail,
    Asset\Models\Instalasi,
    Asset\Models\Aset,
    Asset\Models\Ms52w,
    Asset\Models\Ms4w,
    Asset\Models\Prw52w,
    Asset\Models\Prw4w,
    Asset\Models\Master,
    Asset\Models\MasterJab,
    Asset\Models\Perawatan,
    Asset\Models\Perbaikan,
    Asset\Models\MsPrwrutin,
    Asset\Models\JadwalLibur,
    Asset\Models\JadwalLiburPompa,
    Asset\Models\PrwrutinPdm,
    Asset\Models\PermohonanSc,
    Asset\Models\PermohonanScDetail,
    Asset\Role as tuRoleUser,
    Asset\Models\PmlKeluhan;

use DB;
use Session;
use DateTime;
use Validator;
use Datatables;

class PrwRutinController extends Controller
{
	var $woPrw = array();
    var $woScJml = array();

    public function entri52w()
    {
        $instalasi = Instalasi::whereIn('id', lokasi())
            ->get()->pluck('name', 'id')->toArray(); 
        $labelInstalasi = ["" => "- Pilih Lokasi -"];
        $instalasi = $labelInstalasi + $instalasi;

        $asset = [];
        $template = [];
        $kodepart = [];
        // $template = Template::get()->pluck('nama', 'id')->toArray();        
        // array_unshift($template, "- Pilih Equipment -");
        $komponen = [];

        return view('pages.perawatan_rutin.entri52w',
            ['instalasi' => $instalasi,
            'asset' => $asset,
            'template' => $template,
            'kodepart' => $kodepart,
            'komponen' => $komponen
        ]);
    }

    // Part 52W
    public function lokasiSelect($id)
    {
        $bb = Aset::whereIn('bagian', bagian())
            ->where('instalasi_id', $id)
            ->where('kondisi_id', '<>', '12')
            ->get();
        $template = '';
        foreach ($bb as $row) {
            $template .= '<option value="' . $row->id . '">' . $row->nama_aset . '</option>';
        }

        return response()->json([
            'template' => $template
        ]);
    }

    public function komponenSelect($id)
    {
        $aset = Aset::find($id);
        $kodefm = !empty($aset->kode_fm_pr)?$aset->kode_fm_pr:"";
// dd($kodefm);
        $aa = PrwrutinPdm::select('kode_part')
            ->whereHas('part', function($query) use($kodefm){
                $query->where('kode_fm', $kodefm);
            })
            ->where('komponen_id', $id)
            ->groupBy('kode_part')
            ->get();

        $txt = '<option value=""></option>';        
        foreach ($aa as $row) {
            $txt .= '<option value="' . $row->kode_part . '">' . $row->part->part . '</option>';
        }

        return response()->json([
            'data' => $txt
        ]);
    }

    public function kodepartSelect($id, $kodepart, $tahun)
    {
        DB::connection()->enableQueryLog();
        if ($tahun == "") {
            $tahun = date('Y');
        }

        $arrData = [];
        $part = PrwrutinPdm::where('komponen_id', $id)
            ->where('kode_part', $kodepart)
            ->get();

        if (!empty($part)) {
            $arrTmp = [];

            $p52w = Prw52w::where('komponen_id', $id)
                ->where('part', $kodepart)
                ->where('tahun', $tahun)
                ->get();

            if (sizeof($p52w) > 0) {
                foreach ($p52w as $row) {
                    $arrTmp[$row->perawatan] = [
                        'id' => $row->id,
                        'instalasi_id' => $row->instalasi_id,
                        'komponen_id' => $row->komponen_id,
                        'minggu_mulai' => $row->minggu_mulai,
                        'jumlah_orang' => $row->jumlah_orang,
                        'total_durasi' => $row->total_durasi,
                        'part' => $row->part,
                        'perawatan' => $row->perawatan,
                        'prw_rutin_id' => $row->prw_rutin_id
                    ];
                }
            }
// dd($arrTmp);
            foreach ($part as $row) {
                // dd();
                if (!empty($row)) {
                    $arrData[] = [
                        'id'    =>  !empty($arrTmp[$row->perawatan]['id'])?$arrTmp[$row->perawatan]['id']:"",
                        'perawatan' => !empty($arrTmp[$row->perawatan]['perawatan'])?$arrTmp[$row->perawatan]['perawatan']:$row->perawatan,
                        'frekuensi' => $row->nilai,
                        'minggu_mulai' => !empty($arrTmp[$row->perawatan]['minggu_mulai'])?$arrTmp[$row->perawatan]['minggu_mulai']:"",
                        'jumlah_orang' => !empty($arrTmp[$row->perawatan]['jumlah_orang'])?$arrTmp[$row->perawatan]['jumlah_orang']:"",
                        'total_durasi' => !empty($arrTmp[$row->perawatan]['total_durasi'])?$arrTmp[$row->perawatan]['total_durasi']:""
                    ];
                }
            }
            // dd
        }
// dd($arrData);
        return view('pages.perawatan_rutin.part52w',
            ['arrdata' => $arrData])->render();
    }

    public function simpan52w(Request $request)
    {
        DB::beginTransaction();

        try {
            // dd($request->all());
            if (count($request->periksa) > 0) {
                if ($request->tahun != "") {
                    $tahun = $request->tahun;
                } else {
                    $tahun = date('Y');
                }

                $equipment_id = null;
                $aset = Aset::find($request->komponen_id);
                if ($aset) {
                    if ($aset->equipment == 'yes') {
                        $equipment_id = $request->komponen_id;
                    } else {
                        $equipment_id = $aset->equipment_id;
                    }
                }

                foreach ($request->periksa as $row) {

                    if ($row['id']!="") {
                        $jmlTemp = Prw52w::where('instalasi_id', $request->instalasi_id)
                            ->where('komponen_id', $request->komponen_id)
                            ->where('part', $request->kodepart)
                            ->where('perawatan', $row['perawatan'])
                            ->where('tahun', $tahun)
                            ->count();

                        if ($jmlTemp > 0) {
                            $data = Prw52w::where('id', $row['id'])
                                ->update([
                                    'tahun' => $request->tahun,
                                    'instalasi_id' => $request->instalasi_id,
                                    'komponen_id'  => $request->komponen_id,
                                    'equipment_id'  => $equipment_id,
                                    'part'  => $request->kodepart,

                                    'minggu_mulai' => isset($row['minggu_mulai'])?$row['minggu_mulai']:0,
                                    'jumlah_orang' => isset($row['jumlah_orang'])?$row['jumlah_orang']:0,
                                    'total_durasi' => isset($row['total_durasi'])?$row['total_durasi']:0,
                                    'perawatan'    => isset($row['perawatan'])?$row['perawatan']:0,
                                    'updated_at' => getNow()
                                ]);
                        } else {
                            $data = new Prw52w();

                            $data->tahun = $request->tahun;
                            $data->instalasi_id = $request->instalasi_id;
                            $data->komponen_id = $request->komponen_id;
                            $data->equipment_id  = $equipment_id;
                            $data->part = $request->kodepart;

                            $data->minggu_mulai = isset($row['minggu_mulai'])?$row['minggu_mulai']:0;
                            $data->jumlah_orang = isset($row['jumlah_orang'])?$row['jumlah_orang']:0;
                            $data->total_durasi = isset($row['total_durasi'])?$row['total_durasi']:0;
                            $data->perawatan    = isset($row['perawatan'])?$row['perawatan']:0;

                            $data->save();
                        }
                    } else {
                        $data = new Prw52w();

                        $data->tahun = $request->tahun;
                        $data->instalasi_id = $request->instalasi_id;
                        $data->komponen_id = $request->komponen_id;
                        $data->equipment_id  = $equipment_id;
                        $data->part = $request->kodepart;

                        $data->minggu_mulai = isset($row['minggu_mulai'])?$row['minggu_mulai']:0;
                        $data->jumlah_orang = isset($row['jumlah_orang'])?$row['jumlah_orang']:0;
                        $data->total_durasi = isset($row['total_durasi'])?$row['total_durasi']:0;
                        $data->perawatan    = isset($row['perawatan'])?$row['perawatan']:0;

                        $data->save();
                    }
                }
            }

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('mstrategi::mstrategi-rutin52w');
    }

    public function entri4w()
    {
        DB::connection()->enableQueryLog();
        $nipception = ['10601445'];
        $komponen = [];
        $kodepart = [];

        $instalasi = Instalasi::whereIn('id', lokasi())
            ->get()->pluck('name', 'id')->toArray(); 
        $labelInstalasi = ["" => "- Pilih Lokasi -"];
        $instalasi = $labelInstalasi + $instalasi;
// dd($instalasi);
        $week = [
            "" => "- Pilih Minggu-"
        ];

        if (namaRole()=="Super Administrator" || in_array(trim(\Auth::user()->userid), $nipception)) {
            if ( in_array(trim(\Auth::user()->userid), $nipception) ) {
                for ($i = 1; $i <= 53; $i++) { 
                    $week[$i] = "Minggu ke ".$i;
                }
            } else {
                for ($i = 1; $i <= 52; $i++) { 
                    $week[$i] = "Minggu ke ".$i;
                }
            }
        } else {
            $curWeek = date('W');
            // $curWeek = 48;

            if ($curWeek == '52') {
                $b_atas = (int)0;
                $b_bawah = (int)4;
            } else {
                $b_atas = (int)floor($curWeek / 4) * 4;
                $b_bawah = (int)floor($curWeek / 4) + 1;
                $b_bawah *= 4;
            }

            if ($curWeek % 4 == 0 || in_array(trim(\Auth::user()->userid), $nipception)) {
                for ($i = $b_atas + 1; $i <= $b_bawah; $i++) { 
                    $week[$i] = "Minggu ke ".$i;
                }
            }
            // CUSTOM (PLEASE REMARK AFTER USE)
            else {  
                $b_atas = (int)13;
                $b_bawah = (int)16;

                for ($i = $b_atas; $i <= $b_bawah; $i++) { 
                    $week[$i] = "Minggu ke ".$i;
                }
            }
        }

        $bagian = Master::Bagian()
                ->get()->pluck('name', 'id')->toArray();
        $label = ["" => "- Pilih Bagian -"];
        $bagian = $label + $bagian;
// dd(date('W'));
        $tahun = date('Y');
        if (date('W') == '52') {
            $tahun = $tahun + 1;
        }
        // dd($tahun);
        
        return view('pages.perawatan_rutin.entri4w',[
            // 'aset' => $aset,
            'week' => $week,
            'tahun' => $tahun,
            'bagian' => $bagian,
            'instalasi' => $instalasi,
            'komponen' => $komponen,
            'kodepart' => $kodepart
        ]);
    }

    public function showPrwrutin(Request $request)
    {
        DB::connection()->enableQueryLog();
        $tahun = $request->tahun;
        $week = $request->week;
        $bagian = $request->bagian;
        $lokasi = $request->lokasi;
        // $asetId = $request->komponen_id;

        // dd(\Auth::user()->role->jabatan->recidjabatan);
        DB::connection()->enableQueryLog();
        
        if ($tahun == "") {
            $tahun = date('Y');
        }
        $weekMin = $week-1;

        // in case using jadwal libur
        /*$komponen = Prw52w::select('prw_52w.*', 'aset.nama_aset', 'aset.kode_barcode', 'aset.kode_fm', 'aset.id as aset_id', 'prw_4w.id as prw_4w_id', 'prw_4w.prw_52w_id', 'prw_4w.urutan_minggu', 'prw_4w.hari', 'prw_4w.petugas', 'prw_4w.wo_id', 'ms_komponen_detail.part', 'ms_komponen_detail.id as part_id', 'prw_rutin_pdm.id as pdm_id')
                    ->join('aset', 'prw_52w.komponen_id', '=', 'aset.id')
                    ->join('prw_rutin_pdm', 'prw_52w.komponen_id', '=', DB::raw("prw_rutin_pdm.komponen_id and (prw_52w.part = prw_rutin_pdm.kode_part and prw_52w.perawatan = prw_rutin_pdm.perawatan)"))
                    ->join('ms_komponen_detail', 'prw_52w.part', '=', 'ms_komponen_detail.id')
                    ->leftJoin('prw_4w', 'prw_52w.id', '=', DB::raw("prw_4w.prw_52w_id and prw_4w.urutan_minggu = $week"))
                    ->where('prw_52w.tahun', $tahun)
                    ->where('aset.instalasi_id', $lokasi)
                    ->where('aset.kondisi_id', '<>', '12');

        // Check Admin for bagian
        if (namaRole() == 'Super Administrator') {
            $komponen = $komponen->whereIn('aset.bagian', ['1', '2']);
        } else {
            $komponen = $komponen->whereIn('aset.bagian', bagian());
        }

        $komponen = $komponen->join('jadwal_libur_pompa', 'prw_52w.equipment_id', '=', 'jadwal_libur_pompa.equipment_id')
                    ->whereRaw('(mod(prw_52w.minggu_mulai,SUBSTR(prw_rutin_pdm.nilai,2,2)) = mod('.$week.',SUBSTR(prw_rutin_pdm.nilai,2,2)) OR (prw_52w.id not in (select prw_52w_id from prw_4w where urutan_minggu = '.$weekMin.' and prw_52w_id = prw_52w.id) AND prw_52w.minggu_mulai = mod('.$weekMin.',SUBSTR(prw_rutin_pdm.nilai,2,2))))')
                    // ->whereRaw('prw_52w.id not in (select prw_52w_id from prw_4w where urutan_minggu = '.$weekMin.')')
                    ->where(function($query) use($week, $weekMin){
                        $query->where('jadwal_libur_pompa.minggu', 'like', '%,'.$week.',%')
                            // ->orWhere('jadwal_libur_pompa.minggu', 'not like', '%,'.$weekMin.',%');  
                            ->orWhere(function($sql) use($week, $weekMin){
                                $sql->where('jadwal_libur_pompa.minggu', 'not like', '%,'.$weekMin.',%')
                                    ->where('jadwal_libur_pompa.minggu', 'like', '%,'.$week.',%');
                            });   
                    });
        $komponen = $komponen->get();*/
        
        // in case NOT using jadwal libur
        $komponenSecond = Prw52w::select('prw_52w.*', 'aset.nama_aset', 'aset.kode_barcode', 'aset.kode_fm', 'aset.id as aset_id', 'prw_4w.id as prw_4w_id', 'prw_4w.prw_52w_id', 'prw_4w.urutan_minggu', 'prw_4w.hari', 'prw_4w.petugas', 'prw_4w.wo_id', 'ms_komponen_detail.part', 'ms_komponen_detail.id as part_id', 'prw_rutin_pdm.id as pdm_id')
                ->join('aset', 'prw_52w.komponen_id', '=', 'aset.id')
                ->join('prw_rutin_pdm', 'prw_52w.komponen_id', '=', DB::raw("prw_rutin_pdm.komponen_id and prw_52w.part = prw_rutin_pdm.kode_part and prw_rutin_pdm.perawatan = prw_52w.perawatan"))
                // ->join('prw_rutin_pdm', 'prw_52w.komponen_id', '=', 'prw_rutin_pdm.komponen_id')
                ->leftJoin('prw_4w', 'prw_52w.id', '=', DB::raw("prw_4w.prw_52w_id and prw_4w.urutan_minggu = $week"))
                // ->join('ms_komponen_detail', 'prw_52w.part', '=', 'ms_komponen_detail.id')
                ->join('ms_komponen_detail', 'prw_rutin_pdm.kode_part', '=', 'ms_komponen_detail.id')
                ->where('prw_52w.tahun', $tahun)
                ->where('aset.instalasi_id', $lokasi)
                ->where('aset.kondisi_id', '<>', '12')
                ->whereRaw('(mod(prw_52w.minggu_mulai,SUBSTR(prw_rutin_pdm.nilai,2,2)) = mod('.$week.',SUBSTR(prw_rutin_pdm.nilai,2,2)))')
                ->whereNull('prw_rutin_pdm.deleted_at')
                ->whereNull('prw_4w.deleted_at');
         // END:in case NOT using jadwal libur

        // Check Admin for bagian
        if (namaRole() == 'Super Administrator') {
            $komponenSecond = $komponenSecond->whereIn('aset.bagian', [$bagian]);
        } else {
            $komponenSecond = $komponenSecond->whereIn('aset.bagian', bagian());
        }
        $komponenSecond = $komponenSecond->get();
        
        // END:in case NOT using jadwal kerja

        // MERGE
        // $merged = $komponenSecond->merge($komponen);
        $merged = $komponenSecond;
        // $resultkomponen = $merged->all();
        $resultkomponen = $merged;
// dd($resultkomponen);
        $jmlKomponen = count($resultkomponen);
        // end:MERGE

// dd($jmlKomponen);
        $arrkomponen = [];
        $arrwo = [];
        $pairAset = [];
        foreach ($resultkomponen as $row) {
            $arrkomponen[$row->komponen_id] = $row->nama_aset;
            if (!empty($row->wo_id)) {
                $arrwo[$row->komponen_id] = $row->wo_id;
            }
            $pairAset[$row->wo_id] = $row->nama_aset;
        }
// dd($arrwo);
        // List Petugas
        $nipLokasi = MasterJab::where('lokasi', 'like', '%'.$lokasi.'%')
            ->get()->pluck('nip')->toArray();

        $arrUser = ["" => "-             Pilih Petugas             -"];
        $users = tuRoleUser::select('nip', 'nama')
            ->whereNull('is_manajer')
            ->where('recidrole', \Auth::user()->role->jabatan->recidjabatan)
            ->get();
        foreach ($users as $row) {
            if (in_array($row->nip, $nipLokasi)) {
                $arrUser[trim($row->nip)] = trim($row->nama);
            }
        }

        // Part Suku Cadang
        $sukucadang = null;
        $dataSc = [];        
        $tmpKdAlias = [];
        // Get Existing Suku cadang
        if (sizeof($arrwo) > 0) {
            $dataSc = PermohonanSc::select('permohonan_sc.prw_rutin_id', 'permohonan_sc_detail.*')
                ->join('permohonan_sc_detail', 'permohonan_sc.id', '=', 'permohonan_sc_detail.permohonan_sc_id')
                ->whereIn('prw_rutin_id', $arrwo)
                ->get();
            $tmpKdAlias = $dataSc->pluck('kode_alias')->toArray();
        }

        // Dropdown Sukucadang
        $pairKodeAlias = [];
        $sc = ["" => "-  Pilih Suku Cadang  -"];
        $sqlSc = DB::connection('koneksigudang')->table('v_saldogdg')
            ->select('v_saldogdg.kd_barang', 'v_saldogdg.gudang', 'v_saldogdg.kelompok_barang', 'v_saldogdg.saldo', 'm_barang.kd_barang_alias', 'm_barang_alias.nama', 'm_barang.SATUAN', 'm_gudang.nama_gdg')
            ->join('m_barang', 'v_saldogdg.kd_barang', '=', 'm_barang.kd_barang')
            ->join('m_barang_alias', 'm_barang.kd_barang_alias', '=', 'm_barang_alias.recid')
            ->join('m_gudang', 'v_saldogdg.gudang', '=', 'm_gudang.kd_gdg')
            ->where('gudang', 'like', 'GSC%')
            ->get();
        if (count($sqlSc) > 0) {
            foreach ($sqlSc as $row) {
                $sc[$row->kd_barang_alias.'#'.$row->saldo.'#'.$row->gudang.'#'.$row->kelompok_barang] = $row->nama.' @ '.$row->nama_gdg;

                if (in_array($row->kd_barang_alias, $tmpKdAlias)) {
                    $pairKodeAlias[$row->kd_barang_alias] = $row->nama;
                }
            }
        }

        $arrTmp = [];
        foreach ($resultkomponen as $row) {
            if ($row->wo_id == '2414') $arrTmp[] = $row->prw_4w_id;
        }
// dd($arrTmp);
        return view('pages.perawatan_rutin.part4w',[
            'komponens' => $resultkomponen,
            'arrkomponen' => $arrkomponen,
            'petugas' => $arrUser,
            'dataSc' => $dataSc,
            'cbSukucadang' => $sc,
            'sukucadang' => $sukucadang,
            'arrPair' => !empty($arrwo)?array_flip($arrwo):null,
            'pairKodeAlias' => $pairKodeAlias,
            'pairAset' => $pairAset,
            'jmlKomponen' => $jmlKomponen])->render();
    }

    public function simpanRutin4w(Request $request)
    {
        DB::beginTransaction();
        try {
            if (is_array($request->week) && count($request->week) > 0) {
                // dd($request->all());
                $asetBatch = null;
                $sequence = DB::getSequence();
                // dd($request->week);
                foreach ($request->week as $row) {
                    // self::sukucadang($row, $request->arrsukucadang);

                    $urutanMinggu = $request->urutan_minggu;

                    if (isset($row['is_equipment'])) {
                        if ($row['is_equipment'] == 'no' && isset($row['equipment_id'])) {
                            $jadwalKerja = JadwalLiburPompa::where('equipment_id', $row['equipment_id'])->first();
                        }else{
                            $jadwalKerja = JadwalLiburPompa::where('equipment_id', $row['aset_id'])->first();
                        }
                    }

                    if ($jadwalKerja) {
                        if (!in_array($urutanMinggu, explode(",", $jadwalKerja->minggu))) {
                            $urutanMinggu = $urutanMinggu + 1;
                        }
                    }

                    // Tanggal Monitoring
                    $tgl_monitor = new \DateTime();
                    $tgl_monitor->setISODate($request->tahun, $request->urutan_minggu);
                    // End Tanggal Monitoring

                    if (isset($row['id'])) {
        	            // asetBatch // generate WO ID
                        if (!empty($row['wo_id'])) {
                            $woId = $row['wo_id']; 
                        }

        	            if (empty($asetBatch)) {
        	            	$asetBatch = $row['aset_id'];
        	            	$petugas = isset($row['petugas'])?strtoupper($row['petugas']):"";

        	            	if (empty($row['wo_id'])) $woId = $sequence->nextValue('PRW_4W_WO_ID_SEQ');
        	            }

        	            if ($row['aset_id'] != $asetBatch) {
        	            	$asetBatch = $row['aset_id'];
        	            	$petugas = isset($row['petugas'])?strtoupper($row['petugas']):"";

        	            	if (empty($row['wo_id'])) $woId = $sequence->nextValue('PRW_4W_WO_ID_SEQ');
        	            }
        	            // end asetBatch

                        // Cek Perbaikan aset tidak dapat beroperasi
                        $status = '0';
                        $jmlNonOp = Perbaikan::where('komponen_id', $row['aset_id'])
                            ->whereNotIn('status', config('custom.skipStatus'))
                            ->where('kondisi', 'tidak beroperasi')
                            ->count();

                        if ($jmlNonOp > 0) {
                            $status = '99';
                        }

                        if ($row['id']!="") {
                            $data = Prw4w::withTrashed()->find($row['id']);
// dd($row['id']);
                            if ( !empty($data) ) {
                                $data->petugas = $petugas;
                                $data->tanggal = date('Y-m-d');
                                $data->tanggal_monitoring = $tgl_monitor->format('Y-m-d');
                                $data->manajer = manajer(\Auth::user()->userid);
    // dd($data);
                                $data->save();
                                DB::commit();
                                
                                // Reset Suku Cadang
                                if (!in_array($woId, $this->woPrw)) {
                                    self::resetSc($woId);
                                }
                                // end reset
                                // Store Suku Cadang
                                if (sizeof($request->arrsukucadang) > 0 && !in_array($woId, $this->woPrw)) {
                                    $i = 0;
                                    foreach ($request->arrsukucadang as $sukucadang) {
                                        if ($sukucadang['komponen_id'] == $row['aset_id'] && !in_array($woId, $this->woPrw)) {
                                            self::sukucadang($row, $request->arrsukucadang);

                                            $i++;
                                        }
                                    }

                                    if ($i > 0) {
                                        if ($status == '0') $status = '0.3';

                                        Prw4w::where('wo_id', $woId)->update(['status' => $status]);
                                        self::notif($row['aset_id']);
                                    } else {
                                        Prw4w::where('wo_id', $woId)->update(['status' => $status]);
                                    }
                                    $this->woPrw[] = $woId;
                                    $this->woScJml[$woId] = $i;
                                }
                                DB::commit();
                                // End Store Suku Cadang
                            }
                            
                        } else {
                            $cek = Prw4w::where('prw_52w_id', $row['prw_52w_id'])
                                ->where('urutan_minggu', $request->urutan_minggu)
                                ->count();

                            if ($cek == 0) {
                                $data = new Prw4w();

                                $data->prw_52w_id = $row['prw_52w_id'];
                                $data->urutan_minggu = $request->urutan_minggu;
                                $data->hari = isset($row['hari'])?strtoupper($row['hari']):"";
                                $data->petugas = $petugas;
                                $data->tanggal = date('Y-m-d');
                                $data->tanggal_monitoring = $tgl_monitor->format('Y-m-d');
                                $data->manajer = manajer(\Auth::user()->userid);

                                $data->wo_id = $woId;
                                $row['wo_id'] = $woId;

                                $data->status = $status;

                                // kode wo
                                $prw52w = Prw52w::find($row['prw_52w_id']);
                                $urutnya = prwRutinUrutan();
                                $gen = generateKodeWo('prwRutin',
                                    $urutnya,
                                    $prw52w->komponen->bagian,
                                    $prw52w->instalasi_id,
                                    date('Y-m-d')
                                );
                                $data->kode_wo = $gen;
                                $data->urutan = $urutnya;
                                // kode wo

                                $data->save();
                                DB::commit();

                                // Reset Suku Cadang
                                if (!in_array($woId, $this->woPrw)) {
                                    self::resetSc($woId);
                                }
                                // end reset
                                // Store Suku Cadang
                                if (sizeof($request->arrsukucadang) > 0 && !in_array($woId, $this->woPrw)) {
                                    $i = 0;
                                    foreach ($request->arrsukucadang as $sukucadang) {
                                        if ($sukucadang['komponen_id'] == $row['aset_id'] && !in_array($woId, $this->woPrw)) {
                                            self::sukucadang($row, $request->arrsukucadang);

                                            $i++;
                                        }
                                    }

                                    if ($i > 0) {
                                        if ($status == '0') $status = '0.3';

                                        Prw4w::where('wo_id', $woId)->update(['status' => $status]);
                                        self::notif($row['aset_id']);
                                    }

                                    $this->woPrw[] = $woId;
                                    $this->woScJml[$woId] = $i;
                                } else {
                                    if ($status == '0') $status = '0.3';
                                    
                                    if ($this->woScJml[$woId] > 0) {
                                        Prw4w::where('wo_id', $woId)->update(['status' => $status]);
                                    }
                                }
                                DB::commit();
                                // End Store Suku Cadang
                            }
                        }
                    }
                }
            }

            Session::flash('success', 'Data berhasil disimpan');
        } catch (Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');       
        }

        return redirect()->route('mstrategi::mstrategi-simpanRutin4w'); 
    }

    private static function resetSc($woId)
    {
        $arrRst = PermohonanSc::where('prw_rutin_id', $woId)
            ->get()
            ->pluck('id')
            ->toArray();
        if (sizeof($arrRst) > 0) {
            PermohonanSc::destroy($arrRst);
        }
    }

    private static function sukucadang($data, $arrSc)
    {
        // dd($arrSc);
        $aset = Aset::find($data['aset_id']);
        $title = sprintf("INI TEST ABAIKAN SAJA Permohonan Suku Cadang untuk Perawatan Rutin %s", $aset->nama_aset);
        $fid = $data['wo_id'];
        $wo = "prw-rutin"; //utk d header
        
        if ($aset->bagian == '4') {
            $kdUnit = "B33 ";
        } else {
            $kdUnit = "B32 ";
        }

        // reformat array
        $arrDetail = [];
        foreach ($arrSc as $row) {
            if ($row['komponen_id'] == $data['aset_id']) {
                $arrDetail[] = [
                    'kode_alias' => $row['kode_alias'],
                    'jumlah' => $row['jumlah'],
                    'keterangan' => $row['keterangan'],
                    'dibeli_by' => $row['dibeli_by'],
                    'kelompok_barang' => $row['kelompok_barang'],
                ]; 
            }
        }

        $header = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'nip' => trim(\Auth::user()->userid),
            'wo' => $wo
        ];
        $postData = [
            'nama' => $title,
            'kd_unitkrj' => $kdUnit,
            'detail' => $arrDetail,
            'fid' => $fid,
            'bagian_id' => $aset->bagian,
            'prw_rutin_id' => $fid
        ];
// dd($postData['detail']);
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', \URL::to('/').'/api/sukucadang/permohonan', [
            'headers' => $header,
            'form_params' => $postData,
            'verify' => false,
        ]);

        $response = $response->getBody()->getContents();
        $response = json_decode($response);
// dd('halt');
        return true;
    }

    private static function notif($asetId)
    {
        $aset = Aset::find($asetId);
        $tipe = '263';

        kirimnotif(manajer(\Auth::user()->userid),
            [
                'title' => 'Approval WO Perawatan Rutin',
                'text' => sprintf('Approval WO Perawatan Rutin %s', $aset->nama_aset),
                'sound' => 'default',
                'click_action' => 'OPEN_ACTIVITY_NOTIF',
                'tipe' => $tipe, 
                'id' => $id
            ]
        );
    }

    // TO DO LIST
    public function todolist()
    {
        $data = '';
        $week = [
            "" => "- Pilih Minggu-"
        ];

        for ($i = 1; $i <= 52; $i++) { 
            $week[$i] = "Minggu ke ".$i;
        }

        $instalasi = Instalasi::get()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Pilih Lokasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        $bagian = [
            "" => "Pilih Bagian",
            "1" => "Mekanikal",
            "2" => "Elektrikal",
            "3" => "Instrumentasi",
            "4" => "Sipil",
        ];

        $status = [
            "" => "Pilih Status",
            "nulldisposisi" => "Belum Disposisi",
            "belum" => "Belum/Tidak Selesai",
            "selesai" => "Selesai"
        ];

        return view('pages.perawatan_rutin.todolist', [
            'data' => $data,
            'minggu' => $week,
            'instalasi' => $instalasi,
            'bagian' => $bagian,
            'status' => $status
        ]);
    }

    public function todolistData(Request $request)
    {
        $minggu = !empty($request->minggu)?$request->minggu:"";
        $tahun = !empty($request->year)?$request->year:date('Y');

        $instalasi = !empty($request->instalasi)?$request->instalasi:""; 
        $bagian = !empty($request->bagian)?$request->bagian:""; 
        $status = !empty($request->status)?$request->status:""; 

        $query = Prw4w::select('prw_4w.*', DB::raw("TO_CHAR(prw_4w.TANGGAL_MONITORING, 'YYYY') as tahun"), 'instalasi.name as lokasi', 'aset.nama_aset', 'ms_komponen_detail.part as partname', 'prw_52w.perawatan')
            ->join('prw_52w', 'prw_4w.prw_52w_id', '=', 'prw_52w.id')
            ->join('aset', 'prw_52w.komponen_id', '=', 'aset.id')
            ->join('instalasi', 'prw_52w.instalasi_id', '=', 'instalasi.id')
            ->join('ms_komponen_detail', 'prw_52w.part', '=', 'ms_komponen_detail.id')
            ->whereRaw("TO_CHAR(prw_4w.TANGGAL_MONITORING, 'YYYY') = $tahun")
            ->where('aset.kondisi_id', '<>', '12')
            ->where('prw_4w.status', '<>', '99');

        if ($instalasi != "") {
            $query = $query->where('prw_52w.instalasi_id', $instalasi);
        } else {
            $query = $query->whereIn('prw_52w.instalasi_id', lokasi());
        }

        if ($bagian != "") {
            $query = $query->where('aset.bagian', $bagian);
        } else {
            $query = $query->whereIn('aset.bagian', bagian());
        }

        if ($minggu != "") {
            $query = $query->where('prw_4w.urutan_minggu', $minggu);
        }

        if ($status != "" ) {
            switch ($status) {
                case 'nulldisposisi':
                    $query = $query->whereNull('prw_4w.petugas');
                    break;
                case 'belum':
                    $query = $query->whereNull('prw_4w.foto')->whereNotNull('prw_4w.petugas');
                    break;
                case 'selesai':
                    $query = $query->whereNotNull('prw_4w.foto')->whereNotNull('prw_4w.petugas');
                    break;
            }
        }

        if (namaRole() == 'PETUGAS MONITORING') {
            $query = $query->where('prw_4w.petugas', trim(\Auth::user()->userid));
        }

        return Datatables::of($query)
                ->addColumn('menu', function ($model) {
                    // dd($model->ms52w->komponen->id);
                    $edit = '<a href="'.route('todolist::prwrutin-sukucadang', ['woid' => $model->wo_id]).'" class="btn btn-xs purple"><i class="fa fa-cogs"></i> Suku Cadang </a>';

                    if (namaRole() == "Super Administrator") {
                        $delete = '<form style="float:right;" method="POST" action="'.route('todolist::prwrutin-delete', ['id' => $model->id]).'" onsubmit="return ConfirmDelete()">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-xs red"><i class="fa fa-trash"></i>Delete</button>
                            </form>';
                    } else {
                        $delete = "";
                    }

                    return $edit.'<br>'.$delete;
                })
                ->addColumn('status', function ($model) {
                    if (!empty($model->foto)) {
                        $edit = '<a href="#" class="badge badge-success"> Selesai </a>';
                    } else {
                        $edit = '<a href="#" class="badge badge-primary"> Baru </a>';
                    }
                    return $edit;
                })
                ->make(true);
    }

    public function sukucadangShow(Request $request)
    {
        dd('Hello our big apologize, this feature will coming soon. Programmer still work on another important feature and bugfix');
    }

    public function delete(Request $request)
    {
        // dd($request->id);
        DB::beginTransaction();
        try {
            $data = Prw4w::find($request->id);

            $data->delete();

            DB::commit();
            Session::flash('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal dihapus');
        }

        return redirect()->route('todolist::todolist-prwrutin');
    }
}