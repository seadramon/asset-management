<?php

namespace Asset\Console\Commands;

use Illuminate\Console\Command;

use Asset\Models\Prw52w;

class PrwTahunan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prwrutin:tahun {tahun}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat duplikat PRW 52 Week sesuai tahun';

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
        $query = Prw52w::where('tahun', '2021');

        $data = $query->get();
        $total = $query->count();

        $bar = $this->output->createProgressBar ($total);
        $bar->start();

        foreach ($data as $row) {
            // dd($row->perawatan);
            $tmpCek = Prw52w::where([
                'tahun', '=', $this->argument('tahun'),
                'instalasi_id', '=', $row->instalasi_id,
                'equipment_id', '=', $row->equipment_id,
                'komponen_id', '=', $row->komponen_id
            ])
            ->where('part', $row->part)
            ->where('perawatan', $row->perawatan)
            ->count();
// dd($tmpCek);
            if ($tmpCek == 0) {
                $store = new Prw52w();

                $store->tahun = $this->argument('tahun');
                $store->instalasi_id = $row->instalasi_id;
                $store->equipment_id = $row->equipment_id;
                $store->komponen_id  = $row->komponen_id;

                $store->minggu_mulai = isset($row->minggu_mulai)?$row->minggu_mulai:0;
                $store->jumlah_orang = isset($row->jumlah_orang)?$row->jumlah_orang:0;
                $store->total_durasi = isset($row->total_durasi)?$row->total_durasi:0;
                $store->part = isset($row->part)?$row->part:0;
                $store->perawatan = isset($row->perawatan)?$row->perawatan:0;
                // $store->perawatan = isset($row->perawatan)?$row->perawatan:0;

                $store->save();

            }
// dd('halt');
            $bar->advance ();
        }

        $bar->finish ();               
        unset ($bar);
    }
}
