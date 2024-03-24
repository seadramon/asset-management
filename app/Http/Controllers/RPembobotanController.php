<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Libraries\EvaluasiAset;

use Asset\Models\Aset,
    Asset\Models\Instalasi,
    Asset\Models\MasterJab,
    Asset\Models\MasterKodeFm,
    Asset\Models\Master,
    Asset\Models\Ms4w,
    Asset\Models\Perawatan,
    Asset\Models\Perbaikan;

use DB;
use Datatables;
use Session;
use Validator;
use PDF;
use DateTime;

class RPembobotanController extends Controller
{
    public function index()
    {
        $instalasi = Instalasi::get()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Pilih Instalasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        $bagian = Master::Bagian()->get()->pluck('name', 'id')->toArray();
        $label = ["" => "- Pilih Bagian -"];
        $bagian = $label + $bagian;

        $minggu = getMinggu();

        return view('pages.flaporan.index', [
            'instalasi' => $instalasi,
            'bagian' => $bagian,
            'minggu' => $minggu,
        ]);
    }

    public function laporan(Request $request)
    {
        $instalasi = $request->instalasi;
        $bagian = MasterJab::where('nip', trim(\Auth::user()->userid))->first();
        $end = date('Y-m-d', strtotime($request->tanggal));
        $start = strtotime($end.' -1 year');
        $start = date('Y-m-d', $start);
        $result = [];

        if ($instalasi !="") {
            $arrIdKomponen = [];
            DB::connection()->enableQueryLog();

            $startChar = str_replace("-", "", $start);
            $endChar = str_replace("-", "", $end);

            $data = Aset::select('id','nama_aset', 'verify_ts', 'kode_fm', 'tahun_pasang', 'umur', 'kode_aset',
                DB::raw("(SELECT 
                        count(PRW_DATA.ID) 
                    FROM 
                        PRW_DATA 
                    WHERE 
                        KOMPONEN_ID = aset.id and 
                        ((TO_CHAR(TGL_FINISH, 'YYYYMMDD') >= $startChar AND 
                        TO_CHAR(TGL_FINISH, 'YYYYMMDD') <= $endChar) OR TGL_FOTO_ANALISA IS NULL) and 
                        status not in ('99', '98')) AS jmlperawatan"),
                DB::raw("(SELECT 
                        count(PRB_DATA.ID) 
                    FROM 
                        PRB_DATA 
                    WHERE 
                        KOMPONEN_ID = aset.id and 
                        ((TO_CHAR(TGL_FINISH, 'YYYYMMDD') >= $startChar AND 
                        TO_CHAR(TGL_FINISH, 'YYYYMMDD') <= $endChar) OR TGL_FOTO_ANALISA IS NULL) and 
                        status not in ('99', '98')) AS jmlperbaikan"),
                DB::raw("(SELECT 
                        count(PRB_DATA.ID) 
                    FROM 
                        PRB_DATA 
                    WHERE 
                        KOMPONEN_ID = aset.id and 
                        ((TO_CHAR(TGL_FINISH, 'YYYYMMDD') >= $startChar AND 
                        TO_CHAR(TGL_FINISH, 'YYYYMMDD') <= $endChar) OR TGL_FOTO_ANALISA IS NULL) and 
                        tingkat = 'ringan' and status not in ('99', '98')) AS jmlperbaikanringan"),
                DB::raw("(SELECT 
                        count(PRB_DATA.ID) 
                    FROM 
                        PRB_DATA 
                    WHERE 
                        KOMPONEN_ID = aset.id and 
                        ((TO_CHAR(TGL_FINISH, 'YYYYMMDD') >= $startChar AND 
                        TO_CHAR(TGL_FINISH, 'YYYYMMDD') <= $endChar) OR TGL_FOTO_ANALISA IS NULL) and 
                        tingkat = 'berat' and status not in ('99', '98')) AS jmlperbaikanberat")
                )
                ->where('instalasi_id', $instalasi)
                ->where('bagian', $request->bagian)
                ->where('kondisi_id', '<>', '12')
                // ->limit(250)
                // ->whereIn('id', $arrIdKomponen)
                ->get();
// dd($request->bagian);
            $dataKondisi = EvaluasiAset::getKondisi($startChar, $endChar, $instalasi, $request->bagian);

            $arrKondisi = [];
            foreach ($dataKondisi as $row) {
                $arrKondisi[$row->id] = strtolower(!empty($row->operasional)?$row->operasional:'NORMAL');
            }

            $no = 1;
            foreach ($data as $row) {
                $umurBerjalan = EvaluasiAset::getUmurBerjalan($row->tahun_pasang);
                
                $param = MasterKodeFm::find($row->kode_fm);

                if (!empty($param)) {
                    $kodeBobot = $param->kode_bobot;
                    $umurEkonomis = $param->umur_ekonomis;

                    $jmlPerawatan = !empty($row->jmlperawatan)?$row->jmlperawatan:0;
                    $jmlPerbaikanRingan = !empty($row->jmlperbaikanringan)?$row->jmlperbaikanringan:0;
                    $jmlPerbaikanBerat = !empty($row->jmlperbaikanberat)?$row->jmlperbaikanberat:0;
                    $jmlPerbaikan = !empty($row->jmlperbaikan)?$row->jmlperbaikan:0;

                    $kondisi = 'normal';

                    if ($kodeBobot == 'B3') {
                        $kondisi = !empty($arrKondisi[$row->id])?$arrKondisi[$row->id]:'normal';
                    }

                    // rata-rata
                    switch ($kodeBobot) {
                        case 'B1':
                            $rata = EvaluasiAset::getB1($kodeBobot,
                                $umurBerjalan, 
                                $jmlPerawatan, 
                                $jmlPerbaikanRingan,
                                $jmlPerbaikanBerat);
                            break;
                        case 'B2':
                            $rata = EvaluasiAset::getB2($umurBerjalan, 
                                $jmlPerawatan, 
                                $jmlPerbaikan);
                            break;
                        case 'B3':
                            $rata = EvaluasiAset::getB3($kondisi, $umurBerjalan);
                            break;
                        case 'B4':
                            $rata = EvaluasiAset::getB4($umurBerjalan, 
                                $jmlPerawatan, 
                                $jmlPerbaikan);
                            break;
                    }
                    /*if ($row->id == '7524') {
                        dd('test');
                        EvaluasiAset::getB1($kodeBobot,
                                $umurBerjalan, 
                                $jmlPerawatan, 
                                $jmlPerbaikanRingan,
                                $jmlPerbaikanBerat, $row->id);
                    }*/

                    /*if ($row->id =="14946") {
                        dd($rata);
                    }*/
                    $rata = round($rata, 2);

                    // hasil akhir
                    switch ($kodeBobot) {
                        case 'B1':
                            $hasil = EvaluasiAset::getHasilB1($rata);
                            break;
                        case 'B2':
                            $hasil = EvaluasiAset::getHasilB2($rata);
                            break;
                        case 'B3':
                            $hasil = EvaluasiAset::getHasilB3($rata);
                            break;
                        case 'B4':
                            $hasil = EvaluasiAset::getHasilB4($rata);
                            break;
                    }    
                } else {
                    $umurEkonomis = "-";   
                    $umurBerjalan = "-";   
                    $jmlPerawatan = "-";   
                    $jmlPerbaikan = "-";   
                    $jmlPerbaikanRingan = "-";   
                    $jmlPerbaikanBerat = "-";   
                    $rata = "-";   
                    $hasil = "-";   
                }
                

                $result[] = [
                    'no' => $no,
                    'nama_aset' => $row->nama_aset,
                    'kode_aset' => $row->kode_aset,
                    'umurEkonomis' => $umurEkonomis,
                    'umurBerjalan' => $umurBerjalan,
                    'jmlPerawatan' => $jmlPerawatan,
                    'jmlPerbaikan' => $jmlPerbaikan,
                    'jmlPerbaikanRingan' => $jmlPerbaikanRingan,
                    'jmlPerbaikanBerat' => $jmlPerbaikanBerat,
                    'rata' => $rata,
                    'hasil' => $hasil
                ];
                $no++;
            }

            $tanggal = date("d F Y", strtotime($start)).' s/d '.date("d F Y", strtotime($end));
// dd($data);
            $pdf = PDF::loadView('pages.laporan.pembobotan', ['data' => $result, 'tanggal' => $tanggal]);

            return $pdf->stream('pembobotan.pdf');
        } else {
            Session::flash('error', 'Anda belum memilih Lokasi');

            return redirect()->route('rpembobotan::rpembobotan-index');
        }
    }

    private function backup() 
    {
        $data = Aset::select('id','nama_aset', 'verify_ts', 'kode_fm', 'tahun_pasang', 'umur', 'kode_aset',
                DB::raw("(SELECT count('komponen_id') FROM PRW_DATA WHERE KOMPONEN_ID = aset.id and (TO_CHAR(tanggal, 'YYYYMMDD') >= $startChar AND TO_CHAR(tanggal, 'YYYYMMDD') <= $endChar) and status not in ('99', '98')) AS jmlperawatan"),
                DB::raw("(SELECT count('komponen_id') FROM PRB_DATA WHERE KOMPONEN_ID = aset.id and (TO_CHAR(tanggal, 'YYYYMMDD') >= $startChar AND TO_CHAR(tanggal, 'YYYYMMDD') <= $endChar) and status not in ('99', '98')) AS jmlperbaikan"),
                DB::raw("(SELECT count('komponen_id') FROM PRB_DATA WHERE KOMPONEN_ID = aset.id and (TO_CHAR(tanggal, 'YYYYMMDD') >= $startChar AND TO_CHAR(tanggal, 'YYYYMMDD') <= $endChar) and tingkat = 'ringan' and status not in ('99', '98')) AS jmlperbaikanringan"),
                DB::raw("(SELECT count('komponen_id') FROM PRB_DATA WHERE KOMPONEN_ID = aset.id and (TO_CHAR(tanggal, 'YYYYMMDD') >= $startChar AND TO_CHAR(tanggal, 'YYYYMMDD') <= $endChar) and tingkat = 'berat' and status not in ('99', '98')) AS jmlperbaikanberat")
                )
                ->where('instalasi_id', $instalasi)
                ->where('bagian', $request->bagian)
                ->where('kondisi_id', '<>', '12')
                // ->limit(250)
                // ->whereIn('id', $arrIdKomponen)
                ->get();
    }
}