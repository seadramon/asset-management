<?php

namespace Asset\Http\Controllers\Api;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Aset,
    Asset\Models\Ms4w,
    Asset\Models\Prw4w,
    Asset\Models\JadwalLibur,
    Asset\Models\JadwalLiburPompa;

use DB;

class JadwalPompaController extends Controller
{
    public function index(Request $request)
    {
        $equipment_id = $request->equipment_id;
        $jadwallibur = !empty($request->jadwallibur)?(bool)$request->jadwallibur:false;

        if (!$jadwallibur) {
            $query = JadwalLibur::select('jadwal_libur.*', 'aset.nama_aset', 'instalasi.id as instalasi_id', 'instalasi.name as instalasi', 'lokasi.id as lokasi_id', 'lokasi.name as lokasi')
                ->join('aset', 'jadwal_libur.equipment_id', '=', 'aset.id')
                ->join('instalasi', 'aset.instalasi_id', '=', 'instalasi.id')
                ->join('lokasi', 'aset.lokasi_id', '=', 'lokasi.id')
                ->orderBy('instalasi.name');

            if ($equipment_id !== null) {
                $query = $query->where('jadwal_libur.equipment_id', $equipment_id);    
            }
            $caption = "jadwal kerja";
        } else {
            $query = JadwalLiburPompa::select('jadwal_libur_pompa.*', 'aset.nama_aset', 'instalasi.id as instalasi_id', 'instalasi.name as instalasi', 'lokasi.id as lokasi_id', 'lokasi.name as lokasi')
                ->join('aset', 'jadwal_libur_pompa.equipment_id', '=', 'aset.id')
                ->join('instalasi', 'aset.instalasi_id', '=', 'instalasi.id')
                ->join('lokasi', 'aset.lokasi_id', '=', 'lokasi.id')
                ->orderBy('instalasi.name');

            if ($equipment_id !== null) {
                $query = $query->where('jadwal_libur_pompa.equipment_id', $equipment_id);    
            }

            $caption = "jadwal libur";
        }
        
        $result = $query->get();

        $return = ['tipeJadwal' => $caption, 'weekNow' => weekNumber(date("Y-m-d")), 'data' => $result];

        return response()->json($return)->setStatusCode(200, 'OK');
    }

    public function switch(Request $request)
    {
    	DB::beginTransaction();

    	try{
        	$equipment_id = $request->equipment_id;
        	$switch_to = $request->switch_to;
        	$minggu = $request->minggu;

        	$qKerja = JadwalLibur::select('minggu', 'id')->where('equipment_id', $equipment_id)->first();
        	$mingguKerja = $qKerja->minggu;

        	$qLibur = JadwalLiburPompa::select('minggu')->where('equipment_id', $equipment_id)->first();
        	$mingguLibur = $qLibur->minggu;
        	
        	$kerjaArr = explode(',', trim($mingguKerja, ','));
        	$liburArr = explode(',', trim($mingguLibur, ','));

        	if ($switch_to == 1) {
        		array_splice($kerjaArr, count($kerjaArr)-1, 0, $minggu);
        		if (($key = array_search($minggu, $liburArr)) !== false) {
				    unset($liburArr[$key]);
				}
        	} else {
        		array_splice($liburArr, count($liburArr)-1, 0, $minggu);
        		if (($key = array_search($minggu, $kerjaArr)) !== false) {
				    unset($kerjaArr[$key]);
				}
        	}

            $kerjaArrUnique = array_unique($kerjaArr);
            $liburArrUnique = array_unique($liburArr);
        	asort($kerjaArrUnique);
        	asort($liburArrUnique);

        	$kerja = ",".implode(',', $kerjaArrUnique).",";
        	$libur = ",".implode(',', $liburArrUnique).",";
        	
        	//dd("Kerja : ".$kerja." Libur : ".$libur);

        	$ker = JadwalLibur::where('equipment_id', $equipment_id)
		            ->update(['minggu' => $kerja]);

		    $lib = JadwalLiburPompa::where('equipment_id', $equipment_id)
		            ->update(['minggu' => $libur]);

		    DB::commit();

            /*Here trigger fire*/
            if ($switch_to == 1) {
                self::triggerOn($equipment_id, $minggu);
            } else {
                self::triggerOff($equipment_id, $minggu);
            }

		    return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Diubah'])->setStatusCode(200, 'OK');
    	} catch(Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    private static function triggerOn($equipment_id, $minggu)
    {
        // JADWAL KERJA ON
        $arrId = [];

        // RESTORE FROM DELETED_AT
        $data = Ms4w::onlyTrashed()
            ->koneksi52w($equipment_id, date('Y'))
            ->where('urutan_minggu', $minggu)
            ->get();

        if (count($data) > 0) {
            foreach ($data as $row) {
                $arrId[] = $row->id;
            }

            Ms4w::onlyTrashed()
                ->whereIn('id', $arrId)
                ->restore();
        }
        // END:RESTORE FROM DELETED_AT

        // RESTORE FROM GESER MINGGU
        $data = Ms4w::koneksi52w($equipment_id, date('Y'))
            ->where('log_geser', $minggu)
            ->get();

        if (count($data) > 0) {
            foreach ($data as $row) {
                $arrId[] = $row->id;
            }

            $tgl = getFromWeeknumber($minggu, date('Y'));

            Ms4w::whereIn('id', $arrId)
                ->whereNull('tanggal_selesai')
                ->update([
                    'urutan_minggu' => $minggu,
                    'tanggal_monitoring' => $tgl['week_start'],
                    'log_geser' => null
                ]);
        }
        // END:RESTORE FROM GESER MINGGU
        // END JADWAL KERJA ON

        // JADWAL LIBUR OFF
        $weekMonth = getWeekInMonth($minggu); //list minggu dalam 1 bulan

        // Jadwal libur
        $jadwalLibur = JadwalLiburPompa::where('equipment_id', $equipment_id)->first();
        $arrJadwal = !empty($jadwalLibur)?explode(",", $jadwalLibur->minggu):[];

        // menyamakan value jadwal libur dan list minggu dalam bulan tsb
        $weekJadwal = array_intersect($weekMonth, $arrJadwal);

        $arrKomponen = Aset::where(function ($query) use($equipment_id) {
                $query->where('equipment_id', $equipment_id)
                    ->orWhere('id', $equipment_id);
            })
            ->get()
            ->pluck('id');

        $data = Prw4w::koneksi52wDev($arrKomponen, date('Y'))
            ->where('urutan_minggu', $minggu)
            ->get();
        
        $arrKomponenRmv = [];
        $arrIdRmv = [];

        if (count($data) < 1) {
            return true;
        }

        foreach ($data as $row) {
            $arrKomponenRmv[] = $row->komponen_id;
            $arrIdRmv[] = $row->id;

            $del = Prw4w::find($row->id);
            $del->delete();
        }

        // cari next week untuk geser jadwal
        if (count($weekJadwal) > 0) {
            $maxWeekJadwal = (int)max($weekJadwal);

            $geserTmp = $minggu + 1;
            $geser = 0;
            for ($i = $geserTmp; $i <= $maxWeekJadwal; $i++) { 
                if ($geser == 0) {
                    if (in_array($i, $weekJadwal)) {
                        $geser = $i;
                    }
                }
            }
            
            if (in_array($geser, $weekMonth)) {
                $cekMonitoring = Prw4w::koneksi52w($equipment_id, date('Y'))
                    ->whereIn('komponen_id', $arrKomponenRmv)                
                    ->where('urutan_minggu', $geser)
                    ->get(); 

                // cek monitoring yg ada d minggu geser
                if (count($cekMonitoring) == 0) {
                    Prw4w::withTrashed()
                        ->whereIn('id', $arrIdRmv)
                        ->restore();

                    $tgl = getFromWeeknumber($geser, date('Y'));

                    Prw4w::whereIn('id', $arrIdRmv)
                        ->whereNull('tanggal_selesai')
                        ->update([
                            'urutan_minggu' => $geser,
                            'tanggal_monitoring' => $tgl['week_start'],
                            'log_geser' => $minggu
                        ]);
                }
            }
        }
    }

    private static function triggerOff($equipment_id, $minggu)
    {
        // JADWAL KERJA OFF
        $weekMonth = getWeekInMonth($minggu); //list minggu dalam 1 bulan

        // Jadwal Kerja
        $jadwalKerja = JadwalLibur::where('equipment_id', $equipment_id)->first();
        $arrJadwal = !empty($jadwalKerja)?explode(",", $jadwalKerja->minggu):[];

        // menyamakan value jadwal kerja dan list minggu dalam bulan tsb
        $weekJadwal = array_intersect($weekMonth, $arrJadwal);

        $data = Ms4w::koneksi52w($equipment_id, date('Y'))
            ->where('urutan_minggu', $minggu)
            ->get();
        
        $arrKomponenRmv = [];
        $arrIdRmv = [];

        if (count($data) < 1) {
            return true;
        }

        foreach ($data as $row) {
            $arrKomponenRmv[] = $row->komponen_id;
            $arrIdRmv[] = $row->id;

            $del = Ms4w::find($row->id);
            $del->delete();
        }

        // cari next week untuk geser jadwal
        if (count($weekJadwal) > 0) {
            $maxWeekJadwal = (int)max($weekJadwal);

            $geserTmp = $minggu + 1;
            $geser = 0;
            for ($i = $geserTmp; $i <= $maxWeekJadwal; $i++) { 
                if ($geser == 0) {
                    if (in_array($i, $weekJadwal)) {
                        $geser = $i;
                    }
                }
            }
            
            if (in_array($geser, $weekMonth)) {
                $cekMonitoring = Ms4w::koneksi52w($equipment_id, date('Y'))
                    ->whereIn('komponen_id', $arrKomponenRmv)                
                    ->where('urutan_minggu', $geser)
                    ->get(); 

                // cek monitoring yg ada d minggu geser
                if (count($cekMonitoring) == 0) {
                    Ms4w::withTrashed()
                        ->whereIn('id', $arrIdRmv)
                        ->restore();

                    $tgl = getFromWeeknumber($geser, date('Y'));

                    Ms4w::whereIn('id', $arrIdRmv)
                        ->whereNull('tanggal_selesai')
                        ->update([
                            'urutan_minggu' => $geser,
                            'tanggal_monitoring' => $tgl['week_start'],
                            'log_geser' => $minggu
                        ]);
                }
            }
        }
        // END JADWAL KERJA OFF


        // JADWAL LIBUR ON
        $arrId = [];

        // RESTORE FROM DELETED_AT
        $data = Prw4w::onlyTrashed()
            ->koneksi52w($equipment_id, date('Y'))
            ->where('urutan_minggu', $minggu)
            ->get();

        if (count($data) > 0) {
            foreach ($data as $row) {
                $arrId[] = $row->id;
            }

            Prw4w::onlyTrashed()
                ->whereIn('id', $arrId)
                ->restore();
        }
        // END:RESTORE FROM DELETED_AT

        // RESTORE FROM GESER MINGGU
        $data = Prw4w::koneksi52w($equipment_id, date('Y'))
            ->where('log_geser', $minggu)
            ->get();

        if (count($data) > 0) {
            foreach ($data as $row) {
                $arrId[] = $row->id;
            }

            $tgl = getFromWeeknumber($minggu, date('Y'));

            Prw4w::whereIn('id', $arrId)
                ->whereNull('tanggal_selesai')
                ->update([
                    'urutan_minggu' => $minggu,
                    'tanggal_monitoring' => $tgl['week_start'],
                    'log_geser' => null
                ]);
        }
        // END:RESTORE FROM GESER MINGGU
        // END JADWAL LIBUR ON
    }
}
