<?php

namespace Asset\Console\Commands;

use Illuminate\Console\Command;

use Asset\Models\Ms4w;
use Asset\Models\Ms52w;
use Asset\Models\PmlKeluhan;

class MsTahunan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ms:tahun {tahun}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat duplikat MS 52 Week sesuai tahun';

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

        /*if (empty($this->argument('tahun'))){
            dd('a')
        }*/

        $this->info ("Listing data from database\n");
        $query = Ms52w::where('tahun', '2021');

        $data = $query->get();
        $total = $query->count();

        $bar = $this->output->createProgressBar ($total);
        $bar->start();

        foreach ($data as $row) {
            $tmpCek = Ms52w::where([
                'tahun', '=', $this->argument('tahun'),
                'instalasi_id', '=', $row->instalasi_id,
                'equipment_id', '=', $row->equipment_id,
                'komponen_id', '=', $row->komponen_id
            ])->count();

            if ($tmpCek == 0) {
                $store = new Ms52w();

                $store->tahun = $this->argument('tahun');
                $store->instalasi_id = $row->instalasi_id;
                $store->equipment_id = $row->equipment_id;

                $store->komponen_id  = $row->komponen_id;
                $store->minggu_mulai = isset($row->minggu_mulai)?$row->minggu_mulai:0;
                $store->jumlah_orang = isset($row->jumlah_orang)?$row->jumlah_orang:0;
                $store->total_durasi = isset($row->total_durasi)?$row->total_durasi:0;

                $store->save();
            }

            $bar->advance ();
        }

        $bar->finish ();               
        unset ($bar);
    }
}
