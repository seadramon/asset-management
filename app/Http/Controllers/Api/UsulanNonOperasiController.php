<?php

namespace Asset\Http\Controllers\Api;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\AduanNonOperasi,
    Asset\Models\Usulan,
    Asset\Models\Aset,
    Asset\Models\Instalasi,
    Asset\Models\Lokasi,
    Asset\Models\RoleUser,
    Asset\Models\Role,
    Asset\Role as tuRoleUser;

use Asset\Jabatan;

use DB;
use Datatables;
use Session;
use Validator;
use Storage;
use Image;

class UsulanNonOperasiController extends Controller
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

        $title = $request->get('nama');
        $id = $request->get('id');
        $filterStatus = $request->get('filterStatus');
        $start = $request->get('start'); //offset
        $limit = $request->get('limit'); 

        $dalops = [
            '10601344',
            config('custom.manajerDalops')
        ];

        try {
            $levelJab = cekJabatan($nip);
            $result = [];

            $data = Usulan::with(['petugas', 'jabatan', 'bagian', 'instalasi', 'sukucadang'])
                ->select('usulan_non_operasi.*', 'instalasi.name as instalasi')
                ->join('instalasi', 'usulan_non_operasi.instalasi_id', '=', 'instalasi.id')
                ->whereNotIn('usulan_non_operasi.status', ['10', '99']);

            if (!in_array(trim($nip), $dalops)) {
                switch ($levelJab) {
                    case 'petugas':
                        $data = $data->where('trim(petugas_id)', trim($nip));
                        break;
                    case 'spv':
                        $data = $data->where('trim(nip_spv)', trim($nip));
                        break;
                    case 'manajer':
                        $data = $data->where('trim(manajer)', trim($nip));
                        break;
                }
            }

            // if ($levelJab == 'manajer') {
            //     if (!in_array(trim($nip), $dalops)) { //if not dalops manajer
            //         $data = $data->where('usulan_non_operasi.status', '1.1');
            //     } else {
            //         $data = $data->whereIn('usulan_non_operasi.status', ['1.2', '2'])
            //             ->whereNull('usulan_non_operasi.approve_dalops');
            //     }
            // }

            if (!empty($title)) {
                $data = $data->where('nama', 'like', '%'.$title.'%');
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
            // dd($data);
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $action = aksiTindakanWeb($row);
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
        $perencanaan = null;
        if (!empty($row['perencanaan'])) {
            $arrTemp = explode(";", $row['perencanaan']);

            foreach ($arrTemp as $tmp) {                
                $perencanaan[] = url('pic-api/gambar/non-operasi&usulan&'.$row['id'].'&'.$tmp);
            }
        }

         return [
            'id'                    => $row['id'],
            'nama'                  => $row['nama'],
            'instalasi_id'          => $row['instalasi_id'],
            'instalasi'             => $row['instalasi']['name'],
            'lokasi'                => $row['lokasi'],
            'spv'                   => $row['spv'],
            'spv_tujuan'            => $row['jabatan']['namajabatan'],
            'sifat'                 => !empty($row['sifat'])?$row['sifat']:"",
            'bagian'                => bagianCaption($row['bagian']['bagian']),
            'foto_kondisi'          => !empty($row['foto_kondisi'])?url('pic-api/gambar/non-operasi&usulan&'.$row['id'].'&'.$row['foto_kondisi']):"",
            // 'foto'                  => !empty($row['foto'])?url('pic-api/gambar/non-operasi&usulan&'.$row['id'].'&'.$row['foto']):"",
            'tujuan'                => $row['tujuan'],
            'keterangan'            => $row['keterangan'],
            'tanggal'               => $row['created_at'],
            'nip_spv'               => $row['nip_spv'],
            'manajer'               => $row['manajer'],
            'petugas_id'            => $row['petugas_id'],
            'petugas'               => $row['petugas']['nama'],
            'tgl_disposisi'         => $row['tgl_disposisi'],
            'tgl_start'             => $row['tgl_start'],
            'tgl_finish'            => $row['tgl_finish'],
            'hasil_investigasi'     => $row['hasil_investigasi'],
            'tgl_foto_investigasi'  => $row['tgl_foto_investigasi'],
            'foto_investigasi'      => !empty($row['foto_investigasi'])?url('pic-api/gambar/non-operasi&usulan&'.$row['id'].'&'.$row['foto_investigasi']):"",
            'foto_investigasi2'     => !empty($row['foto_investigasi2'])?url('pic-api/gambar/non-operasi&usulan&'.$row['id'].'&'.$row['foto_investigasi2']):"",

            'jenis_penanganan'      => $row['jenis_penanganan'],
            'perkiraan'             => $row['perkiraan'],
            'perkiraan_revisi'      => !empty($row['perkiraan_revisi'])?$row['perkiraan_revisi']:"",
            'metode'                => $row['metode'],            
            'material'              => $row['material'],            
            'perencanaan'           => $perencanaan,            
            'tgl_input_metode'      => $row['tgl_input_metode'],

            'tgl_foto_analisa'      => $row['tgl_foto_analisa'],
            'uraian'                => $row['uraian'],
            'foto'                 => !empty($row['foto'])?url('pic-api/gambar/non-operasi&usulan&'.$row['id'].'&'.$row['foto']):"",
            'foto2'                 => !empty($row['foto2'])?url('pic-api/gambar/non-operasi&usulan&'.$row['id'].'&'.$row['foto2']):"",
            'approve_manajer'       => $row['approve_manajer'],
            'approve_dalops'        => $row['approve_dalops'],
            'catatan_m'             => $row['catatan_rev_manajer'],
            'catatan_dalpro'        => $row['dalpro_catatan'],
            'catatan_p'             => $row['petugas_catatan'],

            // Add by Nafi 16-04-2021
            'perkiraan_anggaran'    => !empty($row['perkiraan_anggaran'])?$row['perkiraan_anggaran']:"0",
            'proposal'              => !empty($row['proposal'])?url('doc-api/dokumen/non-operasi&usulan&'.$row['id'].'&'.$row['proposal']):"",
            //

            'status_web'    => $row['status'],
            'status'        => $status,
            'action'        => $action
        ];
    }

    // Tambah usulan
    public function store(Request $request)
    {
        // dd($request->all());
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

            $asetId = null;
            if ($request->lingkup_kerja == "Perbaikan/Overhoul/ Penggantian Aset Operasi") {
                $aset = Aset::barcode($request->kode_barcode)->first();

                if (!empty($aset)) {
                    $asetId = $aset->id;
                }
            }
// dd($asetId);
            if (!empty($request->id)) {
                $data = Usulan::find($request->id);

                if (!empty($data->foto_kondisi)) {
                    $fileExist = 'non-operasi/usulan/'.$request->id.'/'.$data->foto_kondisi;
                    $exist = Storage::disk($this->disk)->has($fileExist);
                    if ($exist) {
                        Storage::disk($this->disk)->delete($fileExist);
                    }
                } 
            } else {
                $data = new Usulan;
            }

            $data->nama = $request->nama;
            $data->instalasi_id = $request->instalasi_id;
            $data->lokasi = $request->lokasi;
            $data->spv = $request->spv;
            $data->tujuan = $request->tujuan;
            $data->keterangan = $request->keterangan;
            $data->nip_spv = $nip_spv;
            $data->manajer = str_pad($manajer, 30);
            $data->status = 0;
            $data->pic = str_pad($nip, 30);

            // additional
            $data->penggunaan_anggaran = $request->penggunaan_anggaran;
            $data->lingkup_kerja = $request->lingkup_kerja;
            $data->aset_id = $asetId;

            $data->save();

            if ($request->hasFile('foto_kondisi')) {
                $id = $data->id;
                $file = $request->file('foto_kondisi');
                $extension = $file->getClientOriginalExtension();
                $filename = 'kondisi_' . date('dmYHis') .'.'. $extension;

                $dir = 'non-operasi/usulan/'.$id;
                cekDir($dir);
                Storage::disk($this->disk)->put($dir .'/'. $filename, \File::get($file));

                $temp = Usulan::find($id);
                $temp->foto_kondisi = $filename;

                $temp->save();
            }

            DB::commit();

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

            $filename = '';
            if ($request->hasFile('foto_investigasi')) {
                $file = $request->file('foto_investigasi');
                $extension = $file->getClientOriginalExtension();

                $dir = 'non-operasi/usulan/'.$request->id;
                cekDir($dir);

                $filename = trim($nip) . '_foto_investigasi.' . $extension;
                \Storage::disk($this->disk)->put($dir.'/'.$filename, \File::get($file));

                $data = [
                    'hasil_investigasi' => $request->hasil_investigasi,
                    'tgl_foto_investigasi' => DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')"),
                    'status' => '1',
                    'tgl_start' => DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')"),
                    'foto_investigasi' => $filename
                ];
            }

            $filename2 = '';
            if ($request->hasFile('foto_investigasi2')) {
                $file = $request->file('foto_investigasi2');
                $extension = $file->getClientOriginalExtension();
                
                $dir = 'non-operasi/usulan/'.$request->id;
                cekDir($dir);

                $filename2 = trim($nip) . '_foto_investigasi2.' . $extension;
                \Storage::disk($this->disk)->put($dir.'/'.$filename2, \File::get($file));

                $data['foto_investigasi2'] = $filename2;
            }

            // update Aduan Non Operasi
            $simpan = Usulan::where('id', $request->id)->update($data);
            DB::commit();

            // Notif
            $usulanNotif = Usulan::where('id', $request->id)->first();
            $notif = kirimnotif(trim($usulanNotif->nip_spv),
                [
                    'title' => 'Pemberitahuan Hasil Investigasi Usulan Non-Operasi',
                    'text' => sprintf('Pemberitahuan Hasil Investigasi Usulan Non-Operasi untuk %s', $usulanNotif->nama),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '252', 
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
                    'tgl_foto_analisa' => DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')"),
                    'petugas_catatan' => $request->petugas_catatan
                ];

                $data['approve_dalops'] = null;
            }

            if ($request->revisi != 'yes') {
                $filename = '';
                if ($request->hasFile('foto')) {
                    $file = $request->file('foto');
                    $extension = $file->getClientOriginalExtension();
                    
                    $dir = 'non-operasi/usulan/'.$request->id;
                    cekDir($dir);

                    $filename = trim($nip) . '_foto_penanganan.' . $extension;
                    \Storage::disk($this->disk)->put($dir.'/'.$filename, \File::get($file));

                    $data = [
                        'uraian' => $request->uraian,
                        'status' => $status,
                        'tgl_foto_analisa' => DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')"),
                        //'petugas_catatan' => $request->petugas_catatan
                        'foto' => $filename
                    ];
                }
                
                $filename2 = '';
                if ($request->hasFile('foto2')) {
                    $file = $request->file('foto2');
                    $extension = $file->getClientOriginalExtension();

                    $dir = 'non-operasi/usulan/'.$request->id;
                    cekDir($dir);

                    $filename2 = trim($nip) . '_foto_penanganan2.' . $extension;
                    \Storage::disk($this->disk)->put($dir.'/'.$filename2, \File::get($file));
                    
                    $data['foto2'] = $filename2;
                }
            }

            // update Usulan
            $simpan = Usulan::where('id', $request->id)->update($data);
            DB::commit();

            // Notif
            $aduanNotif = Usulan::where('id', $request->id)->first();

            $title = 'Pemberitahuan Hasil Penanganan Usulan Non Operasi';
            $tipe = 253;
            if ($request->revisi == 'yes') {
                $title = 'Pemberitahuan Revisi Input Metode dari Penanganan';
                $tipe = 252;
            }

            $notif = kirimnotif(spv($nip),
                [
                    'title' => $title,
                    'text' => sprintf('Pemberitahuan Hasil Penanganan Usulan Non Operasi untuk %s', $aduanNotif->judul),
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
