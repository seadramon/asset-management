<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\MasterJab;
use Asset\Models\Aset;
use Asset\Models\Instalasi;
use Asset\Models\MasterFm;

use FusionCharts;
use TimeSeries;
use FusionTable;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $schema = self::monitoringSchema();
        $arrData = "";
        $arrSelect = ['suhu_de', 'suhu_nde', 'suhu_b_de', 'suhu_b_nde', 'suhu_bearing_atas', 'suhu_bearing_bawah'];

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {   
    		$data = Aset::select('aset.nama_aset', 'aset.kode_fm', 'fm_'.$request->kode_fm.'.*')
    		    ->join('ms_52w', 'aset.id', '=', 'ms_52w.KOMPONEN_ID')
    		    ->join('MS_4W', 'ms_52w.ID', '=', 'ms_4w.MS_52W_ID')
    		    ->join('fm_'.$request->kode_fm, 'ms_4w.ID', '=', 'fm_'.$request->kode_fm.'.MS_4W_ID')
    		    ->where('aset.id', $request->komponen_id)
    		    ->get();
    	} else {
    		$data = Aset::select('aset.nama_aset', 'aset.kode_fm', 'fm_m2.*')
    		    ->join('ms_52w', 'aset.id', '=', 'ms_52w.KOMPONEN_ID')
    		    ->join('MS_4W', 'ms_52w.ID', '=', 'ms_4w.MS_52W_ID')
    		    ->join('fm_m2', 'ms_4w.ID', '=', 'fm_m2.MS_4W_ID')
    		    ->where('aset.id', '7125')
    		    ->get();
    	}
        
        if (!empty($data) && count($data) > 0) {
        	$namaAset = $data[0]->nama_aset;
	        $arrM = MasterFm::where('kode_fm', $data[0]->kode_fm)
	            ->whereIn('nama_field', $arrSelect)
	            ->get();
	// dd($data[0]);
	        foreach ($arrM as $row) {
	            $arrVar[$row->nama_field] = $row->pengukuran;
	        }

	        $arrData = [];

	        foreach ($data as $row) {
	            $tes = date('d-M-y', strtotime($row->tanggal));

	            foreach ($arrVar as $key => $val) {
	                $arrData[] = [
	                    $tes, 
	                    $val,
	                    (int)$row->$key
	                ];
	            }
	        }
        } else {
        	$namaAset = Aset::find($request->komponen_id)->nama_aset;
        }

        if (namaRole() != "SPV PEMELIHARAAN") {
            $instalasi = Instalasi::get()->pluck('name', 'id')->toArray(); 
            $labelInstalasi = ["" => "- Pilih Lokasi -"];
            $instalasi = $labelInstalasi + $instalasi;
        } else {
            $instalasi = Instalasi::whereIn('id', lokasi())
            ->get()->pluck('name', 'id')->toArray(); 
            $labelInstalasi = ["" => "- Pilih Lokasi -"];
            $instalasi = $labelInstalasi + $instalasi;
        }

        $asset = [];
        $komponen = ["" => "- Pilih Lokasi terlebih dahulu -"];
        
        return view('pages.home', [
            'namaAset' => $namaAset,
            'schema' => $schema,
            'data' => json_encode($arrData),
            'arrData' => $arrData,
            'instalasi' => $instalasi,
            'komponen' => $komponen
        ]);
    }

    private static function monitoringSchema()
    {
        $arr = [
            toObject([
                            'name' => "Time",
                            'type' => "date",
                            'format' => "%d-%b-%y"
                        ]),
            toObject([
                            'name' => "Pengukuran",
                            'type' => "string"
                        ]),
            toObject([
                            'name' => "Jumlah",
                            'type' => "number"
                        ])
        ];

        return json_encode($arr);
    }

    public function assetSelect($id)
    {
        $aa = aset::where('instalasi_id', $id)
        	->whereIn('kode_fm', ['E5', 'E11', 'M1', 'M2'])
        	->get();
        $txt = '';
        foreach ($aa as $row) {
            $txt .= '<option value="' . $row->id . '">' . $row->nama_aset . '</option>';
        }

        $bb = Aset::where('instalasi_id', $id)
            ->where('instalasi_id', $id)
            ->where('equipment', 'yes')
            ->where('kondisi_id', '<>', '12')
            ->get();
        $template = '';
        foreach ($bb as $row) {
            $template .= '<option value="' . $row->id . '">' . $row->nama_aset . '</option>';
        }

        return response()->json([
            'data' => $txt,
            'template' => $template
        ]);
    }

    public function getAssetKodefm($id)
    {
    	$data = Aset::find($id);

    	return response()->json([
    		'data' => $data
    	]);
    }
}
