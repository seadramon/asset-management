<?php

namespace Asset\Console\Commands;

use Illuminate\Console\Command;
use Asset\Models\Ms4wdev;

use Asset\Models\Ms52w;
use Asset\Models\JadwalLibur;

use Asset\Models\Perawatan;
use Asset\Models\Perbaikan;

use DB;

class MonitoringDev extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoringdev:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Schedule Monitoring for 4 weeks in development';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

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

        if (count($arrWeek) > 0) {
            foreach ($arrWeek as $weeknya) {
                $this->info ("Listing Monitoring Minggu ke ".$weeknya."\n");
                $urutan_minggu = $weeknya;

                $arrData = self::getData($weeknya);

                $totalFiles = sizeof($arrData);

                $bar = $this->output->createProgressBar($totalFiles);
                $bar->start();

                $i = 0;
                foreach ($arrData as $row) {
                    $urutanMinggu = $urutan_minggu;
                    $jadwalKerja = null;

                    if (isset($row->equipment)) {
                        if ($row->equipment == 'no' && isset($row->equipment_id)) {
                            $jadwalKerja = JadwalLibur::where('equipment_id', $row->equipment_id)->first();
                        }else{
                            $jadwalKerja = JadwalLibur::where('equipment_id', $row->aset_id)->first();
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
                    $ms_52w_id = $row->id;

                    if (isset($row->ms_4w_id)) {
                        if ($row->ms_4w_id != "") {
                            dd('editnya');
                            $data = Ms4wdev::where('id', $row->ms_4w_id)
                                ->update([
                                    'ms_52w_id' => $ms_52w_id,
                                    'urutan_minggu' => $urutan_minggu,
                                    'hari' => isset($row->hari)?strtoupper($row->hari):"",
                                    'petugas' => isset($row->petugas)?strtoupper($row->petugas):"",
                                    'status' => 0,
                                    'tanggal' => date('Y-m-d'),
                                    'tanggal_monitoring' => $tgl_monitor->format('Y-m-d')
                            ]);
                        } else {
                            dd('testB');
                            $data = new Ms4wdev();

                            $data->ms_52w_id = $ms_52w_id;
                            $data->urutan_minggu = $urutan_minggu;
                            $data->hari = isset($row->hari)?strtoupper($row->hari):"";
                            $data->petugas = isset($row->petugas)?strtoupper($row->petugas):"";
                            $data->status = 0;
                            $data->tanggal = date('Y-m-d');
                            $data->tanggal_monitoring = $tgl_monitor->format('Y-m-d');

                            // kode wo
                            $ms52w = Ms52w::find($ms_52w_id);
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
                    } else {
                        $data = new Ms4wdev();
// dd($ms_52w_id);
                        $data->ms_52w_id = $ms_52w_id;
                        $data->urutan_minggu = $urutan_minggu;
                        $data->hari = isset($row->hari)?strtoupper($row->hari):"";
                        $data->petugas = isset($row->petugas)?strtoupper($row->petugas):"";
                        $data->status = 0;
                        $data->tanggal = date('Y-m-d');
                        $data->tanggal_monitoring = $tgl_monitor->format('Y-m-d');

                        // kode wo
                        $ms52w = Ms52w::find($ms_52w_id);
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
        
        $tbMonitor = "ms_4w";
        
        // in case not using jadwal kerja
        $komponenSecond = Ms52w::select('ms_52w.*', 'aset.nama_aset', 'aset.kode_barcode', 'aset.kode_fm', 'aset.id as aset_id', 'ms_4w_dev.id as ms_4w_id', 'ms_4w_dev.ms_52w_id', 'ms_4w_dev.urutan_minggu', 'ms_4w_dev.hari', 'ms_4w_dev.petugas', 'ms_4w_dev.foto_lokasi')
                ->join('aset', 'ms_52w.komponen_id', '=', 'aset.id')
                ->join('ms_datapdm', 'ms_52w.komponen_id', '=', 'ms_datapdm.komponen_id')
                ->leftJoin('ms_4w_dev', 'ms_52w.id', '=', DB::raw("ms_4w_dev.ms_52w_id and  ms_4w_dev.urutan_minggu = $week"))
                ->where('ms_52w.tahun', $tahun)
                ->where('ms_52w.equipment_id', '15346') //dev only
                // ->where('aset.instalasi_id', $lokasi)
                ->where('aset.kondisi_id', '<>', '12')
                ->whereRaw('(mod(ms_52w.minggu_mulai,SUBSTR(ms_datapdm.nilai,2,2)) = mod('.$week.',SUBSTR(ms_datapdm.nilai,2,2)))')
                ->whereIn('aset.bagian', ['1', '2', '3', '4']);
        // END in case not using jadwal kerja

        $resultkomponen = $komponenSecond->get();

        $jmlKomponen = count($resultkomponen);

        return $resultkomponen;
    }
}
