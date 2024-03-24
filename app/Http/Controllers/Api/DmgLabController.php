<?php
namespace Asset\Http\Controllers\Api;
/* INI ADALAH LAB CONTROLLER UNTUK TESTING DAN DEVELOPMENT SCRIPT, FUNGSI, DLL */

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Ms4w;
use Asset\Models\Ms4wdev;
use Asset\Models\Ms52w;
use Asset\Models\Prw52w;
use Asset\Models\Prw4w;
use Asset\Models\Prw4wDev;
use Asset\Models\Perawatan;
use Asset\Models\Perbaikan;
use Asset\Models\PmlKeluhan;
use Asset\Models\PmlKeluhanDev;
use Asset\Models\MsPrwrutin;
use Asset\Models\Aset;
use Asset\Models\MasterJab;
use Asset\Models\MsdataPdm;
use Asset\Models\JadwalLiburPompa;
use Asset\RoleUser;
use Asset\User;
use Asset\Models\MasterFm;
use Asset\Models\Master;
use Asset\Models\JadwalLibur;
use Asset\Role as tuRoleUser;
use Asset\Models\AduanNonOperasi;
use Asset\Models\Usulan;
use Asset\Models\PermohonanSc;
use Asset\Models\PermohonanScDetail;
use Asset\Models\Barang;
use Asset\Models\KomponenDetail;
use Asset\Models\KpiSetting;
use Asset\Jabatan;
use Asset\Models\MasterKodeFm;
use Asset\Models\Kategori;
use Asset\Models\Proposal;
use Asset\Models\Role;

use Asset\Libraries\ParamCheckerDev;
use Asset\Libraries\ValidasiWo;
use Asset\Libraries\EvaluasiAset;

use League\Flysystem\Filesystem;
use League\Flysystem\Sftp\SftpAdapter;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Excel;
use DB;
use Storage;
use Image;
use DateTime;
use DateTimeZone;
use Artisan;
use Cache;
use File;

class DmgLabController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private static function getDate($week, $year)
    {
        $dto = new DateTime();
        $dto->setISODate($year, $week);
        $ret['week_start'] = $dto->format('Y-m-d');
        $dto->modify('+6 days');
        $ret['week_end'] = $dto->format('Y-m-d');
        
        return $ret;
    }

    public function index(Request $request)
    {
        // dd(spv('11401606'));
        dd(rupiah('110567050', 0, '.', ',', false));
        /*$data = Aset::with(['kategori', 'subkategori', 'subsubkategori', 'instalasi', 'lokasi', 'ruangan', 'type', 'kondisi', 'pemindahan' => function($query) {
                    $query->orderBy('id', 'DESC');
                }])
        ->where('id', '7942')
        ->get();

        foreach ($data as $row) {
            dd($row->pemindahan[0]->tgl_pindah);
        }
        dd($data);*/
    }

    public function postlab(Request $request)
    {
        $arrNip = [];
        $tmpData = Role::with('roleuser')
            ->where('name', 'ADMIN PENGOLAHAN')
            ->get();

        if (!empty($tmpData[0])) {
            if (!empty($tmpData[0]->roleuser)) {
                foreach ($tmpData[0]->roleuser as $row) {
                    $arrNip[] = $row->user_id;
                }
            }
        }
        
    }

    protected static function getPrb($period, $lokasi, $bagian, $jenis = "monitoring", $tipe = "corrective")
    {
        $prb = [];
// dd($bagian);
        DB::setDateFormat('DD-MON-YYYY');
        $sqlPrb = Perbaikan::where('tipe', $jenis)
            ->whereRaw("TO_CHAR(TANGGAL, 'YYYYMM') = $period")
            ->join('aset', 'prb_data.komponen_id', '=', 'aset.id')
            ->whereIn('aset.instalasi_id', $lokasi)
            ->whereIn('aset.bagian', $bagian)
            ->where('prb_data.metode', '<>', 'masa garansi investasi')
            ->where('status', '<>', '99');

        if ($period == "202012") {
            $sqlPrb = $sqlPrb->whereNull('petugas_catatan')
                ->whereNull('m_catatan');
        }
        
        $prb['total'] = !empty($sqlPrb->first()->jumlah)?$sqlPrb->first()->jumlah:0;

        if ($tipe == "corrective") {
            $target = 95;
            $tmp = $sqlPrb->select(DB::raw("count(prb_data.id) jumlah, SUM(
                    CASE WHEN abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   tgl_input_metode, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 48 THEN 1 
                    ELSE 0
                    END
                ) AS selesai,
                SUM(
                    CASE WHEN abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   tgl_input_metode, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > 48 THEN 1 
                    WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > 48) AND TGL_INPUT_METODE IS NULL THEN 1 
                    ELSE 0
                    END
                ) AS tidak_selesai,
                SUM(
                    CASE WHEN abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mm-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   sysdate, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 48 AND TGL_INPUT_METODE IS NULL THEN 1 
                    ELSE 0
                    END
                ) AS belum_selesai"))->first();

            $prb['total'] = !empty($tmp->jumlah)?$tmp->jumlah:0;
            $prb['selesai'] = !empty($tmp->selesai)?$tmp->selesai:0;
            $prb['tidak_selesai'] = !empty($tmp->tidak_selesai)?$tmp->tidak_selesai:0;
            $prb['belum_selesai'] = !empty($tmp->belum_selesai)?$tmp->belum_selesai:0;
        } else {
            $target = 90;
            $tmp = $sqlPrb->select(DB::raw("COUNT(prb_data.ID) AS jumlah,
            SUM(
                CASE WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   TGL_FOTO_ANALISA, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 192) AND METODE = 'internal' THEN 1
                    WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   TGL_FOTO_ANALISA, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   perkiraan, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND perkiraan IS NOT NULL THEN 1
                ELSE 0
                END
            ) AS selesai,
            SUM(
                CASE WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   TGL_FOTO_ANALISA, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > 192) AND METODE = 'internal' THEN 1
                WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > 192) AND METODE = 'internal' AND TGL_FOTO_ANALISA IS NULL THEN 1
                WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   TGL_FOTO_ANALISA, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   perkiraan, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND perkiraan IS NOT NULL THEN 1
                WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) > abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz(   perkiraan, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND TGL_FOTO_ANALISA IS NULL AND perkiraan IS NOT NULL THEN 1 
                ELSE 0
                END
            ) AS tidak_selesai,
            SUM(
                CASE WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= 192) AND METODE = 'internal' AND TGL_FOTO_ANALISA IS NULL THEN 1
                    WHEN (abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( SYSDATE, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm')))) <= abs(24*extract(day from (to_timestamp_tz(  tanggal, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm') 
                    - to_timestamp_tz( perkiraan, 'dd-mon-yyyy hh:mi:ssxff PM tzh:tzm'))))) AND METODE <> 'internal' AND TGL_FOTO_ANALISA IS NULL  AND perkiraan IS NOT NULL THEN 1
                    WHEN METODE IS NULL AND perkiraan is null THEN 1
                ELSE 0
                END
            ) AS belum_selesai"))
            // ->whereNotNull('perkiraan')
            ->first();

            $prb['total'] = !empty($tmp->jumlah)?$tmp->jumlah:0;
            $prb['selesai'] = !empty($tmp->selesai)?$tmp->selesai:0;
            $prb['tidak_selesai'] = !empty($tmp->tidak_selesai)?$tmp->tidak_selesai:0;
            $prb['belum_selesai'] = !empty($tmp->belum_selesai)?$tmp->belum_selesai:0;
        }
// dd('a');
        if ($prb['selesai'] > 0) {
            /*if ($jenis == 'aduan') {
                $raw = $prb['selesai'] / ($prb['total'] - $prb['belum_selesai']);
                dd(round($raw, 3) * 100);
            }*/
            $prb['persentase'] = round( ($prb['selesai'] / ($prb['total'] - $prb['belum_selesai'])), 3 ) * 100;
        } else {
            if ($prb['total'] > 0) {
                if ($prb['belum_selesai'] > 0 && $prb['tidak_selesai'] == 0) {
                    $prb['persentase'] = "Progress";
                } elseif ($prb['belum_selesai'] > 0 && $prb['tidak_selesai'] > 0) {
                    $prb['persentase'] = "Tdktercapai-Progress";
                } else {
                    $prb['persentase'] = "0";
                }
            } else {
                $prb['persentase'] = "-";
            }            
        } 

        if (is_numeric($prb['persentase'])) {
            if ($prb['persentase'] < $target) {
                $prb['status'] = "Tidak Tercapai";
            } else {
                $prb['status'] = "Tercapai";
            }
        } elseif ($prb['persentase'] == '-') {
            $prb['status'] = "Tercapai";
        } else {
            if ($prb['persentase'] == "Progress") {
                $prb['status'] = "Tercapai";
                $prb['persentase'] = "-";
            } elseif ($prb['persentase'] == "Tdktercapai-Progress") {
                $prb['status'] = "Tidak Tercapai";
                $prb['persentase'] = "0";
            } else {
                $prb['status'] = "-";
            }
        }

        return $prb;
    }

    private static function migrateMonitoring($aset_id, $wId)
    {
        /*$nama_aset = 'Pompa Distribusi Utara No. 5';
        $aset = Aset::where('nama_aset', $nama_aset)
            ->where('kondisi_id', '<>', '12')
            ->first();*/
        $aset = Aset::find($aset_id);
// dd($aset);
        // $wId = '165623';
        $tblFm = 'FM_'.$aset->kode_fm;
        $koloms = [];

        $sources = DB::table('FM_'.$aset->kode_fm_pr)->where('ms_4w_id', $wId)->first();
        $target = Schema::getColumnListing($tblFm);

        foreach ($target as $row) {
            if (strtolower($row) != "id") {
                $koloms[] = $row;
            }
        }
        
        foreach ($koloms as $kolom) {
            $temp = strtolower($kolom);
            $data[$kolom] = $sources->$temp;
        }
// dd($data);
        $migrate = DB::table($tblFm)->insert($data);

        dd('done');
    }

    private static function njajal($data)
    {
        return $data->where('prw_data.instalasi_id', '17')->limit(5);
    }


    private static function cekPerawatan($komponen)
    {
        $ret = true;

        $prw = Perawatan::where('komponen_id', $komponen)
            ->whereNotIn('status', config('custom.skipStatus'))
            ->count();

        $prb = Perbaikan::where('komponen_id', $komponen)
            ->whereNotIn('status', config('custom.skipStatus'))
            ->count();

        if ($prw > 0 || $prb > 0) {
            $ret =  false;
        }

        return $ret;
    }

    private static function dataLaporan()
    {
        $total = Prw4w::whereRaw("TO_CHAR(TANGGAL_MONITORING, 'YYYYMM') = '202102'")
            ->select("prw_4w.id, tanggal_monitoring, urutan_minggu")
            ->join('prw_52w', 'prw_4w.prw_52w_id', '=', 'prw_52w.id')
            ->join('aset', 'prw_52w.komponen_id', '=', 'aset.id')
            ->whereIn('aset.instalasi_id', ['16','36','37','24','25','26','27','28','29','30','31','32','33','34','35','39','61','62','63','64','67'])
            ->whereIn('aset.bagian', ['1', '2', '4'])
            ->where('prw_52w.tahun', '2021')
            ->where('prw_4w.status', '<>', '99')
            ->where('kondisi_id', '<>', '12')
            ->whereRaw("(to_number(to_char(SYSDATE,'IW')) <= urutan_minggu) AND (tanggal_selesai IS NULL AND foto IS NULL) AND TO_CHAR(TANGGAL_MONITORING, 'YYYY') = '2021'")
            ->get();
        
        foreach ($total as $row) {
            // $data[] = $row->id;

            $tgl = weeknumber($row->tanggal_monitoring);

            Prw4w::where('id', $row->id)
                ->update(['urutan_minggu' => $tgl]);
        }
        dd('test');

        // $selesai = prw4w::whereRaw("TO_CHAR(TANGGAL_MONITORING, 'YYYYMM') = '202102'")
        //     ->select("Ms_4w.id")
        //     ->join('ms_52w', 'ms_4w.ms_52w_id', '=', 'ms_52w.id')
        //     ->join('aset', 'ms_52w.komponen_id', '=', 'aset.id')
        //     ->whereIn('aset.instalasi_id', ['17', '18', '19'])
        //     ->whereIn('aset.bagian', ['1'])
        //     ->where('ms_4w.status', '<>', '99')
        //     ->whereRaw("(to_number(to_char(tanggal_selesai,'IW')) = urutan_minggu) AND tanggal_selesai IS NOT NULL")
        //     ->get();
        // foreach ($selesai as $row) {
        //     $finish[] = $row->id;
        // }
        // // dd($finish);

        // $tdkSelesai = Ms4w::whereRaw("TO_CHAR(TANGGAL_MONITORING, 'YYYYMM') = '202102'")
        //     ->select("Ms_4w.id")
        //     ->join('ms_52w', 'ms_4w.ms_52w_id', '=', 'ms_52w.id')
        //     ->join('aset', 'ms_52w.komponen_id', '=', 'aset.id')
        //     ->whereIn('aset.instalasi_id', ['17', '18', '19'])
        //     ->whereIn('aset.bagian', ['1'])
        //     ->where('ms_4w.status', '<>', '99')
        //     ->whereRaw("(tanggal_selesai IS NULL AND to_number(to_char(SYSDATE,'IW')) > urutan_minggu)")
        //     ->get();
        // foreach ($tdkSelesai as $row) {
        //     $ggl[] = $row->id;
        // }
        
        $arr = array_merge($finish, $ggl);

        $result = array_diff($data, $arr);
        $test = "";
        foreach ($result as $row) {
            $test .= "'$row',"; 
        }
        dd($test);
    }

    private static function triggerOnDev($equipment_id, $minggu)
    {
        $arrId = [];

        $data = Ms4w::onlyTrashed()
            ->koneksi52w($equipment_id, date('Y'))
            ->where('urutan_minggu', $minggu)
            ->get();
// dd($data);
        if (count($data) > 0) {
            foreach ($data as $row) {
                $arrId[] = $row->id;
            }

            Ms4wdev::onlyTrashed()
                ->whereIn('id', $arrId)
                ->restore();
        }

        // JADWAL LIBUR OFF
        $weekMonth = getWeekInMonth($minggu); //list minggu dalam 1 bulan

        // Jadwal libur
        $jadwalLibur = JadwalLiburPompa::minggu($equipment_id);
        $arrJadwal = !empty($jadwalLibur)?explode(",", $jadwalLibur->minggu):[];

        // menyamakan value jadwal libur dan list minggu dalam bulan tsb
        $weekJadwal = array_intersect($weekMonth, $arrJadwal);

        $data = Prw4w::koneksi52w($equipment_id, date('Y'))
            ->where('urutan_minggu', $minggu)
            ->get();
        // dd($data);
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

                    Prw4w::whereIn('id', $arrIdRmv)->update([
                        'urutan_minggu' => $geser
                    ]);
                }
            }
        }
    }

    private static function triggerOffDev($equipment_id, $minggu)
    {
        // JADWAL KERJA OFF
        $weekMonth = getWeekInMonth($minggu); //list minggu dalam 1 bulan

        // Jadwal Kerja
        $jadwalKerja = JadwalLibur::minggu($equipment_id);
        $arrJadwal = !empty($jadwalKerja)?explode(",", $jadwalKerja->minggu):[];

        // menyamakan value jadwal kerja dan list minggu dalam bulan tsb
        $weekJadwal = array_intersect($weekMonth, $arrJadwal);

        $data = Ms4wdev::koneksi52w($equipment_id, date('Y'))
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

            $del = Ms4wdev::find($row->id);
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
                $cekMonitoring = Ms4wdev::koneksi52w($equipment_id, date('Y'))
                    ->whereIn('komponen_id', $arrKomponenRmv)                
                    ->where('urutan_minggu', $geser)
                    ->get(); 

                // cek monitoring yg ada d minggu geser
                if (count($cekMonitoring) == 0) {
                    Ms4wdev::withTrashed()
                        ->whereIn('id', $arrIdRmv)
                        ->restore();

                    Ms4wdev::whereIn('id', $arrIdRmv)->update([
                        'urutan_minggu' => $geser
                    ]);
                }
            }
        }
        // END JADWAL KERJA OFF


        // JADWAL LIBUR ON
        $arrId = [];

        $data = Prw4wDev::onlyTrashed()
            ->koneksi52w($equipment_id, date('Y'))
            ->where('urutan_minggu', $minggu)
            ->get();

        if (count($data) > 0) {
            foreach ($data as $row) {
                $arrId[] = $row->id;
            }

            Prw4wDev::onlyTrashed()
                ->whereIn('id', $arrId)
                ->restore();
        }
        // END JADWAL LIBUR ON
    }

    private static function switchkan($swithTo, $minggu)
    {
        DB::beginTransaction();

        try{
            $equipment_id = '15346';
            $switch_to = $swithTo;
            // $minggu = $request->minggu;

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

            if ($switch_to == 1) {
                // d minggu tsb
                // cek 4w ada jadwal(turunan 52w) atau tidak
                    // kalo ada dan aktif skip,, kalo ada tpi non aktif direstore
                // kalo tidak ada skip

                // cek wo yg dihapus(softdeletes) d minggu sebelumnya 
                self::triggerOn($equipment_id, $minggu);
            } else {
                // cek 4w ada jadwal(turunan 52w) atau tidak
                    // kalo ada dan aktif softdelete dan cek minggu selanjutnya
                        // kalo minggu slanjutnya ada jadwal(turunan 52w) skip
                        // kalo tidak ada update urutan_minggu di row saat ini menjadi minggu slanjutnya
                    // kalo ada dan tidak aktif skip
                // kalo tidak ada skip
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

    public function pemisahanForm()
    {
        $kodeFm = "M13";

        $data = MasterFm::where('kode_fm', $kodeFm)
            /*->whereIn('recid', 
            ['575',
                '612','613','614','615','616','617','618','619','620','621','622','623',
                '624', '625'
            ])*/
            ->whereNotIn('recid', ['1906','1907','1908','1909','1910','1911','1912','1913','1914','1915','1916','1917'])
            ->orderBy('recid')
            ->get();

        $nextFm = "M13C";
        foreach ($data as $row) {
            $tmp = new MasterFm();

            $tmp->kode_fm = $nextFm;
            $tmp->pengukuran = $row->pengukuran;
            $tmp->dropdown = $row->dropdown;
            $tmp->nama_field = $row->nama_field;
            $tmp->tipe = $row->tipe;
            $tmp->param = $row->param;
            $tmp->waspada = $row->waspada;
            $tmp->bahaya = $row->bahaya;
            $tmp->aktif = $row->aktif;
            $tmp->required = $row->required;

            $tmp->save();
        }

        dd("Selesai");
    }
    /*public function index(Request $request)
    {
        $urutan_minggu = 5;
        $tahun = date('Y');

        $arrWeek = self::getWeek();
        dd($arrWeek);

        $arrData = self::getData(4);

        foreach ($arrData as $row) {
            $urutanMinggu = $urutan_minggu;

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
            $tgl_monitor->setISODate($tahun, $urutan_minggu);
            // dd($tgl_monitor->format('Y-m-d'));
            // End Tanggal Monitoring

            if (isset($row['ms_4w_id'])) {
                if ($row['ms_4w_id']!="") {
                    $data = Ms4w::where('id', $row['ms_4w_id'])
                        ->update([
                            'ms_52w_id' => $row['ms_52w_id'],
                            'urutan_minggu' => $urutan_minggu,
                            'hari' => isset($row['hari'])?strtoupper($row['hari']):"",
                            'petugas' => isset($row['petugas'])?strtoupper($row['petugas']):"",
                            'status' => 0,
                            'tanggal' => date('Y-m-d'),
                            'tanggal_monitoring' => $tgl_monitor->format('Y-m-d')
                    ]);
                } else {
                    $data = new Ms4w();

                    $data->ms_52w_id = $row['ms_52w_id'];
                    $data->urutan_minggu = $urutan_minggu;
                    $data->hari = isset($row['hari'])?strtoupper($row['hari']):"";
                    $data->petugas = isset($row['petugas'])?strtoupper($row['petugas']):"";
                    $data->status = 0;
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
    }*/

    public static function getWeek()
    {
        // $curWeek = date('W');
        $curWeek = 4;
        $week = [];

        if ($curWeek == '52') {
            $b_atas = (int)0;
            $b_bawah = (int)4;
        } else {
            $b_atas = (int)floor($curWeek / 4) * 4;
            $b_bawah = (int)floor($curWeek / 4) + 1;
            $b_bawah *= 4;
        }

        if ($curWeek % 4 == 0) {
            if ( lastWeekNumber() == '53' ) {
                $week['53'] = 53;
            }

            for ($i = $b_atas + 1; $i <= $b_bawah; $i++) { 
                $week[$i] = $i;
            }
        }

        return $week;
    }

    public static function getData($week)
    {
        $tahun = date('Y');

        $weekMin = $week-1;

        $lokasi = "19";
        $bagian = "2";

        // in case using jadwal kerja
        // DB::connection()->enableQueryLog();
        $komponen = Ms52w::select('ms_52w.*', 'aset.nama_aset', 'aset.kode_barcode', 'aset.kode_fm', 'aset.id as aset_id', 'ms_4w.id as ms_4w_id', 'ms_4w.ms_52w_id', 'ms_4w.urutan_minggu', 'ms_4w.hari', 'ms_4w.petugas', 'ms_4w.foto_lokasi')
                    ->join('aset', 'ms_52w.komponen_id', '=', 'aset.id')
                    ->join('ms_datapdm', 'ms_52w.komponen_id', '=', 'ms_datapdm.komponen_id')
                    ->leftJoin('ms_4w', 'ms_52w.id', '=', DB::raw("ms_4w.ms_52w_id and ms_4w.urutan_minggu = $week"))
                    ->where('ms_52w.tahun', $tahun)
                    // ->where('aset.instalasi_id', $lokasi)
                    ->where('aset.kondisi_id', '<>', '12');

        // Check Admin for bagian
        /*if (namaRole() == 'Super Administrator') {
            $komponen = $komponen->whereIn('aset.bagian', [$bagian]);
        } else {
            $komponen = $komponen->whereIn('aset.bagian', bagian());
        }*/
        $komponen = $komponen->whereIn('aset.bagian', ['1', '2', '3', '4']);

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
        $komponen = $komponen->get();

        // in case not using jadwal kerja
        $komponenSecond = Ms52w::select('ms_52w.*', 'aset.nama_aset', 'aset.kode_barcode', 'aset.kode_fm', 'aset.id as aset_id', 'ms_4w.id as ms_4w_id', 'ms_4w.ms_52w_id', 'ms_4w.urutan_minggu', 'ms_4w.hari', 'ms_4w.petugas', 'ms_4w.foto_lokasi')
                ->join('aset', 'ms_52w.komponen_id', '=', 'aset.id')
                ->join('ms_datapdm', 'ms_52w.komponen_id', '=', 'ms_datapdm.komponen_id')
                ->leftJoin('ms_4w', 'ms_52w.id', '=', DB::raw("ms_4w.ms_52w_id and  ms_4w.urutan_minggu = $week"))
                ->where('ms_52w.tahun', $tahun)
                // ->where('aset.instalasi_id', $lokasi)
                ->where('aset.kondisi_id', '<>', '12')
                ->whereRaw('(mod(ms_52w.minggu_mulai,SUBSTR(ms_datapdm.nilai,2,2)) = mod('.$week.',SUBSTR(ms_datapdm.nilai,2,2)))');
                
        // Check Admin for bagian
        /*if (namaRole() == 'Super Administrator') {
            $komponenSecond = $komponenSecond->whereIn('aset.bagian', [$bagian]);
        } else {
            $komponenSecond = $komponenSecond->whereIn('aset.bagian', bagian());
        }*/
        $komponenSecond = $komponenSecond->whereIn('aset.bagian', ['1', '2', '3', '4']);

        $komponenSecond = $komponenSecond->get();
        // END in case not using jadwal kerja

        // merge
        $merged = $komponenSecond->merge($komponen);
        $resultkomponen = $merged->all();

        /*$arrKomponen = [];
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
        // End Pml Keluhan--------------------------------

        $jmlKomponen = count($resultkomponen);

        return $resultkomponen;
    }

    public static function store()
    {

    }

    public static function convertDateTime($date, $format = 'Y-m-d H:i:s')
    {
        $tz1 = 'UTC';
        $tz2 = 'Asia/Jakarta'; // UTC +7

        $d = new DateTime($date, new DateTimeZone($tz1));
        $d->setTimeZone(new DateTimeZone($tz2));

        return $d->format($format);
    }
}
