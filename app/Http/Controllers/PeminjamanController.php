<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Peminjaman,
    Asset\Models\Aset,
    Asset\Models\Instalasi,
    Asset\Models\Ruangan,
    Asset\Models\Lokasi;

use DB;
use Datatables;
use Session;
use Validator;

class PeminjamanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = '';
        return view('pages.peminjaman.index', [
            'data' => $data,
            'TAG'=>'aset',
            'TAG2'=>'peminjaman'
        ]);
    }

    public function peminjamanData()
    {
        // $query = Peminjaman::with('aset');
        $query = Peminjaman::with('aset')->select('peminjaman_aset.*', 
            DB::raw("to_char(peminjaman_aset.tgl_pengajuan, 'dd Mon yyyy') as pengajuan,
                to_char(peminjaman_aset.tgl_rencana_dipinjam, 'dd Mon yyyy') as rencana_dipinjam,
                to_char(peminjaman_aset.tgl_dipinjam, 'dd Mon yyyy') as dipinjam,
                to_char(peminjaman_aset.tgl_renc_kembali, 'dd Mon yyyy') as renc_kembali,
                to_char(peminjaman_aset.tgl_dikembalikan, 'dd Mon yyyy') as dikembalikan"));

        return Datatables::of($query)
                ->addColumn('menu', function ($model) {
                    // dd($model);
                    $edit = '<a href="' . route('peminjaman::peminjaman-entri', ['id' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                    return $edit;
                })
                ->make(true);
    }

    public function peminjamanEntri($id = "")
    {
        $title = "Tambah Peminjaman Aset";
        $data = Peminjaman::find($id);

        $aset = Aset::get()->pluck('nama_aset', 'id')->toArray();
        $labelAset = ["" => "- Pilih Aset -"];
        $aset = $labelAset + $aset;

        if ($id != "") {
            $title = "Edit Peminjaman Aset";
        }

        return view('pages.peminjaman.entri', [
            'title' => $title,
            'data' => $data,
            'aset' => $aset
        ]);
    }

    public function asetSelect($id)
    {
        $data = Aset::with(array('instalasi' => function($query){
                        $query->select('id', 'name');
                    }))
                    ->with(array('lokasi' => function($query){
                        $query->select('id', 'name');
                    }))
                    ->with(array('ruangan' => function($query){
                        $query->select('id', 'name');
                    }))
                    ->find($id)->toArray();

        return response()->json(['data' =>$data]);
    }

    public function simpan(Request $request)
    {
    // dd($request->all());
        try {
            $validator = Validator::make($request->all(), [
                'aset_id' => 'required'
            ]);

            if ($validator->fails()) {
                return redirect('peminjaman/entri')
                            ->withErrors($validator)
                            ->withInput();
            }

            DB::beginTransaction();

            if ($request->id) {
                $tmp = Peminjaman::find($request->id);
                $tmp->updater_id = '1';
                $tmp->ts_update = date('Y-m-d H:i:s');
                $tmp->operator_pengajuan_id = $request->operator_pengajuan_id;
            } else {
                $tmp = new Peminjaman();
                $tmp->tgl_pengajuan = date('Y-m-d H:i:s');
                $tmp->creator_id = '1';
                $tmp->ts_create = date('Y-m-d H:i:s');
            }

            $tmp->aset_id = $request->aset_id;
            $tmp->peminjam = $request->peminjam;
            $tmp->tgl_rencana_dipinjam = $request->tgl_rencana_dipinjam;
            $tmp->tgl_dipinjam = $request->tgl_dipinjam;
            $tmp->tgl_renc_kembali = $request->tgl_renc_kembali;
            $tmp->tgl_renc_kembali_extend = $request->tgl_renc_kembali_extend;
            $tmp->catatan_pinjam = $request->catatan_pinjam;
            $tmp->tgl_dikembalikan = $request->tgl_dikembalikan;
            $tmp->catatan_kembali = $request->catatan_kembali;
            $tmp->operator_pinjam_id = $request->operator_pinjam_id;
            $tmp->operator_kembali_id = $request->operator_kembali_id;


            $tmp->save();

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch (Exception $e) {
            DB::rollback();
            Session::flash('success', 'Data berhasil diupdate');
        }

        return redirect()->route('peminjaman::peminjaman-index');
    }
}
