<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Pemindahan,
    Asset\Models\Aset,
    Asset\Models\Instalasi,
    Asset\Models\Ruangan,
    Asset\Models\Ms52w,
    Asset\Models\Prw52w,
    Asset\Models\Perawatan,
    Asset\Models\Perbaikan,
    Asset\Models\Lokasi;

use DB;
use Datatables;
use Session;
use Validator;

class PemindahanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = '';
        return view('pages.pemindahan.index', [
            'data' => $data,
            'TAG'=>'aset',
            'TAG2'=>'pemindahan'
        ]);
    }

    public function pemindahanData()
    {
        $query = Pemindahan::with('aset')
                        ->with('instalasi_baru')
                        ->with('ruangan_baru')
                        ->with('lokasi_baru')
                        ->with('instalasi_lama')
                        ->with('ruangan_lama')
                        ->with('lokasi_lama')
                // ->select('pemindahan_aset.*', DB::raw("to_char(pemindahan_aset.tgl_pindah, 'dd Mon yyyy') as pindah"))
                ->select('pemindahan_aset.*')
                ->orderBy('id', 'desc');
// dd($query->get());
        return Datatables::of($query)
                ->addColumn('menu', function ($model) {
                    $edit = '<a href="' . route('pemindahan::pemindahan-entri', ['id' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                    return $edit;
                })
                ->make(true);
    }

    public function pemindahanEntri($id = "")
    {
        $title = "Tambah Pemindahan Aset";
        $data = Pemindahan::find($id);

        $aset = Aset::get()->pluck('nama_aset', 'id')->toArray();
        $labelAset = ["" => "- Pilih Aset -"];
        $aset = $labelAset + $aset;

        $instalasi = Instalasi::get()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "- Pilih Instalasi Baru -"];
        $instalasi = $labelInstalasi + $instalasi;

        $lokasi = [];
        $ruang = [];
// dd($data);
        if ($id != "") {
            $title = "Edit Pemindahan Aset";

            $lokasi = Lokasi::get()->pluck('name', 'id')->toArray();
            $labelLokasi = ["" => "- Pilih Lokasi Baru -"];
            $lokasi = $labelLokasi + $lokasi;

            $ruang = Ruangan::get()->pluck('name', 'id')->toArray();
            $labelRuang = ["" => "- Pilih Ruang Baru -"];
            $ruang = $labelRuang + $ruang;
        }

        return view('pages.pemindahan.entri', [
            'title' => $title,
            'data' => $data,
            'aset' => $aset,
            'instalasi' => $instalasi,
            'lokasi' => $lokasi,
            'ruang' => $ruang
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
        try {
            $validator = Validator::make($request->all(), [
                'aset_id' => 'required'
            ]);

            if ($validator->fails()) {
                return redirect('pemindahan/entri')
                            ->withErrors($validator)
                            ->withInput();
            }

            DB::beginTransaction();
            // DB::connection()->enableQueryLog();
// dd($request->all());
            if ($request->id) {
                $tmp = Pemindahan::find($request->id);
                $tmp->updater_id = '1';
                $tmp->ts_update = date('Y-m-d H:i:s');
            } else {
                $tmp = new Pemindahan();
                $tmp->tgl_pengajuan = date('Y-m-d H:i:s');
                $tmp->creator_id = '1';
                $tmp->ts_create = date('Y-m-d H:i:s');
            }
            $tmp->aset_id = $request->aset_id;
            $tmp->instalasi_lama_id = $request->instalasi_lama_id;
            $tmp->lokasi_lama_id = $request->lokasi_lama_id;
            $tmp->ruang_lama_id = $request->ruang_lama_id;
            $tmp->instalasi_baru_id = $request->instalasi_baru_id;
            $tmp->lokasi_baru_id = $request->lokasi_baru_id;
            $tmp->ruang_baru_id = $request->ruang_baru_id;
            $tmp->operator_pengajuan_id = $request->operator_pengajuan_id;
            $tmp->tgl_pindah = $request->tgl_pindah;
            // dd($tmp->tgl_pindah);
            $tmp->save();
            // dd(DB::getQueryLog());

            // Update aset
            $tmpAset = Aset::find($tmp->aset_id);

            $kodeAsetBaru = $this->formatKodeaset(
                $tmp->aset_id,
                $tmpAset->kode_aset,
                $tmp->instalasi_baru_id,    
                $tmp->lokasi_baru_id,
                $tmp->ruang_baru_id
            );
// dd($tmp->instalasi_baru_id);
            $tmpAset->kode_aset = $kodeAsetBaru;
            $tmpAset->instalasi_id = $tmp->instalasi_baru_id;
            $tmpAset->lokasi_id = $tmp->lokasi_baru_id;
            $tmpAset->ruang_id = $tmp->ruang_baru_id;
            $tmpAset->save();

            // Update 52w
            $tmp52w = Ms52w::where('komponen_id', $tmp->aset_id)
                ->update(['instalasi_id' => $tmp->instalasi_baru_id]);

            // Update Prw 52w
            $tmpPrw52w = Prw52w::where('komponen_id', $tmp->aset_id)
                ->update(['instalasi_id' => $tmp->instalasi_baru_id]);

            // update perawatan
            Perawatan::where('komponen_id', $tmp->aset_id)
                ->update([
                    'instalasi_id' => $tmp->instalasi_baru_id,
                    'last_action' => 'Pemindahan Aset',
                    'updated_at' => getNow(),
                ]);

            // update perbaikan
            Perbaikan::where('komponen_id', $tmp->aset_id)
                ->update([
                    'instalasi_id' => $tmp->instalasi_baru_id,
                    'last_action' => 'Pemindahan Aset',
                    'updated_at' => getNow(),
                ]);

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch (Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('pemindahan::pemindahan-index');
    }

    private function formatKodeaset($aset_id, 
                                    $kodeLama,
                                    $instalasiBaru,
                                    $lokasiBaru = "",
                                    $ruangBaru = "")
    {
        $kodeInstalasi = sprintf('%02d', $instalasiBaru);
        $kodeLokasi = "00";
        $kodeRuang = "00";
        $kodeBaru = "";

        if ($lokasiBaru!="") {
            $lokasiTmp = Lokasi::find($lokasiBaru);
            if (!is_null($lokasiTmp->kode)) {
                $kodeLokasi = sprintf('%02d', $lokasiTmp->kode);
            } else {
                $kodeLokasi = '00';
            }
        }

        if ($ruangBaru!="") {
            $ruangTmp = Ruangan::find($ruangBaru);
            if (!is_null($ruangTmp->kode)) {
                $kodeRuang = sprintf('%02d', $ruangTmp->kode);
            } else {
                $kodeRuang = '00';
            }
        }

        $arrKodelama = explode("/", $kodeLama);
        $partKodeBaru = sprintf("%02d.%02d.%02d", $kodeInstalasi, $kodeLokasi, $kodeRuang);
        $arrKodelama[1] = $partKodeBaru;
        $kodeBaru = implode('/', $arrKodelama);

        return $kodeBaru;
    }
}
