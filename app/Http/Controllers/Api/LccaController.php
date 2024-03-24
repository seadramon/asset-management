<?php

namespace Asset\Http\Controllers\Api;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\BiayaOperasional;

use DB;

class LccaController extends Controller
{
    
    public function oprStore(Request $request)
    {
        DB::beginTransaction();

        try {
            if (!empty($request->id)) {
                $data = BiayaOperasional::find($request->id);
            } else {
                $data = new BiayaOperasional;
            }

            $data->tanggal = $request->tanggal;
            $data->pemakaian = $request->pemakaian;
            $data->harga = $request->harga;
            $data->angka_meter = $request->angka_meter;
            $data->biaya = $request->harga * $request->pemakaian;
            $data->aset_id = $request->aset_id;

            $data->save();
            DB::commit();

            return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function oprDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = BiayaOperasional::find($request->id);

            $data->delete();

            DB::commit();
            return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Dihapus'])->setStatusCode(200, 'OK');
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function show(Request $request)
    {
        try {
            $aset_id = $request->aset_id;

            $query = BiayaOperasional::select('biaya_operasional.angka_meter')
                ->where('aset_id', '=', $aset_id)
                ->orderBy('biaya_operasional.tanggal', 'DESC')
                ->first();

            return response()->json($query)->setStatusCode(200, 'OK');
        } catch (Exception $e) {
            return response()->json($query)->setStatusCode(500, 'Error');
        }
    }

    public function index(Request $request)
    {
        try {
            $aset_id = $request->aset_id;

            if (!empty($aset_id)) {
                $query = BiayaOperasional::select('biaya_operasional.*', 'aset.nama_aset', 'instalasi.id as instalasi_id', 'instalasi.name as instalasi', 'lokasi.id as lokasi_id', 'lokasi.name as lokasi')
                    ->join('aset', 'biaya_operasional.aset_id', '=', 'aset.id')
                    ->join('instalasi', 'aset.instalasi_id', '=', 'instalasi.id')
                    ->join('lokasi', 'aset.lokasi_id', '=', 'lokasi.id')
                    ->where('biaya_operasional.aset_id', '=', $aset_id)
                    ->orderBy('biaya_operasional.tanggal', 'DESC')
                    ->get()->toArray();
            } else {
                $query = BiayaOperasional::select('biaya_operasional.*', 'aset.nama_aset', 'instalasi.id as instalasi_id', 'instalasi.name as instalasi', 'lokasi.id as lokasi_id', 'lokasi.name as lokasi')
                    ->join('aset', 'biaya_operasional.aset_id', '=', 'aset.id')
                    ->join('instalasi', 'aset.instalasi_id', '=', 'instalasi.id')
                    ->join('lokasi', 'aset.lokasi_id', '=', 'lokasi.id')
                    ->orderBy('biaya_operasional.tanggal', 'DESC')
                    ->get()->toArray();
            }

            $return = ['data' => $query];

            return response()->json($return)->setStatusCode(200, 'OK');
        } catch (Exception $e) {
            return response()->json($return)->setStatusCode(500, 'Error');
        }
    }

}
