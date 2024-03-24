<?php

namespace Asset\Http\Controllers\Api;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\User;
use Asset\Models\Aset,
    Asset\Models\PmlKeluhan,
    Asset\Models\Master,
    Asset\Models\MasterFm,
    Asset\Models\MasterJab,
    Asset\Models\Perawatan,
    Asset\Models\Perbaikan,
    Asset\Models\Prw4w,
    Asset\Role as tuRoleUser,
    Asset\Models\AduanNonOperasi,
    Asset\Models\Usulan,
    Asset\Models\PermohonanSc;

use Asset\Libraries\ValidasiWo;

use Illuminate\Support\Facades\File;
use DB;
use Html;
use Validator;
use Redirect;
use Session;
use Image;
use Storage;

class ApprovalController extends Controller
{
    function __construct(Request $request)
    {
        $nip = $request->header('nip');
// dd(namaRole($nip));
        if ( !in_array(namaRole($nip), config('custom.mainRole')) ) {
            abort(404);
        }       
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function aduan(Request $request)
    {
        $start = $request->get('start'); //offset
        $limit = $request->get('limit');

        $nip = $request->header('nip');
        $dalops = [
            '10601344',
            config('custom.manajerDalops')
        ];
        $result = [];

        try {
            $data = Perbaikan::with('sukucadang')
                ->select('prb_data.*','aset.id as aset_id', 'aset.nama_aset', 'aset.pemeliharaan_start', 'aset.pemeliharaan_end', 'lokasi.name as lokasinm', 'instalasi.name as instalasi','x.name as bagian', 'aset.instalasi_id', 'aset.bagian as bagian_id', 'aset.lokasi_id')
                ->leftJoin('aset', 'prb_data.komponen_id', '=', 'aset.id')
                ->leftJoin('instalasi', 'aset.instalasi_id', '=', 'instalasi.id')
                ->leftJoin('lokasi', 'aset.lokasi_id', '=', 'lokasi.id')
                ->leftJoin('master x', 'aset.bagian', '=', 'x.id')
                ->where('tipe', 'aduan')
                // ->whereNotIn('prb_data.status', config('custom.skipStatus'))
                ->orderBy('prb_data.id', 'desc');

            if ( !in_array(trim($nip), $dalops) ) { //if not dalops manajer
                if ( $nip == trim(getMsPpp()->nip) ) {
                    $data->where('prb_data.status', '1.3'); // Manajer PKO
                } else {
                    $data->where(DB::raw('trim(prb_data.manajer)'), trim($nip)) //manajer pemeliharaan
                        ->where('prb_data.status', '1.1');
                }
            } else {
                $data->whereIn('prb_data.status', ['1.2', '2']);
            }

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            $data = $data->get()->toArray();

            //dd($data);
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $action = aksiTindakan($row);
                    $status = statusTindakanManajer($row);

                    if (!in_array(trim($nip), $dalops)) { //if not dalops manajer
                        $result[] = self::aduanTransformer($row, $status, $action);
                    } else {
                        //if ($row['metode'] != 'eksternal emergency' && $row['status'] == '1.2') {
                    	if ($row['metode'] != 'eksternal emergency') {
                            $result[] = self::aduanTransformer($row, $status, $action);    
                        }

                        if ($row['metode'] == 'eksternal emergency' && ($row['status']=='2' && !empty($row['foto']))) {
                            $result[] = self::aduanTransformer($row, $status, $action);
                        }
                    }
                }
            }

            $return = ['data' => $result, 'next' => 'false'];

            if ($limit !== null) {
                $return = ['data' => array_slice($result, 0, $limit), 'next' => (count($result) > $limit)];    
            }

            return response()->json($return)->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    private static function aduanTransformer($row, $status, $action)
    {
        $isMasaPemeliharaan = "no";
        $masaPemeliharaan = "";
        
        if (!empty($row['pemeliharaan_start']) && !empty($row['pemeliharaan_end'])) {
            $masaPemeliharaan = $row['pemeliharaan_start'].' s/d '.$row['pemeliharaan_end'];

            $isMasaPemeliharaan = cekMasaPemeliharaan($row['pemeliharaan_start'], $row['pemeliharaan_end']);
        }

        $period = date("Y-m", strtotime($row['tanggal']));

        return [
            'recidkeluhan'       => $row['id'],
            'judul'              => $row['aduan_judul'],
            'aset_id'            => $row['aset_id'],
            'aset'               => $row['nama_aset'],
            'instalasi'          => $row['instalasi'],
            'instalasi_id'       => $row['instalasi_id'],
            // add by Nafi (11/05/2020)
            'lokasi'             => $row['lokasinm'],
            'lokasi_id'          => $row['lokasi_id'],
            //
            'bagian'             => $row['bagian'],
            'bagian_id'          => $row['bagian_id'],
            'tanggal'            => $row['tanggal'],
            // add by Nafi (18/03/2021)
            'spv'                => roleid($row['petugas_id']),
            //
            // prb
            'id'                 => $row['id'],
            'petugas'            => $row['petugas_id'],
            'tanggal'            => $row['tanggal'],
            'perkiraan'          => !empty($row['perkiraan'])?$row['perkiraan']:"",
            'perkiraan_revisi'   => !empty($row['perkiraan_revisi'])?$row['perkiraan_revisi']:"",
            'tgl_start'          => $row['tgl_start'],
            'tgl_finish'         => $row['tgl_finish'],
            'metode'             => $row['metode'],
            'kondisi'            => $row['kondisi'],
            'uraian'             => $row['uraian'],
            'foto_investigasi'   => !empty($row['foto_investigasi'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto_investigasi'])):"",
            'foto_investigasi2'  => !empty($row['foto_investigasi2'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto_investigasi2'])):"",
            'foto'               => !empty($row['foto'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto'])):"",
            'foto2'              => !empty($row['foto2'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto2'])):"",
            'foto_kondisi'       => !empty($row['aduan_kondisi'])?url('pic-api/gambar/aduan&'.$period.'&'.$row['aduan_kondisi']):"",
            'penyebab'           => $row['penyebab'],
            'tgl_foto_investigasi' => $row['tgl_foto_investigasi'],
            'tgl_foto_analisa'   => $row['tgl_foto_analisa'],
            'jenis_penanganan'   => $row['jenis_penanganan'],
            'tgl_disposisi'      => $row['tgl_disposisi'],
            'tgl_input_metode'   => $row['tgl_input_metode'],
            'sifat'              => !empty($row['sifat'])?$row['sifat']:"",
            'tingkat'            => $row['tingkat'],
            'manajer'            => $row['manajer'],
            'approve_manajer'    => $row['approve_manajer'],
            'approve_dalops'     => $row['approve_dalops'],

            'masaPemeliharaan'   => $masaPemeliharaan,
            'isMasaPemeliharaan'   => $isMasaPemeliharaan,

            'm_catatan'         => $row['m_catatan'],
            'ms_ppp_catatan'    => $row['ms_ppp_catatan'],
            'dalpro_catatan'    => $row['dalpro_catatan'],
            'petugas_catatan'   => $row['petugas_catatan'],
            // Add by Nafi 29-03-2021
            'perkiraan_anggaran'    => !empty($row['perkiraan_anggaran'])?$row['perkiraan_anggaran']:"0",
            'tahun_anggaran'    => !empty($row['tahun_anggaran'])?$row['tahun_anggaran']:"",
            'proposal'              => !empty($row['proposal'])?url('doc-api/dokumen/perbaikan&proposal&'.$row['proposal']):"",
            //

            'action'             => $action,
            'status'             => $status,
        ];
    }

    public function approveAduanManajer(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip');
            $id = $request->id;
            $konfirmasi = $request->konfirmasi;
            $nipNotif = null;

            $data = Perbaikan::find($id);
            $recidkeluhan = $data->aduan_id;

// dd(sizeof($data->sukucadang));
            if ($konfirmasi == 'yes') {
                if ($data->metode == 'eksternal emergency' || $data->komponen->bagian == '3') {
                    $data->status = '2';

                    if ($data->komponen->bagian == '3') {
                        $data->approve_dalops = getNow();
                        $data->status = ValidasiWo::approveDalproNextStatus($data->metode, $data->sifat, $data->is_ded);
                    }

                    $nipNotif = $data->petugas_id;
                    $title = 'Penanganan ';
                    $tipe = 111;

                    // konfirm suku cadang
                    self::confirmSc('prb_data_id', $id);
                } elseif ($data->metode == 'internal' && sizeof($data->sukucadang) < 1) {
                    $data->status = '2';
                    // $data->approve_dalops = getNow();

                    $nipNotif = $data->petugas_id;
                    $title = 'Penanganan ';
                    $tipe = 111;              
                } else {
                    $data->status = '1.2';

                    $nipNotif = config('custom.manajerDalops');
                    $title = 'Approval';
                    $tipe = 411;
                }
// dd('halt');
                $data->approve_manajer = getNow();

                $data->last_action = 'Approve Manajer Pemeliharaan';
                $data->updated_at = getNow();

                $data->save();

                // Notif
                if ($konfirmasi == 'yes') {
                    kirimnotif($nipNotif,
                        [
                            'title' => $title.' WO Perbaikan Aduan',
                            'text' => sprintf('%s WO Perbaikan untuk %s', $title, $data->komponen->nama_aset),
                            'sound' => 'default',
                            'click_action' => 'OPEN_ACTIVITY_NOTIF',
                            'tipe' => $tipe, 
                            'id' => $id
                        ] 
                        // ['tipe' => '2', 'id' => $request->id]
                    );
                }
                // End Notif
            }elseif ($konfirmasi == "revisi") {
                $data->status = '3.1';
                $data->m_catatan = $request->m_catatan;
                $data->tgl_m_catatan = getNow();

                $data->last_action = 'Revisi Manajer Pemeliharaan';
                $data->updated_at = getNow();

                // notif
                $nipNotif = spv($data->petugas_id);
                $title = 'Revisi Input Metode ';
                $tipe = 216;
                // end:notif

                $data->save();

                kirimnotif($nipNotif,
                    [
                        'title' => $title.' WO Perbaikan Aduan',
                        'text' => sprintf('%s WO Perbaikan untuk %s', $title, $data->komponen->nama_aset),
                        'sound' => 'default',
                        'click_action' => 'OPEN_ACTIVITY_NOTIF',
                        'tipe' => $tipe, 
                        'id' => $id
                    ] 
                    // ['tipe' => '2', 'id' => $request->id]
                );
            } else {
                $data->status = '99';
                $data->m_catatan_tolak = $request->m_catatan;
                $data->tgl_m_catatan_tolak = getNow();

                $data->last_action = 'Ditolak Manajer Pemeliharaan';
                $data->updated_at = getNow();

                $data->save();
            }
            
            DB::commit();
            return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function approveAduanDalops(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip');
            $id = $request->id;
            $konfirmasi = $request->konfirmasi;

            $data = Perbaikan::find($id);
            $recidkeluhan = $data->aduan_id;
// dd($data);
            if ($konfirmasi == 'yes') {
                // if ($data->metode == 'eksternal pp') {
                $nextStatus = ValidasiWo::approveDalproNextStatus($data->metode, $data->sifat, $data->is_ded);

                if ($data->metode == 'eksternal emergency' && ($data->status=='2' && !empty($data->foto))) {
                    $data->approve_dalops = getNow();
                }

                if ($data->metode != 'eksternal emergency') {
                    $data->approve_dalops = getNow();

                    $data->status = $nextStatus;

                    // konfirm suku cadang
                    self::confirmSc('prb_data_id', $id);

                    if ($nextStatus == '2') {
	                	// Notif penanganan
			            kirimnotif(trim($data->petugas_id),
	                        [
	                            'title' => 'Penanganan WO Perbaikan Aduan',
	                            'text' => sprintf('Penanganan WO Perbaikan untuk %s', $data->komponen->nama_aset),
	                            'sound' => 'default',
	                            'click_action' => 'OPEN_ACTIVITY_NOTIF',
	                            'tipe' => 111, 
	                            'id' => $id
	                        ] 
	                    );
			            // End Notif
	                } else {
	                	// Notif manajer ppp
	                	kirimnotif(trim(getMsPpp()->nip),
			                    [
			                        'title' => 'Approval WO Perbaikan Aduan',
			                        'text' => sprintf('Approval WO Perbaikan Aduan untuk %s', $data->komponen->nama_aset),
			                        'sound' => 'default',
			                        'click_action' => 'OPEN_ACTIVITY_NOTIF',
			                        'tipe' => 511, 
			                        'id' => $id
			                    ] 
			                );
	                }
                }
                $data->last_action = 'Approve Manajer Dalpro';
                $data->updated_at = getNow();

                $data->save();
            }elseif ($konfirmasi == "revisi") {
                $data->status = '3.3';
                $data->dalpro_catatan = $request->m_catatan;
                $data->tgl_dalpro_catatan = getNow();

                // set null approval manajer sebelumnya
                $data->approve_manajer = null;

                $data->last_action = 'Revisi Manajer Dalpro';
                $data->updated_at = getNow();

                // notif
                $nipNotif = spv($data->petugas_id);
                $title = 'Revisi Input Metode ';
                $tipe = 216;
                // end:notif

                $data->save();

                kirimnotif($nipNotif,
                    [
                        'title' => $title.' WO Perbaikan Aduan',
                        'text' => sprintf('%s WO Perbaikan untuk %s', $title, $data->komponen->nama_aset),
                        'sound' => 'default',
                        'click_action' => 'OPEN_ACTIVITY_NOTIF',
                        'tipe' => $tipe, 
                        'id' => $id
                    ] 
                    // ['tipe' => '2', 'id' => $request->id]
                );
            } else {
                $data->status = '99';
                $data->dalpro_catatan_tolak = $request->m_catatan;
                $data->tgl_dalpro_catatan_tolak = getNow();

                $data->last_action = 'Revisi Manajer Dalpro';
                $data->updated_at = getNow();

                $data->save();
            }
            
            DB::commit();
            return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    private function perbaikanTransformer($row, $status = '', $action = '')
    {
        $isMasaPemeliharaan = "no";
        $masaPemeliharaan = "";
        
        if (!empty($row['komponen']['pemeliharaan_start']) && !empty($row['komponen']['pemeliharaan_end'])) {
            $masaPemeliharaan = $row['komponen']['pemeliharaan_start'].' s/d '.$row['komponen']['pemeliharaan_end'];

            $isMasaPemeliharaan = cekMasaPemeliharaan($row['komponen']['pemeliharaan_start'], $row['komponen']['pemeliharaan_end']);
        }

        return [
            'aset'               => $row['komponen']['nama_aset'],
            'instalasi'          => $row['instalasi']['name'],
            'instalasi_id'       => $row['instalasi']['id'],
            'bagian'             => $row['bagian']['name'],
            'bagian_id'          => $row['bagian']['id'],
            // add by Nafi (18/03/2021)
            'spv'                => roleid($row['petugas_id']),
            //
            
            // prb
            'id'                 => $row['id'],
            'ms_4w_id'           => $row['ms_4w_id'],
            'petugas'            => $row['petugas_id'],
            'tanggal'            => $row['tanggal'],
            'perkiraan'          => !empty($row['perkiraan'])?$row['perkiraan']:"",
            'perkiraan_revisi'   => !empty($row['perkiraan_revisi'])?$row['perkiraan_revisi']:"",
            'tgl_start'          => $row['tgl_start'],
            'tgl_finish'         => $row['tgl_finish'],
            'metode'             => $row['metode'],
            'sifat'              => !empty($row['sifat'])?$row['sifat']:"",
            'tingkat'            => !empty($row['tingkat'])?$row['tingkat']:"",
            'kondisi'            => $row['kondisi'],
            'uraian'             => $row['uraian'],
            'foto_investigasi'  => !empty($row['foto_investigasi'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto_investigasi'])):"",
            'foto_investigasi2' => !empty($row['foto_investigasi2'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto_investigasi2'])):"",
            'foto'              => !empty($row['foto'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto'])):"",
            'foto2'             => !empty($row['foto2'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto2'])):"",
            'penyebab'           => $row['penyebab'],
            'tgl_foto_investigasi' => $row['tgl_foto_investigasi'],
            'tgl_foto_analisa'   => $row['tgl_foto_analisa'],
            'jenis_penanganan'   => $row['jenis_penanganan'],
            'tgl_disposisi'      => $row['tgl_disposisi'],
            'tgl_input_metode'   => $row['tgl_input_metode'],
            'manajer'            => $row['manajer'],
            'approve_manajer'    => $row['approve_manajer'],
            'approve_dalops'     => $row['approve_dalops'],
            'tanggal'            => $row['tanggal'],

            'masaPemeliharaan'   => $masaPemeliharaan,
            'isMasaPemeliharaan'   => $isMasaPemeliharaan,

            'm_catatan'         => $row['m_catatan'],
            'ms_ppp_catatan'    => $row['ms_ppp_catatan'],
            'dalpro_catatan'    => $row['dalpro_catatan'],
            'petugas_catatan'   => $row['petugas_catatan'],
            // Add by Nafi 29-03-2021
            'perkiraan_anggaran'    => !empty($row['perkiraan_anggaran'])?$row['perkiraan_anggaran']:"0",
            'tahun_anggaran'    => !empty($row['tahun_anggaran'])?$row['tahun_anggaran']:"",
            'proposal'              => !empty($row['proposal'])?url('doc-api/dokumen/perbaikan&proposal&'.$row['proposal']):"",
            //

            'action'             => $action,
            'status'             => $status,
        ];
    }

    // ----Perbaikan Monitoring
    public function perbaikan(Request $request)
    {
        $start = $request->get('start'); //offset
        $limit = $request->get('limit');

        $nip = $request->header('nip');
        $dalops = [
            '10601344',
            config('custom.manajerDalops')
        ];
        $result = [];

        //dd(trim(getMsPpp()->nip));

        try {
            $data = Perbaikan::with(['komponen', 'bagian', 'instalasi', 'ms4w', 'sukucadang'])
                ->select('prb_data.*')
                //->where('prb_data.status', '<>', '10')
                // ->whereNotIn('prb_data.status', config('custom.skipStatus'))
                ->where('prb_data.tipe', 'monitoring');

            if ( !in_array(trim($nip), $dalops) ) { //if not dalops manajer

                if ( $nip == trim(getMsPpp()->nip) ) {
                    $data->where('prb_data.status', '1.3'); // Manajer PKO
                } else {
                    $data->where(DB::raw('trim(prb_data.manajer)'), trim($nip)) //manajer pemeliharaan
                        ->where('prb_data.status', '1.1');
                }
            } else {
                $data->where('prb_data.status', '1.2');
            }

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            $data = $data->get()->toArray();

            // dd($data);
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $action = aksiTindakan($row);
                    $status = statusTindakanManajer($row);

                    if (!in_array(trim($nip), $dalops)) { //if not dalops manajer
                        // dd($action);
                        $result[] = self::perbaikanTransformer($row, $status, $action);
                    } else {
                        //if ($row['metode'] != 'eksternal emergency' && $row['status'] == '1.2') {
                    	if ($row['metode'] != 'eksternal emergency') {
                            $result[] = self::perbaikanTransformer($row, $status, $action);    
                        } 

                        if ($row['metode'] == 'eksternal emergency' && ($row['status']=='2' && !empty($row['foto']))) {
                            $result[] = self::perbaikanTransformer($row, $status, $action);
                        } 
                    }
                }
            }

            $return = ['data' => $result, 'next' => 'false'];

            if ($limit !== null) {
                $return = ['data' => array_slice($result, 0, $limit), 'next' => (count($result) > $limit)];    
            }

            return response()->json($return)->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function approvePerbaikanManajer(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip');
            $id = $request->id;
            $konfirmasi = $request->konfirmasi;

            $data = Perbaikan::find($id);
// dd($data);            
            if ($konfirmasi == 'yes') {
                if ($data->metode == 'eksternal emergency' || $data->komponen->bagian == '3') {
                    $data->status = '2';

                    if ($data->komponen->bagian == '3') {
                        $data->approve_dalops = getNow();
                    }

                    $nipNotif = $data->petugas_id;
                    $title = 'Penanganan ';
                    $tipe = 122;

                    // konfirm suku cadang
                    self::confirmSc('prb_data_id', $id);
                } elseif ($data->metode == 'internal' && sizeof($data->sukucadang) < 1) {
                    $data->status = '2';
                    // $data->approve_dalops = getNow();

                    $nipNotif = $data->petugas_id;
                    $title = 'Penanganan ';
                    $tipe = 122;
                } else {
                    $data->status = '1.2';

                    $nipNotif = config('custom.manajerDalops');
                    $title = 'Approval';
                    $tipe = 421;
                }

                $data->approve_manajer = getNow();

                $data->last_action = 'Approve Manajer Pemeliharaan';
                $data->updated_at = getNow();

            } elseif ($konfirmasi == "revisi") { //REVISI
                $data->status = '3.1';
                $data->m_catatan = $request->m_catatan;
                $data->tgl_m_catatan = getNow();

                $data->last_action = 'Revisi Manajer Pemeliharaan';
                $data->updated_at = getNow();

                $nipNotif = spv($data->petugas_id);
                $title = 'Revisi Input Metode ';
                $tipe = 224;

                //$data->save();

                kirimnotif($nipNotif,
                    [
                        'title' => $title.' WO Perbaikan Monitoring',
                        'text' => sprintf('%s WO Perbaikan untuk %s', $title, $data->komponen->nama_aset),
                        'sound' => 'default',
                        'click_action' => 'OPEN_ACTIVITY_NOTIF',
                        'tipe' => $tipe, 
                        'id' => $id
                    ] 
                    // ['tipe' => '2', 'id' => $request->id]
                );
            } else {
                $data->status = '99';
                $data->m_catatan_tolak = $request->m_catatan;
                $data->tgl_m_catatan_tolak = getNow();

                $data->last_action = 'Ditolak Manajer Pemeliharaan';
                $data->updated_at = getNow();
            }

            $data->save();
            
            DB::commit();

            // Notif
            if ($konfirmasi == 'yes') {
                kirimnotif($nipNotif,
                    [
                        'title' => $title.' WO Perbaikan Monitoring',
                        'text' => sprintf('%s WO Perbaikan untuk %s', $title, $data->komponen->nama_aset),
                        'sound' => 'default',
                        'click_action' => 'OPEN_ACTIVITY_NOTIF',
                        'tipe' => $tipe, 
                        'id' => $id
                    ] 
                    // ['tipe' => '2', 'id' => $request->id]
                );
            }
            // End Notif

            return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function approvePerbaikanDalops(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip');
            $id = $request->id;
            $konfirmasi = $request->konfirmasi;

            $data = Perbaikan::find($id);
            // $recidkeluhan = $data->aduan_id;
// dd($data);
            if ($konfirmasi == 'yes') {
                $nextStatus = ValidasiWo::approveDalproNextStatus($data->metode, $data->sifat, $data->is_ded);

                if ($data->metode == 'eksternal emergency' && ($data->status=='2' && !empty($data->foto))) {
                    $data->approve_dalops = getNow();
                }

                if ($data->metode != 'eksternal emergency') {
                    $data->approve_dalops = getNow();
                    
                    $data->status = $nextStatus;

                    // konfirm suku cadang
                    self::confirmSc('prb_data_id', $id);

                    if ($nextStatus == '2') {
	                	// Notif penanganan
			            kirimnotif($data->petugas_id,
			                    [
			                        'title' => 'Penanganan WO Perbaikan Monitoring',
			                        'text' => sprintf('Penanganan WO Perbaikan untuk %s', $data->komponen->nama_aset),
			                        'sound' => 'default',
			                        'click_action' => 'OPEN_ACTIVITY_NOTIF',
			                        'tipe' => 122, 
			                        'id' => $id
			                    ] 
			                );
			            // End Notif
	                } else {
	                	// Notif manajer ppp
	                	kirimnotif(trim(getMsPpp()->nip),
			                    [
			                        'title' => 'Approval WO Perbaikan Monitoring',
			                        'text' => sprintf('Approval WO Perbaikan untuk %s', $data->komponen->nama_aset),
			                        'sound' => 'default',
			                        'click_action' => 'OPEN_ACTIVITY_NOTIF',
			                        'tipe' => 521, 
			                        'id' => $id
			                    ] 
			                );
	                }
                }

                $data->last_action = 'Approve Manajer Dalpro';
                $data->updated_at = getNow();

            } elseif ($konfirmasi == "revisi") { //REVISI
                $data->status = '3.3';
                $data->dalpro_catatan = $request->m_catatan;
                $data->tgl_dalpro_catatan = getNow();

                // set null approval manajer sebelumnya
                $data->approve_manajer = null;

                $data->last_action = 'Revisi Manajer Dalpro';
                $data->updated_at = getNow();

                // notif
                $nipNotif = spv($data->petugas_id);
                $title = 'Revisi Input Metode ';
                $tipe = 224;
                // end:notif

                //$data->save();

                kirimnotif($nipNotif,
                    [
                        'title' => $title.' WO Perbaikan Monitoring',
                        'text' => sprintf('%s WO Perbaikan untuk %s', $title, $data->komponen->nama_aset),
                        'sound' => 'default',
                        'click_action' => 'OPEN_ACTIVITY_NOTIF',
                        'tipe' => $tipe, 
                        'id' => $id
                    ] 
                    // ['tipe' => '2', 'id' => $request->id]
                );
            } else {
                $data->status = '99';
                $data->dalpro_catatan_tolak = $request->m_catatan;
                $data->tgl_dalpro_catatan_tolak = getNow();

                $data->last_action = 'Ditolak Manajer Dalpro';
                $data->updated_at = getNow();
            }
            $data->save();
            
            DB::commit();

            return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }
    // ./ Perbaikan Monitoring

    public function approveMsPpp(Request $request)
    {
        DB::beginTransaction();
// dd($request->all());
        try {
            $nip = $request->header('nip');
            $id = $request->id;
            $konfirmasi = $request->konfirmasi;
            $wo = $request->wo;

            switch ($wo) {
                case 'perawatan':
                    $data = Perawatan::find($id);
                    break;
                case 'perbaikan':
                    $data = Perbaikan::find($id);
                    break;
                case 'aduan non operasi':
                    $data = AduanNonOperasi::find($id);
                    break;
                case 'usulan':
                    $data = Usulan::find($id);
                    break;
                case 'aduan':
                    $data = Perbaikan::find($id);
                    break;
            }
            // $recidkeluhan = $data->aduan_id;
// dd($data);
            if ($konfirmasi == 'yes') {
                $data->status = '4.0';

                $data->approve_ms_ppp = getNow();

                $data->last_action = 'Approve Manajer PKO';
                $data->updated_at = getNow();

            } elseif ($konfirmasi == "revisi") { //REVISI
                $data->status = '3.4';
                $data->ms_ppp_catatan = $request->m_catatan;
                $data->tgl_ms_ppp_catatan = getNow();

                // set null approval manajer sebelumnya
                $data->approve_manajer = null;
                $data->approve_dalops = null;

                $data->last_action = 'Revisi Manajer PKO';
                $data->updated_at = getNow();
            } else {
                $data->status = '99';
                $data->ms_ppp_catatan_tolak = $request->m_catatan;
                $data->tgl_ms_ppp_catatan_tolak = getNow();

                $data->last_action = 'Ditolak Manajer PKO';
                $data->updated_at = getNow();
            }
            $data->save();
            
            DB::commit();

            return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    // Perawatan
    public function perawatan(Request $request)
    {
        $start = $request->get('start'); //offset
        $limit = $request->get('limit');

        $nip = $request->header('nip');
        $dalops = [
            '10601344',
            config('custom.manajerDalops')
        ];
        $result = [];

        try {
            $data = Perawatan::with(['komponen', 'bagian', 'instalasi', 'ms4w', 'sukucadang'])
                ->select('prw_data.*');
                //->where('prw_data.status', '<>', '10')
                // ->whereNotIn('prw_data.status', config('custom.skipStatus'));

            if ( !in_array(trim($nip), $dalops) ) { //if not dalops manajer
                if ( $nip == trim(getMsPpp()->nip) ) {
                    $data->where('prw_data.status', '1.3'); // Manajer PKO
                } else {
                    $data->where(DB::raw('trim(prw_data.manajer)'), trim($nip)) //manajer pemeliharaan
                        ->where('prw_data.status', '1.1');
                }
            } else {
                $data->where('prw_data.status', '1.2');
            }

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            $data = $data->get()->toArray();

            // dd($data);
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $action = aksiTindakan($row);
                    $status = statusTindakanManajer($row);

                    if (!in_array(trim($nip), $dalops)) { //if not dalops manajer
                        $result[] = self::perawatanTransformer($row, $status, $action);
                    } else {
                        //if ($row['metode'] != 'eksternal emergency' && $row['status'] == '1.2') {
                    	if ($row['metode'] != 'eksternal emergency') {
                            $result[] = self::perawatanTransformer($row, $status, $action);    
                        }

                        if ($row['metode'] == 'eksternal emergency' && ($row['status']=='2' && !empty($row['foto']))) {
                            $result[] = self::perawatanTransformer($row, $status, $action);
                        }
                    }
                }
            }

            $return = ['data' => $result, 'next' => 'false'];

            if ($limit !== null) {
                $return = ['data' => array_slice($result, 0, $limit), 'next' => (count($result) > $limit)];    
            }

            return response()->json($return)->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    private function perawatanTransformer($row, $status = '', $action = '')
    {
        $isMasaPemeliharaan = "no";
        $masaPemeliharaan = "";
        
        if (!empty($row['komponen']['pemeliharaan_start']) && !empty($row['komponen']['pemeliharaan_end'])) {
            $masaPemeliharaan = $row['komponen']['pemeliharaan_start'].' s/d '.$row['komponen']['pemeliharaan_end'];

            $isMasaPemeliharaan = cekMasaPemeliharaan($row['komponen']['pemeliharaan_start'], $row['komponen']['pemeliharaan_end']);
        }

        return [
            'aset'               => $row['komponen']['nama_aset'],
            'instalasi'          => $row['instalasi']['name'],
            'instalasi_id'       => $row['instalasi']['id'],
            'bagian'             => $row['bagian']['name'],
            'bagian_id'          => $row['bagian']['id'],
            // add by Nafi (18/03/2021)
            'spv'                => roleid($row['petugas_id']),
            //
            
            // prb
            'id'                 => $row['id'],
            'ms_4w_id'           => $row['ms_4w_id'],
            'komponen_id'        => $row['komponen_id'],
            'foto_investigasi'  => !empty($row['foto_investigasi'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto_investigasi'])):"",
            'foto_investigasi2' => !empty($row['foto_investigasi2'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto_investigasi2'])):"",
            'foto'              => !empty($row['foto'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto'])):"",
            'foto2'             => !empty($row['foto2'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto2'])):"",
            'penyebab'           => $row['penyebab'],
            /*'operasional'        => $row['operasional'],*/
            'jenis_penanganan'   => $row['jenis_penanganan'],
            'perkiraan'          => !empty($row['perkiraan'])?$row['perkiraan']:"",
            'perkiraan_revisi'   => !empty($row['perkiraan_revisi'])?$row['perkiraan_revisi']:"",
            'metode'             => $row['metode'],
            'sifat'              => !empty($row['sifat'])?$row['sifat']:"",
            'petugas_id'         => $row['petugas_id'],
            'tanggal'            => $row['tanggal'],
            'kondisi'            => $row['kondisi'],

            'm_catatan'         => $row['m_catatan'],
            'ms_ppp_catatan'    => $row['ms_ppp_catatan'],
            'dalpro_catatan'    => $row['dalpro_catatan'],
            'petugas_catatan'   => $row['petugas_catatan'],

            'masaPemeliharaan'   => $masaPemeliharaan,
            'isMasaPemeliharaan'   => $isMasaPemeliharaan,
            // Add by Nafi 29-03-2021
            'perkiraan_anggaran'    => !empty($row['perkiraan_anggaran'])?$row['perkiraan_anggaran']:"0",
            'tahun_anggaran'    => !empty($row['tahun_anggaran'])?$row['tahun_anggaran']:"",
            'proposal'              => !empty($row['proposal'])?url('doc-api/dokumen/perawatan&proposal&'.$row['proposal']):"",
            //

            'action'             => $action,
            'status'             => $status,
        ];
    }

    public function approvePerawatanManajer(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip');
            $id = $request->id;
            $konfirmasi = $request->konfirmasi;

            $data = Perawatan::find($id);
// dd($data);            
            if ($konfirmasi == 'yes') {
                if ($data->metode == 'eksternal emergency' || $data->komponen->bagian == '3') {
                    $data->status = '2';

                    if ($data->komponen->bagian == '3') {
                        $data->approve_dalops = getNow();
                    }

                    $nipNotif = $data->petugas_id;
                    $title = 'Penanganan ';
                    $tipe = 132;

                    // konfirm suku cadang
                    self::confirmSc('prw_data_id', $id);
                } elseif ($data->metode == 'internal' && sizeof($data->sukucadang) < 1) {
                    $data->status = '2';
                    // $data->approve_dalops = getNow();

                    $nipNotif = $data->petugas_id;
                    $title = 'Penanganan ';
                    $tipe = 132;
                } else {
                    $data->status = '1.2';

                    $nipNotif = config('custom.manajerDalops');
                    $title = 'Approval';
                    $tipe = 431;
                }

                $data->approve_manajer = getNow();

                $data->last_action = 'Approve Manajer Pemeliharaan';
                $data->updated_at = getNow();

            } elseif ($konfirmasi == "revisi") { //REVISI
                $data->status = '3.1';
                $data->tgl_m_catatan = getNow();
                $data->m_catatan = $request->m_catatan;

                $nipNotif = spv($data->petugas_id);
                $title = 'Revisi Input Metode ';
                $tipe = 234;

                //$data->save();
                $data->last_action = 'Revisi Manajer Pemeliharaan';
                $data->updated_at = getNow();

                kirimnotif($nipNotif,
                    [
                        'title' => $title.' WO Perawatan Monitoring',
                        'text' => sprintf('%s WO Perawatan untuk %s', $title, $data->komponen->nama_aset),
                        'sound' => 'default',
                        'click_action' => 'OPEN_ACTIVITY_NOTIF',
                        'tipe' => $tipe, 
                        'id' => $id
                    ] 
                    // ['tipe' => '2', 'id' => $request->id]
                );
            } else {
                $data->status = '99';
                $data->m_catatan_tolak = $request->m_catatan;
                $data->tgl_m_catatan_tolak = getNow();

                $data->last_action = 'Ditolak Manajer Pemeliharaan';
                $data->updated_at = getNow();
            }
            $data->save();
            
            DB::commit();

            // Notif
            if ($konfirmasi == 'yes') {
                kirimnotif($nipNotif,
                    [
                        'title' => $title.' WO Perawatan Monitoring',
                        'text' => sprintf('%s WO Perawatan untuk %s', $title, $data->komponen->nama_aset),
                        'sound' => 'default',
                        'click_action' => 'OPEN_ACTIVITY_NOTIF',
                        'tipe' => $tipe, 
                        'id' => $id
                    ] 
                    // ['tipe' => '2', 'id' => $request->id]
                );
            }
            // End Notif

            return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function approvePerawatanDalops(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip');
            $id = $request->id;
            $konfirmasi = $request->konfirmasi;

            $data = Perawatan::find($id);

            if ($konfirmasi == 'yes') {
                $nextStatus = ValidasiWo::approveDalproNextStatus($data->metode, $data->sifat, $data->is_ded);

                if ($data->metode == 'eksternal emergency' && ($data->status=='2' && !empty($data->foto))) { //sudah ditangani
                    $data->approve_dalops = getNow();
                }

                if ($data->metode != 'eksternal emergency') { //belum ditangani
                    $data->approve_dalops = getNow();
                    
                    $data->status = $nextStatus;

                    // konfirm suku cadang
                    self::confirmSc('prw_data_id', $id);

                    if ($nextStatus == '2') {
	                	// Notif penanganan
			            kirimnotif($data->petugas_id,
		                    [
		                        'title' => 'Penanganan WO Perawatan Monitoring',
		                        'text' => sprintf('Penanganan WO Perawatan untuk %s', $data->komponen->nama_aset),
		                        'sound' => 'default',
		                        'click_action' => 'OPEN_ACTIVITY_NOTIF',
		                        'tipe' => 132, 
		                        'id' => $id
		                    ] 
		                );
			            // End Notif
	                } else {
	                	// Notif manajer ppp
	                	kirimnotif(trim(getMsPpp()->nip),
			                    [
			                        'title' => 'Approval WO Perawatan Monitoring',
			                        'text' => sprintf('Approval WO Perawatan untuk %s', $data->komponen->nama_aset),
			                        'sound' => 'default',
			                        'click_action' => 'OPEN_ACTIVITY_NOTIF',
			                        'tipe' => 531, 
			                        'id' => $id
			                    ] 
			                );
	                }
                }

                $data->last_action = 'Approve Manajer Dalpro';
                $data->updated_at = getNow();

            } elseif ($konfirmasi == "revisi") { //REVISI
                $data->status = '3.3';
                $data->dalpro_catatan = $request->m_catatan;
                $data->tgl_dalpro_catatan = getNow();

                // set null approval manajer sebelumnya
                $data->approve_manajer = null;

                $data->last_action = 'Revisi Manajer Dalpro';
                $data->updated_at = getNow();

                // notif
                $nipNotif = spv($data->petugas_id);
                $title = 'Revisi Input Metode ';
                $tipe = 234;
                // end:notif

                //$data->save();

                kirimnotif($nipNotif,
                    [
                        'title' => $title.' WO Perawatan Monitoring',
                        'text' => sprintf('%s WO Perawatan untuk %s', $title, $data->komponen->nama_aset),
                        'sound' => 'default',
                        'click_action' => 'OPEN_ACTIVITY_NOTIF',
                        'tipe' => $tipe, 
                        'id' => $id
                    ] 
                    // ['tipe' => '2', 'id' => $request->id]
                );
            } else {
                $data->status = '99';
                $data->dalpro_catatan_tolak = $request->m_catatan;
                $data->tgl_dalpro_catatan_tolak = getNow();

                $data->last_action = 'Ditolak Manajer Dalpro';
                $data->updated_at = getNow();
            }
            $data->save();
            
            DB::commit();

            return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }
    // ./Perawatan

    // Perawatan Rutin
    // Perawatan
    public function perawatanRutin(Request $request)
    {
        /*
        0.3 = Manajer pemeliharaan
        0.6 = Manajer Dalops
        0.9 = ready to penanganan
        */

        $start = $request->get('start'); //offset
        $limit = $request->get('limit');
        $nip = $request->header('nip');

        $lokasi = lokasi($nip);
        $bagian = bagian($nip);
        $tahun = date('Y');
// dd($bagian);
        $dalops = [
            '10601344',
            config('custom.manajerDalops')
        ];

        try {
            $result = [];
            $woGroup = Prw4w::select('wo_id')
                ->join('prw_52w', 'prw_4w.prw_52w_id', '=', 'prw_52w.id')
                ->join('aset', 'prw_52w.komponen_id', '=', 'aset.id')
                ->join('instalasi', 'prw_52w.instalasi_id', '=', 'instalasi.id')
                ->join('ms_komponen_detail', 'prw_52w.part', '=', 'ms_komponen_detail.id')
                ->whereIn('aset.bagian', $bagian)
                // ->where('prw_4w.urutan_minggu', '41')
                // ->where('prw_4w.urutan_minggu', date('W'))
                ->whereRaw("TO_CHAR(prw_4w.TANGGAL_MONITORING, 'YYYY') = $tahun")
                ->where('aset.kondisi_id', '<>', '12')
                ->groupBy('wo_id');

            if (!in_array(trim($nip), $dalops)) { //if not dalops manajer
                $woGroup = $woGroup->where(DB::raw('trim(prw_4w.manajer)'), trim($nip))
                    ->whereNotIn('prw_4w.status', ['1', '99', '0.6', '0.9', '0']);
            } else {
                $woGroup = $woGroup->whereNotIn('prw_4w.status', ['1', '99', '0.3', '0.9', '0']);
            }

            $woGroup = $woGroup->get('wo_id')->pluck('wo_id')->toArray();            

            $data = Prw4w::select('prw_4w.*', 'prw_52w.komponen_id', 'prw_52w.perawatan', 'prw_52w.instalasi_id', 'aset.id as aset_id', 'aset.nama_aset', 'aset.kode_barcode', 'aset.bagian', 'instalasi.name as instalasinya', 'ms_komponen_detail.part')
                ->join('prw_52w', 'prw_4w.prw_52w_id', '=', 'prw_52w.id')
                ->join('aset', 'prw_52w.komponen_id', '=', 'aset.id')
                ->join('instalasi', 'prw_52w.instalasi_id', '=', 'instalasi.id')
                ->join('ms_komponen_detail', 'prw_52w.part', '=', 'ms_komponen_detail.id')
                ->whereIn('aset.bagian', $bagian)
                ->whereRaw("TO_CHAR(prw_4w.TANGGAL_MONITORING, 'YYYY') = $tahun")
                ->where('aset.kondisi_id', '<>', '12');

            if (!in_array(trim($nip), $dalops)) { //if not dalops manajer
                $data = $data->where(DB::raw('trim(prw_4w.manajer)'), trim($nip))
                    ->whereNotIn('prw_4w.status', ['1', '99', '0.6', '0.9', '0']);
            } else {
                $data = $data->whereNotIn('prw_4w.status', ['1', '99', '0.3', '0.9', '0']);
            }

            $dataSc = [];
            $sqlSc = PermohonanSc::with(['detail', 'namaSpv'])
                ->whereIn('prw_rutin_id', $woGroup)
                ->get();

            foreach ($sqlSc as $row) {
                $dataSc[$row->prw_rutin_id][$row->id] = [
                    'nama' => $row->nama,
                    'status' => $row->status,
                    'nipspv' => $row->nip,
                    'spv' => $row->namaSpv->nama,
                    'keterangan' => $row->keterangan,
                ];

                if (!empty($row->detail)) {
                    foreach ($row->detail as $det) {
                        $dataSc[$row->prw_rutin_id][$row->id]['detail_sukucadang'][] = [
                            'kode_alias' => $det->kode_alias,
                            'nama_barang' => $det->barang->nama,
                            'jumlah' => $det->jumlah,
                            'keterangan' => $det->keterangan,
                            'kelompok_barang' => $det->kelompok_barang,
                            'dibeli_by' => $det->dibeli_by,
                            'kode_gudang' => !empty($det->gudang)?$det->gudang:"",
                            'gudang' => !empty($det->gudangRelasi)?$det->gudangRelasi->nama_gdg:"",
                        ];        
                    }
                }
            }

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            $data = $data->get();
            // dd($data);
            if (count($data) > 0) {
                $tempWo = [];
                foreach ($data as $row) {
                    if (!in_array($row->wo_id, $tempWo)) {
                        $tempWo[] = $row->wo_id;

                        $result[$row->wo_id] = [
                            'wo_id' => $row->wo_id,
                            'aset_id' => $row->aset_id,
                            'nama_aset' => $row->nama_aset,
                            'instalasi_id' => $row->instalasi_id,
                            'instalasi' => $row->instalasinya,
                            'kode_barcode' => $row->kode_barcode,
                            'bagian' => $row->bagian,
                            'urutan_minggu' => $row->urutan_minggu,
                            'hari' => $row->hari,
                            'petugas' => $row->petugas,
                            'tanggal' => $row->tanggal,
                            'sukucadang' => !empty($dataSc[$row->wo_id])?$dataSc[$row->wo_id]:null
                        ];

                        $result[$row->wo_id]['detail_perawatan'][] = [
                                'id' => $row->id,
                                'part' => $row->part,
                                'perawatan' => $row->perawatan,
                                'foto' => !empty($row->foto)?$row->foto:"",
                                'status' => $row->status
                            ];
                    } else {
                        $result[$row->wo_id]['detail_perawatan'][] = [
                            'id' => $row->id,
                            'part' => $row->part,
                            'perawatan' => $row->perawatan,
                            'foto' => !empty($row->foto)?$row->foto:"",
                            'status' => $row->status
                        ];
                    }                    
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

    public function approvePerawatanRutinManajer(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip');
            $woid = $request->woid;
            $konfirmasi = $request->konfirmasi;
     
            if ($konfirmasi == 'yes') {
                $temp = Prw4w::where('wo_id', $woid)->first();
                $tempBg = $temp->prw52w->komponen->bagian;

                if ($tempBg == '3') {
                    $data = Prw4w::where('wo_id', $woid)->update([
                        'status' => '0.9',
                        'approve_manajer' => getNow(),
                        'approve_dalops' => getNow()
                    ]);
                } else {
                    // $cekSc = PermohonanSc::where('prw_rutin_id', )

                    $data = Prw4w::where('wo_id', $woid)->update([
                        'status' => '0.6',
                        'approve_manajer' => getNow()
                    ]);
                }

                // self::notif()
            } else {
                $data = Prw4w::where('wo_id', $woid)->update([
                    'status' => '99'
                ]);
            }
            
            DB::commit();

            return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function approvePerawatanRutinDalops(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip');
            $woid = $request->woid;
            $konfirmasi = $request->konfirmasi;

            if ($konfirmasi == 'yes') {
                $data = Prw4w::where('wo_id', $woid)->update([
                    'status' => '0.9',
                    'approve_dalops' => getNow()
                ]);
                // konfirm suku cadang
                self::confirmSc('prw_rutin_id', $woid);
            } else {
                $data = Prw4w::where('wo_id', $woid)->update([
                    'status' => '99'
                ]);
            }
            
            DB::commit();

            return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }
    // End Perawatan Rutin

    private static function notif($asetId, $tipe)
    {
        $aset = Aset::find($asetId);

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

    // Confirm Permohonan Suku Cadang
    public static function confirmSc($wo, $woId)
    {
        $trg = PermohonanSc::where($wo, $woId)
            ->where('status', 'permintaan')
            ->update(['status' => 'baru']);
    }
}
