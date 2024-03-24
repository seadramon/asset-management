<?php

namespace Asset\Http\Controllers\Api;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\User;
use Asset\Models\Aset,
    Asset\Models\PmlKeluhan,
    Asset\Models\PmlKeluhanDev,
    Asset\Models\Master,
    Asset\Models\MasterFm,
    Asset\Models\MasterJab,
    Asset\Models\Perawatan,
    Asset\Models\Perbaikan,
    Asset\Models\Ms4w,
    Asset\Models\Prw4w,
    Asset\Models\JadwalLibur,
    Asset\Models\JadwalLiburPompa,
    Asset\Models\Role,
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
use Route;

class AduanSpvController extends Controller
{
    function __construct(Request $request)
    {
        $nip = $request->header('nip');
        if ( !in_array(namaRole($nip), config('custom.mainRole')) ) {
            abort(404);
        }       
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $start = $request->get('start'); //offset
        $limit = $request->get('limit');

        $nip = $request->header('nip');
        $result = [];

        // dd(lokasi($nip));

        try {
            $data = Perbaikan::with(['pelapor', 'sukucadang'])
                ->leftJoin('aset', 'prb_data.komponen_id', '=', 'aset.id')
                ->leftJoin('instalasi', 'aset.instalasi_id', '=', 'instalasi.id')
                ->leftJoin('lokasi', 'aset.lokasi_id', '=', 'lokasi.id')
                ->leftJoin('master x', 'aset.bagian', '=', 'x.id')
                ->select('prb_data.*', 'aset.id as aset_id', 'aset.nama_aset', 'aset.pemeliharaan_start', 'aset.pemeliharaan_end', 'aset.penyedia', 'aset.ppk', 'lokasi.name as lokasinm', 'instalasi.name as instalasi', 'x.name as bagian', 'aset.instalasi_id', 'aset.bagian as bagian_id', 'aset.lokasi_id')
                ->whereIn('aset.instalasi_id', lokasi($nip))
                ->whereIn('aset.bagian', bagian($nip))
                ->where('prb_data.tipe', 'aduan')
                ->whereNotIn('prb_data.status', config('custom.skipStatus'))
                ->orderBy('prb_data.id', 'desc');

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }
// dd($data->get());
            $data = $data->get()->toArray();
            $arrTmp = [];
            // dd($data);
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $action = aksiTindakan($row);
                    $status = statusTindakanManajer($row);
                    $period = date("Y-m", strtotime($row['tanggal']));

                    $isMasaPemeliharaan = "no";
                    $masaPemeliharaan = "";
                    
                    if (!empty($row['pemeliharaan_start']) && !empty($row['pemeliharaan_end'])) {
                        $masaPemeliharaan = $row['pemeliharaan_start'].' s/d '.$row['pemeliharaan_end'];

                        $isMasaPemeliharaan = cekMasaPemeliharaan($row['pemeliharaan_start'], $row['pemeliharaan_end']);
                    }

                    $result[] = [
                        'recidkeluhan'       => $row['id'],
                        'judul'              => $row['aduan_judul'],
                        'aset_id'            => $row['aset_id'],
                        'aset'               => $row['nama_aset'],
                        'penyedia'           => $row['penyedia'],
                        'ppk'                => $row['ppk'],
                        'lokasi'             => $row['lokasinm'],
                        'lokasi_id'          => $row['lokasi_id'],
                        'instalasi'          => $row['instalasi'],
                        'instalasi_id'       => $row['instalasi_id'],
                        'bagian'             => $row['bagian'],
                        'bagian_id'          => $row['bagian_id'],
                        'tanggal'            => $row['tanggal'],
                        'path_kondisi'       => !empty($row['aduan_kondisi'])?url('pic-api/gambar/aduan&'.$period.'&'.$row['aduan_kondisi']):"",

                        // prb
                        'id'                 => $row['id'],
                        'petugas'            => $row['petugas_id'],
                        // Add by Nafi (18/03/2021)
                        'petugas_nama'       => namaPegawai($row['petugas_id']),
                        //
                        'sifat'              => $row['sifat'],
                        // 'tanggal'            => $row['tanggal'],
                        'perkiraan'          => $row['perkiraan'],
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
                        'proposal'     			=> !empty($row['proposal'])?url('doc-api/dokumen/perbaikan&proposal&'.$row['proposal']):"",
                        //

                        'action'             => $action,
                        'status'             => $status,
                    ];
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

    public function detail($recidkeluhan, Request $request)
    {
        try {
            $nip = $request->header('nip');
            $result = null;
            
            if (empty($recidkeluhan)) {
                return response()->json(['error' => 'recidkeluhan Perbaikan kosong'])->setStatusCode(500, 'Error');
            }

            $query = Perbaikan::select('prb_data.*','aset.id as aset_id', 'aset.nama_aset', 'lokasi.name as lokasinm', 'aset.instalasi_id', 'aset.bagian as bagian_id', 'instalasi.name as instalasi', 'x.name as bagian')
                ->leftJoin('aset', 'prb_data.komponen_id', '=', 'aset.id')
                ->leftJoin('instalasi', 'aset.instalasi_id', '=', 'instalasi.id')
                ->leftJoin('lokasi', 'aset.lokasi_id', '=', 'lokasi.id')
                ->leftJoin('master x', 'aset.bagian', '=', 'x.id')
                ->whereIn('prb_data.instalasi_id', lokasi($nip))
                ->whereIn('prb_data.bagian_id', bagian($nip))
                ->whereNotIn('prb_data.status', ['99', '10'])
                ->where('prb_data.id', $recidkeluhan)
                ->get();

// dd(bagian($nip));
            if (!empty($query)) {
                $row = (count($query) > 0)?$query[0]->toArray():null;
                $urlImgPml = 'https://pemeliharaan.pdam-sby.go.id/uploads/'; 
                $period = date("Y-m", strtotime($row['tanggal']));
                
                if ($row) {
                    $result = [
                        'recidkeluhan'       => $row['id'],
                        'aduan_id'           => $row['id'],
                        'id'                 => $row['id'],
                        'aset'               => $row['nama_aset'],
                        'lokasi'             => $row['lokasinm'],
                        'instalasi'          => $row['instalasi'],
                        'komponen_id'        => $row['aset_id'],
                        'instalasi_id'       => $row['instalasi_id'],
                        'bagian_id'          => $row['bagian_id'],
                        'bagian'             => $row['bagian'],
                        'tanggal'            => $row['tanggal'],
                        'judul'              => $row['aduan_judul'],
                        'catatankerusakan'   => $row['aduan_catatan'],
                        'ind_kerusakan'      => $row['aduan_indikasi'],
                        'sifat'              => $row['sifat'],
                        'path_kondisi'       => !empty($row['aduan_kondisi'])?url('pic-api/gambar/aduan&'.$period.'&'.$row['aduan_kondisi']):"",
                        // 'path_lokasi'        => !empty($row['path_lokasi'])?url('pic-api/gambar/perbaikan&'.$row['path_lokasi']):"",

                        'petugas'            => $row['petugas_id'],
                        'foto_investigasi'   => !empty($row['foto_investigasi'])?url('pic-api/gambar/perbaikan&'.$row['foto_investigasi']):"",
                        'foto'               => !empty($row['foto'])?url('pic-api/gambar/perbaikan&'.$row['foto']):"",
                        'foto_investigasi2'   => !empty($row['foto_investigasi2'])?url('pic-api/gambar/perbaikan&'.$row['foto_investigasi2']):"",
                        'foto2'               => !empty($row['foto2'])?url('pic-api/gambar/perbaikan&'.$row['foto2']):""
                    ];
                }
            }

            return response()->json($result)->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function disposisi(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip');
            $id = $request->id;
// dd(manajer(trim($nip)));
            // dev
            $data = [
                'tgl_disposisi' => getNow(),
                'manajer' => manajer(trim($nip)),
                'petugas_id' => $request->petugas_id,
                'last_action' => 'Disposisi',
                'updated_at' => getNow(),
                // 'spv' => $nip,
                'spv' => getRecidJabatan($nip),
                'nip_spv' => $nip
            ];
            // end:dev

            $update = Perbaikan::where('id', $id)->update($data);

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

    public function petugas(Request $request)
    {
        try {
            $nip = $request->header('nip').'                      ';
            $lokasi = $request->get('lokasi');
            $nipLokasi = null;

            $recidjabatan = User::find($nip)->role->jabatan->recidjabatan;
            $petugas = ["" => "-             Pilih Petugas             -"];


            if (in_array(namaRole($nip), config('custom.rolePengolahan'))) {
                
                $arrNip = [];
                $tmpData = Role::with('roleuser')
                    ->where('name', 'ADMIN PENGOLAHAN')
                    ->get();

                if (!empty($tmpData[0])) {
                    if (!empty($tmpData[0]->roleuser)) {
                        foreach ($tmpData[0]->roleuser as $row) {
                            $arrNip[] = $row->user_id;
                        }
                    }
                }

                if (!empty($lokasi)) {
                    $nipLokasi = MasterJab::where('lokasi', 'like', '%'.$lokasi.'%')
                        ->whereIn('nip', $arrNip)
                        ->get()->pluck('nip')->toArray();
                }

                $users = tuRoleUser::select('nip', 'nama')
                    ->whereIn('nip', $nipLokasi)
                    ->get();
            } else {
                $users = tuRoleUser::select('nip', 'nama')
                    ->whereNull('is_manajer')
                    ->where('recidrole', $recidjabatan)
                    ->get();

                if ( !empty($lokasi) ) {
                    $nipLokasi = MasterJab::where('lokasi', 'like', '%'.$lokasi.'%')
                    ->get()->pluck('nip')->toArray();
                    // dd($nipLokasi);
                }
            }

            foreach ($users as $row) {
                if ( !empty($nipLokasi) ) {
                    if (in_array($row->nip, $nipLokasi)) {
                        $petugas[trim($row->nip)] = trim($row->nama);
                    }
                } else {
                    $petugas[trim($row->nip)] = trim($row->nama);
                }
            }

            return response()->json($petugas)->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function metode(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip'); 

            $data = Perbaikan::find($request->id);
            $komponenId = $data->komponen_id;
            $data->kondisi = $request->kondisi;
            $data->metode = $request->metode;
            $data->sifat = $request->sifat;

            if ($data->status == '1') {
                $data->perkiraan = $request->perkiraan;
                $data->tgl_input_metode = DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')");
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
            kirimnotif(trim($data->manajer),
                [
                    'title' => 'Approval WO Perbaikan Aduan',
                    'text' => sprintf('Approval WO Perbaikan untuk %s', $data->komponen->nama_aset),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '311', 
                    'id' => $request->id
                ] 
                // ['tipe' => '2', 'id' => $request->id]
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

    public function close(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip'); 

            // Prb Data
            $dataPrb = [
                'tgl_finish' => getNow(),
                'status' => '10',
                'last_action' => 'Closing',
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
