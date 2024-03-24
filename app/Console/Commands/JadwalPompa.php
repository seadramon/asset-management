<?php

namespace Asset\Console\Commands;

use Illuminate\Console\Command;

use Asset\Models\Aset;

use Asset\Models\Ms4w;
use Asset\Models\Prw4w;

use Asset\Models\Ms52w;
use Asset\Models\Prw52w;
use Asset\Models\JadwalLibur;
use Asset\Models\JadwalLiburPompa;

use Asset\Models\Perawatan;
use Asset\Models\Perbaikan;

use Asset\Models\LogArtisan;    

use DB;

class JadwalPompa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jadwal:refresh {--equip=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Penyesuaian wo perawatan rutin dan monitoring pada jadwal pompa';

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

        $this->info ("Listing Equipments\n");

        $argMinggu = date('W');

        $optEquip = $this->option('equip');

        if (!empty($optEquip)) {
            $equipments = Aset::where('id', $optEquip)
                // ->where('bagian', '1')
                ->get();
        } else {
            $equipments = Aset::whereNull('equipment_id')
                ->where('bagian', '1')
                // ->where('id', '16826')
                ->get();    
        }
        
// dd($equipments);
        $totalFiles = sizeof($equipments);

        $bar = $this->output->createProgressBar($totalFiles);
        $bar->start();

        $res = [];

        foreach ($equipments as $equip) {

            $temp = JadwalLibur::where('equipment_id', $equip->id)->first();
// dd($temp);
            if (!empty($temp)) {
                $arr = explode(",", $temp->minggu);
                
                if ( in_array($argMinggu, $arr) ) {
                    // dd('aa');
                    self::triggerOn($equip->id, $argMinggu);
                } else {
                    self::triggerOff($equip->id, $argMinggu);
                }
            }
            
            $bar->advance();
        }

        $bar->finish ();               
        unset ($bar);

        $logger = new LogArtisan;
        $logger->name = "Jadwal Pompa";
        $logger->via = "Scheduler";

        $logger->save();
    }

    // KETIKA trigger ON => perawatan rutin / jadwal Libur OFF
    private function triggerOn($equipment_id, $minggu)
    {
        // ON kan todolist monitoring-----------------
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
        // END:ON kan todolist monitoring-------------


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
        // dd($arrKomponen);

        $data = Prw4w::koneksi52wDev($arrKomponen, date('Y'))
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

    // KETIKA trigger OFF monitoring / jadwal kerja OFF
    private function triggerOff($equipment_id, $minggu)
    {
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

        // ON kan todolist Perawatan rutin-----------------
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
        // END:ON kan todolist Perawatan rutin-------------
    }
}
