<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Template,
    Asset\Models\Komponen,
    Asset\Models\KomponenDetail,
    Asset\Models\Kelompok,
    Asset\Models\KelompokDetail,
    Asset\Models\SpekGroup,
    Asset\Models\MsData,
    Asset\Models\MsdataPdm,
    Asset\Models\Instalasi,
    Asset\Models\Aset,
    Asset\Models\Ms52w,
    Asset\Models\Ms4w,
    Asset\Models\Master,
    Asset\Models\MasterJab,
    Asset\Models\Perawatan,
    Asset\Models\Perbaikan,
    Asset\Models\MsPrwrutin,
    Asset\Models\PrwrutinPdm,
    Asset\Models\JadwalLibur,
    Asset\Role as tuRoleUser,
    Asset\Models\PmlKeluhan;

use DB;
use Session;
use DateTime;
use Validator;

class MStrategiController extends Controller
{
    public function entri()
    {
        $template = Template::get()->pluck('nama', 'id')->toArray();        
        array_unshift($template, "- Pilih Template -");

        $speks = SpekGroup::with(['item' => function($q) {
                        $q->orderBy('urutan');
                    }])->orderBy('urutan')->get();

        $komponen = [];

        return view('pages.mstrategi.entri',
            ['template' => $template,
            'komponen' => $komponen
        ]);
    }

    public function part($recid)
    {
        $parts = Komponen::with('komponendetail')
                ->where('ms_template_id', $recid)
                ->get();
        // $partDetail = KomponenDetail::where('ms_komponen_id', $recid)->get();
        $kelompok = Kelompok::all();
        $kelompokDetail = KelompokDetail::all();
        $msdata = MsData::where('ms_template_id', $recid)->get();

        return view('pages.mstrategi.part',
            ['parts' => $parts,
            'kelompok' => $kelompok,
            'kelompokDetail' => $kelompokDetail,
            'msdata' => $msdata]
        )->render();
    }

    public function simpan(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();

        try {
            $arrdata = [];
            if ($request->msdata) {
                $exists = Template::find($request->template);

                if ($exists) {
                    MsData::where('ms_template_id', $request->template)->delete();
                }

                foreach ($request->msdata as $row) {

                    if ($row['kelompok']!="" && $row['komponen']!="") {
                        $kelompok = explode("#", $row['kelompok']);0;

                        $arrdata[] = [
                            'MS_TEMPLATE_ID' => $request->template,
                            'MS_KOMPONEN_DETAIL_ID' => $row['komponen'],
                            'MS_KELOMPOK_DETAIL_ID' => isset($kelompok[1])?$kelompok[1]:0,
                            'NILAI' => isset($row['nilai'])?$row['nilai']:""
                        ];
                    }
                }

                $data = DB::table('ms_data')
                        ->insert($arrdata);
            }

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('mstrategi::mstrategi-entri');
    }

    public function lihat()
    {
        return view('pages.mstrategi.lihat');
    }

    public function entripdm()
    {
        $instalasi = Instalasi::get()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Pilih Lokasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        /*$equipment = Aset::select(DB::raw("aset.id || ' - ' || aset.nama_aset as fullname"), 'id')
            ->where('equipment', 'yes')->get()->pluck('fullname', 'id')->toArray();
        $labelEquipment = ["" => "-             Pilih Equipment             -"];
        $equipment = $labelEquipment + $equipment;*/
        $equipment = [];
        $komponen = [];

        return view('pages.mstrategi.entripdm',[
            'equipment' => $equipment,
            'komponen' => $komponen,
            'instalasi' => $instalasi
        ]);
    }

    public function partpdm($recid)
    {
        // $equipment = Aset::where('id', $recid);
// DB::connection()->enableQueryLog();
        $parts = Aset::where('equipment_id', $recid)
            ->where('equipment', 'no')
            ->where('kondisi_id', '<>', '12')
            ->orWhere('id', $recid)
            ->orderBy('equipment', 'desc')
            ->get();
// dd(DB::getQueryLog());
        $val = MsdataPdm::where('equipment_id', $recid)->get()->toArray();

        return view('pages.mstrategi.partpdm',
            [
                // 'equip' => $equipment,
                'parts' => $parts,
                'val' => $val
            ])->render();
    }

    public function simpanpdm(Request $request)
    {        
        DB::beginTransaction();

        try {
            $arrdata = [];
            if ($request->msdatapdm) {
                $exists = MsdataPdm::where('equipment_id', $request->template)->count();

                if ($exists > 0) {
                    MsdataPdm::where('equipment_id', $request->template)->delete();
                }

                foreach ($request->msdatapdm as $row) {
                    MsdataPdm::where('komponen_id', $row['part'])->delete();

                    $arrdata[] = [
                        'EQUIPMENT_ID' => $request->template,
                        'KOMPONEN_ID' => $row['part'],
                        'NILAI' => isset($row['nilai'])?strtoupper($row['nilai']):""
                    ];
                }

                $data = DB::table('ms_datapdm')
                        ->insert($arrdata);
            }

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('mstrategi::mstrategi-entripdm');
    }

    public function entri52w()
    {
        $instalasi = Instalasi::whereIn('id', lokasi())
            ->get()->pluck('name', 'id')->toArray(); 
        $labelInstalasi = ["" => "- Pilih Lokasi -"];
        $instalasi = $labelInstalasi + $instalasi;

        $asset = [];
        $template = [];
        // $template = Template::get()->pluck('nama', 'id')->toArray();        
        // array_unshift($template, "- Pilih Equipment -");
        $komponen = [];

        return view('pages.mstrategi.entri52w',
            ['instalasi' => $instalasi,
            'asset' => $asset,
            'template' => $template,
            'komponen' => $komponen
        ]);
    }

    public function simpan52w(Request $request)
    {
        DB::beginTransaction();

        try {
            // dd($request->all());
            if (count($request->periksa) > 0) {
                if ($request->tahun != "") {
                    $tahun = $request->tahun;
                } else {
                    $tahun = date('Y');
                }

                foreach ($request->periksa as $row) {
                    // reset
                    /*Ms52w::where('instalasi_id', $request->instalasi_id)
                        ->where('equipment_id', $request->equipment_id)
                        ->where('komponen_id', $row['komponen_id'])
                        ->where('tahun', $tahun)
                        ->delete();*/
// dd($row);
                    if ($row['52w_id']!="") {
                        $jmlTemp = Ms52w::where('instalasi_id', $request->instalasi_id)
                                ->where('equipment_id', $request->equipment_id)
                                ->where('komponen_id', $row['komponen_id'])
                                ->where('tahun', $tahun)
                                ->count();
// dd($jmlTemp);
                        if ($jmlTemp > 0) {
                            $data = Ms52w::where('id', $row['52w_id'])
                                ->update([
                                    'tahun' => $request->tahun,
                                    'instalasi_id' => $request->instalasi_id,
                                    'equipment_id' => $request->equipment_id,
                                    'komponen_id'  => $row['komponen_id'],
                                    'minggu_mulai' => isset($row['minggu_mulai'])?$row['minggu_mulai']:0,
                                    'jumlah_orang' => isset($row['jumlah_orang'])?$row['jumlah_orang']:0,
                                    'total_durasi' => isset($row['total_durasi'])?$row['total_durasi']:0,
                                ]);
                        } else {
                            $data = new Ms52w();

                            $data->tahun = $request->tahun;
                            $data->instalasi_id = $request->instalasi_id;
                            $data->equipment_id = $request->equipment_id;

                            $data->komponen_id  = $row['komponen_id'];
                            $data->minggu_mulai = isset($row['minggu_mulai'])?$row['minggu_mulai']:0;
                            $data->jumlah_orang = isset($row['jumlah_orang'])?$row['jumlah_orang']:0;
                            $data->total_durasi = isset($row['total_durasi'])?$row['total_durasi']:0;

                            $data->save();
                        }
                    } else {
                        $data = new Ms52w();

                        $data->tahun = $request->tahun;
                        $data->instalasi_id = $request->instalasi_id;
                        $data->equipment_id = $request->equipment_id;

                        $data->komponen_id  = $row['komponen_id'];
                        $data->minggu_mulai = isset($row['minggu_mulai'])?$row['minggu_mulai']:0;
                        $data->jumlah_orang = isset($row['jumlah_orang'])?$row['jumlah_orang']:0;
                        $data->total_durasi = isset($row['total_durasi'])?$row['total_durasi']:0;

                        $data->save();
                    }
                }
            }

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('mstrategi::mstrategi-entri52w');
    }

    // Part 52W
    public function assetSelect($id)
    {
        $aa = aset::where('instalasi_id', $id)
            ->whereIn('bagian', bagian())
            ->get();
        $txt = '';
        foreach ($aa as $row) {
            $txt .= '<option value="' . $row->id . '">' . $row->nama_aset . '</option>';
        }

        $bb = Aset::where('instalasi_id', $id)
            ->whereIn('bagian', bagian())
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

    // Part 52W
    public function komponenSelect($id, $tahun = "")
    {
        DB::connection()->enableQueryLog();
        if ($tahun == "") {
            $tahun = date('Y');
        }

        /*$komponen = Aset::with(['ms52w_komponen' => function($query) use($tahun){
                $query->where('ms_52w.tahun', $tahun);
            }])
            ->with('pdm')
            ->where(function ($query) use ($id) {
                $query->where('equipment_id', $id)
                    ->where('equipment', 'no')
                    ->orWhere('id', $id);
            });*/

        $komponen = Aset::with(['pdm', 'ms52w_komponen' => function($q) use($tahun) {
                $q->where('tahun', $tahun);
            }])
			->where(function ($query) use ($id) {
                $query->where('equipment_id', $id)
					->where('equipment', 'no')
					->orWhere('id', $id);
            });
// dd($komponen->get());
        if (is_array(bagian())) {
            $komponen = $komponen->whereIn('bagian', bagian());
        } else {
            $komponen = $komponen->where('bagian', bagian());
        }
        $komponen = $komponen->orderBy('equipment', 'desc')
						->get();
		// dd(DB::query);
		// return response()->json(bagian());
// dd(DB::getQueryLog());
        return view('pages.mstrategi.part52w',
            ['komponens' => $komponen])->render();
    }

    public function entri4w()
    {        
        DB::connection()->enableQueryLog();

        // dd(trim(\Auth::user()->userid));
        /*$aset = Ms52w::select('ms_52w.*', 'aset.nama_aset')
        		->join('aset', 'ms_52w.komponen_id', '=', 'aset.id')
                ->where('tahun', date('Y'))
                ->get()->pluck('komponen.nama_aset', 'id')->toArray();
        $label = ["" => "- Pilih Aset -"];
        $aset = $label + $aset;*/
        $nipception = [];

        $instalasi = Instalasi::whereIn('id', lokasi())
            ->get()->pluck('name', 'id')->toArray(); 
        $labelInstalasi = ["" => "- Pilih Lokasi -"];
        $instalasi = $labelInstalasi + $instalasi;

        $week = [
            "" => "- Pilih Minggu-"
        ];

        // All Weeks
        if (namaRole()=="Super Administrator"/* || in_array(trim(\Auth::user()->userid), $nipception)*/) {
            for ($i = 1; $i <= lastWeekNumber(); $i++) { 
                $week[$i] = "Minggu ke ".$i;
            }
            // $week[8] = "Minggu ke 8";
        } else {
            // $curWeek = '52';
            $curWeek = date('W');
// dd($curWeek);
            if ($curWeek == '52') {
                $b_atas = (int)0;
                $b_bawah = (int)4;
            } else {
                $b_atas = (int)floor($curWeek / 4) * 4;
                $b_bawah = (int)floor($curWeek / 4) + 1;
                $b_bawah *= 4;
            }

            // SEVERAL WEEKS
            if ($curWeek % 4 == 0 /*|| in_array(trim(\Auth::user()->userid), $nipception)*/) {
                if ( lastWeekNumber() == '53' ) {
                    $week['53'] = "Minggu ke 53";
                }

                for ($i = $b_atas + 1; $i <= $b_bawah; $i++) { 
                    $week[$i] = "Minggu ke ".$i;
                }
            } 
            // CUSTOM (PLEASE REMARK AFTER USE)
            else {  
                $b_atas = (int)13;
                $b_bawah = (int)16;

                for ($i = $b_atas; $i <= $b_bawah; $i++) { 
                    $week[$i] = "Minggu ke ".$i;
                }
            }
        }

        $bagian = Master::Bagian()
                ->get()->pluck('name', 'id')->toArray();
        $label = ["" => "- Pilih Bagian -"];
        $bagian = $label + $bagian;
// dd(date('W'));
        $tahun = date('Y');
        if (date('W') == lastWeekNumber()) {
            $tahun = $tahun + 1;
        }
        // dd($tahun);
        
        return view('pages.mstrategi.entri4w',[
            // 'aset' => $aset,
            'week' => $week,
            'tahun' => $tahun,
            'bagian' => $bagian,
            'instalasi' => $instalasi
        ]);
    }

    public function weekSelect($id)
    {
        $ms52 = Ms52w::find($id);
        $dataPdm = MsdataPdm::where('komponen_id', $ms52->komponen_id)->first();
        // dd($dataPdm);
        $dayList = [
            ''          => 'Pilih Hari',
            'senin'     => 'Senin',
            'selasa'    => 'Selasa',
            'rabu'      => 'Rabu',
            'kamis'     => 'Kamis',
            'jumat'     => 'Jumat'
        ];

        if ($dataPdm) {
            $jmlWeek = str_replace("W", "", $dataPdm->nilai);

            $ddate = date('Y-m-d');
            $date = new DateTime($ddate);
            $week = $date->format("W");
            // dd($jmlWeek);
        }

        return view('pages.mstrategi.part4w', [
            'jmlMinggu' => $jmlWeek,
            'urutanMinggu' => $week,
            'dayList' => $dayList
        ])->render();
    }

    public function weekSelectnew($week, $bagian, $tahun = "", $lokasi = "")
    {
        // dd(\Auth::user()->userid)
        // dd();
        DB::connection()->enableQueryLog();
        if ($tahun == "") {
            $tahun = date('Y');
        }
        $weekMin = $week-1;

        $komponen = Ms52w::select('ms_52w.*', 'aset.nama_aset', 'aset.kode_fm', 'aset.id as aset_id', 'ms_4w.id as ms_4w_id', 'ms_4w.ms_52w_id', 'ms_4w.urutan_minggu', 'ms_4w.hari', 'ms_4w.petugas')
                    ->join('aset', 'ms_52w.komponen_id', '=', 'aset.id')
                    ->join('ms_datapdm', 'ms_52w.komponen_id', '=', 'ms_datapdm.komponen_id')
                    ->leftJoin('ms_4w', 'ms_52w.id', '=', DB::raw("ms_4w.ms_52w_id and  ms_4w.urutan_minggu = $week"))
                    // ->where('aset.bagian', $bagian)
                    /*->whereRaw('(ms_52w.minggu_mulai = mod('.$week.',SUBSTR(ms_datapdm.nilai,2)) OR ms_52w.minggu_mulai = mod('.$weekMin.',SUBSTR(ms_datapdm.nilai,2)))')*/
                    ->where('ms_52w.tahun', $tahun)
                    ->where('aset.instalasi_id', $lokasi);

        if (namaRole() == 'Super Administrator') {
            $komponen = $komponen->whereIn('aset.bagian', [$bagian]);
        } else {
            $komponen = $komponen->whereIn('aset.bagian', bagian());
        }

        if (!in_array($bagian, ['3', '4'])) {
            $komponen = $komponen->join('jadwal_libur', 'ms_52w.equipment_id', '=', 'jadwal_libur.equipment_id')
            			->whereRaw('(mod(ms_52w.minggu_mulai,SUBSTR(ms_datapdm.nilai,2,2)) = mod('.$week.',SUBSTR(ms_datapdm.nilai,2,2)) OR (ms_52w.id not in (select ms_52w_id from ms_4w where urutan_minggu = '.$weekMin.' and ms_52w_id = ms_52w.id) AND ms_52w.minggu_mulai = mod('.$weekMin.',SUBSTR(ms_datapdm.nilai,2,2))))')
            			// ->whereRaw('ms_52w.id not in (select ms_52w_id from ms_4w where urutan_minggu = '.$weekMin.')')
                        ->where(function($query) use($week, $weekMin){
                            $query->where('jadwal_libur.minggu', 'like', '%,'.$week.',%')
                                // ->orWhere('jadwal_libur.minggu', 'not like', '%,'.$weekMin.',%');  
                                ->orWhere(function($sql) use($week, $weekMin){
                                    $sql->where('jadwal_libur.minggu', 'not like', '%,'.$weekMin.',%')
                                        ->where('jadwal_libur.minggu', 'like', '%,'.$week.',%');
                                });   
                        });
        } else {
        	$komponen = $komponen->whereRaw('(mod(ms_52w.minggu_mulai,SUBSTR(ms_datapdm.nilai,2,2)) = mod('.$week.',SUBSTR(ms_datapdm.nilai,2,2)))');
        }
// dd($komponen);
        $jmlKomponen = $komponen->count();
        $komponen = $komponen->get();
        /*foreach ($komponen as $row) {
            dd($row);
            $arr[] = $row;
        }*/
        // dd($komponen);
        // dd(DB::getQueryLog());
        $dayList = [
            ''          => 'Pilih Hari',
            'senin'     => 'Senin',
            'selasa'    => 'Selasa',
            'rabu'      => 'Rabu',
            'kamis'     => 'Kamis',
            'jumat'     => 'Jumat'
        ];

        return view('pages.mstrategi.part4w',
            ['komponens' => $komponen,
            'dayList' => $dayList,
            'jmlKomponen' => $jmlKomponen])->render();
    }

    public function weekSelectnewdev($week, $bagian, $tahun = "", $lokasi = "")
    {
        DB::connection()->enableQueryLog();
        
        if ($tahun == "") {
            $tahun = date('Y');
        }

        $weekMin = $week-1;

        // in case using jadwal kerja
        // DB::connection()->enableQueryLog();
        /*$komponen = Ms52w::select('ms_52w.*', 'aset.nama_aset', 'aset.kode_barcode', 'aset.kode_fm', 'aset.id as aset_id', 'ms_4w.id as ms_4w_id', 'ms_4w.ms_52w_id', 'ms_4w.urutan_minggu', 'ms_4w.hari', 'ms_4w.petugas', 'ms_4w.foto_lokasi')
                    ->join('aset', 'ms_52w.komponen_id', '=', 'aset.id')
                    ->join('ms_datapdm', 'ms_52w.komponen_id', '=', 'ms_datapdm.komponen_id')
                    ->leftJoin('ms_4w', 'ms_52w.id', '=', DB::raw("ms_4w.ms_52w_id and ms_4w.urutan_minggu = $week"))
                    ->where('ms_52w.tahun', $tahun)
                    ->where('aset.instalasi_id', $lokasi)
                    ->where('aset.kondisi_id', '<>', '12');

        // Check Admin for bagian
        if (namaRole() == 'Super Administrator') {
            $komponen = $komponen->whereIn('aset.bagian', [$bagian]);
        } else {
            $komponen = $komponen->whereIn('aset.bagian', bagian());
        }
        $komponen = $komponen->join('jadwal_libur', 'ms_52w.equipment_id', '=', 'jadwal_libur.equipment_id')
                    ->whereRaw('(mod(ms_52w.minggu_mulai,SUBSTR(ms_datapdm.nilai,2,2)) = mod('.$week.',SUBSTR(ms_datapdm.nilai,2,2)) OR (ms_52w.id not in (select ms_52w_id from ms_4w where urutan_minggu = '.$weekMin.' and ms_52w_id = ms_52w.id) AND ms_52w.minggu_mulai = mod('.$weekMin.',SUBSTR(ms_datapdm.nilai,2,2))))')
                    // ->whereRaw('ms_52w.id not in (select ms_52w_id from ms_4w where urutan_minggu = '.$weekMin.')')
                    ->where(function($query) use($week, $weekMin){
                        $query->where('jadwal_libur.minggu', 'like', '%,'.$week.',%')
                            // ->orWhere('jadwal_libur.minggu', 'not like', '%,'.$weekMin.',%');  
                            ->orWhere(function($sql) use($week, $weekMin){
                                $sql->where('jadwal_libur.minggu', 'not like', '%,'.$weekMin.',%')
                                    ->where('jadwal_libur.minggu', 'like', '%,'.$week.',%');
                            });   
                    });
        $komponen = $komponen->get();*/
        // dd($komponen);
        // END in case using jadwal kerja

        // in case not using jadwal kerja
        $komponenSecond = Ms52w::select('ms_52w.*', 'aset.nama_aset', 'aset.kode_barcode', 'aset.kode_fm', 'aset.id as aset_id', 'ms_4w.id as ms_4w_id', 'ms_4w.ms_52w_id', 'ms_4w.urutan_minggu', 'ms_4w.hari', 'ms_4w.petugas', 'ms_4w.foto_lokasi')
                ->join('aset', 'ms_52w.komponen_id', '=', 'aset.id')
                ->join('ms_datapdm', 'ms_52w.komponen_id', '=', 'ms_datapdm.komponen_id')
                ->leftJoin('ms_4w', 'ms_52w.id', '=', DB::raw("ms_4w.ms_52w_id and  ms_4w.urutan_minggu = $week"))
                ->where('ms_52w.tahun', $tahun)
                ->where('aset.instalasi_id', $lokasi)
                ->where('aset.kondisi_id', '<>', '12')
                ->whereRaw('(mod(ms_52w.minggu_mulai,SUBSTR(ms_datapdm.nilai,2,2)) = mod('.$week.',SUBSTR(ms_datapdm.nilai,2,2)))');
                
        // Check Admin for bagian
        if (namaRole() == 'Super Administrator') {
            $komponenSecond = $komponenSecond->whereIn('aset.bagian', [$bagian]);
        } else {
            $komponenSecond = $komponenSecond->whereIn('aset.bagian', bagian());
        }
        $komponenSecond = $komponenSecond->get();
        // END in case not using jadwal kerja

        // merge
        // $merged = $komponenSecond->merge($komponen);
        $merged = $komponenSecond;
        // $resultkomponen = $merged->all();
        $resultkomponen = $merged;
// dd($resultkomponen);
        /*$arrTemp = [];
        foreach ($resultkomponen as $temp) {
            $arrTemp[$temp->komponen_id] = $temp->nama_aset;
        }
dd($arrTemp);*/
    	// Populate barcode from query
        /*$arrBarcode = [];
    	$arrKomponen = [];
    	foreach ($resultkomponen as $row) {
    		$arrBarcode[] = $row->kode_barcode;
            $arrKomponen[] = $row->komponen_id;
    	}*/
        // end populate

        // menutup jadwal monitoring jika perawatan perbaikan(aduan/monitoring) belum close
        /*$arrPrw = [];
        $prw = Perawatan::select('komponen_id')->whereIn('komponen_id', $arrKomponen)
            // ->where('status', '<>', '10')
            ->whereNotIn('status', ['10', '99'])
            ->get();

        foreach ($prw as $row) {
            $arrPrw[] = $row->komponen_id;
        }

        $arrPrb = [];
        $prb = Perbaikan::select('komponen_id')->whereIn('komponen_id', $arrKomponen)
            // ->where('status', '<>', '10')
            ->whereNotIn('status', ['10', '99'])
            ->get();
        foreach ($prb as $row) {
            $arrPrb[] = $row->komponen_id;
        }

        foreach ($resultkomponen as $key => $row) {
        	if (in_array($row->komponen_id, $arrPrw) || in_array($row->komponen_id, $arrPrb)) {
        		if (!empty($row->ms_4w_id)) {
                    if (empty($row->foto_lokasi)) {
                        Ms4w::where('id', $row->ms_4w_id)
                        ->update([
                            'status' => '99'
                        ]);
                    }
        		}
        		unset($resultkomponen[$key]);
        	}
        }*/

        // END menutup jadwal monitoring jika perawatan perbaikan(aduan/monitoring) belum close
		// dd(count($resultkomponen));
        $jmlKomponen = count($resultkomponen);
        // dd($jmlKomponen);
        
        $dayList = [
            ''          => 'Pilih Hari',
            'senin'     => 'Senin',
            'selasa'    => 'Selasa',
            'rabu'      => 'Rabu',
            'kamis'     => 'Kamis',
            'jumat'     => 'Jumat'
        ];

        $nipLokasi = MasterJab::where('lokasi', 'like', '%'.$lokasi.'%')
            ->get()->pluck('nip')->toArray();
        $arrUser = ["" => "-             Pilih Petugas             -"];
        $users = tuRoleUser::select('nip', 'nama')
            ->whereNull('is_manajer')
            ->where('recidrole', \Auth::user()->role->jabatan->recidjabatan)
            ->get();
// dd($lokasi);
        foreach ($users as $row) {
            if (in_array($row->nip, $nipLokasi)) {
                $arrUser[trim($row->nip)] = trim($row->nama);
            }
        }
// dd($arrUser);
        return view('pages.mstrategi.part4w',
            ['komponens' => $resultkomponen,
            'dayList' => $dayList,
            'petugas' => $arrUser,
            'jmlKomponen' => $jmlKomponen])->render();
    }

    public function simpan4w(Request $request)
    {
        DB::beginTransaction();
        try {
            if (is_array($request->week) && count($request->week) > 0) {
                
                foreach ($request->week as $row) {
                    $urutanMinggu = $request->urutan_minggu;
                    $jadwalKerja = null;

                    if (isset($row['is_equipment'])) {
                        if ($row['is_equipment'] == 'no' && isset($row['equipment_id'])) {
                            $jadwalKerja = JadwalLibur::where('equipment_id', $row['equipment_id'])->first();
                        }else{
                            $jadwalKerja = JadwalLibur::where('equipment_id', $row['aset_id'])->first();
                        }
                    }

                    if ($jadwalKerja) {
                        if (!in_array($urutanMinggu, explode(",", $jadwalKerja->minggu))) {
                            $urutanMinggu = $urutanMinggu + 1;
                        }
                    }

                    // Tanggal Monitoring
                    $tgl_monitor = new \DateTime();
                    $tgl_monitor->setISODate($request->tahun, $request->urutan_minggu);
                    // dd($tgl_monitor->format('Y-m-d'));
                    // End Tanggal Monitoring

                    /*$arrKode = Ms4w::find($id);
                    // $arrKode->ms52w->komponen
                    $urutan = Ms4w::select(DB::raw('max(urutan) as urutan'))
                        ->whereRaw("TO_CHAR(TANGGAL, 'MM') = ".date('m'))
                        ->first();
                    
                    $gen = generateKodeWo('monitoring', 
                            !empty($urutan->urutan)?$urutan->urutan++:1,
                            $arrKode->ms52w->komponen->bagian,
                            $arrKode->ms52w->komponen->instalasi_id,
                            date('d-m-Y'));
                    dd($request->all());*/

                    // Cek Perbaikan aset tidak dapat beroperasi
                    $status = 0;
                    $jmlNonOp = Perbaikan::where('komponen_id', $row['aset_id'])
                        ->whereNotIn('status', config('custom.skipStatus'))
                        ->where('kondisi', 'tidak beroperasi')
                        ->count();

                    if ($jmlNonOp > 0) {
                        $status = 99;
                    }

                    if (isset($row['ms_4w_id'])) {
                        if ($row['ms_4w_id']!="") {
                            $data = Ms4w::where('id', $row['ms_4w_id'])
                            	->withTrashed()
                                ->update([
                                    'ms_52w_id' => $row['ms_52w_id'],
                                    'urutan_minggu' => $request->urutan_minggu,
                                    'hari' => isset($row['hari'])?strtoupper($row['hari']):"",
                                    'petugas' => isset($row['petugas'])?strtoupper($row['petugas']):"",
                                    'status' => $status,
                                    'tanggal' => date('Y-m-d'),
                                    'tanggal_monitoring' => $tgl_monitor->format('Y-m-d')
                            ]);
                        } else {
                            $cek = Ms4w::where('ms_52w_id', $row['ms_52w_id'])
                            ->where('urutan_minggu', $request->urutan_minggu)
                            ->count();
                        // dd($cek);
                            if ($cek == 0) {
                                $data = new Ms4w();

                                $data->ms_52w_id = $row['ms_52w_id'];
                                $data->urutan_minggu = $request->urutan_minggu;
                                $data->hari = isset($row['hari'])?strtoupper($row['hari']):"";
                                $data->petugas = isset($row['petugas'])?strtoupper($row['petugas']):"";
                                $data->status = $status;
                                $data->tanggal = date('Y-m-d');
                                $data->tanggal_monitoring = $tgl_monitor->format('Y-m-d');

                                // kode wo
                                $ms52w = Ms52w::find($row['ms_52w_id']);
                                $urutnya = monitoringUrutan();
                                $gen = generateKodeWo('monitoring',
                                    $urutnya,
                                    $ms52w->komponen->bagian,
                                    $ms52w->instalasi_id,
                                    date('Y-m-d')
                                );
                                $data->kode_wo = $gen;
                                $data->urutan = $urutnya;
                                // kode wo

                                $data->save();
                            }
                        }
                    }
                }
            }

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch (Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');       
        }

        return redirect()->route('mstrategi::mstrategi-entri4w');
    }

    public function entriPenugasan()
    {
        $aset = [];
        $week = [0 => "- Pilih Minggu-"];
        for ($i=1; $i <= 52; $i++) { 
            $week[$i] = "Minggu ke ".$i;
        }

        return view('pages.mstrategi.penugasan',
            ['aset' => $aset,
            'week' => $week
        ]);
    }

    public function penugasanSelect($id)
    {
        $komponen = Ms4w::with('ms52w.komponen')
                ->with('ms52w.komponen.instalasi')
                ->where('urutan_minggu', $id)
                ->get(); 
                
        return view('pages.mstrategi.partpenugasan', [
                'komponen' => $komponen
            ])->render();
    }

    public function simpanPenugasan(Request $request)
    {
        DB::connection()->enableQueryLog();
        DB::beginTransaction();
        try{
            $ms4w = $request->ms4w;
            
            if (is_array($ms4w) && count($ms4w) > 0) {
                foreach ($ms4w as $row) {
                    $data = Ms4w::find($row['id']);
                    $data->petugas = $row['petugas'];

                    $data->save();
                }
                DB::commit();
                Session::flash('success', 'Data berhasil disimpan');
            }

        }catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('mstrategi::mstrategi-entriPenugasan');
    }

    public function entriOverhaul()
    {
    	$aset = Aset::where('kondisi_id', '<>', '12')
            ->get()->pluck('nama_aset', 'id')->toArray(); 
        $labelAset = ["" => "- Pilih Aset -"];
        $aset = $labelAset + $aset;

        return view('pages.mstrategi.entrioverhaul', ['aset' => $aset]);
    }

    public function overhaulSelect($id)
    {
    	$data = Aset::where('id', $id)
    		->select('id', 'nama_aset', 'overhaul', 'tgl_overhaul')
    		->first();

    	return response()->json([
            'data' => $data
        ]);
    }

    public function simpanoverhaul(Request $request)
    {
    	DB::beginTransaction();

    	try {
    		$id = $request->aset;

    		if ($id!= "") {
    			$data = Aset::where('id', $id)
    				->update(['overhaul' => $request->overhaul,
    					'tgl_overhaul' => $request->tgl_overhaul
    				]);

    			DB::commit();
            	Session::flash('success', 'Data berhasil disimpan');
    		} else {
    			Session::flash('error', 'Id Aset wajib diisi');
    		}
    	} catch(Exception $e) {
    		DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
    	}

    	return redirect()->route('mstrategi::mstrategi-entriOverhaul');
    }

    // Perawatan Rutin Begin
    public function entriPrwRutin()
    {
        /*if (trim(\Auth::user()->userid) != "10901554") {
            abort(503, 'Under maintenance');
        }*/
        $perawatan = Master::Prwrutin()->get();

        $instalasi = Instalasi::get()->pluck('name', 'id')->toArray(); 
        $labelInstalasi = ["" => "- Pilih Lokasi -"];
        $instalasi = $labelInstalasi + $instalasi;

        $equipment = ["" => "-             Pilih Komponen             -"];
        $komponen = [];

        return view('pages.mstrategi.entriprwrutin',[
            'equipment' => $equipment,
            'komponen' => $komponen,
            'instalasi' => $instalasi,
            'perawatan' => $perawatan
        ]);
    }

    public function assetPrwSelect($id)
    {
        $aa = aset::where('instalasi_id', $id)->get();
        $txt = '';
        foreach ($aa as $row) {
            $txt .= '<option value="' . $row->id . '">' . $row->nama_aset . '</option>';
        }

        $bb = Aset::where('instalasi_id', $id)
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

    public function prwrutinSelect($id)
    {
        /*$parts = Aset::select('aset.id as idaset', 'aset.nama_aset', 'aset.kode_fm', 'ms_komponen_detail.id as idpart', 'ms_komponen_detail.part','ms_prwrutin.*')
            ->where('aset.id', $id)
            ->leftJoin('ms_komponen_detail', 'aset.kode_fm', '=', 'ms_komponen_detail.kode_fm')
            ->leftJoin('ms_prwrutin', 'ms_komponen_detail.id', '=', DB::raw("ms_prwrutin.kode_part and ms_prwrutin.komponen_id = $id"))
            ->get();*/
        $parts = [];
        $query = Aset::select('aset.id as idaset', 'aset.nama_aset', 'aset.kode_fm_pr', 'ms_komponen_detail.id as idpart', 'ms_komponen_detail.part','prw_rutin_pdm.*')
            ->where('aset.id', $id)
            ->leftJoin('ms_komponen_detail', 'aset.kode_fm_pr', '=', 'ms_komponen_detail.kode_fm')
            ->leftJoin('prw_rutin_pdm', 'ms_komponen_detail.id', '=', DB::raw("prw_rutin_pdm.kode_part and prw_rutin_pdm.komponen_id = $id"))
            ->get();
// dd($query);
        foreach ($query as $row) {
            $parts[$row->idpart.'#'.$row->part][$row->perawatan] = [
                'idaset' => $row->idaset,
                'nama_aset' => $row->nama_aset,
                'kode_fm' => $row->kode_fm_pr,
                'idpart' => $row->idpart,
                'part' => $row->part,
                'id' => $row->id,
                'komponen_id' => $row->komponen_id,
                'kode_part' => $row->kode_part,
                'perawatan' => $row->perawatan,
                'nilai' => $row->nilai,
            ];
        }

        /*foreach ($parts as $key => $part) {
            if (sizeof($part) > 0 && is_array($part)) {
                dd($part);
            }
        }*/
        $perawatan = Master::Prwrutin()->get();
        
        return view('pages.mstrategi.partPrwRutin', [
                'parts' => $parts,
                'perawatan' => $perawatan,
                'komponen_id' => $id,
            ])->render();
    }

    public function simpanPrwRutin(Request $request)
    {
        DB::beginTransaction();
        try {
            // dd(count($request->komponen));
        	if (count($request->komponen)) {
                $arrdata = [];

                // populate all key
        		foreach ($request->komponen as $row) {
        			$aset = $request->komponen_id;
                    $part = $row['kode_part'];
        			unset($row['kode_part']);

                    // populate per row
                    foreach ($row as $prw => $nilai) {
                        if ($nilai != "") {
                            $check = PrwrutinPdm::where('komponen_id', $aset)
                                ->where('kode_part', $part)
                                ->where('perawatan', $prw)
                                ->count();
                            /*dd($check.' - '.$prw);*/

                            // edit case
                            if ($check > 0) {
                                PrwrutinPdm::where('komponen_id', $aset)
                                    ->where('kode_part', $part)
                                    ->where('perawatan', $prw)
                                    ->update(['nilai' => strtoupper($nilai)]);
                            } else {
                            // insert new case
                                PrwrutinPdm::insert([
                                    'komponen_id' => $aset,
                                    'kode_part' => $part,
                                    'perawatan' => $prw,
                                    'nilai' => strtoupper($nilai)
                                ]);
                            }

                            DB::commit();
                        } else {
                            DB::table('PRW_RUTIN_PDM')->where('komponen_id', $aset)
                                ->where('kode_part', $part)
                                ->where('perawatan', $prw)
                                ->delete();
                            DB::commit();
                        }
                    }
        		}
        	}

            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('mstrategi::mstrategi-entriPrwRutin');
    }

    // Perawatan Rutin END
}
