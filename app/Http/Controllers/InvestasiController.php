<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Instalasi,
    Asset\Models\Master,
    Asset\Models\Npv;

use DB;
use Datatables;
use Session;
use Validator;
use PDF;
use DateTime;

class InvestasiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function npv()
    {
        $instalasi = Instalasi::whereIn('id', lokasi())->get()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Pilih Lokasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        $bagian = Master::bagian()->get()->pluck('name', 'kode')->toArray();
        $labelBagian = ["" => "-             Pilih Bagian             -"];
        $bagian = $labelBagian + $bagian;

        $umurEkonomis = [
            '4' => '4 Tahun',
            '8' => '8 Tahun',
            '16' => '16 Tahun',
            '20' => '20 Tahun'
        ];

        return view('pages.investasi.npv', [
            'instalasi' => $instalasi,
            'bagian' => $bagian,
            'umurEkonomis' => $umurEkonomis
        ]);
    }

    public function npvstore(Request $request)
    {   
        $lokasi = "";
        if (!empty($request->instalasi_id)) {
            $tmp = Instalasi::find($request->instalasi_id);
            $lokasi = $tmp->name;
        }
// dd($lokasi);
        $arrData = [
            'judul' => $request->judul,
            'tahun' => $request->tahun,
            'umur_ekonomis' => $request->umur_ekonomis,
            'instalasi_id' => $request->instalasi_id,
            'lokasi' => $lokasi,
            'rab' => str_replace(",", "", $request->rab),
            'discount_rate' => $request->discount_rate,
            'cash_in' => str_replace(",", "", $request->cash_in),
            'cash_out' => str_replace(",", "", $request->cash_out),
        ];

        if (isset($request->simpancetak)) {
            DB::beginTransaction();

            try {
                $data = new Npv;

                $data->judul = $arrData['judul'];
                $data->tahun = $arrData['tahun'];
                $data->umur_ekonomis = $arrData['umur_ekonomis'];
                $data->instalasi_id = $arrData['instalasi_id'];
                $data->rab = $arrData['rab'];
                $data->discount_rate = $arrData['discount_rate'];
                $data->cash_in = $arrData['cash_in'];
                $data->cash_out = $arrData['cash_out'];

                $data->save();

                DB::commit();   
                Session::flash('success', 'Data berhasil disimpan');
            } catch(Exception $e) {
                DB::rollback();
                Session::flash('error', 'Data gagal disimpan');
            }
        }

        // perhitungan
        // dd($arrData['discount_rate']);
        $biRatePlus = 1 + ($arrData['discount_rate'] / 100);
        $net = $arrData['cash_in'] - $arrData['cash_out'];
        $arrD = [];

        for ($i = 1; $i <= $arrData['umur_ekonomis'] ; $i++) { 
            $tmp = $net / ( pangkat($biRatePlus, $i) );

            // $arrD[] = rupiah($tmp, 2, ".", ",");
            $arrD[] = $tmp;
        }
        
        $arrData['npv'] = array_sum($arrD);
        // dd($arrData['rab']);
        $arrData['npv_presentase'] = round($arrData['npv'] / $arrData['rab'], 2) * 100;
        // end:perhitungan

        $filename = "Npv-print";

        $pdf = PDF::loadView('pages.investasi.npv-print', $arrData)->setPaper('a4', 'portrait');

        return $pdf->stream($filename);
    }
}
