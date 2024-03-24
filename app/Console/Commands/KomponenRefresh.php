<?php

namespace Asset\Console\Commands;

use Illuminate\Console\Command;

use Asset\Models\Aset;
use Asset\Models\Prw52w;
use Asset\Models\Ms52w;

use Asset\Models\LogArtisan;

use DB;

class KomponenRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'komponen:refresh {--wo=monitoring}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Komponen menyesuaikan equipment saat ini pada prw52 dan ms52';

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

        $this->info ("Listing Ms 52 weeks\n");

        $wo = $this->option('wo');

        if ($wo == "monitoring") {
            $data = Ms52w::where('tahun', date('Y'))
                ->whereRaw("equipment_id <> komponen_id")
                ->get();
        } else {
            $data = Prw52w::where('tahun', date('Y'))
                ->whereRaw("equipment_id <> komponen_id")
                ->get();
        }

        $totalFiles = sizeof($data);

        $bar = $this->output->createProgressBar($totalFiles);
        $bar->start();

        foreach ($data as $row) {
            $aset = Aset::find($row->komponen_id);

            if ( !empty($aset->equipment_id) ) {
                if ( $row->equipment_id != $aset->equipment_id ) {

                    $this->info ("Detected komponen = ".$row->komponen_id."\n");

                    /*if ($wo == "monitoring") {
                        Ms52w::where('id', $row->id)->update([
                            'equipment_id' => $aset->equipment_id
                        ]);
                    } else {
                        Prw52w::where('id', $row->id)->update([
                            'equipment_id' => $aset->equipment_id
                        ]);
                    }*/
                }
            }

            $bar->advance();
        }

        $bar->finish ();               
        unset ($bar);

        // Logger
        $log = new LogArtisan();
        $log->name = "komponen:refresh";
        $log->via = "console";

        $log->save();
        // End:Logger
    }
}
