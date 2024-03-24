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
    Asset\Models\Proposal as ProModel,
    Asset\Role as tuRoleUser;

use Asset\Jabatan;
use Asset\Libraries\ValidasiWo;

use DB;
use Datatables;
use Session;
use Validator;
use Storage;

class AduanNonOperasiSpvController extends Controller
{
    function __construct(Request $request)
    {
        $nip = $request->header('nip');
        if ( !in_array(namaRole($nip), config('custom.mainRole')) ) {
            abort(404);
        }       
    }
    
    public function disposisi(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip');

            $data = AduanNonOperasi::where('id', $request->id)
                ->update([
                    'petugas_id' => str_pad($request->petugas_id, 30),
                    'tgl_disposisi' => DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')"),
            ]);

            DB::commit();

            // Notif
            $aduan = AduanNonOperasi::find($request->id);
            $notif = kirimnotif(trim($request->petugas_id),
                [
                    'title' => 'Pemberitahuan Aduan Non OperasI',
                    'text' => sprintf('Pemberitahuan Aduan Non OperasI untuk %s', $aduan->judul),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '141', 
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

    public function metode(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip');
            // dd($request->all());
            $data = AduanNonOperasi::find($request->id);
            $data->kondisi = $request->kondisi;
            $data->metode = $request->metode;
            $data->perkiraan = $request->perkiraan;
            $data->perkiraan_revisi = $request->perkiraan_revisi;
            $data->jenis_penanganan = $request->jenis_penanganan;
            $data->tingkat = $request->tingkat;
            if ($data->status == '1') {
                $data->tgl_input_metode = getNow();
            }
            $data->sifat = $request->sifat;

            // eksternal pp
            if ($request->metode == "eksternal pp") {
                $data->tahun_anggaran = $request->tahun_anggaran;
                $data->perkiraan_anggaran = $request->perkiraan_anggaran;

                if ($request->hasFile('proposal')) {
                    $file = $request->file('proposal');
                    $extension = $file->getClientOriginalExtension();

                    $dir = 'non-operasi/aduan/'.$request->id;
                    cekDir($dir);

                    $filename = trim($nip) . '_proposal.' . $extension;
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

            // cek masuk DED, revisi dr penanganan tdk masuk DED lg
            $data->is_ded = ValidasiWo::cekDedRevisiPenanganan($data->status, $request->metode, $request->sifat, $data->tgl_ded_selesai);
            
            // $data->status = '2';
            $data->status = '1.1';

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
            $aduanNotif = AduanNonOperasi::where('id', $request->id)->first();
            $notif = kirimnotif(trim($aduanNotif->manajer),
                [
                    'title' => 'Approval WO Aduan Non-Operasi',
                    'text' => sprintf('Approval WO Aduan Non-Operasi untuk %s', $aduanNotif->judul),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '341', 
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

    public function aduanClose(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = AduanNonOperasi::find($request->id);
            $data->status = '10';
            $data->tgl_finish = DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')");

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
}
