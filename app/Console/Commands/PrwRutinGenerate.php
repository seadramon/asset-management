<?php

namespace Asset\Console\Commands;

use Illuminate\Console\Command;

use Asset\Models\Template,
    Asset\Models\Komponen,
    Asset\Models\KomponenDetail,
    Asset\Models\Instalasi,
    Asset\Models\Aset,
    Asset\Models\JadwalLiburPompa,
    Asset\Models\Prw52w,
    Asset\Models\Prw4w;
    // Asset\Models\Prw4w;

use DB;

class PrwRutinGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prwrutin:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Schedule Perawatan Rutin for 4 weeks';

    private static $dev = false;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    var $woPrw = array();
    var $woScJml = array();

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('max_execution_time', 0);
        set_time_limit(0);

        $tahun = date('Y');

        $arrWeek = self::getWeek();
// dd($arrWeek);
        if (count($arrWeek) > 0) {
            foreach ($arrWeek as $weeknya) {

                $this->info ("Listing Monitoring Minggu ke ".$weeknya."\n");

                $urutan_minggu = $weeknya;

                $arrData = self::getData($weeknya);

                $totalFiles = sizeof($arrData);
// dd($totalFiles);
                $bar = $this->output->createProgressBar($totalFiles);
                $bar->start();

                $asetBatch = null;
                $sequence = DB::getSequence();
                // start loop
                foreach ($arrData as $row) {
// dd($row);
                    $urutanMinggu = $urutan_minggu;

                    // Tanggal Monitoring
                    $tgl_monitor = new \DateTime();
                    $tgl_monitor->setISODate($tahun, $urutan_minggu);
                    // End Tanggal Monitoring

                    // asetBatch // generate WO ID
                    if (!empty($row->wo_id)) {
                        $woId = $row->wo_id; 
                    }

                    if (empty($asetBatch)) {
                        $asetBatch = $row->komponen->id;
                        $petugas = isset($row->petugas)?strtoupper($row->petugas):"";

                        if (empty($row->wo_id)) $woId = $sequence->nextValue('PRW_4W_DEV_WO_ID_SEQ');
                    }

                    if ($row->komponen->id != $asetBatch) {
                        $asetBatch = $row->komponen->id;
                        $petugas = isset($row->petugas)?strtoupper($row->petugas):"";

                        if (empty($row->wo_id)) $woId = $sequence->nextValue('PRW_4W_DEV_WO_ID_SEQ');
                    }
                    // end asetBatch

                    if ($row->prw_4w_id!="") {
                        /*$data = Prw4w::find($row->prw_4w_id);

                        $data->petugas = $petugas;
                        $data->tanggal = date('Y-m-d');
                        $data->tanggal_monitoring = $tgl_monitor->format('Y-m-d');
// dd($data);
                        $data->save();
                        DB::commit();*/
                    } else {
                        $cek = Prw4w::where('prw_52w_id', $row->id)
                            ->where('urutan_minggu', $urutan_minggu)
                            ->count();

                        if ($cek == 0) {
                            $data = new Prw4w();

                            $data->prw_52w_id = $row->id;
                            $data->urutan_minggu = $urutan_minggu;
                            $data->hari = isset($row->hari)?strtoupper($row->hari):"";
                            $data->petugas = $petugas;
                            $data->tanggal = date('Y-m-d');
                            $data->tanggal_monitoring = $tgl_monitor->format('Y-m-d');
                            // $data->manajer = manajer(\Auth::user()->userid);

                            $data->wo_id = $woId;
                            $row->wo_id = $woId;

                            $data->status = '0';

                            // kode wo
                            $prw52w = Prw52w::find($row->id);
                            // dd($row->id);
                            $urutnya = prwRutinUrutan();
                            $gen = generateKodeWo('prwRutin',
                                $urutnya,
                                $prw52w->komponen->bagian,
                                $prw52w->instalasi_id,
                                date('Y-m-d')
                            );
                            $data->kode_wo = $gen;
                            $data->urutan = $urutnya;
                            // kode wo

                            $data->save();
                            DB::commit();
                        }
                    }

                    $bar->advance();
                }
                // end loop

                $bar->finish();               
                unset ($bar);
            }
        }
    }

    public static function getWeek()
    {
        $week = [];
        $curWeek = date('W');
        // $curWeek = 8;

        if ($curWeek == '52') {
            $b_atas = (int)0;
            $b_bawah = (int)4;
        } else {
            $b_atas = (int)floor($curWeek / 4) * 4;
            $b_bawah = (int)floor($curWeek / 4) + 1;
            $b_bawah *= 4;
        }

        if ($curWeek % 4 == 0) {
            for ($i = $b_atas + 1; $i <= $b_bawah; $i++) { 
                $week[$i] = $i;
            }
        }        

        return $week;
    }

    public static function getData($week)
    {
        $tahun = date('Y');

        $tbMonitor = "prw_4w";

        if (static::$dev == true) {
            $tbMonitor = "prw_4w_dev";
        }
        
        if ($tahun == "") {
            $tahun = date('Y');
        }
        $weekMin = $week-1;

        // in case NOT using jadwal libur
        $komponenSecond = Prw52w::select('prw_52w.*', 'aset.nama_aset', 'aset.kode_barcode', 'aset.kode_fm', 'aset.id as aset_id', 'prw_4w.id as prw_4w_id', 'prw_4w.prw_52w_id', 'prw_4w.urutan_minggu', 'prw_4w.hari', 'prw_4w.petugas', 'prw_4w.wo_id', 'ms_komponen_detail.part', 'ms_komponen_detail.id as part_id', 'prw_rutin_pdm.id as pdm_id')
                ->join('aset', 'prw_52w.komponen_id', '=', 'aset.id')
                ->join('prw_rutin_pdm', 'prw_52w.komponen_id', '=', DB::raw("prw_rutin_pdm.komponen_id and prw_52w.part = prw_rutin_pdm.kode_part and prw_rutin_pdm.perawatan = prw_52w.perawatan"))
                // ->join('prw_rutin_pdm', 'prw_52w.komponen_id', '=', 'prw_rutin_pdm.komponen_id')
                ->leftJoin('prw_4w', 'prw_52w.id', '=', DB::raw("prw_4w.prw_52w_id and prw_4w.urutan_minggu = $week"))
                // ->join('ms_komponen_detail', 'prw_52w.part', '=', 'ms_komponen_detail.id')
                ->join('ms_komponen_detail', 'prw_rutin_pdm.kode_part', '=', 'ms_komponen_detail.id')
                ->where('prw_52w.tahun', $tahun)
                ->where('aset.kondisi_id', '<>', '12')
                ->whereRaw('(mod(prw_52w.minggu_mulai,SUBSTR(prw_rutin_pdm.nilai,2,2)) = mod('.$week.',SUBSTR(prw_rutin_pdm.nilai,2,2)))')
                ->whereNull('prw_rutin_pdm.deleted_at');
         // END:in case NOT using jadwal libur

        $resultkomponen = $komponenSecond->get();

        $jmlKomponen = count($resultkomponen);

        return $resultkomponen;
    }
}
