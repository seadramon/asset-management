<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\AduanNonOperasi,
    Asset\Models\Usulan,
    Asset\Models\Instalasi,
    Asset\Models\Lokasi,
    Asset\Models\RoleUser,
    Asset\Models\Role,
    Asset\Models\Aset,
    Asset\Models\PermohonanSc,
    Asset\Models\Proposal,
    Asset\Models\PermohonanScDetail,
    Asset\User,
    Asset\Role as tuRoleUser;

use Asset\Jabatan;

use Asset\Libraries\ValidasiWo;

use DB;
use Datatables;
use Session;
use Validator;
use Storage;

class NonOperasiController extends Controller
{
	protected $arrJab = ['81', '83', '80', '82', '84', '85', '86', '87'];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function aduanIndex()
    {
        /*$nip = \Auth::user()->userid;
        $query = AduanNonOperasi::whereNotIn('aduan_non_operasi.status', ['99', '10'])
                ->orderBy('id', 'desc')
                ->where('manajer', $nip);
        // dd($query->get());*/
        $data = '';

        $penggunaanAngg = config('custom.penggunaan_anggaran');
        $lingkupKerja = config('custom.lingkup_kerja');

        if (in_array(namaRole(), config('custom.pko'))) {
            $status = config('custom.filterStatus');
        }else{
            $status = config('custom.filterStatus');
        }

        return view('pages.non-operasi.index', [
            'data' => $data,
            'status' => $status,
            'penggunaanAngg' => $penggunaanAngg,
            'lingkupKerja' => $lingkupKerja
        ]);
    }

    public function aduanData(Request $request)
    {
        
        DB::connection()->enableQueryLog();

        $nip = \Auth::user()->userid;
        $levelJab = cekJabatan(\Auth::user()->userid);
// dd();
        $period = !empty($request->period)?$request->period:date('Y');
        $status = !empty($request->status)?$request->status:'';
        $recidjabatan = getRecidJabatan($nip);

        if (empty(cekSpvPengolahan()) && !in_array(namaRole(), config('custom.pko'))) {
            $query = AduanNonOperasi::with(['petugas', 'jabatan', 'bagian', 'instalasi', 'pelapor', 'sukucadang'])
                // ->whereNotIn('aduan_non_operasi.status', ['99'])
                ->whereRaw("TO_CHAR(aduan_non_operasi.created_at, 'YYYY') = $period")
                ->orderBy('id', 'desc');

            if (!in_array(trim($nip), config('custom.dalops'))) {
                switch ($levelJab) {
                    case 'petugas':
                        $query = $query->where('trim(petugas_id)', trim($nip));
                        break;
                    case 'spv':
                        $query = $query->where('trim(nip_spv)', trim($nip));
                        break;
                    case 'manajer':
                        $query = $query->where('manajer', $nip);
                        break;
                }
            }

            ValidasiWo::filterStatus($query, $status);
        } else {            
            // SPV Pengolahan
            if (!empty(cekSpvPengolahan())) {
                $query = AduanNonOperasi::with(['petugas', 'jabatan', 'bagian', 'pelapor', 'instalasi'])
                    ->whereIn('aduan_non_operasi.instalasi_id', lokasi())
                    // ->whereNotIn('aduan_non_operasi.status', ['99'])
                    ->whereRaw("TO_CHAR(aduan_non_operasi.created_at, 'YYYY') = $period")
                    ->orderBy('id', 'desc');

                ValidasiWo::filterStatus($query, $status);
            } else {
                // SPV Perencanaan
                $query = AduanNonOperasi::with(['petugas', 'jabatan', 'bagian', 'pelapor'])
                    // ->whereNotIn('aduan_non_operasi.status', ['99', '10'])
                    ->whereIn('metode', ['eksternal pp', 'eksternal emergency'])
                    ->whereRaw("TO_CHAR(aduan_non_operasi.created_at, 'YYYY') = $period")
                    ->orderBy('id', 'desc');

                if ($status !== '') {
                    ValidasiWo::filterStatus($query, $status);
                }/* else{
                    $query->whereIn('aduan_non_operasi.status', config('custom.pko-statusdisplay'));
                }*/
            }
        }

        return Datatables::of($query)
                ->addColumn('menu', function ($model) use($levelJab, $recidjabatan) {                    
                    $temp = aksiTindakanWeb($model->toArray());
                    $edit = $temp;
                    if (!in_array(namaRole(), ["Super Administrator", "SPV PERENCANAAN OPERASI", "MANAJER PERENCANAAN OPERASI"])) {
                    	$edit = filterMenu($levelJab, $temp);
                    }

                    $detail = '<a href="' . route('non-operasi::aduan-view', ['id' => $model->id]) . '" class="btn btn-sm purple" target="_blank"><i class="fa fa-eye"></i> View </a>'.'&nbsp;';

                    $takeover = '';
                    if ((!empty($model->petugas_id) && $model->status != '10') && namaRole() == "Super Administrator") {
                        $takeover = '<a href="' . route('non-operasi::takeover', ['wo' => 'AduanNonOperasi', 'id' => $model->id]) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Take Over </a>';
                    }

                    $metode = '';
                    if (($model->spv == $recidjabatan) || namaRole() == "Super Administrator") {
                        if ($model->status == '1' && !empty($model->tgl_foto_investigasi)) {
                            $metode = '<a href="' . route('non-operasi::aduan-metode', ['id' => $model->id, 'tipe' => 'aduan']) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Input Metode </a>';
                        }

                        if (in_array($model->status, ['3.1', '3.2', '3.3', '3.4'])) {
                            $metode = '<a href="' . route('non-operasi::aduan-metode', ['id' => $model->id, 'tipe' => 'aduan']) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Input Metode </a>';
                        }
                    }

                    return $detail.$takeover.$metode;
                })
                ->addColumn('status', function ($model) {                    
                    $edit = statusTindakanWeb($model->toArray());

                    return $edit;
                })
                ->make(true);
    }

    public function aduanEntri($id = null)
    {
        $data = null;

        if (!empty($id)) {
            $data = AduanNonOperasi::find($id);
        }

        $jabatan = Jabatan::whereIn('recidjabatan', $this->arrJab)->get()->pluck('namajabatan', 'recidjabatan')->toArray();
        $labelJabatan = ["" => "-             Pilih SPV             -"];
        $jabatan = $labelJabatan + $jabatan;

        $sifat = ["biasa" => "Biasa",
            "darurat" => "Darurat"
        ];

        return view('pages.non-operasi.entri', [
            'id' => $id,
            'jabatan' => $jabatan,
            'data' => $data,
            'sifat' => $sifat
        ]);
    }

    public function aduanView($id)
    {
        $data = null;

        $data = $data = AduanNonOperasi::with(['petugas', 'jabatan', 'bagian', 'instalasi'])
                ->where('id', $id)
                ->first();
        
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
        $dataSc = listSukucadang('aduan_non_operasi_id', $id, false, true);
        $dataScWait = listSukucadang('aduan_non_operasi_id', $id, true);

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
        // dd($cekMasukProposal);

    	return view('pages.non-operasi.aduan-view', [
            'id' => $id,
            'data' => $data,
            'statusDed' => $statusDed,
            'statusMsppp' => $statusMsppp,
            'dataSc' => $dataSc,
            'dataScWait' => $dataScWait,
            'pairKodeAlias' => $pairKodeAlias,
            'keyPairKodeAlias' => $keyPairKodeAlias,
            'cekMasukProposal' => $cekMasukProposal
        ]);
    }

    public function aduanDedSimpan(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = AduanNonOperasi::find($request->id);
            $next = $request->status;

            if ($next == '4.3') {
                $next = '2';
                $data->tgl_ded_selesai = getNow();
                $data->last_action = "Proses DED Selesai";
            }

            if ($next == '99') {
                $data->pko_catatan_tolak = $request->pko_catatan;
                $data->tgl_pko_catatan_tolak = getNow();

                $data->last_action = "Ditolak dari Proses DED";
            }

            $data->status = $next;

            $data->save();

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.');
        }

        return redirect()->route('non-operasi::aduan-index');
    }

    public function aduanMetode($id, Request $request)
    {
        $tipe = $request->tipe;

        $dataProposal = null;

        if ($tipe == 'aduan') {
            $data = AduanNonOperasi::find($id);

            $fkey = 'aduan_non_operasi_id';
            $wo = 'aduan-non-op';
            
            $dataSc = listSukucadang('aduan_non_operasi_id', $id);
            $dataScWait = listSukucadang('aduan_non_operasi_id', $id, true);

            $fKeyProposal = "aduan_non_op_id";
            $dataProposal = Proposal::where($fKeyProposal, $id)->first();
        } else {
            $data = Usulan::find($id);

            $fkey = 'usulan_non_operasi_id';
            $wo = 'usulan-non-op';

            $dataSc = listSukucadang('usulan_non_operasi_id', $id);
            $dataScWait = listSukucadang('usulan_non_operasi_id', $id, true);

            $fKeyProposal = "usulan_id";
            $dataProposal = Proposal::where($fKeyProposal, $id)->first();
        }
        
        $tmpKdAlias = $dataSc->pluck('kode_alias')->toArray();
        $tmpKdAliasWait = $dataScWait->pluck('kode_alias')->toArray();
        $arrKdAlias = array_merge($tmpKdAlias, $tmpKdAliasWait);
        
        $pairKodeAlias = sukucadangSaldo($arrKdAlias);

        $kondisi = config('custom.kondisi');
        $metode = config('custom.metode');
        $tingkat = config('custom.tingkat');
        $sifat = config('custom.sifat');

        // Sukucadang
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

        $listProposal = getListProposal($data->spv);

        return view('pages.non-operasi.metode', [
            'data' => $data,
            'id' => $id,
            'kondisi' => $kondisi,
            'metode' => $metode,
            'sifat' => $sifat,
            'tingkat' => $tingkat,
            'dataSc' => $dataSc,
            'dataScWait' => $dataScWait,
            'pairKodeAlias' => $pairKodeAlias,
            'tipe' => $tipe,

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
            'c_proposal' => $listProposal,

            'data_proposal' => $dataProposal,
            'f_key_proposal' => $fKeyProposal
        ]);
    }

    public function aduanMetodeSimpan(Request $request)
    {
        DB::beginTransaction();
// dd($request->all());
        try {
            $data = AduanNonOperasi::find($request->id);
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

                    $dir = 'non-operasi/aduan/'.$request->id;
                    cekDir($dir);

                    $filename = trim(\Auth::user()->userid) . '_proposal.' . $extension;
                    \Storage::disk('sftp-doc')->put($dir.'/'.$filename, \File::get($file));

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

            $data->jenis_penanganan = $request->jenis_penanganan;
            $data->tingkat = $request->tingkat;

            // cek masuk DED, revisi dr penanganan tdk masuk DED lg
            $data->is_ded = ValidasiWo::cekDedRevisiPenanganan($data->status, $request->metode, $request->sifat);

            $data->status = '1.1';

            // Proposal
            if (!empty($request->c_proposal)) {
                $data->proposal_id = $request->c_proposal;
            }

            $data->save();

            DB::commit();

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

            $cekProposal = ValidasiWo::cekMasukProposal($request->metode, $request->sifat);

            // Notif
            $aduanNotif = AduanNonOperasi::where('id', $request->id)->first();
            $notif = kirimnotif(trim($aduanNotif->manajer),
                [
                    'title' => 'Pemberitahuan Hasil Input Metode Aduan Non-Operasi',
                    'text' => sprintf('Pemberitahuan Hasil Input Metode untuk %s', $aduanNotif->judul),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '341', 
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

        /*if ($cekMasukProposal) {
            return redirect()->route('proposal::pekerjaan', ['wo' => 'aduan_non_op_id', 'id' => $request->id]);
        } else {
            return redirect()->route('non-operasi::aduan-index');
        }*/
        return redirect()->route('non-operasi::aduan-index');
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

    public function usulanMetodeSimpan(Request $request)
    {
        DB::beginTransaction();
// dd($request->all());
        try {
            $data = Usulan::find($request->id);
            // $data->kondisi = $request->kondisi;
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

                    $dir = 'non-operasi/usulan/'.$request->id;
                    cekDir($dir);

                    $filename = trim(\Auth::user()->userid) . '_proposal.' . $extension;
                    \Storage::disk('sftp-doc')->put($dir.'/'.$filename, \File::get($file));

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

            $data->jenis_penanganan = $request->jenis_penanganan;

            // cek masuk DED, revisi dr penanganan tdk masuk DED lg
            $data->is_ded = ValidasiWo::cekDedRevisiPenanganan($data->status, $request->metode, $request->sifat);
            
            $data->status = '1.1';

            $data->save();

            DB::commit();

            // $cekProposal = ValidasiWo::cekMasukProposal($request->metode, $request->sifat);

            // Notif
            $aduanNotif = Usulan::where('id', $request->id)->first();
            $notif = kirimnotif(trim($aduanNotif->manajer),
                [
                    'title' => 'Pemberitahuan Hasil Input Metode Usulan Non Aduan',
                    'text' => sprintf('Pemberitahuan Hasil Input Metode untuk %s', $aduanNotif->nama),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '341', 
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

        /*if ($cekProposal) {
            return redirect()->route('proposal::pekerjaan', ['wo' => 'usulan_id', 'id' => $request->id]);
        } else {
            return redirect()->route('non-operasi::usulan-index');
        }*/
        return redirect()->route('non-operasi::usulan-index');
    }

    public function aduanSimpan(Request $request)
    {
        DB::beginTransaction();
        try {
            $filename = null;
            $nip_spv = null;
            $manajer = null;

            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $extension = $file->getClientOriginalExtension();
                $filename = 'aduan_' . date('dmYHis') .'.'. $extension;

                $dir = 'non-operasi/'.date('Y-m');
                cekDir($dir);
                Storage::disk('sftp')->put($dir .'/'. $filename, \File::get($file));
            }

            if (!empty($request->spv)) {
                $temp = tuRoleUser::where('roleuserid', $request->spv)
                    ->where('is_manajer', 1)
                    ->first();
                $nip_spv = !empty($temp->nip)?trim($temp->nip):"";
                $manajer = manajer($nip_spv);
            }

            if (!empty($request->id)) {
                $data = AduanNonOperasi::find($request->id);

                if (!empty($data->foto)) {
                    $tmp = date('Y-m', strtotime($data->created_at));
                    $fileExist = 'non-operasi/'.$tmp.'/'.$data->foto;
                    $exist = Storage::disk('sftp')->has($fileExist);
                    if ($exist) {
                        Storage::disk('sftp')->delete($fileExist);
                    }
                } 
            } else {
                $data = new AduanNonOperasi;
            }

            $data->judul = $request->judul;
            $data->lokasi = $request->lokasi;
            $data->sifat = $request->sifat;
            $data->spv = $request->spv;
            $data->catatan = $request->catatan;
            $data->indikasi = $request->indikasi;
            $data->foto = $filename;
            $data->nip_spv = $nip_spv;
            $data->manajer = $manajer;
            $data->status = 0;

            $data->save();

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        }catch(Exception $e){
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('non-operasi::aduan-index');
    }

    public function takeover($wo, $id)
    {
        if ($wo == 'AduanNonOperasi') {
            $data = AduanNonOperasi::find($id);
            $title = "Aduan Non Operasi";
        } else {
            $data = Usulan::find($id);
            $title = "Usulan Non Operasi";
        }

        $petugas = getPetugas($data->instalasi_id, true, trim($data->nip_spv), false);

        return view('pages.non-operasi.takeover', [
            'id' => $id,
            'data' => $data,
            'petugas' => $petugas,
            'wo' => $wo,
            'title' => $title
        ]);
    }

    public function takeoverSimpan(Request $request)
    {
        DB::beginTransaction();

        try {
            $wo = $request->wo;

            if ($wo == 'AduanNonOperasi') {
                $data = AduanNonOperasi::find($request->id);

                $tipenotif = '143';
                $title = "Oper Aduan Non Operasi ". $data->judul;

                $red = 'non-operasi::aduan-index';
            } else {
                $data = Usulan::find($request->id);

                $tipenotif = '153';
                $title = "Oper Usulan Non Operasi ". $data->nama;

                $red = 'non-operasi::usulan-index';
            }
            
            $data->petugas_id = $request->petugas_id;

            $data->save();

            DB::commit();

            kirimnotif($request->petugas_id,
                [
                    'title' => $title,
                    'text' => $title,
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => $tipenotif, 
                    'id' => $request->id
                ]
            );
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.');
        }

        return redirect()->route($red);
    }

    public function usulanIndex()
    {
        $data = '';

        if (in_array(namaRole(), config('custom.pko'))) {
            $status = config('custom.filterStatus');
        }else{
            $status = config('custom.filterStatus');
        }

        $jabatan = ValidasiWo::selectJab(lokasi(), 'usulan');
        // dd($jabatan);

        $instalasi = Instalasi::whereIn('id', lokasi())->get()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Semua Lokasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        $rencanaAnggaran = ["" => "-    Pilih Rencana Penggunaan Anggaran   -",
            "rkap" => "RKAP",
            "tahun berjalan" => "Tahun Berjalan"
        ];
        
        return view('pages.non-operasi.usulan.index', [
            'data' => $data,
            'status' => $status,
            'instalasi' => $instalasi,
            'rencanaAnggaran' => $rencanaAnggaran,
            'jabatan' => $jabatan
        ]);
    }

    public function usulanData(Request $request)
    {
        DB::connection()->enableQueryLog();

        $nip = \Auth::user()->userid;
        $levelJab = cekJabatan(\Auth::user()->userid);

        $period = !empty($request->period)?$request->period:date('Y');
        $status = !empty($request->status)?$request->status:'';
        $anggaran = !empty($request->anggaran)?$request->anggaran:'';
        $instalasi = !empty($request->instalasi)?$request->instalasi:'';
        $spv = !empty($request->spv)?$request->spv:'';
        $recidjabatan = getRecidJabatan($nip);

        $query = Usulan::with(['petugas', 'jabatan', 'bagian', 'instalasi', 'pelapor', 'sukucadang', 'aset'])
            ->whereRaw("TO_CHAR(usulan_non_operasi.created_at, 'YYYY') = $period")
            ->orderBy('id', 'desc');

        if (!in_array(trim($nip), config('custom.dalops')) && !in_array(namaRole(), config('custom.pko'))) {
            if (in_array(namaRole(), config('custom.rolePengolahan'))) {
                $nipPengolahan = getUserPengolahan(lokasi());
                
                switch ($levelJab) {
                    case 'petugas':
                        $query->where(function($sql) use($nip, $nipPengolahan) {
                            $sql->whereIn(DB::raw("TRIM(pic)"), $nipPengolahan)
                                ->orWhere('trim(petugas_id)', trim($nip));
                        });
                        break;
                    case 'spv':
                        $query->where(function($sql) use($nip, $nipPengolahan) {
                            $sql->whereIn(DB::raw("TRIM(pic)"), $nipPengolahan)
                                ->orWhere('trim(nip_spv)', trim($nip));
                        });
                        break;
                    case 'manajer':
                        $query->where(function($sql) use($nip, $nipPengolahan) {
                            $sql->whereIn(DB::raw("TRIM(pic)"), $nipPengolahan)
                                ->orWhere('manajer', $nip);
                        });
                        break;
                }
            }
        }

        if (in_array(namaRole(), config('custom.pko'))) {
            $query->whereIn('metode', ['eksternal pp', 'eksternal emergency']);
        }

        if (!empty($anggaran)) {
            $query->where('penggunaan_anggaran', $anggaran);
        }

        if (!empty($instalasi)) {
            $query->where('instalasi_id', $instalasi);
        } else {
            $query->whereIn('instalasi_id', lokasi());
        }

        if (!empty($spv)) {
            $query->where('spv', $spv);
        }

        ValidasiWo::filterStatus($query, $status);

        return Datatables::of($query)
                ->addColumn('menu', function ($model) use($levelJab, $recidjabatan) {                    
                    $temp = aksiTindakanWeb($model->toArray());
                    $edit = $temp;
                    if (namaRole() != "Super Administrator") {
                        $edit = filterMenu($levelJab, $temp);
                    }

                    $detail = '<a href="' . route('non-operasi::usulan-view', ['id' => $model->id]) . '" class="btn btn-sm purple" target="_blank"><i class="fa fa-eye"></i> View </a>'.'&nbsp;';

                    $takeover = '';
                    if ((!empty($model->petugas_id) && $model->status != '10') && namaRole() == "Super Administrator") {
                        $takeover = '<a href="' . route('non-operasi::takeover', ['wo' => 'Usulan', 'id' => $model->id]) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Take Over </a>';
                    }

                    $metode = '';
                    if (($model->spv == $recidjabatan) || namaRole() == "Super Administrator") {
                        if ($model->status == '1' && !empty($model->tgl_foto_investigasi)) {
                            $metode = '<a href="' . route('non-operasi::aduan-metode', ['id' => $model->id, 'tipe' => 'usulan']) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Input Metode </a>';
                        }

                        if (in_array($model->status, ['3.1', '3.2', '3.3', '3.4'])) {
                            $metode = '<a href="' . route('non-operasi::aduan-metode', ['id' => $model->id, 'tipe' => 'usulan']) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Input Metode </a>';
                        }
                    }

                    return $detail.$takeover.$metode;
                })
                ->addColumn('status', function ($model) {                    
                    $edit = statusTindakanWeb($model->toArray(), 'usulan');

                    return $edit;
                })
                ->editColumn('penggunaan_anggaran', function ($row) {
                    if (!empty($row->penggunaan_anggaran)) {
                        if ($row->penggunaan_anggaran == 'rkap') 
                            return strtoupper($row->penggunaan_anggaran);
                        else
                            return ucwords($row->penggunaan_anggaran);
                    } 
                }) 
                ->make(true);
    }

    public function usulanEntri($id = null)
    {
        $data = null;
        $lokasi = [];
        $aset = [];

        if (!empty($id)) {
            $data = Usulan::find($id);

            if (!empty($data->instalasi_id)) {
                $lokasi = Lokasi::get()->pluck('name', 'name')->toArray();
                $labelLokasi = ["" => "-             Pilih Lokasi             -"];
                $lokasi = $labelLokasi + $lokasi;
            }
        }

        $instalasi = Instalasi::get()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Pilih Instalasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        $jabatan = [];

        $labelPenggunaanAngg = ["" => "-             Pilih Penggunaan Anggaran             -"];
        $penggunaanAngg = $labelPenggunaanAngg + config('custom.penggunaan_anggaran');

        $lingkupKerja = config('custom.lingkup_kerja');
        $labellk = ["" => "-             Pilih Lingkup Kerja             -"];
        $lingkupKerja = $labellk + $lingkupKerja;
// dd($data);
        return view('pages.non-operasi.usulan.entri', [
            'id' => $id,
            'jabatan' => $jabatan,
            'instalasi' => $instalasi,
            'lokasi' => $lokasi,
            'aset' => $aset,
            'data' => $data,
            'penggunaanAngg' => $penggunaanAngg,
            'lingkupKerja' => $lingkupKerja
        ]);
    }

    public function usulanView($id)
    {
        $data = null;

        $data = $data = Usulan::with(['petugas', 'jabatan', 'bagian', 'instalasi', 'aset'])
                ->where('id', $id)
                ->first();

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
        $dataSc = listSukucadang('usulan_non_operasi_id', $id, false, true);
        $dataScWait = listSukucadang('usulan_non_operasi_id', $id, true);

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

        return view('pages.non-operasi.usulan.usulan-view', [
            'id' => $id,
            'data' => $data,
            'statusDed' => $statusDed,
            'statusMsppp' => $statusMsppp,
            'dataSc' => $dataSc,
            'dataScWait' => $dataScWait,
            'pairKodeAlias' => $pairKodeAlias,
            'keyPairKodeAlias' => $keyPairKodeAlias,
            'cekMasukProposal' => $cekMasukProposal
        ]);
    }

    public function mspppSimpan(Request $request)
    {
        DB::beginTransaction();

        try {
            if ($request->wo == 'aduan_non_op') {
                $data = AduanNonOperasi::find($request->id);
                $rute = 'non-operasi::aduan-index';
            } else {
                $data = Usulan::find($request->id);
                $rute = 'non-operasi::usulan-index';
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

    public function usulanDedSimpan(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = Usulan::find($request->id);
            $next = $request->status;

            if ($next == '4.3') {
                $next = '2';
                $data->tgl_ded_selesai = getNow();
            }

            if ($next == '99') {
                $data->pko_catatan_tolak = $request->pko_catatan;
                $data->tgl_pko_catatan_tolak = getNow();
                
                $data->last_action = "Ditolak dari Proses DED";
            }

            $data->status = $next;

            $data->save();

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.');
        }

        return redirect()->route('non-operasi::usulan-index');
    }

    public function lokasiSelect($id)
    {
        $bb = Lokasi::where('instalasi_id', $id)->get();
        $template = '';
        foreach ($bb as $row) {
            $template .= '<option value="' . $row->name . '">' . $row->name . '</option>';
        }

        return response()->json([
            'template' => $template
        ]);
    }

    public function selectJab($instalasi, $wo)
    {
        try {
            $jabatan = ValidasiWo::selectJab($instalasi, $wo);

            $template = '';
            foreach ($jabatan as $key => $value) {
                
                $template .= '<option value="' . $key . '">' . $value . '</option>';
            }

            return response()->json([
                'template' => $template
            ])->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function asetSelect(Request $request, $id)
    {
        $instalasi = $request->instalasi;
        $lokasi = $id;

        $bb = Aset::where('instalasi_id', $instalasi)
            ->where('lokasi_id', $lokasi)
            ->get();

        $template = '';

        foreach ($bb as $row) {
            $template .= '<option value="' . $row->id . '">' . $row->nama_aset . '</option>';
        }

        return response()->json([
            'template' => $template
        ]);
    }

    public function usulanSimpan(Request $request)
    {
        DB::beginTransaction();
        // dd($request->all());
        try {
            $filename = null;
            $nip_spv = null;
            $manajer = null;

            if (!empty($request->spv)) {
                $temp = tuRoleUser::where('roleuserid', $request->spv)
                    ->where('is_manajer', 1)
                    ->first();
                $nip_spv = !empty($temp->nip)?$temp->nip:"";
                $manajer = manajer(trim($nip_spv));
            }

            if (!empty($request->id)) {
                $data = Usulan::find($request->id);
            } else {
                $data = new Usulan;
            }

            $data->pic = str_pad($request->pic, 30);
            $data->nama = $request->nama;
            $data->instalasi_id = $request->instalasi_id;
            $data->lokasi = $request->lokasi_id;
            $data->spv = $request->spv;
            $data->tujuan = $request->tujuan;
            $data->keterangan = $request->keterangan;
            $data->nip_spv = $nip_spv;
            $data->manajer = str_pad($manajer, 30);
            $data->status = 0;

            // additional
            $data->penggunaan_anggaran = $request->penggunaan_anggaran;
            $data->lingkup_kerja = $request->lingkup_kerja;

            if ($request->lingkup_kerja == "Perbaikan/Overhoul/ Penggantian Aset Operasi") {
                $data->aset_id = $request->aset_id;
            }

            $data->save();

            if ($request->hasFile('foto_kondisi')) {
                // dd($data->id);
                $id = $data->id;
                $file = $request->file('foto_kondisi');
                $extension = $file->getClientOriginalExtension();
                $filename = 'kondisi_' . date('dmYHis') .'.'. $extension;

                $dir = 'non-operasi/usulan/'.$id;
                cekDir($dir);
                Storage::disk('sftp')->put($dir .'/'. $filename, \File::get($file));

                $temp = Usulan::find($id);
                $temp->foto_kondisi = $filename;

                $temp->save();
            }
// dd('test');
            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');

            // Notif
            $aduanNotif = Usulan::where('id', $data->id)->first();
            $notif = kirimnotif(trim($nip_spv),
                [
                    'title' => 'Pemberitahuan Usulan Non-Operasi Baru',
                    'text' => sprintf('Pemberitahuan Usulan Non-Operasi %s', $aduanNotif->nama),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '251', 
                    'id' => $data->id
                ]
                // ['tipe' => '1', 'id' => $request->id]
            );
            // End Notif
        }catch(Exception $e){
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('non-operasi::usulan-index');
    }
}
