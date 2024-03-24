<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Asset\Models\Ms4w,
    Asset\Models\Master,
    Asset\Models\MasterFm;

use DB;
use Datatables;
use Session;
use Validator;

class TempController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = MasterFm::select(DB::raw('count(recid) as jmlid, kode_fm'))
            ->groupBy('kode_fm')
            ->get()->pluck('kode_fm', 'kode_fm')->toArray();

        $form = [];
        foreach ($data as $row) {
            if (!Schema::connection('oracleaplikasi')->hasTable('FM_'.$row)) {
                $form[$row] = $row;
            }
        }
        
        $labelAset = ["" => "-             Pilih Form             -"];
        $form = $labelAset + $form;

        return view('pages.tempcreatefm', [
            'forms' => $form
        ]);
    }

    public function formData()
    {
        $query = MasterFm::where('aktif', '<>', 'N')->orderBy('recid');

        return Datatables::of($query)->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function simpan(Request $request)
    {
        $fm = $request->selform;
        $data = MasterFm::where('kode_fm', $fm)
            ->orderBy('recid', 'ASC')
            ->get();

        try {
            if (!Schema::connection('oracleaplikasi')->hasTable('FM_'.$fm)) {
                Schema::connection('oracleaplikasi')->create('FM_'.$fm, function($table) use($data)
                {
                    $table->increments('id');
                    $table->integer('ms_4w_id');

                    foreach ($data as $row) {
                        $table->string($row->nama_field)->nullable();
                    }

                    $table->text('waspada')->nullable();
                    $table->text('bahaya')->nullable();
                    $table->date('tanggal')->useCurrent();
                });
                Session::flash('success', 'Tabel berhasil dibuat');
            } else {
                Session::flash('error', 'Tabel sudah ada.');    
            }
        } catch(Exception $e) {
            Session::flash('error', 'Tabel gagal dibuat. '.$e->getMessage());
        }

        return redirect()->route('temp::temp-index');
    }

// ALTER ADD COLUMN TO EXISTING TABLE
    public function manual($id)
    {
        $fm = $id;
        $namatable = 'FM_'.$id;
        $data = MasterFm::where('kode_fm', $fm)
            ->orderBy('recid', 'ASC')
            ->get();

        try {
            Schema::table($namatable, function (Blueprint $table) use($data, $namatable){
                foreach ($data as $row) {
                    if (!Schema::hasColumn($namatable, $row->nama_field)) {
                        // dd($row->nama_field);
                        $table->string($row->nama_field)->nullable();
                    }
                }
            });
            Session::flash('success', 'Column Berhasil ditambahkan');
        } catch(Exception $e) {
            Session::flash('error', 'Column gagal ditambahkan. '.$e->getMessage());
        }

        return redirect()->route('temp::temp-index');
    }
}
