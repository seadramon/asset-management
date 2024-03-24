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
    Asset\Models\Ms52w,
    Asset\Models\Ms4w,
    Asset\Models\Prw4w,
    Asset\Models\JadwalLibur,
    Asset\Models\JadwalLiburPompa,
    Asset\Models\Proposal as ProModel,
    Asset\Role as tuRoleUser;

use Asset\Libraries\ValidasiWo;

use Illuminate\Support\Facades\File;
use DB;
use Html;
use Validator;
use Redirect;
use Session;
use Image;
use Storage;

class MonitoringSpvController extends Controller
{
    function __construct(Request $request)
    {
        $nip = $request->header('nip');
        if ( !in_array(namaRole($nip), config('custom.mainRole')) ) {
            abort(404);
        }       
    }

    public function perawatan(Request $request)
    {
        $start = $request->get('start'); //offset
        $limit = $request->get('limit');
        $status = $request->get('status');

        $nip = $request->header('nip');

        // dd($request->all());

        $captStatus = [
            '',
            'disposisi',
            'input-metode',
            'closing'
        ];
        
        $result = [];

        try {
            $data = Perawatan::with(['komponen', 'instalasi', 'bagian', 'ms4w', 'sukucadang'])
                ->whereIn('prw_data.instalasi_id', lokasi($nip))
                ->whereIn('prw_data.bagian_id', bagian($nip))
                ->whereNotIn('prw_data.status', config('custom.skipStatus'));

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            if (!empty($status)) {
                switch ($status) {
                    case 'disposisi':
                        $data = $data->where('prw_data.status', 0)
                            ->whereNull('prw_data.petugas_id');
                        break;
                    case 'input-metode':
                        $data = $data->whereIn('prw_data.status', ['1', '3.1', '3.2', '3.3', '3.4'])
                            ->whereNotNull('prw_data.tgl_foto_investigasi');
                        break;
                    case 'closing':
                        $data = $data->where('prw_data.status', 2)
                            ->whereNotNull('prw_data.foto')
                            ->whereNotNull('prw_data.approve_dalops');
                        break;
                }
            }

            $data = $data->get()->toArray();
            /*foreach ($data as $row) {
                if (!in_array($row['bagian_id'], ['1', '2', '3', '4'])) {
                    dd($row);
                }
            }*/
// dd($data);
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $action = aksiTindakan($row);
                    $status = statusTindakanManajer($row);

                    //dd($row);

                    $result[] = self::tindakanTransformer($row, $status, $action, 'perawatan');
                }
            }

            //dd($result[]);

            $return = ['data' => $result, 'next' => 'false'];

            if ($limit !== null) {
                $return = ['data' => array_slice($result, 0, $limit), 'next' => (count($result) > $limit)];    
            }

            return response()->json($return)->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function prwDisposisi(Request $request)
    {
        DB::beginTransaction();

        try {
            // dd($request->all());
            $nip = $request->header('nip');

            $data = Perawatan::where('id', $request->id)
                ->update([
                    'petugas_id' => $request->petugas,
                    'tgl_disposisi' => getNow(),
                    'manajer' => manajer(trim($nip)),
                    'last_action' => 'Disposisi',
                    'updated_at' => getNow(),
                    // 'spv' => $nip,
                    'spv' => getRecidJabatan($nip),
                    // 'nip_spv' => getRecidJabatan($nip)
                    'nip_spv' => $nip
            ]);

            DB::commit();

            // Notif
            $perawatan = Perawatan::with('komponen')->where('id', $request->id)->first();
            $notif = kirimnotif($request->petugas,
                [
                    'title' => 'Pemberitahuan WO Perawatan',
                    'text' => sprintf('Pemberitahuan WO Perawatan untuk %s', $perawatan->komponen->nama_aset),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '131', 
                    'id' => $request->id
                ]
            );
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

    public function prwMetode(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip');
            // dd($request->all());
            $data = Perawatan::find($request->id);
            $komponenId = $data->komponen_id;
            $data->kondisi = $request->kondisi;
            $data->metode = $request->metode;
            $data->sifat = $request->sifat;
            $data->tingkat = $request->tingkat;
            
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

                    $filename = trim($nip) . '_' . $request->id . '_proposal.' . $extension;
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
            $data->is_ded = ValidasiWo::cekDedRevisiPenanganan($data->status, $request->metode, $request->sifat, $data->tgl_ded_selesai);

            $data->jenis_penanganan = $request->jenis_penanganan;
            // $data->status = '2';
            $data->status = '1.1';

            $data->last_action = 'Input Metode';
            $data->updated_at = getNow();

            $data->save();

            DB::commit();

            if ($data->proposal_id != null) {
                $dataProposal = ProModel::find($data->proposal_id);

                if ($data->perkiraan_revisi != null) {
                    $dataProposal->perkiraan = $data->perkiraan_revisi;
                } else {
                    $dataProposal->perkiraan = $data->perkiraan;
                }

                $dataProposal->perkiraan_anggaran = $data->perkiraan_anggaran;
                $dataProposal->tahun_anggaran = $data->tahun_anggaran;
                $dataProposal->spv = $data->spv;
                $dataProposal->nip_spv = $data->nip_spv;

                $dataProposal->save();

                DB::commit();
            }

            // Notif
            $prwNotif = Perawatan::where('id', $request->id)->first();
            $notif = kirimnotif(manajer($nip),
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

    public function prwClose(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = Perawatan::find($request->id);
            $data->status = '10';
            $data->tgl_finish = getNow();

            $data->last_action = 'Closing';
            $data->updated_at = getNow();

            $data->save();

            // kembalikan status todolist monitoring ke semula
            /*self::unlockMonitoring([
                'id' => $request->id,
                'komponen_id' => $data->komponen_id,                
                'otherWo' => 'prb_data',
                'ms_4w_id' => $data->ms_4w_id
            ]);*/

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

    private static function unlockMonitoring($param)
    {
        $cek = DB::table($param['otherWo'])
            ->where('ms_4w_id', $param['ms_4w_id'])
            ->where('status', '<>', '10')
            ->count();
// dd($cek);
        if ($cek < 1) {
            $data = Ms4w::whereIn('ms_52w_id', function($query) use($param){
                $query->select('id')
                    ->from('MS_52W')
                    ->where('komponen_id', $param['komponen_id'])
                    ->where('tahun', date('Y'));
            })
            ->where('status', '<>', '1')
            ->where('urutan_minggu', '>=', date('W'))
            ->update(['status' => '0']);
        }
    }

    public function tindakanTransformer($row, $status, $action, $tindakan)
    {
        $isMasaPemeliharaan = "no";
        $masaPemeliharaan = "";
    // dd($row);
        if (!empty($row['pemeliharaan_start']) && !empty($row['pemeliharaan_end'])) {
            $masaPemeliharaan = $row['pemeliharaan_start'].' s/d '.$row['pemeliharaan_end'];

            $isMasaPemeliharaan = cekMasaPemeliharaan($row['pemeliharaan_start'], $row['pemeliharaan_end']);
        }

        return [
            'aset_id'            => $row['komponen']['id'],
            'aset'               => $row['komponen']['nama_aset'],
            'penyedia'           => $row['komponen']['penyedia'],
            'ppk'                => $row['komponen']['ppk'],
            'instalasi'          => $row['instalasi']['name'],
            'instalasi_id'       => $row['instalasi']['id'],
            'bagian_id'          => $row['bagian']['id'],
            'bagian'             => $row['bagian']['name'],

            // prw
            'id'                 => $row['id'],
            'petugas'            => $row['petugas_id'],
            // Add by Nafi (18/03/2021)
            'petugas_nama'       => namaPegawai($row['petugas_id']),
            //
            'tanggal'            => $row['tanggal'],
            'perkiraan'          => $row['perkiraan'],
            'perkiraan_revisi'   => !empty($row['perkiraan_revisi'])?$row['perkiraan_revisi']:"",
            'tgl_start'          => $row['tgl_start'],
            'tgl_finish'         => $row['tgl_finish'],
            'metode'             => $row['metode'],
            'kondisi'            => $row['kondisi'],
            'uraian'             => $row['uraian'],
            'sifat'             => $row['sifat'],
            'tingkat'            => !empty($row['tingkat'])?$row['tingkat']:"",
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

            'masaPemeliharaan'   => $masaPemeliharaan,
            'isMasaPemeliharaan'   => $isMasaPemeliharaan,

            'm_catatan'         => $row['m_catatan'],
            'ms_ppp_catatan'    => $row['ms_ppp_catatan'],
            'dalpro_catatan'    => $row['dalpro_catatan'],
            'petugas_catatan'   => $row['petugas_catatan'],
            // Add by Nafi 29-03-2021
            'perkiraan_anggaran'    => !empty($row['perkiraan_anggaran'])?$row['perkiraan_anggaran']:"0",
            'tahun_anggaran'    => !empty($row['tahun_anggaran'])?$row['tahun_anggaran']:"",
            'proposal'              => !empty($row['proposal'])?url('doc-api/dokumen/'.$tindakan.'&proposal&'.$row['proposal']):"",
            //

            'action'             => $action,
            'status'             => $status,
        ];
    }

    public function perbaikan(Request $request)
    {
        $start = $request->get('start'); //offset
        $limit = $request->get('limit');
        $status = $request->get('status');

        $nip = $request->header('nip');

        $captStatus = [
            '',
            'disposisi',
            'input-metode',
            'closing'
        ];
        
        $result = [];

        try {
            $data = Perbaikan::with(['komponen', 'bagian', 'instalasi', 'ms4w', 'sukucadang'])
                ->whereIn('prb_data.instalasi_id', lokasi($nip))
                ->whereIn('prb_data.bagian_id', bagian($nip))
                ->whereNotIn('prb_data.status', config('custom.skipStatus'))
                ->where('prb_data.tipe', 'monitoring');

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            if (!empty($status)) {
                switch ($status) {
                    case 'disposisi':
                        $data = $data->where('prb_data.status', 0)
                            ->whereNull('prb_data.petugas_id');
                        break;
                    case 'input-metode':
                        $data = $data->whereIn('prb_data.status', ['1', '3.1', '3.2', '3.3', '3.4'])
                            ->whereNotNull('prb_data.tgl_foto_investigasi');
                        break;
                    case 'closing':
                        $data = $data->where('prb_data.status', 2)
                            ->whereNotNull('prb_data.foto')
                            ->whereNotNull('prb_data.approve_dalops');
                        break;
                }
            }

            $data = $data->get()->toArray();
// dd($data);
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $action = aksiTindakan($row);
                    $status = statusTindakanManajer($row);

                    $result[] = self::tindakanTransformer($row, $status, $action, 'perbaikan/monitoring');
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

    public function prbDisposisi(Request $request)
    {
        DB::beginTransaction();

        try {
            // dd($request->all());
            $nip = $request->header('nip');

            $data = Perbaikan::where('id', $request->id)
                ->update([
                    'petugas_id' => $request->petugas,
                    'tgl_disposisi' => getNow(),
                    'manajer' => manajer(trim($nip)),
                    'last_action' => 'Disposisi',
                    'updated_at' => getNow(),
                    // 'spv' => $nip,
                    'spv' => getRecidJabatan($nip),
                    // 'nip_spv' => getRecidJabatan($nip)
                    'nip_spv' => $nip
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
                    'tipe' => '121', 
                    'id' => $request->id
                ]
            );
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

    public function prbMetode(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip');
            // dd($request->all());
            $data = Perbaikan::find($request->id);
            $komponenId = $data->komponen_id;

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

                    $filename = trim($nip) . '_' . $request->id . '_proposal.' . $extension;
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
                ValidasiWo::tidakBeroperasi($komponenId);
            }

            // cek masuk DED, revisi dr penanganan tdk masuk DED lg
            $data->is_ded = ValidasiWo::cekDedRevisiPenanganan($data->status, $request->metode, $request->sifat, $data->tgl_ded_selesai);

            $data->jenis_penanganan = $request->jenis_penanganan;
            // $data->status = '2';
            $data->status = '1.1'; //to manajer pemeliharaan
            $data->tingkat = $request->tingkat;

            $data->last_action = 'Input Metode';
            $data->updated_at = getNow();

            $data->save();

            DB::commit();

            if ($data->proposal_id != null) {
                $dataProposal = ProModel::find($data->proposal_id);

                if ($data->perkiraan_revisi != null) {
                    $dataProposal->perkiraan = $data->perkiraan_revisi;
                } else {
                    $dataProposal->perkiraan = $data->perkiraan;
                }
                
                $dataProposal->perkiraan_anggaran = $data->perkiraan_anggaran;
                $dataProposal->tahun_anggaran = $data->tahun_anggaran;
                $dataProposal->spv = $data->spv;
                $dataProposal->nip_spv = $data->nip_spv;

                $dataProposal->save();

                DB::commit();
            }

            // Notif
            $prbNotif = Perbaikan::where('id', $request->id)->first();
            $notif = kirimnotif(manajer($nip),
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

    public function prbClose(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = Perbaikan::find($request->id);
            $data->status = '10';
            $data->tgl_finish = getNow();

            $data->last_action = 'Closing';
            $data->updated_at = getNow();

            $data->save();

            // kembalikan status todolist monitoring ke semula
            /*self::unlockMonitoring([
                'id' => $request->id,
                'komponen_id' => $data->komponen_id,                
                'otherWo' => 'prw_data',
                'ms_4w_id' => $data->ms_4w_id
            ]);*/

            DB::commit();

            $prb = Perbaikan::find($request->id);
            if ($prb->kondisi == "tidak beroperasi") {
                ValidasiWo::tidakBeroperasiRevive($prb->komponen_id);
            }
            
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
}
