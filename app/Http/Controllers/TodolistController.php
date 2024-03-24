<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Aset,
    Asset\Models\Instalasi,
    Asset\Models\Ms52w,
    Asset\Models\Ms4w,
    Asset\Models\Master,
    Asset\Models\MasterFm,
    Asset\Models\Perawatan,
    Asset\Models\Perbaikan,
    Asset\Models\MasterJab;

use DB;
use Datatables;
use Session;
use Validator;

class TodolistController extends Controller
{
    
    public function index()
    {
    	// dd(namaRole());
        $data = '';
        $week = [
            "" => "- Pilih Minggu-"
        ];

        for ($i = 1; $i <= lastWeekNumber(); $i++) { 
            $week[$i] = "Minggu ke ".$i;
        }

        return view('pages.todolist.index', [
            'data' => $data,
            'minggu' => $week
        ]);
    }

    public function todolistData(Request $request)
    {
        // dd('aaaaaaaaa');
        DB::connection()->enableQueryLog();
        
        $minggu = !empty($request->minggu)?$request->minggu:"";
        $tahun = !empty($request->year)?$request->year:date('Y');
// dd($tahun);
        $query = Ms4w::select('ms_4w.*', 'ms_52w.instalasi_id', 'aset.kode_aset', 'aset.kode_fm', 'aset.nama_aset', 'instalasi.name as lokasi', DB::raw("TO_CHAR(ms_4w.TANGGAL_MONITORING, 'YYYY') as tahun"))
            ->join('ms_52w', 'ms_4w.ms_52w_id', '=', 'ms_52w.id')
            ->join('aset', 'ms_52w.komponen_id', '=', 'aset.id')
            ->join('instalasi', 'ms_52w.instalasi_id', '=', 'instalasi.id')
            // ->join(DB::connection('oraclesecman')->table('usrtab'), 'ms_4w.petugas', '=', 'usrtab.userid')
            ->whereIn('ms_52w.instalasi_id', lokasi())
            ->whereIn('aset.bagian', bagian())
            // ->where('ms_4w.petugas', trim(\Auth::user()->userid))
            ->whereRaw("TO_CHAR(ms_4w.TANGGAL_MONITORING, 'YYYY') = $tahun")
            ->where('aset.kondisi_id', '<>', '12')
            ->where('ms_4w.status', '<>', '99');

        if ($minggu == "") {
            // dd('test');
            // $query = $query->where('ms_4w.urutan_minggu', date('W'));
        } else {
            $query = $query->where('ms_4w.urutan_minggu', $minggu);
        }

        if (namaRole() == 'PETUGAS MONITORING') {
            $query = $query->where('ms_4w.petugas', trim(\Auth::user()->userid));
        }

        return Datatables::of($query)
                ->addColumn('menu', function ($model) {
                    $delete = "";
                    $edit = '<a href="' . route('monitoring::monitoring-entri', ['id' => $model->kode_fm, 'id4w' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Monitoring </a>';

                    if (namaRole() == "Super Administrator") {
                    	$delete = '<form style="float:right;" method="POST" action="'.route('todolist::todolist-delete', ['id' => $model->id]).'" onsubmit="return ConfirmDelete()">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-xs red"><i class="fa fa-trash"></i>Delete</button>
                            </form>';
                    }
                    return $edit.'<br>'.$delete;
                })
                ->make(true);
    }

    public function delete(Request $request)
    {
    	DB::beginTransaction();
        try {
            $data = Ms4w::find($request->id);

            $data->delete();

            DB::commit();
            Session::flash('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal dihapus');
        }

        return redirect()->route('todolist::todolist-index');
    }
}
