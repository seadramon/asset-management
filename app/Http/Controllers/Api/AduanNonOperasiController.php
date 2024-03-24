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
    Asset\Role as tuRoleUser;

use Asset\Jabatan;
use Asset\Libraries\ValidasiWo;

use DB;
use Datatables;
use Session;
use Validator;
use Storage;
use Image;

class AduanNonOperasiController extends Controller
{
    protected $disk = "sftp";
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $nip = $request->header('nip');
// dd($nip);
        $title = $request->get('judul');
        $id = $request->get('id');
        $filterStatus = $request->get('filterStatus');
        $start = $request->get('start'); //offset
        $limit = $request->get('limit'); 

        $dalops = [
            '10601344',
            config('custom.manajerDalops')
        ];
// dd(in_array(trim($nip), $dalops));
        try {
            $levelJab = cekJabatan($nip);
            $result = [];

            $data = AduanNonOperasi::with(['petugas', 'jabatan', 'bagian', 'sukucadang'])
                ->select('aduan_non_operasi.*', 'instalasi.name as instalasi')
                ->join('instalasi', 'aduan_non_operasi.instalasi_id', '=', 'instalasi.id')
                ->whereNotIn('aduan_non_operasi.status', config('custom.skipStatus'));

            if (!in_array(trim($nip), $dalops)) {
                switch ($levelJab) {
                    case 'petugas':
                        $data = $data->where('trim(petugas_id)', trim($nip));
                        break;
                    case 'spv':
                        $data = $data->where('trim(nip_spv)', trim($nip))->whereNotIn('manajer' , ['me                            ', '                            ']);
                        break;
                    case 'manajer':
                        $data = $data->where('trim(manajer)', trim($nip));
                        break;
                }
            }

            // if ($levelJab == 'manajer') {
            //     if (!in_array(trim($nip), $dalops)) { //if not dalops manajer
            //         $data = $data->where('aduan_non_operasi.status', '1.1');
            //     } else {
            //         $data = $data->whereIn('aduan_non_operasi.status', ['1.2', '2']);
            //             // ->whereNull('aduan_non_operasi.approve_dalops');
            //     }
            // }
// dd($levelJab);
            if (!empty($title)) {
                $data = $data->where('judul', 'like', '%'.$title.'%');
            }

            if (!empty($id)) {
                $data = $data->where('id', $id);
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

                    if (!empty($filterStatus)) {
                        if ($status == $filterStatus) {
                            $result[] = self::transformer($row, $status, $action);    
                        }
                    } else {
                        $result[] = self::transformer($row, $status, $action);
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

    private static function transformer($row, $status = "", $action = "")
    {
        return [
            'id'                    => $row['id'],
            'judul'                 => $row['judul'],
            'instalasi'             => $row['instalasi'],
            'instalasi_id'          => $row['instalasi_id'],
            'lokasi'                => $row['lokasi'],
            'sifat'                 => $row['sifat'],
            'spv'                   => $row['spv'],
            'spv_tujuan'            => $row['jabatan']['namajabatan'],
            'bagian'                => bagianCaption($row['bagian']['bagian']),
            'foto_aduan'            => !empty($row['foto_aduan'])?url('pic-api/gambar/non-operasi&aduan&'.$row['id'].'&'.$row['foto_aduan']):"",
            'foto'                  => !empty($row['foto'])?url('pic-api/gambar/non-operasi&aduan&'.$row['id'].'&'.$row['foto']):"",
            'catatan'               => $row['catatan'],
            'indikasi'              => $row['indikasi'],
            'tanggal'               => $row['created_at'],
            'nip_spv'               => $row['nip_spv'],
            'manajer'               => $row['manajer'],
            'petugas_id'            => $row['petugas_id'],
            'petugas'               => $row['petugas']['nama'],
            'perkiraan'          => !empty($row['perkiraan'])?$row['perkiraan']:"",
            'perkiraan_revisi'   => !empty($row['perkiraan_revisi'])?$row['perkiraan_revisi']:"",
            'tgl_start'             => $row['tgl_start'],
            'tgl_finish'            => $row['tgl_finish'],
            'metode'                => $row['metode'],
            'kondisi'               => $row['kondisi'],
            'tgl_disposisi'         => $row['tgl_disposisi'],
            'penyebab'              => $row['penyebab'],
            'tgl_foto_investigasi'  => $row['tgl_foto_investigasi'],
            'foto_investigasi'      => !empty($row['foto_investigasi'])?url('pic-api/gambar/non-operasi&aduan&'.$row['id'].'&'.$row['foto_investigasi']):"",
            'foto_investigasi2'     => !empty($row['foto_investigasi2'])?url('pic-api/gambar/non-operasi&aduan&'.$row['id'].'&'.$row['foto_investigasi2']):"",
            'jenis_penanganan'      => $row['jenis_penanganan'],
            'tgl_input_metode'      => $row['tgl_input_metode'],
            'tgl_foto_analisa'      => $row['tgl_foto_analisa'],
            'uraian'                => $row['uraian'],
            'foto2'                 => !empty($row['foto2'])?url('pic-api/gambar/non-operasi&aduan&'.$row['id'].'&'.$row['foto2']):"",
            'approve_manajer'       => $row['approve_manajer'],
            'approve_dalops'        => $row['approve_dalops'],
            'catatan_m'             => $row['m_catatan'],
            'catatan_dalpro'        => $row['dalpro_catatan'],
            'catatan_p'             => $row['petugas_catatan'],
             // Add by Nafi 16-04-2021
            'perkiraan_anggaran'    => !empty($row['perkiraan_anggaran'])?$row['perkiraan_anggaran']:"0",
            'proposal'              => !empty($row['proposal'])?url('doc-api/dokumen/non-operasi&aduan&'.$row['id'].'&'.$row['proposal']):"",
            //

            'status_web'    => $row['status'],
            'status'        => $status,
            'action'        => $action
        ];
    }

    public function selectJab(Request $request)
    {
        try {
            $instalasi = $request->instalasi;
            $wo = $request->wo;

            $jabatan = ValidasiWo::selectJab($instalasi, $wo);

            return response()->json($jabatan)->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    // Tambah Aduan
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $nip = $request->header('nip');

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
// dd($nip_spv);
            if (!empty($request->id)) {
                $data = AduanNonOperasi::find($request->id);

                if (!empty($data->foto_aduan)) {
                    $tmp = date('Y-m', strtotime($data->created_at));
                    $fileExist = 'non-operasi/'.$tmp.'/'.$data->foto_aduan;
                    $exist = Storage::disk($this->disk)->has($fileExist);
                    if ($exist) {
                        Storage::disk($this->disk)->delete($fileExist);
                    }
                } 
            } else {
                $data = new AduanNonOperasi;
            }

            $data->judul = $request->judul;
            $data->instalasi_id = $request->instalasi_id;
            $data->lokasi = $request->lokasi;
            $data->sifat = $request->sifat;
            $data->spv = $request->spv;
            $data->catatan = $request->catatan;
            $data->indikasi = $request->indikasi;
            $data->nip_spv = $nip_spv;
            $data->manajer = str_pad($manajer, 30);
            $data->status = 0;
            $data->nip_pengadu = str_pad($nip, 30);

            $data->save();

            if ($request->hasFile('foto_aduan')) {
                $id = $data->id;
                $file = $request->file('foto_aduan');
                $extension = $file->getClientOriginalExtension();
                $filename = 'aduan_' . date('dmYHis') .'.'. $extension;

                $dir = 'non-operasi/aduan/'.$id;
                cekDir($dir);
                Storage::disk($this->disk)->put($dir .'/'. $filename, \File::get($file));

                $temp = AduanNonOperasi::find($id);
                $temp->foto_aduan = $filename;

                $temp->save();
            }

            DB::commit();

            // Notif
            $aduanNotif = AduanNonOperasi::where('id', $data->id)->first();
            $notif = kirimnotif(trim($nip_spv),
                [
                    'title' => 'Pemberitahuan Aduan Non-Operasi Baru',
                    'text' => sprintf('Pemberitahuan Aduan Non-Operasi Baru untuk %s', $aduanNotif->judul),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '244', 
                    'id' => $data->id
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

    public function investigasi(Request $request)
    {
        $nip = $request->header('nip');

        DB::beginTransaction();
        try {
            $data = [];
            $status = '1';

            if ($request->isKerusakan == 'no') {
                $status = '99';

                $data = [
                    'penyebab' => $request->penyebab,
                    'status' => $status
                ];
            }

            $filename = '';
            if ($request->hasFile('foto_investigasi')) {
                $file = $request->file('foto_investigasi');
                $extension = $file->getClientOriginalExtension();

                $dir = 'non-operasi/aduan/'.$request->id;
                cekDir($dir);

                $filename = trim($nip) . '_foto_investigasi.' . $extension;
                \Storage::disk($this->disk)->put($dir.'/'.$filename, \File::get($file));

                $data = [
                    'penyebab' => $request->penyebab,
                    'tgl_foto_investigasi' => DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')"),
                    'status' => $status,
                    'tgl_start' => DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')"),
                    'foto_investigasi' => $filename
                ];
            }

            $filename2 = '';
            if ($request->hasFile('foto_investigasi2')) {
                $file = $request->file('foto_investigasi2');
                $extension = $file->getClientOriginalExtension();
                
                $dir = 'non-operasi/aduan/'.$request->id;
                cekDir($dir);

                $filename2 = trim($nip) . '_foto_investigasi2.' . $extension;
                \Storage::disk($this->disk)->put($dir.'/'.$filename2, \File::get($file));

                $data['foto_investigasi2'] = $filename2;
            }

            // update Aduan Non Operasi
            $simpan = AduanNonOperasi::where('id', $request->id)->update($data);
            DB::commit();

            // Notif
            $aduanNotif = AduanNonOperasi::where('id', $request->id)->first();
            $notif = kirimnotif(trim($aduanNotif->nip_spv),
                [
                    'title' => 'Pemberitahuan Hasil Investigasi Aduan Non-Operasi',
                    'text' => sprintf('Pemberitahuan Hasil Investigasi Aduan Non-Operasi untuk %s', $aduanNotif->judul),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '241', 
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

    public function penanganan(Request $request)
    {
        $nip = $request->header('nip');

        DB::beginTransaction();
        try {
            $status = '2';
            $data = [];

            if ($request->revisi == 'yes') {
                $status = '3.2';

                $data = [
                    'uraian' => $request->uraian,
                    'status' => $status,
                    // 'tgl_foto_analisa' => DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')"),
                    'petugas_catatan' => $request->petugas_catatan
                ];

                $data['approve_dalops'] = null;
            }

            if ($request->revisi != 'yes') {
                $filename = '';
                if ($request->hasFile('foto')) {
                    $file = $request->file('foto');
                    $extension = $file->getClientOriginalExtension();
                    
                    $dir = 'non-operasi/aduan/'.$request->id;
                    cekDir($dir);

                    $filename = trim($nip) . '_foto_penanganan.' . $extension;
                    \Storage::disk($this->disk)->put($dir.'/'.$filename, \File::get($file));

                    $data = [
                        'uraian' => $request->uraian,
                        'status' => $status,
                        'tgl_foto_analisa' => DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')"),
                        //'petugas_catatan' => $request->petugas_catatan
                    ];
                    
                    $data['foto'] = $filename;
                }
                
                $filename2 = '';
                if ($request->hasFile('foto2')) {
                    $file = $request->file('foto2');
                    $extension = $file->getClientOriginalExtension();

                    $dir = 'non-operasi/aduan/'.$request->id;
                    cekDir($dir);

                    $filename2 = trim($nip) . '_foto_penanganan2.' . $extension;
                    \Storage::disk($this->disk)->put($dir.'/'.$filename2, \File::get($file));
                    
                    $data['foto2'] = $filename2;
                }
            }

            // update PRB_DATA
            $simpan = AduanNonOperasi::where('id', $request->id)->update($data);
            DB::commit();

            // Notif
            $aduanNotif = AduanNonOperasi::where('id', $request->id)->first();
            
            $title = 'Pemberitahuan Hasil Penanganan Aduan Non Operasi';
            $tipe = 243;
            if ($request->revisi == 'yes') {
                $title = 'Pemberitahuan Revisi Input Metode dari Penanganan';
                $tipe = 241;
            }

            $notif = kirimnotif(trim($aduanNotif->nip_spv),
                [
                    'title' => $title,
                    'text' => sprintf('Pemberitahuan Hasil Penanganan Aduan Non Operasi untuk %s', $aduanNotif->judul),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => $tipe, 
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
}
