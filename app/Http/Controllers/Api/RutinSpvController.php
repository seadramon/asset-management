<?php

namespace Asset\Http\Controllers\Api;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Aset,
    Asset\Models\Instalasi,
    Asset\Models\PrwRutin,
    Asset\Models\PrwRutinDetail;

use DB;
use Html;
use Validator;
use Redirect;
use Session;
use Image;

class RutinSpvController extends Controller
{
    public function disposisi(Request $request)
    {
        DB::beginTransaction();

        try {
            // dd($request->all());
            $nip = $request->header('nip');

            $data = PrwRutin::where('id', $request->id)
                ->update([
                    'petugas_id' => $request->petugas_id,
                    'tgl_disposisi' => DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')"),
            ]);

            DB::commit();

            // Notif
            $perawatan = PrwRutin::with('aset')->where('id', $request->id)->first();
            // dd($perawatan->aset->nama_aset);
            $notif = kirimnotif($request->petugas_id,
                [
                    'title' => 'Pemberitahuan WO Perawatan Rutin',
                    'text' => sprintf('Pemberitahuan WO Perawatan Rutin untuk %s', $perawatan->aset->nama_aset),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '161', 
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
}
