<?php

namespace Asset\Console\Commands;

use Illuminate\Console\Command;

use Asset\Models\Perawatan,
    Asset\Models\Perbaikan,
    Asset\Models\Ms4w,
    Asset\Models\Prw4w;

use Asset\Models\LogArtisan;    

class RemoveByKondisi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'komponen:filterkondisi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Filter komponen dalam kondisi tidak dapat beroperasi';

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
        $this->info ("Listing Komponen\n");

        $urutan_minggu = date('W');

        $prb = Perbaikan::where('kondisi', 'tidak beroperasi')
            ->whereNotIn('status', config('custom.skipStatus'))
            ->get()->pluck('komponen_id')->toArray();
        $arrPrb = !empty($prb)?array_unique($prb):[];

        $prw = Perawatan::where('kondisi', 'tidak beroperasi')
            ->whereNotIn('status', config('custom.skipStatus'))
            ->get()->pluck('komponen_id')->toArray();
        $arrPrw = !empty($prw)?array_unique($prw):[];

        $woKomponen = array_unique(array_merge($arrPrw, $arrPrb));

        $totalFiles = sizeof($woKomponen);        

        $bar = $this->output->createProgressBar($totalFiles);
        $bar->start();

        foreach ($woKomponen as $komponen) {
            $monitoring = Ms4w::select('ms_4w.id, ms_52w.equipment_id, ms_52w.komponen_id')
                ->join('ms_52w', 'ms_4w.ms_52w_id', '=', 'ms_52w.id')
                ->where(function ($query) use($komponen){
                    $query->where('ms_52w.komponen_id', $komponen)
                        ->orWhere('ms_52w.equipment_id', $komponen);
                    })
                ->where('urutan_minggu', $urutan_minggu)
                ->where('tahun', date('Y'))
                ->whereNull('foto_lokasi')
                ->whereNotNull('petugas')
                ->get()
                ->pluck('id');

            if (count($monitoring) > 0) {
                foreach ($monitoring as $row) {
                    $del = Ms4w::find($row);
                    $del->delete();
                }
            }

            $prwRutin = Prw4w::select('prw_4w.id, prw_52w.equipment_id, prw_52w.komponen_id')
                ->join('prw_52w', 'prw_4w.prw_52w_id', '=', 'prw_52w.id')
                ->where(function ($query) use($komponen){
                    $query->where('prw_52w.komponen_id', $komponen)
                        ->orWhere('prw_52w.equipment_id', $komponen);
                    })
                ->where('urutan_minggu', $urutan_minggu)
                ->where('tahun', date('Y'))
                ->whereNull('foto')
                ->whereNotNull('petugas')
                ->get()
                ->pluck('id');

            if (count($prwRutin) > 0) {
                foreach ($prwRutin as $row) {
                    $del = Prw4w::find($row);
                    $del->delete();
                }
            }

            $bar->advance();
        }

        $bar->finish ();               
        unset ($bar);

        $logger = new LogArtisan;
        $logger->name = "Komponen Filter Kondisi";
        $logger->via = "Scheduler";

        $logger->save();
    }
}
