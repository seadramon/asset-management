<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Aset,
    Asset\Models\Instalasi,
    Asset\Models\Depresiasi;

use Asset\Libraries\General;

use DB;
use Datatables;
use Session;
use Validator;
use DateTime;
use Storage;

class DepresiasiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $instalasi = listInstalasi();
        $lokasi = [];
        $ruang = [];
        $aset = [];
        $arrData = [
            'periode' => null,
            'instalasi' => null,
            'lokasi' => null,
            'ruang' => null,
            'aset' => null,
        ];

        return view('pages.depresiasi.index', [
            'instalasi' => $instalasi,
            'lokasi' => $lokasi,
            'ruang' => $ruang,
            'aset' => $aset,
            'arrdata' => $arrData,
        ]);
    }

    public function data(Request $request)
    {
        $query = Depresiasi::with(['asetnya', 'bulan'])
            ->select('depresiasi.*')
            ->join('aset', 'depresiasi.aset_id', '=', 'aset.id');

        if ($request->instalasi_id) {
            $query->where('aset.instalasi_id', $request->instalasi_id);
        }

        if ($request->lokasi) {
            $query->where('aset.lokasi_id', $request->lokasi);
        }

        if ($request->ruang) {
            $query->where('aset.ruang_id', $request->ruang);
        }

        if ($request->aset_id) {
            $query->where('aset.id', $request->aset_id);
        }

        if ($request->tahun) {
            $query->where('depresiasi.tahun', $request->tahun);
        }

        return Datatables::of($query)
                ->editColumn('instalasi', function($model) {
                    $test = !empty($model->asetnya->instalasi)?$model->asetnya->instalasi->name:"";
                    return $test;
                })
                ->editColumn('lokasi', function($model) {
                    $test = !empty($model->asetnya->lokasi)?$model->asetnya->lokasi->name:"";
                    return $test;
                })
                ->editColumn('ruangan', function($model) {
                    $test = !empty($model->asetnya->ruangan)?$model->asetnya->ruangan->name:"";
                    return $test;
                })
                ->addColumn('menu', function ($model) {
                    $view = '<a href="' . route('depresiasi::view', ['id' => $model->id]) . '" class="btn btn-sm purple"><i class="fa fa-eye"></i></a>';
                    $edit = '<a href="' . route('depresiasi::entri', ['id' => $model->id]) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i></a>';
                    $delete = '<a href="' . route('depresiasi::delete', ['id' => $model->id]) . '" class="btn btn-sm purple" onclick="ConfirmDelete()"><i class="fa fa-trash"></i></a>';
                    
                    return $view.$edit.$delete;
                })
                ->make(true);
    }

    public function entri(Request $request, $id = null)
    {
        $data = null;
        $instalasi = listInstalasi();
        $lokasi = [];
        $ruang = [];
        $aset = [];
        $arrData = [
            'periode' => null,
            'instalasi' => null,
            'lokasi' => null,
            'ruang' => null,
            'aset' => null,
        ];

        if ($id) {
            $data = Depresiasi::find($id);

            $lokasi = listLokasi($data->asetnya->instalasi_id);
            $ruang = listRuang($data->asetnya->lokasi_id);
            $aset = listAset($data->asetnya->instalasi_id);

            $arrData = [
                'periode' => date('F-Y', strtotime($data->tahun.'-'.$data->bulan_id)),
                'instalasi' => $data->asetnya->instalasi_id,
                'lokasi' => $data->asetnya->lokasi_id,
                'ruang' => $data->asetnya->ruang_id,
            ];      
        }
        return view('pages.depresiasi.entri', [
            'data' => $data,
            'instalasi' => $instalasi,
            'lokasi' => $lokasi,
            'ruang' => $ruang,
            'aset' => $aset,
            'arrdata' => $arrData,
        ]);
    }

    public function simpan(Request $request)
    {
        $arrPeriod = date('Y-m', strtotime($request->periode));
        $arrPeriod = explode("-", $arrPeriod);
        $month = (int)$arrPeriod[1];
        $year = $arrPeriod[0];

        DB::beginTransaction();

        try {
            if ($request->id) {
                $data = Depresiasi::find($request->id);
            } else {
                $data = new Depresiasi();

                $data->creator_id = 1/*\Auth::user()->userid*/;
                $data->ts = getNow();
            }

            $data->aset_id = $request->aset_id;
            $data->bulan_id = $month;
            $data->tahun = $year;
            $data->depresiasi_bulanan = $request->depresiasi_bulanan;
            $data->depresiasi_tahunan = $request->depresiasi_tahunan;
            $data->akumulasi_depresiasi = $request->akumulasi_depresiasi;
            $data->nilai_aset = $request->nilai_aset;
            $data->updater_id = 1/*\Auth::user()->userid*/;

            $data->save();

            DB::commit();

            Session::flash('success', 'Data berhasil disimpan');
        }catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.');
        }

        return redirect()->route('depresiasi::index');
    }

    public function show($id)
    {
        $data = Depresiasi::find($id);

        return view('pages.depresiasi.show', [
            'data' => $data
        ]);
    }

    public function delete($id)
    {
        $data = Depresiasi::find($id);

        $data->delete();

        // redirect
        Session::flash('success', 'Data berhasil dihapus');
        return redirect()->route('depresiasi::index');
    }
}
