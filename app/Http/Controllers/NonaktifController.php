<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Aset,
    Asset\Models\Instalasi,
    Asset\Models\Kondisi;

use DB;
use Datatables;
use Session;
use Validator;

class NonaktifController extends Controller
{
    public function index()
    {
        $data = '';
        return view('pages.nonaktif.index', [
            'data' => $data,
            'TAG' => 'aset',
            'TAG2' => 'nonaktif'
        ]);
    }

    public function nonaktifData()
    {
        $query = Aset::with('kondisi')
                        ->with('instalasi')
                ->where('kondisi_id', '12');
// dd($query->get());
        return Datatables::of($query)
                ->addColumn('menu', function ($model) {
                    $edit = '<a href="' . route('nonaktif::nonaktif-entri', ['id' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                    return $edit;
                })
                ->make(true);
    }

    public function entri($id = null)
    {
    	$data = null;
        $aset = null;

        if ($id == null) {
            $aset = Aset::where('kondisi_id', '<>', '12')->get()->pluck('nama_aset', 'id')->toArray();
            $labelAset = ["" => "- Pilih Aset -"];
            $aset = $labelAset + $aset;
        } else {
            $data = Aset::find($id);
        }

    	$kondisi = Kondisi::get()->pluck('name', 'id')->toArray();
        $labelAset = ["" => "- Pilih Kondisi -"];
        $kondisi = $labelAset + $kondisi;

    	return view('pages.nonaktif.entri', [
    		'data' => $data,
    		'kondisi' => $kondisi,
            'aset' => $aset
    	]);
    }

    public function simpan(Request $request)
    {
        DB::beginTransaction();

        try {
            if ($request->tipe == 0) {
                $data = Aset::where('id', $request->aset_id)
                    ->update(['kondisi_id' => '12']);
            } else {
                $data = Aset::where('id', $request->aset_id)
                    ->update(['kondisi_id' => '12']);
            }

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('nonaktif::nonaktif-index');
    }
}
