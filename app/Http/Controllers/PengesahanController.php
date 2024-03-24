<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Datatables;
use Asset\Models\Aset,
    Asset\Models\SpekGroup,
    Asset\Models\Kondisi,
    Asset\Models\Instalasi,
    Asset\Models\Lokasi,
    Asset\Models\Ruangan,
    Asset\Models\Kategori,
    Asset\Models\SubKategori,
    Asset\Models\SubSubKategori,
    Asset\Models\Master
;
use DB;

class PengesahanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.pengesahan.index');
    }

    public function data()
    {
        $query = Aset::with('kategori', 'subkategori', 'subsubkategori', 'instalasi', 'lokasi', 'ruangan', 'type', 'kondisi')
            ->where('verified', '1')
            ->where(function($query) {
                $query->whereNull('is_active')
                    ->orWhere('is_active', '<>', '1');
            });

        return Datatables::of($query)->addColumn('subsubkategori_name', function($m) {
            return $m->subsubkategori ? $m->subsubkategori->name : '-';
        })
        ->addColumn('ruangan_name', function($m) {
            return $m->ruangan ? $m->ruangan->name : '-';
        })
        ->addColumn('select_orders', static function ($row) {
            return '<input type="checkbox" name="pengesahan" value="'.$row->id.'"/>';
        })
        ->make(true);
    }

    public function simpan(Request $request)
    {
        try {
            if ( count($request->pengesahan) > 0 ) {
                // dd($request->all());
                $data = Aset::whereIn('id', $request->pengesahan)
                    ->update([
                        'is_active' => '1',
                        'ts_active' => getNow(),
                        'activator_id' => '1',
                        'ts_update' => getNow(),
                        'updater_id'=> 1
                ]);

                return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
            } else {
                return response()->json([
                'result' => 'error',
                'message' => 'Tidak ada data yang dipilih'])->setStatusCode(500, 'Error');
            }

        } catch(Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }
}
