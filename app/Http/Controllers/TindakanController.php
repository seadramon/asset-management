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
    Asset\Models\MasterFm,
    Asset\Models\MasterJab,
    Asset\Models\Perawatan,
    Asset\Models\Perbaikan,
    Asset\Models\PrbDetail,
    Asset\Models\PrwDetail,
    Asset\Models\PmlKeluhan,
    Asset\Models\PmlKeluhanDev,
    Asset\Models\PermohonanSc,
    Asset\Models\Proposal,
    Asset\Models\PermohonanScDetail,
    Asset\Role as tuRoleUser,
    Asset\Jabatan;

use Asset\Libraries\ValidasiWo;

use DB;
use Datatables;
use Session;
use Validator;
use DateTime;
use Storage;

class TindakanController extends Controller
{
    public function perawatanLink($recid = null)
    {
    	$data = '';

        if (in_array(namaRole(), config('custom.pko'))) {
            // $status = config('custom.filterStatusPko');
            $status = config('custom.filterStatus');
        }else{
            $status = config('custom.filterStatus');
        }

        return view('pages.monitoring.perawatan', [
            'data' => $data,
            'status' => $status
        ]);
    }

    public function perawatanData(Request $request)
    {
    	DB::connection()->enableQueryLog();
        
        /*$period = date("Ym", strtotime(date('F-Y')));
        if (!empty($request->period)) {
            $period = date("Ym", strtotime($request->period));
        }
        DB::setDateFormat('DD-MON-YYYY');*/
        $period = !empty($request->period)?$request->period:date('Y');

        $status = !empty($request->status)?$request->status:'';

        $query = Perawatan::with(['komponen', 'instalasi', 'bagian', 'ms4w'])
            ->whereIn('prw_data.instalasi_id', lokasi())
            ->whereIn('prw_data.bagian_id', bagian())
            // ->whereRaw("TO_CHAR(prw_data.TANGGAL, 'YYYYMM') = $period");
            ->whereRaw("TO_CHAR(prw_data.TANGGAL, 'YYYY') = $period");
            // ->whereNotIn('prw_data.status', config('custom.hideStatus'));

        //menampilkan hanya wo pelaksana eksternal dan emergency
        if (in_array(namaRole(), config('custom.pko'))) {
        	$query->whereIn('metode', ['eksternal pp', 'eksternal emergency']);
                // ->whereIn('prw_data.status', config('custom.pko-statusdisplay'));
        } else {
            ValidasiWo::filterStatus($query, $status);
        }
// dd(DB::getQueryLog());
        return Datatables::of($query)
        		->editColumn('ms4w.hari', function($model) {
        			$test = sprintf('%s / %s', 
        				isset($model->ms4w->hari)?$model->ms4w->hari:"-", 
        				isset($model->ms4w->urutan_minggu)?$model->ms4w->urutan_minggu:"-");

        			return $test;
        		})
                ->addColumn('menu', function ($model) {
                    switch (true) {
                        case ($model->status == '0' && empty($model->petugas_id)):
                            $aksi = '<a href="' . route('perawatan::perawatan-penugasan', ['id' => $model->id, 'idlokasi' => $model->instalasi->id, 'idbagian' => $model->bagian_id]) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Disposisi </a>';
                            break;
                        case ($model->status == '1' && !empty($model->tgl_foto_investigasi)):
                            $aksi = '<a href="' . route('perawatan::perawatan-metode', ['id' => $model->id]) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Input Metode </a>';
                            break;
                        case ($model->status == '3.2'):
                            $aksi = '<a href="' . route('perawatan::perawatan-metode', ['id' => $model->id]) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Revisi Input Metode </a>';
                            break;
                        case ($model->status == '3.3'):
                            $aksi = '<a href="' . route('perawatan::perawatan-metode', ['id' => $model->id]) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Revisi Input Metode </a>';
                            break;
                        case ($model->status == '2' && (!empty($model->foto) && !empty($model->approve_dalops))):
                            $aksi = '<a href="' . route('perawatan::perawatan-analisa', ['id' => $model->id]) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Closing </a>';
                            break;
                        default:
                            $aksi = '';
                            break;
                    }

                    $linkForm = '';
                    if (namaRole() == "Super Administrator") {
                        $linkForm = '<a href="' . route('monitoring::monitoring-entri', ['id' => $model->komponen->kode_fm, 'id4w' => $model->ms_4w_id]) . '" class="btn btn-sm purple" target="_blank"><i class="fa fa-edit"></i> Form </a>'.'&nbsp;<a href="' . route('perawatan::perawatan-view', ['id' => $model->id]) . '" class="btn btn-sm purple" target="_blank"><i class="fa fa-eye"></i> View </a>'.'&nbsp;';
                    }

                    if (!empty($model->id)) {
                        $linkForm = '<a href="' . route('perawatan::perawatan-view', ['id' => $model->id]) . '" class="btn btn-sm purple" target="_blank"><i class="fa fa-eye"></i> View </a>'.'&nbsp;';
                    }

                    $takeover = '';
                    if ((!empty($model->petugas_id) && $model->status != '10') && namaRole() == "Super Administrator") {
                        $takeover = '<a href="' . route('perawatan::perawatan-takeover', ['id' => $model->id]) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Take Over </a>';
                    }
                    
                    return $linkForm.$aksi.$takeover;
                })
                ->addColumn('statusmsg', function ($model) {
                    return self::statusMsg($model);
                })
                ->make(true);
    }

    public function perawatanDataDetail($id)
    {
        $query = PrwDetail::select('prw_detail.*', 
            DB::raw("(select pengukuran from master_fm where kode_fm = prw_detail.kode_fm and nama_field = prw_detail.nama_field) as pengukuran"))
            ->where('prw_detail.prw_data_id', $id)->get();

        return Datatables::of($query)->make(true);
    }

    public function perawatanPenugasan($id, $idlokasi, $bagian = "")
    {
        $data = Perawatan::find($id);

        // $petugas = petugas($idlokasi, $bagian);
        $petugas = ["" => "-             Pilih Petugas             -"];
        $users = tuRoleUser::select('nip', 'nama')
            ->whereNull('is_manajer')
            ->where('recidrole', \Auth::user()->role->jabatan->recidjabatan)
            ->get();

            // dd($users);
        foreach ($users as $row) {
            $petugas[trim($row->nip)] = trim($row->nama);
        }

        $kondisi = [
            '' => '- Pilih Kondisi -',
            'beroperasi' => 'Beroperasi',
            'tidak beroperasi' => 'Tidak Beroperasi'
        ];
        $metode = [
            '' => '- Pilih Pelaksana Pekerjaan -',
            'internal' => 'Internal',
            'eksternal pp' => 'Eksternal PP',
            'eksternal emergency' => 'Eksternal Emergency',
            'masa pemeliharaan' => 'Masa Pemeliharaan'
        ];

        return view('pages.perawatan.penugasan', [
            'petugas' => $petugas,
            'id' => $id,
            'kondisi' => $kondisi,
            'metode' => $metode,
            'data' => $data
        ]);
    }

    public function perawatanPenugasanSimpan(Request $request)
    {
        DB::beginTransaction();

        try {
            // generate kode
            $prw = Perawatan::find($request->id);
            $urutan = Perawatan::select(DB::raw('max(urutan) as urutan'))
                ->whereRaw("TO_CHAR(TANGGAL, 'MM') = ".date('m', strtotime($prw->tanggal)))
                ->first();
            $urutnya = !empty($urutan->urutan)?$urutan->urutan+1:1;
            
            $gen = generateKodeWo('prw', 
                    $urutnya,
                    $prw->komponen->bagian,
                    $prw->komponen->instalasi_id,
                    $prw->tanggal);
            // ./generate kode 

            $data = Perawatan::where('id', $request->id)
                ->update([
                    // 'kode_wo' => $gen,
                    'petugas_id' => $request->petugas,
                    'tgl_disposisi' => getNow(),
                    'manajer' => manajer(trim(\Auth::user()->userid)),
                    'kode_wo' => $gen,
                    'urutan' => $urutnya,

                    'last_action' => 'Disposisi(via WEB)',
                    'updated_at' => getNow(),
            ]);

            DB::commit();
            // Notif
            $perawatan = Perawatan::with('komponen')->where('id', $request->id)->first();
            kirimnotif($request->petugas,
                [
                    'title' => 'Pemberitahuan WO Perawatan',
                    'text' => sprintf('Pemberitahuan WO Perawatan untuk %s', $perawatan->komponen->nama_aset),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '1', 
                    'id' => $request->id
                ]
                // ['tipe' => '1', 'id' => $request->id]
            );
            // End Notif

            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.');
        }

        return redirect()->route('perawatan::perawatan-index');
    }

    public function metodePerawatan($id)
    {
        $data = Perawatan::find($id);

        $kondisi = config('custom.kondisi');
        $metode = config('custom.metode');
        $tingkat = config('custom.tingkat');
        $sifat = config('custom.sifat');

        // Get Existing Suku cadang
        $dataSc = listSukucadang('prw_data_id', $id);
        $dataScWait = listSukucadang('prw_data_id', $id, true);

        $tmpKdAlias = $dataSc->pluck('kode_alias')->toArray();
        $tmpKdAliasWait = $dataScWait->pluck('kode_alias')->toArray();
        $arrKdAlias = array_merge($tmpKdAlias, $tmpKdAliasWait);

        $pairKodeAlias = sukucadangSaldo($arrKdAlias);

        // Sukucadang
        $fkey = 'prw_data_id';
        $wo = 'perawatan';
        $komponen_id = $data->komponen_id;

        $sukucadang = null;
        $dataSc = []; 
        $dataScWait = []; 
        $tmpKdAlias = [];
        $dataAset = null;

        $bagian = config('custom.bagian');

        $tmp = DB::connection('koneksigudang')->table('unit_kerja')
                ->whereIn('TRIM(kd_unitkrj)', ['B32', 'B33'])->get();
        foreach ($tmp as $val) {
            $unitKerja[$val->kd_unitkrj] = $val->nama;
        }

        if (!empty($komponen_id)) {
            $dataAset = Aset::find($komponen_id);
        }

        // Get Existing Suku cadang
        $dataSc = listSukucadang($fkey, $id);
        $dataScWait = listSukucadang($fkey, $id, true);
        
        $tmpKdAlias = $dataSc->pluck('kode_alias')->toArray();
        $tmpKdAliasWait = $dataScWait->pluck('kode_alias')->toArray();
        $arrKdAlias = array_merge($tmpKdAlias, $tmpKdAliasWait);

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

                if (in_array($row->kd_barang_alias, $arrKdAlias)) {
                    $pairKodeAlias[$row->kd_barang_alias] = $row->nama;
                }
            }
        }
        // end sukucadang

        $cekMasukProposal = ValidasiWo::cekMasukProposal($data->metode, $data->sifat);

        $fKeyProposal = "prw_data_id";
        $dataProposal = Proposal::where($fKeyProposal, $id)->first();

        $listProposal = getListProposal($data->spv);

        return view('pages.perawatan.metode', [
            'data' => $data,
            'id' => $id,
            'kondisi' => $kondisi,
            'metode' => $metode,
            'sifat' => $sifat,
            'tingkat' => $tingkat,
            'dataSc' => $dataSc,
            'dataScWait' => $dataScWait,
            'pairKodeAlias' => $pairKodeAlias,

            'woId' => $id,
            'fkey' => $fkey,
            'wo' => $wo,
            'aset' => $dataAset,
            'dataSc' => $dataSc,
            'dataScWait' => $dataScWait,
            'cbSukucadang' => $sc,
            'sukucadang' => $sukucadang,
            'pairKodeAlias' => $pairKodeAlias, 
            'unitkerja' => $unitKerja,
            'bagian' => $bagian,
            'cekMasukProposal' => $cekMasukProposal,
            'c_proposal' => $listProposal,

            'data_proposal' => $dataProposal,
            'f_key_proposal' => $fKeyProposal
        ]);
    }

    public function takeoverPerawatan($id)
    {
        $data = Perawatan::find($id);
// dd($data->petugas_id);
        $petugas = getPetugas($data->komponen->instalasi->id, true, spv($data->petugas_id));
// dd($petugas);
        return view('pages.perawatan.takeover', [
            'id' => $id,
            'data' => $data,
            'petugas' => $petugas
        ]);
    }

    public function takeoverPerawatanSimpan(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = Perawatan::find($request->id);
            
            $data->petugas_id = $request->petugas_id;

            $data->last_action = 'Take Over';
            $data->updated_at = getNow();

            $data->save();

            DB::commit();

            kirimnotif($request->petugas_id,
                [
                    'title' => 'Oper WO Perawatan '.$data->komponen->nama_aset,
                    'text' => sprintf('Oper WO Perawatan untuk %s', $data->komponen->nama_aset),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '133', 
                    'id' => $request->id
                ]
            );
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.');
        }

        return redirect()->route('perawatan::perawatan-index');
    }

    public function viewPerawatan($id)
    {
        $data = Perawatan::find($id);

        $kondisi = [
            '' => '- Pilih Kondisi -',
            'beroperasi' => 'Dapat Beroperasi',
            'tidak beroperasi' => 'Tidak Dapat Beroperasi'
        ];
        $metode = [
            '' => '- Pilih Pelaksana Pekerjaan -',
            'internal' => 'Internal',
            'eksternal pp' => 'Eksternal PP',
            'eksternal emergency' => 'Eksternal Emergency',
            'masa pemeliharaan' => 'Masa Pemeliharaan'
        ];

        $statusDed = [
            '' => '- Pilih Status -',
            '4.0' => 'Baru',
            '4.1' => 'Proses',
            '4.2' => 'Revisi',
            '4.3' => 'Selesai',
            '99'  => 'Tolak'
        ];
        $statusMsppp = [
            '' => '- Pilih Status -',
            '4.0' => 'Setuju',
            '3.4' => 'Revisi',
            '99' => 'Tolak'
        ];

        // Get Existing Suku cadang
        $dataSc = listSukucadang('prw_data_id', $id, false, true);
        $dataScWait = listSukucadang('prw_data_id', $id, true);

        $tmpKdAlias = $dataSc->pluck('kode_alias')->toArray();
        $tmpKdAliasWait = $dataScWait->pluck('kode_alias')->toArray();
        $arrKdAlias = array_merge($tmpKdAlias, $tmpKdAliasWait);

        $pairKodeAlias = sukucadangSaldo($arrKdAlias);

        $keyPairKodeAlias = [];
        if (count($pairKodeAlias) > 0) {
            foreach ($pairKodeAlias as $key => $value) {
                $keyPairKodeAlias[] = $key;
            }
        }

        $cekMasukProposal = ValidasiWo::cekMasukProposal($data->metode, $data->sifat);

        return view('pages.perawatan.view', [
            'data' => $data,
            'id' => $id,
            'kondisi' => $kondisi,
            'metode' => $metode,
            'statusDed' => $statusDed,
            'statusMsppp' => $statusMsppp,
            'dataSc' => $dataSc,
            'dataScWait' => $dataScWait,
            'pairKodeAlias' => $pairKodeAlias,
            'keyPairKodeAlias' => $keyPairKodeAlias,
            'cekMasukProposal' => $cekMasukProposal
        ]);
    }

    public function dedPerawatanSimpan(Request $request)
    {
        DB::beginTransaction();

        try {
            // dd($request->all());
            $data = Perawatan::find($request->id);
            $next = $request->status;

            if ($next == '4.3') {
                $next = '2';
                $data->tgl_ded_selesai = getNow();
            }

            if ($next == '99') {
                $data->pko_catatan_tolak = $request->pko_catatan;
                $data->tgl_pko_catatan_tolak = getNow();
            }

            $data->status = $next;

            $data->last_action = 'DED Selesai';
            $data->updated_at = getNow();

            $data->save();

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.');
        }

        return redirect()->route('perawatan::perawatan-index');
    }

    public function analisaPerawatan($id)
    {
        $data = Perawatan::find($id);
        $kondisi = [
            '' => '- Pilih Kondisi -',
            'beroperasi' => 'Beroperasi',
            'tidak beroperasi' => 'Tidak Beroperasi'
        ];
        $metode = [
            '' => '- Pilih Pelaksana Pekerjaan -',
            'internal' => 'Internal',
            'eksternal pp' => 'Eksternal PP',
            'eksternal emergency' => 'Eksternal Emergency',
            'masa pemeliharaan' => 'Masa Pemeliharaan'
        ];

        return view('pages.perawatan.analisa', [
            'data' => $data,
            'id' => $id,
            'kondisi' => $kondisi,
            'metode' => $metode,
        ]);
    }

    public function metodePerawatanSimpan(Request $request)
    {
        DB::beginTransaction();

        try {
            $cekProposal = false;

            $data = Perawatan::find($request->id);
            $data->kondisi = $request->kondisi;
            $data->metode = $request->metode;
            $data->sifat = $request->sifat;

            if ($data->status == '1') {
                $data->perkiraan = $request->perkiraan;
                $data->tgl_input_metode = getNow();
            }else{                
                $data->perkiraan_revisi = $request->perkiraan_revisi;
            }

            // eksternal pp
            if ($request->metode == "eksternal pp") {
                $data->tahun_anggaran = $request->tahun_anggaran;
                $data->perkiraan_anggaran = $request->perkiraan_anggaran;

                if ($request->hasFile('proposal')) {
                    $file = $request->file('proposal');
                    $extension = $file->getClientOriginalExtension();

                    $filename = trim(\Auth::user()->userid) . '_' . $request->id . '_proposal.' . $extension;
                    Storage::disk('sftp-doc')->put('perawatan/proposal/'.$filename, \File::get($file));
                    
                    $data->proposal = $filename;
                }
            }
            // ./eksternal pp

            // internal
            if ($request->metode == "internal") {
                if ($data->status == '1') {
                    $data->perkiraan = date('Y-m-d', strtotime($data->tanggal. "+8 days"));
                }
            }
            // ./internal

            // cek masuk DED, revisi dr penanganan tdk masuk DED lg
            $data->is_ded = ValidasiWo::cekDedRevisiPenanganan($data->status, $request->metode, $request->sifat);

            $data->jenis_penanganan = $request->jenis_penanganan;
            $data->tingkat = $request->tingkat;

            $data->status = '1.1';

            // Proposal
            if (!empty($request->c_proposal)) {
                $data->proposal_id = $request->c_proposal;
            }

            $data->last_action = 'Input Metode(via WEB)';
            $data->updated_at = getNow();

            $data->save();

            DB::commit();

            // Cek Masuk Proposal
            $cekProposal = ValidasiWo::cekMasukProposal($data->metode, $data->sifat);
            // end:Cek Masuk Proposal

            // Sukucadang
            $wo = $request->wo;
            $woId = $request->wo_id;
            $komponen_id = !empty($request->komponen_id)?$request->komponen_id:null;

            $data = [
                'wo' => $wo, // caption wo
                'fkey' => $request->fkey, // field foreign key pada table
                'wo_id' => $woId, // foreign key ID
                'aset_id' => $komponen_id,
                'unitkerja' => $request->unitkerja,
                'bagian' => $request->bagian
            ];

            self::resetSc($request->dtlRemoved);

            // Store Suku Cadang
            if (sizeof($request->arrsukucadang) > 0) {
                $i = 0;
                self::sukucadang($data, $request->arrsukucadang);
            }

            // Store Sukucadang Waitinglist
            if (sizeof($request->arrsukucadangWaiting) > 0) {
                $i = 0;
                self::sukucadang($data, $request->arrsukucadangWaiting, true);
            }
            // end sukucadang

            // Notif
            $prwNotif = Perawatan::where('id', $request->id)->first();
            $notif = kirimnotif(manajer(trim(\Auth::user()->userid)),
                [
                    'title' => 'Pemberitahuan Perawatan',
                    'text' => sprintf('Pemberitahuan Perawatan untuk %s', $prwNotif->komponen->nama_aset),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '331', 
                    'id' => $request->id
                ]
                // ['tipe' => '1', 'id' => $request->id]
            );
            // End Notif
            
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.');
        }

        return redirect()->route('perawatan::perawatan-index');
    }

    // Untuk sukucadang semua WO tidak hanya perawatan
    public function sukucadangPerawatan(Request $request, $id, $aset = null)
    {
        $sukucadang = null;
        $dataSc = []; 
        $dataScWait = []; 
        $tmpKdAlias = [];
        $dataAset = null;

        $fkey = $request->fkey;
        $wo = $request->wo;

        $bagian = config('custom.bagian');

        $tmp = $data = DB::connection('koneksigudang')->table('unit_kerja')
                ->whereIn('TRIM(kd_unitkrj)', ['B32', 'B33'])->get();
        foreach ($tmp as $val) {
            $unitKerja[$val->kd_unitkrj] = $val->nama;
        }

        if (!empty($aset)) {
            $dataAset = Aset::find($aset);
        }

        // Get Existing Suku cadang
        $dataSc = listSukucadang($fkey, $id);
        $dataScWait = listSukucadang($fkey, $id, true);
        
        $tmpKdAlias = $dataSc->pluck('kode_alias')->toArray();
        $tmpKdAliasWait = $dataScWait->pluck('kode_alias')->toArray();
        $arrKdAlias = array_merge($tmpKdAlias, $tmpKdAliasWait);

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

                if (in_array($row->kd_barang_alias, $arrKdAlias)) {
                    $pairKodeAlias[$row->kd_barang_alias] = $row->nama;
                }
            }
        }

        return view('pages.sukucadang.sukucadang',[
            'woId' => $id,
            'fkey' => $fkey,
            'wo' => $wo,
            'aset' => $dataAset,
            'dataSc' => $dataSc,
            'dataScWait' => $dataScWait,
            'cbSukucadang' => $sc,
            'sukucadang' => $sukucadang,
            'pairKodeAlias' => $pairKodeAlias, 
            'unitkerja' => $unitKerja,
            'bagian' => $bagian
        ]);
    }

    // Receive Request 
    public function sukucadangSimpan(Request $request)
    {
        $wo = $request->wo;
        $woId = $request->wo_id;
        $komponen_id = !empty($request->komponen_id)?$request->komponen_id:null;

        $data = [
            'wo' => $wo, // caption wo
            'fkey' => $request->fkey, // field foreign key pada table
            'wo_id' => $woId, // foreign key ID
            'aset_id' => $komponen_id,
            'unitkerja' => $request->unitkerja,
            'bagian' => $request->bagian
        ];

        self::resetSc($request->dtlRemoved);

        // Store Suku Cadang
        if (sizeof($request->arrsukucadang) > 0) {
            $i = 0;
            self::sukucadang($data, $request->arrsukucadang);
        }

        // Store Sukucadang Waitinglist
        if (sizeof($request->arrsukucadangWaiting) > 0) {
            $i = 0;
            self::sukucadang($data, $request->arrsukucadangWaiting, true);
        }

        switch ($wo) {
            case 'perbaikan':
                $rute = 'perbaikan::perbaikan-metode';
                $tipe = 'perbaikan';
                break;
            case 'perawatan':
                $rute = 'perawatan::perawatan-metode';
                $tipe = 'perawatan';
                break;
            case 'aduan-non-op':
                $rute = 'non-operasi::aduan-metode';
                $tipe = 'aduan';
                break;
            case 'usulan-non-op':
                $rute = 'non-operasi::aduan-metode';
                $tipe = 'usulan';
                break;
            default:
                $rute = 'lost';
                break;
        }

        Session::flash('success', 'Data Suku Cadang berhasil disimpan');
        
        return redirect()->route($rute, ['id' => $woId, 'tipe' => $tipe]);

        /*return redirect()->route('perawatan::perawatan-sukucadang', [
            'id' => $woId, 
            'aset' => $komponen_id, 
            'wo' => $request->wo,
            'fkey' => $request->fkey
        ]);*/
    }

    private static function resetSc($ids)
    {
        $tmp = explode(",", $ids);
        $arrId = array_filter($tmp);

        if (count($arrId)) {
            // dd($arrId);
            PermohonanScDetail::whereIn('id', $arrId)->delete();
            // DB::table('permohonan_sc_detail')->whereIn('id', $arrId)->delete();
        }
    }

    // Call the Sukucadang API
    private static function sukucadang($data, $arrSc, $waitlist = false)
    {
        if (!empty($data['aset_id'])) {
            $aset = Aset::find($data['aset_id']);
            $title = sprintf("Permohonan Suku Cadang untuk %s %s", $data['wo'], $aset->nama_aset);
            $bagian = $aset->bagian;

            if ($bagian == '4') {
                $kdUnit = "B33 ";
            } else {
                $kdUnit = "B32 ";
            }
        } else {
            $aset = null;
            $title = "";
            $kdUnit = $data['unitkerja'];
            $bagian = $data['bagian'];
        }

        $fid = $data['wo_id'];
        $wo = $data['wo']; //utk d header

        // reformat array
        $arrDetail = [];
        foreach ($arrSc as $row) {
            if (!empty($row['kode_alias']) && !empty($row['jumlah'])) {
                $arrDetail[] = [
                    'kode_alias' => $row['kode_alias'],
                    'jumlah' => $row['jumlah'],
                    'keterangan' => $row['keterangan'],
                    'dibeli_by' => $row['dibeli_by'],
                    'kelompok_barang' => $row['kelompok_barang'],
                ]; 
            }
        }

        if (count($arrDetail) > 0) {
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
                'bagian_id' => $bagian,
                $data['fkey'] => $fid
            ];

            if ($waitlist) {
                $url = \URL::to('/').'/api/sukucadang/waiting-list';
            } else {
                $url = \URL::to('/').'/api/sukucadang/permohonan';
            }

            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $url, [
                'headers' => $header,
                'form_params' => $postData,
                'verify' => false,
            ]);

            $response = $response->getBody()->getContents();
            $response = json_decode($response);
        }

        return true;
    }

    public function perawatanClose(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = Perawatan::find($request->id);
            $data->status = '10';
            $data->tgl_finish = getNow();

            $data->last_action = 'Closing';
            $data->updated_at = getNow();

            $data->save();

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.');
        }

        return redirect()->route('perawatan::perawatan-index');
    }

    public function perbaikanLink($recid = null)
    {
        // dd(trim(getMsPpp()));
        $data = '';

        if (in_array(namaRole(), config('custom.pko'))) {
            $status = config('custom.filterStatus');
        }else{
            $status = config('custom.filterStatus');
        }

        $tipe = [
            'monitoring' => 'Monitoring',
            'aduan' => 'Aduan'
        ];

        /*$query = Perbaikan::with(['komponen', 'bagian', 'instalasi', 'ms4w'])
            ->whereIn('prb_data.instalasi_id', lokasi())
            ->whereIn('prb_data.bagian_id', bagian());

        $test = $query->get();
        foreach ($test as $row) {
            dd($row->id);
        }*/
        return view('pages.monitoring.perbaikan', [
            'data' => $data,
            'tipe' => $tipe,
            'status' => $status
        ]);
    }

    public function perbaikanData(Request $request)
    {
        /*$period = date("Ym", strtotime(date('F-Y')));
        if (!empty($request->period)) {
            $period = date("Ym", strtotime($request->period));
        }
        DB::setDateFormat('DD-MON-YYYY');*/
        $period = !empty($request->period)?$request->period:date('Y');

        $status = !empty($request->status)?$request->status:'';

        $query = Perbaikan::with(['komponen', 'bagian', 'instalasi', 'ms4w'])
            ->select('prb_data.*')
            ->whereIn('prb_data.instalasi_id', lokasi())
            ->whereIn('prb_data.bagian_id', bagian())
            // ->whereNotIn('prb_data.status', ['10', '99'])
            // ->whereRaw("TO_CHAR(prb_data.tanggal, 'YYYYMM') = $period")
            ->whereRaw("TO_CHAR(prb_data.tanggal, 'YYYY') = $period")
            //->whereNotIn('prb_data.status', config('custom.hideStatus')) //tampilkan semua status kecuali 99
            ->where('prb_data.tipe', 'monitoring');

        if (in_array(namaRole(), config('custom.pko'))) {
        	$query->whereIn('metode', ['eksternal pp', 'eksternal emergency']);
                // ->whereIn('prb_data.status', config('custom.pko-statusdisplay'));
        } else {
            ValidasiWo::filterStatus($query, $status);
        }

        return Datatables::of($query)
        		->editColumn('ms4w.hari', function($model) {
        			$test = sprintf('%s / %s', 
        				isset($model->ms4w->hari)?$model->ms4w->hari:"-", 
        				isset($model->ms4w->urutan_minggu)?$model->ms4w->urutan_minggu:"-");

        			return $test;
        		})
                ->addColumn('menu', function ($model) {
                    $aksi = '';
                    switch (true) {
                        case ($model->status == '0' && empty($model->petugas_id)):
                            $aksi = '<a href="' . route('perbaikan::perbaikan-penugasan', ['id' => $model->id, 'idlokasi' => $model->instalasi->id, 'idbagian' => $model->bagian_id]) . '" class="btn btn-sm purple"> Disposisi </a>';
                            break;
                        case ($model->status == '1' && !empty($model->tgl_foto_investigasi)):
                            $aksi = '<a href="' . route('perbaikan::perbaikan-metode', ['id' => $model->id]) . '" class="btn btn-sm purple"> Input Metode </a>';
                            break;
                        case ($model->status == '3.1'):
                            $aksi = '<a href="' . route('perbaikan::perbaikan-metode', ['id' => $model->id]) . '" class="btn btn-sm purple"> Revisi Input Metode </a>';
                            break;
                        case ($model->status == '3.2'):
                            $aksi = '<a href="' . route('perbaikan::perbaikan-metode', ['id' => $model->id]) . '" class="btn btn-sm purple"> Revisi Input Metode </a>';
                            break;
                        case ($model->status == '3.3'):
                            $aksi = '<a href="' . route('perbaikan::perbaikan-metode', ['id' => $model->id]) . '" class="btn btn-sm purple"> Revisi Input Metode </a>';
                            break;
                        case ($model->status == '2' && (!empty($model->foto) && !empty($model->approve_dalops))):
                            $aksi = '<a href="' . route('perbaikan::perbaikan-analisa', ['id' => $model->id]) . '" class="btn btn-sm purple"> Closing </a>';
                            break;
                        default:
                            $aksi = '';
                            break;
                    }

                    if (namaRole() == 'SPV PENGOLAHAN') {
                        $aksi = '';
                    }

                    $linkForm = '';
                    if (namaRole() == "Super Administrator") {
                        $linkForm = '<a href="' . route('monitoring::monitoring-entri', ['id' => $model->komponen->kode_fm, 'id4w' => $model->ms_4w_id]) . '" class="btn btn-sm purple" target="_blank"> Form </a>';
                    }

                    $takeover = '';
                    if ((!empty($model->petugas_id) && $model->status != '10') && namaRole() == "Super Administrator") {
                        $takeover = '<a href="' . route('perbaikan::perbaikan-takeover', ['id' => $model->id]) . '" class="btn btn-sm purple"> Take Over </a>';
                    }

                    $detail = '';
                    if (!empty($model->id)) {
                        $detail = '<a href="' . route('perbaikan::perbaikan-view', ['id' => $model->id]) . '" class="btn btn-sm purple" target="_blank"> View </a>';
                    }

                    return $detail.$linkForm.$aksi.$takeover;
                    /*$ret = '<div class="btn-group" dropdown container="body">
                          <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Action
                          </button>
                          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">'.
                                $detail.$linkForm.$aksi.$takeover.$msppp
                          .'</div>
                        </div>';

                    return $ret;*/
                })
                ->addColumn('statusmsg', function ($model) { 
                    return self::statusMsg($model);
                })
                ->make(true);
    }

    public function metodePerbaikan($id)
    {
        $data = Perbaikan::find($id);
        $keluhan = null;
        // dd($data);
// dd($data->keluhan()->ges);
        if ($data->tipe == 'aduan') {
            $keluhan = PmlKeluhan::where('recidkeluhan', $data->aduan_id)->first();
        }

        $kondisi = config('custom.kondisi');
        $metode = config('custom.metode');
        $tingkat = config('custom.tingkat');
        $sifat = config('custom.sifat');

        // Get Existing Suku cadang
        $dataSc = listSukucadang('prb_data_id', $id);
        $dataScWait = listSukucadang('prb_data_id', $id, true);

        $tmpKdAlias = $dataSc->pluck('kode_alias')->toArray();
        $tmpKdAliasWait = $dataScWait->pluck('kode_alias')->toArray();
        $arrKdAlias = array_merge($tmpKdAlias, $tmpKdAliasWait);

        $pairKodeAlias = sukucadangSaldo($arrKdAlias);

        $period = date('Y-m', strtotime($data->tanggal));

        // Sukucadang
        $fkey = 'prb_data_id';
        $wo = 'perbaikan';
        $komponen_id = $data->komponen_id;

        $sukucadang = null;
        $dataSc = []; 
        $dataScWait = []; 
        $tmpKdAlias = [];
        $dataAset = null;

        $bagian = config('custom.bagian');

        $tmp = DB::connection('koneksigudang')->table('unit_kerja')
                ->whereIn('TRIM(kd_unitkrj)', ['B32', 'B33'])->get();
        foreach ($tmp as $val) {
            $unitKerja[$val->kd_unitkrj] = $val->nama;
        }

        if (!empty($komponen_id)) {
            $dataAset = Aset::find($komponen_id);
        }

        // Get Existing Suku cadang
        $dataSc = listSukucadang($fkey, $id);
        $dataScWait = listSukucadang($fkey, $id, true);
        
        $tmpKdAlias = $dataSc->pluck('kode_alias')->toArray();
        $tmpKdAliasWait = $dataScWait->pluck('kode_alias')->toArray();
        $arrKdAlias = array_merge($tmpKdAlias, $tmpKdAliasWait);

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

                if (in_array($row->kd_barang_alias, $arrKdAlias)) {
                    $pairKodeAlias[$row->kd_barang_alias] = $row->nama;
                }
            }
        }
        // end sukucadang

        $cekMasukProposal = ValidasiWo::cekMasukProposal($data->metode, $data->sifat);

        $fKeyProposal = "prb_data_id";
        $dataProposal = Proposal::where($fKeyProposal, $id)->first();

        $listProposal = getListProposal($data->spv);

        return view('pages.perbaikan.metode', [
            'data' => $data,
            'dataSc' => $dataSc,
            'dataScWait' => $dataScWait,
            'keluhan' => $keluhan,
            'id' => $id,
            'kondisi' => $kondisi,
            'metode' => $metode,
            'tingkat' => $tingkat,
            'pairKodeAlias' => $pairKodeAlias,
            'period' => $period,
            'sifat' => $sifat,

            'woId' => $id,
            'fkey' => $fkey,
            'wo' => $wo,
            'aset' => $dataAset,
            'dataSc' => $dataSc,
            'dataScWait' => $dataScWait,
            'cbSukucadang' => $sc,
            'sukucadang' => $sukucadang,
            'pairKodeAlias' => $pairKodeAlias, 
            'unitkerja' => $unitKerja,
            'bagian' => $bagian,
            'cekMasukProposal' => $cekMasukProposal,
            'c_proposal' => $listProposal,

            'data_proposal' => $dataProposal,
            'f_key_proposal' => $fKeyProposal
        ]);
    }

    public function viewPerbaikan($id)
    {
        $data = Perbaikan::find($id);
        $keluhan = null;;
// dd($data->keluhan()->ges);

        $kondisi = [
            '' => '- Pilih Kondisi -',
            'beroperasi' => 'Dapat Beroperasi',
            'tidak beroperasi' => 'Tidak Dapat Beroperasi'
        ];
        $metode = [
            '' => '- Pilih Pelaksana Pekerjaan -',
            'internal' => 'Internal',
            'eksternal pp' => 'Eksternal PP',
            'eksternal emergency' => 'Eksternal Emergency',
            'masa pemeliharaan' => 'Masa Pemeliharaan'
        ];
        $tingkat = [
            '' => '- Pilih Tingkat Perbaikan -',
            'ringan' => 'Perbaikan Ringan',
            'berat' => 'Perbaikan Berat'
        ];
        $statusDed = [
            '' => '- Pilih Status -',
            '4.0' => 'Baru',
            '4.1' => 'Proses',
            '4.2' => 'Revisi',
            '4.3' => 'Selesai',
            '99' => 'Tolak'
        ];
        $statusMsppp = [
            '' => '- Pilih Status -',
            '4.0' => 'Setuju',
            '3.4' => 'Revisi',
            '99' => 'Tolak'
        ];

        // Get Existing Suku cadang
        $dataSc = listSukucadang('prb_data_id', $id, false, true);
        $dataScWait = listSukucadang('prb_data_id', $id, true);
// dd($dataSc);
        $tmpKdAlias = $dataSc->pluck('kode_alias')->toArray();
        $tmpKdAliasWait = $dataScWait->pluck('kode_alias')->toArray();
        $arrKdAlias = array_merge($tmpKdAlias, $tmpKdAliasWait);

        $pairKodeAlias = sukucadangSaldo($arrKdAlias);

        $keyPairKodeAlias = [];
        if (count($pairKodeAlias) > 0) {
            foreach ($pairKodeAlias as $key => $value) {
                $keyPairKodeAlias[] = $key;
            }
        }

        $cekMasukProposal = ValidasiWo::cekMasukProposal($data->metode, $data->sifat);

        return view('pages.perbaikan.view', [
            'data' => $data,
            'keluhan' => $keluhan,
            'id' => $id,
            'kondisi' => $kondisi,
            'metode' => $metode,
            'tingkat' => $tingkat,
            'statusDed' => $statusDed,
            'statusMsppp' => $statusMsppp,
            'dataSc' => $dataSc,
            'dataScWait' => $dataScWait,
            'pairKodeAlias' => $pairKodeAlias,
            'keyPairKodeAlias' => $keyPairKodeAlias,
            'cekMasukProposal' => $cekMasukProposal
        ]);
    }

    public function dedPerbaikanSimpan(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = Perbaikan::find($request->id);
            $next = $request->status;

            if ($next == '4.3') {
                $next = '2';
                $data->tgl_ded_selesai = getNow();
            }

            if ($next == '99') {
                $data->pko_catatan_tolak = $request->pko_catatan;
                $data->tgl_pko_catatan_tolak = getNow();
            }

            $data->status = $next;

            $data->last_action = 'DED Selesai';
            $data->updated_at = getNow();

            $data->save();

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.');
        }

        return redirect()->route('perbaikan::perbaikan-index');
    }

    public function mspppSimpan(Request $request)
    {
        DB::beginTransaction();

        try {
            if ($request->wo == 'prb') {
                $data = Perbaikan::find($request->id);
                $rute = 'perbaikan::perbaikan-index';
            } else {
                $data = Perawatan::find($request->id);
                $rute = 'perawatan::perawatan-index';
            }
            
            $next = $request->status;

            if ($next == '4.0') {
                $data->approve_ms_ppp = getNow();
            }

            if ($next == '99' || $next == '3.4') {
                $data->ms_ppp_catatan = $request->ms_ppp_catatan;
            }

            $data->status = $next;

            $data->last_action = 'Approval MS PPP Selesai';
            $data->updated_at = getNow();

            $data->save();

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.');
        }

        return redirect()->route($rute);
    }

    public function analisaPerbaikan($id)
    {
        $data = Perbaikan::find($id);

        return view('pages.perbaikan.analisa_monitoring', ['data' => $data]);
    }

    public function metodePerbaikanSimpan(Request $request)
    {
        DB::beginTransaction();

        try {
            $cekProposal = false;

            if (namaRole() != "Super Administrator") {
                $data = Perbaikan::find($request->id);

                $data->kondisi = $request->kondisi;
                $data->metode = $request->metode;
                $data->sifat = $request->sifat;

                if ($data->status == '1') {
                    $data->perkiraan = $request->perkiraan;
                    $data->tgl_input_metode = getNow();
                }else{                
                    $data->perkiraan_revisi = $request->perkiraan_revisi;
                }

                // eksternal pp
                if ($request->metode == "eksternal pp") {
                    $data->tahun_anggaran = $request->tahun_anggaran;
                    $data->perkiraan_anggaran = $request->perkiraan_anggaran;

                    if ($request->hasFile('proposal')) {
                        $file = $request->file('proposal');
                        $extension = $file->getClientOriginalExtension();

                        $filename = trim(\Auth::user()->userid) . '_' . $request->id . '_proposal.' . $extension;
                        Storage::disk('sftp-doc')->put('perbaikan/proposal/'.$filename, \File::get($file));
                        
                        $data->proposal = $filename;
                    }
                }
                // ./eksternal pp

                // internal
                if ($request->metode == "internal") {
                    if ($data->status == '1') {
                        $data->perkiraan = date('Y-m-d', strtotime($data->tanggal. "+8 days"));
                    }
                }
                // ./internal

                // tidak beroperasi
                if ($request->kondisi == "tidak beroperasi") {
                    ValidasiWo::tidakBeroperasi($data->komponen_id);
                }

                // cek masuk DED, revisi dr penanganan tdk masuk DED lg
                $data->is_ded = ValidasiWo::cekDedRevisiPenanganan($data->status, $request->metode, $request->sifat);
                
                $data->jenis_penanganan = $request->jenis_penanganan;
                $data->status = '1.1'; //to manajer pemeliharaan
                $data->tingkat = $request->tingkat;

                // Proposal
                if (!empty($request->c_proposal)) {
                    $data->proposal_id = $request->c_proposal;
                }

                $data->last_action = 'Input Metode(via Web)';
                $data->updated_at = getNow();

                $data->save();

                DB::commit();

                // Cek Masuk Proposal
                $cekProposal = ValidasiWo::cekMasukProposal($data->metode, $data->sifat);
                // end:Cek Masuk Proposal
            }

            // Sukucadang
            $wo = $request->wo;
            $woId = $request->wo_id;
            $komponen_id = !empty($request->komponen_id)?$request->komponen_id:null;

            $data = [
                'wo' => $wo, // caption wo
                'fkey' => $request->fkey, // field foreign key pada table
                'wo_id' => $woId, // foreign key ID
                'aset_id' => $komponen_id,
                'unitkerja' => $request->unitkerja,
                'bagian' => $request->bagian
            ];

            self::resetSc($request->dtlRemoved);

            // Store Suku Cadang
            if (sizeof($request->arrsukucadang) > 0) {
                $i = 0;
                self::sukucadang($data, $request->arrsukucadang);
            }

            // Store Sukucadang Waitinglist
            if (sizeof($request->arrsukucadangWaiting) > 0) {
                $i = 0;
                self::sukucadang($data, $request->arrsukucadangWaiting, true);
            }
            // end sukucadang

            // Notif
            $prbNotif = Perbaikan::where('id', $request->id)->first();
            $notif = kirimnotif(manajer(trim(\Auth::user()->userid)),
                [
                    'title' => 'Pemberitahuan Perbaikan',
                    'text' => sprintf('Pemberitahuan Perbaikan untuk %s', $prbNotif->komponen->nama_aset),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '321', 
                    'id' => $request->id
                ]
                // ['tipe' => '1', 'id' => $request->id]
            );
            // End Notif

            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.');
        }

        return redirect()->route('perbaikan::perbaikan-index');
    }

    public function perbaikanClose(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = Perbaikan::find($request->id);
            $data->status = '10';
            $data->tgl_finish = getNow();

            $data->last_action = 'Closing (via Web)';
            $data->updated_at = getNow();

            $data->save();

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.');
        }

        return redirect()->route('perbaikan::perbaikan-index');
    }

    public function perbaikanDataAduan(Request $request)
    {
        DB::connection()->enableQueryLog();

        /*$period = date("Ym", strtotime(date('F-Y')));
        if (!empty($request->period)) {
            $period = date("Ym", strtotime($request->period));
        }
        DB::setDateFormat('DD-MON-YYYY');*/
        $period = !empty($request->period)?$request->period:date('Y');

        $status = !empty($request->status)?$request->status:'';

        $query = Perbaikan::with(['pelapor', 'sukucadang'])
            ->select('prb_data.*', 'aset.nama_aset', 'aset.kode_aset', 'lokasi.name as lokasinm', 'instalasi.name as instalasi', 'x.name as bagian')
            ->leftJoin('aset', 'prb_data.komponen_id', '=', 'aset.id')
            ->leftJoin('instalasi', 'aset.instalasi_id', '=', 'instalasi.id')
            ->leftJoin('lokasi', 'aset.lokasi_id', '=', 'lokasi.id')
            ->leftJoin('master x', 'aset.bagian', '=', 'x.id')
            // ->leftJoin('permohonan_sc', 'prb_data.id', '=', 'permohonan_sc.prb_data_id')
            ->where('tipe', 'aduan')
            // ->whereRaw("TO_CHAR(prb_data.tanggal, 'YYYYMM') = $period")
            ->whereRaw("TO_CHAR(prb_data.tanggal, 'YYYY') = $period")
            ->orderBy('prb_data.id', 'desc');

        if (!in_array(namaRole(), config('custom.roleException'))) {
            if (empty(cekSpvPengolahan())) {
                $query->whereIn('aset.instalasi_id', lokasi())
                    ->whereIn('aset.bagian', bagian());
            }
        }
// dd(cekSpvPengolahan());
        if (cekSpvPengolahan()) {
            $query->whereIn('aset.instalasi_id', lokasi());
        }

        if (in_array(namaRole(), config('custom.pko'))) {
        	$query->whereIn('prb_data.metode', ['eksternal pp', 'eksternal emergency']);
                // ->whereIn('prb_data.status', config('custom.pko-statusdisplay'));
        } else {
            ValidasiWo::filterStatus($query, $status);
        }
// dd($query->get());
        return Datatables::of($query)
                ->addColumn('menu', function ($model) {
                    if (!in_array(namaRole(), config('custom.roleExceptionNoAdmin'))) {
                        $aksi = self::aksi($model);
                    } else {
                        $aksi = '';
                    }

                    if (!empty($model->id)) {
                        $detail = '<a href="' . route('perbaikan::perbaikan-view', ['id' => $model->id]) . '" class="btn btn-sm purple" target="_blank"><i class="fa fa-eye"></i> View </a>'.'&nbsp;';
                    } else {
                        $detail = '<a href="#" class="btn btn-sm purple"><i class="fa fa-eye"></i> View </a>'.'&nbsp;';
                    }

                    $takeover = '';
                    if ((!empty($model->petugas_id) && $model->status != '10') && namaRole() == "Super Administrator") {
                        $takeover = '<a href="' . route('perbaikan::perbaikan-takeover', ['id' => $model->id]) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Take Over </a>';
                    }

                    return $detail.$aksi.$takeover;
                })
                ->addColumn('statusmsg', function ($model) {
                    return self::statusMsg($model);
                })
                ->make(true);
    }

    public function perbaikanDataDetail($id)
    {
        $query = PrbDetail::select('prb_detail.*', 
            DB::raw("(select pengukuran from master_fm where kode_fm = prb_detail.kode_fm and nama_field = prb_detail.nama_field) as pengukuran"))
            ->where('prb_detail.prb_data_id', $id)->get();

        return Datatables::of($query)->make(true);
    }

    public function perbaikanPenugasan($id, $idlokasi, $bagian = "")
    {
        $data = Perbaikan::find($id);

        $petugas = ["" => "-             Pilih Petugas             -"];
        $users = tuRoleUser::select('nip', 'nama')
            ->whereNull('is_manajer')
            ->where('recidrole', \Auth::user()->role->jabatan->recidjabatan)
            ->get();

            // dd($users);
        foreach ($users as $row) {
            $petugas[trim($row->nip)] = trim($row->nama);
        }

        $kondisi = [
            '' => '- Pilih Kondisi -',
            'beroperasi' => 'Beroperasi',
            'tidak beroperasi' => 'Tidak Beroperasi'
        ];
        $metode = [
            '' => '- Pilih Pelaksana Pekerjaan -',
            'internal' => 'Internal',
            'eksternal pp' => 'Eksternal PP',
            'eksternal emergency' => 'Eksternal Emergency',
            'masa pemeliharaan' => 'Masa Pemeliharaan'
        ];

        return view('pages.perbaikan.penugasan', [
            'petugas' => $petugas,
            'id' => $id,
            'kondisi' => $kondisi,
            'metode' => $metode,
            'data' => $data
        ]);
    }

    public function takeoverPerbaikan($id)
    {
        $data = Perbaikan::find($id);
        // dd(spv($data->petugas_id));
        $petugas = getPetugas($data->komponen->instalasi->id, true, spv($data->petugas_id));
// dd($petugas);
        return view('pages.perbaikan.takeover', [
            'id' => $id,
            'data' => $data,
            'petugas' => $petugas
        ]);
    }

    public function takeoverPerbaikanSimpan(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = Perbaikan::find($request->id);
            
            $data->petugas_id = $request->petugas_id;

            $data->last_action = 'Take Over';
            $data->updated_at = getNow();

            $data->save();

            DB::commit();

            kirimnotif($request->petugas_id,
                [
                    'title' => 'Oper WO perbaikan '.$data->komponen->nama_aset,
                    'text' => sprintf('Oper WO perbaikan untuk %s', $data->komponen->nama_aset),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '123', 
                    'id' => $request->id
                ]
            );
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.');
        }

        return redirect()->route('perbaikan::perbaikan-index');
    }

    public function penugasanAnalisa($id, $aduanId = "")
    {
        $data = Perbaikan::find($id);

        $kondisi = [
            '' => '- Pilih Kondisi -',
            'beroperasi' => 'Beroperasi',
            'tidak beroperasi' => 'Tidak Beroperasi'
        ];
        $metode = [
            '' => '- Pilih Pelaksana Pekerjaan -',
            'internal' => 'Internal',
            'eksternal pp' => 'Eksternal PP',
            'eksternal emergency' => 'Eksternal Emergency',
            'masa pemeliharaan' => 'Masa Pemeliharaan'
        ];

        return view('pages.perbaikan.analisa', [
            'id' => $id,
            'aduanId' => $aduanId,
            'kondisi' => $kondisi,
            'metode' => $metode,
            'data' => $data
        ]);
    }

    public function penugasanAnalisaFinish($id, $aduanId)
    {
        $data = Perbaikan::find($id);

        return view('pages.perbaikan.analisaFinish', [
            'id' => $id,
            'aduanId' => $aduanId,
            'data' => $data
        ]);
    }

    public function perbaikanPenugasanSimpan(Request $request)
    {
        DB::beginTransaction();

        try {
            // generate kode
            /*$prb = Perbaikan::find($request->id);
            $urutan = Perbaikan::select(DB::raw('max(urutan) as urutan'))
                ->where('tipe', 'monitoring')
                ->whereRaw("TO_CHAR(TANGGAL, 'MM') = ".date('m', strtotime($prb->tanggal)))
                ->first();
            
            $gen = generateKodeWo('prb', 
                    !empty($urutan->urutan)?$urutan->urutan++:1,
                    $prb->komponen->bagian,
                    $prb->komponen->instalasi_id,
                    $prb->tanggal);*/
            // ./generate kode 

            $data = Perbaikan::where('id', $request->id)
                ->update([
                    // 'kode_wo' => $gen,
                    'petugas_id' => $request->petugas,
                    'tgl_disposisi' => getNow(),
                    'manajer' => manajer(trim(\Auth::user()->userid)),
                    'last_action' => 'Disposisi(via WEB)',
                    'updated_at' => getNow()
                ]);

            DB::commit();

            // Notif
            $perbaikan = Perbaikan::with('komponen')->where('id', $request->id)->first();
            kirimnotif($request->petugas,
                [
                    'title' => 'Pemberitahuan WO Perbaikan',
                    'text' => sprintf('Pemberitahuan WO Perbaikan untuk %s', $perbaikan->komponen->nama_aset),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '2', 
                    'id' => $request->id
                ] 
                // ['tipe' => '2', 'id' => $request->id]
            );
            // End Notif

            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.');
        }

        return redirect()->route('perbaikan::perbaikan-index');
    }

    public function penugasanAduan($aduan_id = null, $barcode = null)
    {
        $keluhan = PmlKeluhan::where('recidkeluhan', $aduan_id)->first();        
        $period = date("Y-m", strtotime($keluhan->tgl_lapor));

        $jab = MasterJab::where('nip', trim(\Auth::user()->userid))->first();
        $arrLokasi = lokasi();

        if (!empty($barcode)) {
            $aset = Aset::where('kode_barcode', trim($barcode))->first();
        }

        $petugas = ["" => "-             Pilih Petugas             -"];
        $users = tuRoleUser::select('nip', 'nama')
            ->whereNull('is_manajer')
            ->where('recidrole', \Auth::user()->role->jabatan->recidjabatan)
            ->get();

            // dd($users);
        foreach ($users as $row) {
            $petugas[trim($row->nip)] = trim($row->nama);
        }

        if ($aduan_id == null) {
            $data = '';
        } else {
            $data = Perbaikan::where('aduan_id', $aduan_id)->first();
        }

        return view('pages.perbaikan.penugasanAduan', [
            'aduan_id' => $aduan_id,
            'aset' => $aset,
            'petugas' => $petugas,
            'data' => $data,
            'period' => $period,
            'keluhan' => $keluhan
        ]);
    }

    public function penugasanAduanSimpan(Request $request)
    {
        DB::beginTransaction();

        /*$prb = PmlKeluhan::where('recidkeluhan', $request->aduan_id)->first();
        $aset = Aset::where('kode_barcode', $prb->kode_barcode)->first();

        $urutan = Perbaikan::select(DB::raw('max(urutan) as urutan'))
            ->where('tipe', 'aduan')
            ->whereRaw("TO_CHAR(TANGGAL, 'MM') = ".date('m'))
            ->first();
        
        $gen = generateKodeWo('aduan', 
                !empty($urutan->urutan)?$urutan->urutan++:1,
                $aset->bagian,
                $aset->instalasi_id,
                date('d-m-Y'));*/
// dd($gen);        

        $request->request->add([
            // 'kode_wo' => $gen,
            'tgl_disposisi' => getNow(),
            'manajer' => manajer(trim(\Auth::user()->userid)),
            'last_action' => 'Disposisi(via WEB)',
            'updated_at' => getNow()
        ]);
        
        try {
            $id = null;
            if ($request->isedit == 0) {
                $data = Perbaikan::insertGetId($request->except(['_token', 'isedit']));
                $id = $data;
            } else {
                $data = Perbaikan::where('id', $request->id)
                    ->update($request->except(['_token', 'isedit']));
                $id = $request->id;
            }

            DB::commit();

            // Notif
            $perbaikan = Perbaikan::with('komponen')->where('id', $id)->first();
            kirimnotif($request->petugas_id,
                [
                    'title' => 'Pemberitahuan WO Perbaikan dari Aduan',
                    'text' => sprintf('Pemberitahuan WO Perbaikan dari Aduan untuk %s', $perbaikan->komponen->nama_aset),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '3', 
                    'id' => $id
                ]
                // ['tipe' => '3', 'id' => $id]
            );
            // End Notif

            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.');
        }

        return redirect()->route('perbaikan::perbaikan-index');
    }

    public function penugasanAnalisaSimpan(Request $request)
    {
        DB::beginTransaction();
        try {
            Perbaikan::where('id', $request->id)
                ->update($request->except(['_token', 'isedit']));

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.');
        }

        return redirect()->route('perbaikan::perbaikan-index');
    }

    public function penugasanAduanClose(Request $request)
    {
        DB::beginTransaction();
        try {
            // PML Keluhan
            $data = [
                'DISPOSISI_SPV_TIPE' => 1,
                'SELESAI_PEKERJAAN' => DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')"),
                'URAIAN_PEKERJAAN' => $request->uraian,
                'HASIL_TIPE' => "",
                'HASIL_CATATAN' => "",
                'DISPOSISI_SPV_STATUS' => 10,
                'STATUS_KELUHAN' => 1,
            ];
            $save = PmlKeluhan::where('recidkeluhan', $request->aduan_id)->update($data);

            // Prb Data
            $dataPrb = [
                'tgl_finish' => getNow(),
                'status' => '10',
                'last_action' => 'Closing(via WEB)',
                'updated_at' => getNow()
            ];
            $save = Perbaikan::where('id', $request->id)->update($dataPrb);

            // Update Ms4w jadwal monitoring
            /*$prb = Perbaikan::find($request->id);
            $date = new DateTime(date('Y-m-d'));
            $week = $date->format("W");
            $ms = Ms4w::whereHas('ms52w', function($query) use($prb) {
                    $query->where('komponen_id', $prb->komponen_id);
                    $query->where('tahun', date('Y'));
                })
                ->where('urutan_minggu', $week)
                ->where('status', '99')
                ->update(['status' => '0']);*/
            // end Update Ms4w jadwal monitoring
// dd('aa');
            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.');
        }

        return redirect()->route('perbaikan::perbaikan-index');
    }

    public function lokasiSelect($id)
    {
        $bb = Aset::where('instalasi_id', $id)
            // ->where('equipment', 'yes')
            ->get();
        $template = '';
        foreach ($bb as $row) {
            $template .= '<option value="' . $row->id . '">' . $row->nama_aset . '</option>';
        }

        $arPetugas = petugas($id);
        $petugas = '';
        foreach ($arPetugas as $key => $value) {
            $petugas .= '<option value="' . $key . '">' . $value . '</option>';
        }
// dd($petugas);
        return response()->json([
            'template' => $template,
            'petugas' => $petugas
        ]);
    }

    public function asetSelect($id)
    {
        $arPetugas = petugas($id);
        $petugas = '';
        foreach ($arPetugas as $key => $value) {
            $petugas .= '<option value="' . $key . '">' . $value . '</option>';
        }
// dd($petugas); 
        return response()->json([
            'petugas' => $petugas
        ]);
    }


    private static function aksi($model)
    {
        $edit = '-';
        switch (true) {
            case ($model->status == '0' && !empty($model->petugas_id)):
                $edit = '';
                break;
            case ($model->status == '1' && !empty($model->tgl_foto_investigasi)):
                $edit = '<a href="' . route('perbaikan::perbaikan-metode', ['id' => $model->id]) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Input Metode </a>';
                break;
            case ($model->status == '1.1'):
                $action = '';
                break;
            case ($model->status == '3.1'):
                $edit = '<a href="' . route('perbaikan::perbaikan-metode', ['id' => $model->id]) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Revisi Input Metode </a>';
                break;
            case ($model->status == '1.2' && $model->metode == 'eksternal emergency'):
                $edit = '';
                break;
            case ($model->status == '1.2' && $model->metode != 'eksternal emergency'):
                $edit = '';
                break;
            case ($model->status == '4.0'):
                $edit = '';
                break;
            case ($model->status == '4.1'):
                $edit = '';
                break;
            case ($model->status == '4.2'):
                $edit = '';
                break;
            case ($model->status == '4.3'):
                $edit = '';
                break;
            case ($model->status == '2' && empty($model->foto)):
                $edit = '';
                break;
            case ($model->status == '3.2'):
                $edit = '<a href="' . route('perbaikan::perbaikan-metode', ['id' => $model->id]) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Revisi Input Metode </a>';
                break;
            case ($model->status == '3.3' || $model->status == '3.4' ):
                $edit = '<a href="' . route('perbaikan::perbaikan-metode', ['id' => $model->id]) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Revisi Input Metode </a>';
                break;
            case ($model->status == '2' && (!empty($model->foto) && empty($model->approve_dalops)) && ($model->metode != "internal" || count($model->sukucadang) > 0)):
                $edit = '';
                break;
            case ($model->status == '2' && (!empty($model->foto) && empty($model->approve_dalops)) && ($model->metode == "internal" && count($model->sukucadang) < 1)):
                $edit = '<a href="' . route('perbaikan::penugasan-analisa-finish', ['id' => $model->id, 'aduan_id' => $model->recidkeluhan]) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Closing </a>';
                break;
            case ($model->status == '2' && (!empty($model->foto) && !empty($model->approve_dalops))):
                $edit = '<a href="' . route('perbaikan::penugasan-analisa-finish', ['id' => $model->id, 'aduan_id' => $model->recidkeluhan]) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Closing </a>';
                break;
            case $model->status == '10':
                $edit = '';
                break;
            default:
                $edit = '';
                break;
        }

        return $edit;
    }

    private static function statusMsg($model)
    {
        $edit = '';
        switch (true) {
            case ($model->status == '0' && empty($model->petugas_id)):
                $edit = '<span class="badge badge-primary"> Baru </span>';
                break;
            case ($model->status == '0' && !empty($model->petugas_id)):
                $edit = '<span class="badge badge-warning"> Investigasi </span>';
                break;
            case ($model->status == '1' && !empty($model->tgl_foto_investigasi)):
                $edit = '<a href="#" class="badge badge-info"> Sudah diinvestigasi </a>';
                break;
            case ($model->status == '1.1' && $model->bagian_id != '3'):
                $edit = '<a href="#" class="badge badge-info"> Menunggu Approval Manajer Pemeliharaan </a>';
                break;
            case ($model->status == '1.1' && $model->bagian_id == '3'):
                $edit = '<a href="#" class="badge badge-info"> Menunggu Approval Manajer TSI </a>';
                break;
            case ($model->status == '1.2' && $model->metode == 'eksternal emergency'):
                $edit = '<a href="#" class="badge badge-info"> Menunggu Approval Manajer DalOps(Optional) </a>';
                break;
            case ($model->status == '1.2' && $model->metode != 'eksternal emergency'):
                $edit = '<a href="#" class="badge badge-info"> Menunggu Approval Manajer DalOps </a>';
                break;
            case ($model->status == '1.3' && $model->metode != 'eksternal emergency'):
                $edit = '<a href="#" class="badge badge-info"> Menunggu Approval Manajer PPP </a>';
                break;
            case ($model->status == '4.0'):
                $edit = '<a href="#" class="badge badge-danger"> Proses DED (Baru) </a>';
                break;
            case ($model->status == '4.1'):
                $edit = '<a href="#" class="badge badge-danger"> Proses DED (Proses) </a>';
                break;
            case ($model->status == '4.2'):
                $edit = '<a href="#" class="badge badge-danger"> Proses DED (Revisi) </a>';
                break;
            case ($model->status == '4.3'):
                $edit = '<a href="#" class="badge badge-danger"> Proses DED (Selesai) </a>';
                break;
            case ($model->status == '2' && empty($model->foto)):
                $edit = '<a href="#" class="badge badge-success"> Penanganan </a>';
                break;
            case ($model->status == '3.1' && $model->bagian_id != '3'):
                $edit = '<a href="#" class="badge badge-info"> Revisi Input Metode dari Manajer Pemeliharaan </a>';
                break;
            case ($model->status == '3.1' && $model->bagian_id == '3'):
                $edit = '<a href="#" class="badge badge-info"> Revisi Input Metode dari Manajer TSI </a>';
                break;
            case ($model->status == '3.2'):
                $edit = '<a href="#" class="badge badge-info"> Revisi Input Metode dari Penanganan </a>';
                break;
            case ($model->status == '3.3'):
                $edit = '<a href="#" class="badge badge-info"> Revisi Input Metode dari Manajer DalOps </a>';
                break;
            case ($model->status == '3.4'):
                $edit = '<a href="#" class="badge badge-info"> Revisi Input Metode dari Manajer PPP </a>';
                break;
            case ($model->status == '2' && (!empty($model->foto) && empty($model->approve_dalops))  && ($model->metode != "internal" || count($model->sukucadang) > 0)):
                $edit = '<a href="#" class="badge badge-info"> Menunggu Approval Manajer DalOps </a>';
                break;
            case ($model->status == '2' && (!empty($model->foto) && empty($model->approve_dalops)) && ($model->metode == "internal" && count($model->sukucadang) < 1)):
                $edit = '<a href="#" class="badge badge-success"> Sudah ditangani </a>';
                break;
            case ($model->status == '2' && (!empty($model->foto) && !empty($model->approve_dalops))):
                $edit = '<a href="#" class="badge badge-success"> Sudah ditangani </a>';
                break;
            case $model->status == '10':
                $edit = '<a href="#" class="badge badge-dark"> Selesai </a>';
                break;
            case $model->status == '98':
                $edit = '<a href="#" class="badge badge-dark"> Digantikan </a>';
                break;
            case $model->status == '99':
                $edit = '<a href="#" class="badge badge-dark"> Bukan Kerusakan </a>';
                break;
        }
        
        return $edit;
    }
}
