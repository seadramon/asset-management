<?php

namespace Asset\Console\Commands;

use Illuminate\Console\Command;

use Asset\Models\Prw4w;

use Asset\Models\Prw52w;
use Asset\Models\JadwalLibur;
use Asset\Models\JadwalLiburPompa;

use Asset\Models\Perawatan;
use Asset\Models\Perbaikan;

use DB;

class RemoveDuplicatePrw extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prwrutin:removeduplicate {minggu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove Duplicate Perawatan Rutin';

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

        $this->info ("Listing test\n");

        $urutanMgg = $this->argument('minggu');

        $data = Prw4w::select(DB::raw("prw_52W_ID, COUNT(prw_4w.ID) AS jml, listagg(prw_4w.ID, ',') as ids"))
            ->join('prw_52w', 'prw_4w.prw_52W_ID', '=', 'prw_52w.id')
            ->join('aset', 'prw_52w.komponen_id', '=', 'aset.ID')
            // ->where('aset.INSTALASI_ID', '19')
            ->where('aset.KONDISI_ID', '<>', '12')
            ->where('prw_52W.TAHUN', date('Y'))
            ->where('prw_4w.URUTAN_MINGGU', $urutanMgg)
            // ->where('aset.BAGIAN', '2')
            ->groupBy('prw_52W_ID')
            ->having(DB::raw('count(prw_4w.ID)'), '>', 1)
            ->get();

        $totalFiles = sizeof($data);
    // dd($totalFiles);
        $bar = $this->output->createProgressBar($totalFiles);
        $bar->start();

        // Lets Loop
        foreach ($data as $row) {
            // dd($row);
            $arrId = explode(",", $row->ids);

            $foo = Prw4w::where('prw_52w_id', $row->prw_52w_id)
                ->where('urutan_minggu', $urutanMgg)
                ->whereIn('id', $arrId);
    // dd($foo->get());
            /*$done = $foo->where('status', '1')
                ->whereNotNull('foto_lokasi')
                ->first();*/
            $done = $foo->whereNotNull('foto_lokasi')
                ->whereNotNull('tanggal_selesai')
                ->first();

            if ($done) {
                $foobar = Prw4w::where('prw_52w_id', $row->prw_52w_id)
                    ->where('urutan_minggu', $urutanMgg)
                    ->whereIn('id', $arrId)
                    ->where('id', '<>', $done->id)->get();

                if (count($foobar) > 0) {
                    foreach ($foobar as $foo) {
                        $del = Prw4w::find($foo->id);
                        $del->delete();
                    }
                }
            }else{
                // keep one
                $keep = Prw4w::where('prw_52w_id', $row->prw_52w_id)
                    ->where('urutan_minggu', $urutanMgg)
                    ->whereIn('id', $arrId)
                    ->orderBy('id', 'desc')
                    ->take(1)
                    ->pluck('id');
    
                $foobar = Prw4w::where('prw_52w_id', $row->prw_52w_id)
                    ->where('urutan_minggu', $urutanMgg)
                    ->whereIn('id', $arrId)
                    ->where('id', '<>', $keep)->get();       
                
                // delete one
                $arrTemp = [];
                if (count($foobar) > 0) {
                    foreach ($foobar as $foo) {
                        $del = Prw4w::find($foo->id);
                        $del->delete();
                    }
                }
            } 

            $bar->advance();
        }
        // End Loop

        $bar->finish ();               
        unset ($bar);
    }
}
