<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Aset,
    Asset\Models\JadwalLibur,
    Asset\Models\JadwalLiburPompa;

use DB;
use Datatables;
use Session;
use Validator;

class JadwalLiburController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = '';
        return view('pages.jadwalkerja.index', ['data' => $data]);
    }

    public function jadwalData()
    {
        DB::connection()->enableQueryLog();
        $query = JadwalLibur::select('jadwal_libur.*', 'aset.nama_aset', 'instalasi.name')
            ->join('aset', 'jadwal_libur.equipment_id', '=', 'aset.id')
            ->join('instalasi', 'aset.instalasi_id', '=', 'instalasi.id')
            ->whereIn('aset.instalasi_id', lokasi())
            ->get();
// dd(DB::getQueryLog());
        return Datatables::of($query)
                ->addColumn('menu', function ($model) {
                    // if (namaRole() != 'SPV PENGOLAHAN') {
                        $edit = '<a href="' . route('jadwalkerja::jadwalkerja-entri', ['id' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                        $delete = '<form style="float:left;" method="POST" action="' . route('jadwalkerja::jadwalkerja-delete', ['id' => $model->id]) . '" onsubmit="return ConfirmDelete()"><input type="hidden" name="_method" value="DELETE"><input class="btn btn-xs purple" type="submit" value="Hapus"></form>';
                    /*} else {
                        $edit = '<a href="#" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                    }*/

                    return $edit.$delete;
                })
                ->make(true);
    }

    public function entri($id = null)
    {
        // abort(503, 'Under maintenance');
        /*if (trim(\Auth::user()->userid) != "10901554") {
            abort(503, 'Under maintenance');
        }*/
        $week = [0 => "- Pilih Minggu-"];
        for ($i=1; $i <= 53; $i++) { 
            $week[$i] = "Minggu ke ".$i;
        }
// DB::connection()->enableQueryLog();

		$aset = '';
		$namaaset = '';

        if ($id == null) {
            $data = '';

            $aset = Aset::select('aset.*', DB::raw("aset.nama_aset || ' # ' || instalasi.name as fullname"))
                ->join('instalasi', 'aset.instalasi_id', '=', 'instalasi.id')
                ->whereIn('aset.instalasi_id', lokasi())
                ->where('aset.equipment', 'yes')
                ->whereRaw("aset.id not in (select equipment_id from jadwal_libur)")
                ->where('kondisi_id', '<>', '12')
                ->whereIn('instalasi_id', lokasi())
                ->get()
                ->pluck('fullname', 'id')
                ->toArray();        
            $labelEquipment = ["" => "-             Pilih Equipment             -"];
            $aset = $labelEquipment + $aset;

            $weekval = null;
        } else {
            $data = JadwalLibur::find($id);            
            $namaaset = Aset::where('id', $data->equipment_id)->first()->nama_aset;

            $weekval = explode(",", $data->minggu);
        }

        return view('pages.jadwalkerja.entri', [
            'data' => $data,
            'week' => $week,
            'aset' => $aset,
            'namaaset' => $namaaset,
            'weekval' => $weekval
        ]);
    }

    public function simpan(Request $request)
    {
        DB::beginTransaction();

        try {
            if ($request->tipe > 0) {
            	$weeks = "";
            	$libur = "";

                if (count($request->minggu) > 0) {
                    $weeks = implode(",", $request->minggu);
                }

                $data = JadwalLibur::find($request->id);
                $data->equipment_id = $request->equipment_id;
                $data->minggu = ','.$weeks.',';
                $data->save();

                /*$data = JadwalLibur::where('id', $request->id)
                    ->update([
                        'equipment_id' => $request->equipment_id,
                        'minggu' => ','.$weeks.','
                    ]);*/
            } else {
                $weeks = "";
                if (count($request->minggu) > 0) {
                    $weeks = implode(",", $request->minggu);
                }

                $data = new JadwalLibur;
                $data->equipment_id = $request->equipment_id;
                $data->minggu = ','.$weeks.',';
                $data->save();
            }

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('jadwalkerja::jadwalkerja-index');
    }

    public function delete($id)
    {
        // dd(phpinfo());
        $data = JadwalLibur::find($id);

        $data->delete();

        // redirect
        Session::flash('success', 'Data berhasil dihapus');
        return redirect()->route('jadwalkerja::jadwalkerja-index');
    }
}
