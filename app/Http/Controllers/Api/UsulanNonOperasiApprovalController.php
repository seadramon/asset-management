<?php

namespace Asset\Http\Controllers\Api;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\AduanNonOperasi,
    Asset\Models\Usulan,
    Asset\Models\Instalasi,
    Asset\Models\Lokasi,
    Asset\Models\RoleUser,
    Asset\Models\Role,
    Asset\Role as tuRoleUser,
    Asset\Models\PermohonanSc;

use Asset\Libraries\ValidasiWo;

use Asset\Jabatan;

use DB;
use Datatables;
use Session;
use Validator;
use Storage;

class UsulanNonOperasiApprovalController extends Controller
{
    public function index(Request $request)
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
            $data = AduanNonOperasi::with('sukucadang')
                ->select('aduan_non_operasi.*', 'instalasi.name as instalasi')
                ->join('instalasi', 'aduan_non_operasi.instalasi_id', '=', 'instalasi.id')
                ->whereNotIn('aduan_non_operasi.status', ['10', '99']);

            if (!in_array(trim($nip), $dalops)) { //if not dalops manajer
                if ( $nip == trim(getMsPpp()->nip) ) {
                    $data->where('aduan_non_operasi.status', '1.3'); // MS PPP
                } else {
                    $data = $data->where(DB::raw('trim(aduan_non_operasi.manajer)'), trim($nip))
                        ->where('aduan_non_operasi.status', '1.1');
                }
            } else {
                $data = $data->whereIn('aduan_non_operasi.status', ['1.2', '2'])
                    ->whereNull('aduan_non_operasi.approve_dalops');
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
                        $result[] = self::aduanTransformer($row, $status, $action);
                    } else {
                        if ($row['metode'] != 'eksternal emergency' && $row['status'] == '1.2') {
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

    private function aduanTransformer($row, $status = '', $action = '')
    {
        // dd($row);
        return [
            'id'                    => $row['id'],
            'judul'                 => $row['judul'],
            'instalasi'             => $row['instalasi'],
            'lokasi'                => $row['lokasi'],
            'sifat'                 => $row['sifat'],
            'spv'                   => $row['spv'],
            'foto'                  => $row['foto'],
            'catatan'               => $row['catatan'],
            'indikasi'              => $row['indikasi'],
            'tanggal'               => $row['created_at'],
            'nip_spv'               => $row['nip_spv'],
            'manajer'               => $row['manajer'],
            'petugas'               => $row['petugas'],
            'perkiraan'             => $row['perkiraan'],
            'perkiraan_revisi'      => $row['perkiraan_revisi'],
            'tgl_start'             => $row['tgl_start'],
            'tgl_finish'            => $row['tgl_finish'],
            'metode'                => $row['metode'],
            'tgl_disposisi'         => $row['tgl_disposisi'],
            'penyebab'              => $row['penyebab'],
            'tgl_foto_investigasi'  => $row['tgl_foto_investigasi'],
            'foto_investigasi'      => $row['foto_investigasi'],
            'foto_investigasi2'     => $row['foto_investigasi2'],
            'jenis_penanganan'      => $row['jenis_penanganan'],
            'tgl_input_metode'      => $row['tgl_input_metode'],
            'tgl_foto_analisa'      => $row['tgl_foto_analisa'],
            'uraian'                => $row['uraian'],
            'foto2'                 => $row['foto2'],
            'approve_manajer'       => $row['approve_manajer'],
            'approve_dalops'        => $row['approve_dalops'],
            'catatan_m'             => $row['catatan_rev_manajer'],
            'catatan_dalpro'        => $row['dalpro_catatan'],
            'catatan_p'             => $row['petugas_catatan'],

            'status'        => $status,
            'action'        => $action
        ];
    }

    public function approveManajer(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip');
            $id = $request->id;
            $konfirmasi = $request->konfirmasi;

            $data = Usulan::find($id);
// dd($data);            
            if ($konfirmasi == 'yes') {
                if ($data->metode == 'eksternal emergency') {
                    $data->status = '2';
                    $data->approve_dalops = getNow();

                    $nipNotif = $data->petugas_id;
                    $title = 'Penanganan ';
                    $tipe = 152;
                } elseif ($data->metode == 'internal' && sizeof($data->sukucadang) < 1) {
                    $data->status = '2';
                    $data->approve_dalops = getNow();

                    $nipNotif = $data->petugas_id;
                    $title = 'Penanganan ';
                    $tipe = 152;
                } else {
                    $data->status = '1.2';

                    $nipNotif = config('custom.manajerDalops');
                    $title = 'Approval';
                    $tipe = 451;
                }

                $data->approve_manajer = DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')");
            } elseif ($konfirmasi == 'revisi') {
                $data->status = '3.1';
                $data->catatan_rev_manajer = $request->m_catatan;
                $data->tgl_m_catatan = getNow();
            } else {
                $data->status = '99';
                $data->m_catatan_tolak = $request->m_catatan;
                $data->tgl_m_catatan_tolak = getNow();
            }
            $data->save();
            
            DB::commit();

            // Notif
            if ($konfirmasi == 'yes') {
                kirimnotif($nipNotif,
                    [
                        'title' => $title.' Usulan Non Aduan',
                        'text' => sprintf('%s Usulan Non Aduan untuk %s', $title, $data->nama),
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

    public function approveDalops(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip');
            $id = $request->id;
            $konfirmasi = $request->konfirmasi;

            $data = Usulan::find($id);

            if ($konfirmasi == 'yes') {
                $nextStatus = ValidasiWo::approveDalproNextStatus($data->metode, $data->sifat, $data->is_ded);

                if ($data->metode == 'eksternal emergency' && ($data->status=='2' && !empty($data->foto))) {
                    $data->approve_dalops = DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')");
                }

                if ($data->metode != 'eksternal emergency') {
                    $data->approve_dalops = DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')");

                    $data->status = $nextStatus;

                    // konfirm suku cadang
                    self::confirmSc('usulan_non_operasi_id', $id);

                    if ($nextStatus == '2') {
                        // Notif penanganan
                        kirimnotif(trim($data->petugas_id),
                            [
                                'title' => 'Penanganan Usulan Non Aduan',
                                'text' => sprintf('Penanganan Usulan Non Aduan untuk %s', $data->nama),
                                'sound' => 'default',
                                'click_action' => 'OPEN_ACTIVITY_NOTIF',
                                'tipe' => 152, 
                                'id' => $id
                            ] 
                            // ['tipe' => '2', 'id' => $request->id]
                        );
                        // End Notif
                    } else {
                        // Notif manajer ppp
                        kirimnotif(trim(getMsPpp()->nip),
                            [
                                'title' => 'Approval Usulan Non Aduan',
                                'text' => sprintf('Approval Usulan Non Aduan untuk %s', $data->nama),
                                'sound' => 'default',
                                'click_action' => 'OPEN_ACTIVITY_NOTIF',
                                'tipe' => 551, 
                                'id' => $id
                            ] 
                            // ['tipe' => '2', 'id' => $request->id]
                        );
                    }
                }

            } else if ($konfirmasi == 'revisi') {
                $data->status = '3.3';
                $data->dalpro_catatan = $request->m_catatan;
                $data->tgl_dalpro_catatan = getNow();

                // set null manajer sebelumnya
                $data->approve_manajer = null;
            } else {
                $data->status = '99';
                $data->dalpro_catatan_tolak = $request->m_catatan;
                $data->tgl_dalpro_catatan_tolak = getNow();
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

    // Confirm Permohonan Suku Cadang
    public static function confirmSc($wo, $woId)
    {
        $trg = PermohonanSc::where($wo, $woId)
            ->where('status', 'permintaan')
            ->update(['status' => 'baru']);
    }
}
