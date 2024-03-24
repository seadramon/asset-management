<?php

namespace Asset\Console\Commands;

use Illuminate\Console\Command;

use Asset\Models\Ms4w;

use Asset\Models\Ms52w;
use Asset\Models\JadwalLibur;

use Asset\Models\Perawatan;
use Asset\Models\Perbaikan;

use DB;

class removeDuplicateMonitoring extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:removeduplicate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove Duplicate Monitoring';

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

        $urutanMgg = '9';
// dd($urutanMgg);
        $data = Ms4w::select(DB::raw("MS_52W_ID, COUNT(MS_4w.ID) AS jml, listagg(MS_4w.ID, ',') as ids"))
            ->join('ms_52w', 'MS_4w.MS_52W_ID', '=', 'ms_52w.id')
            ->join('aset', 'ms_52w.komponen_id', '=', 'aset.ID')
            // ->where('aset.INSTALASI_ID', '19')
            ->where('aset.KONDISI_ID', '<>', '12')
            ->where('MS_52W.TAHUN', date('Y'))
            ->where('ms_4w.URUTAN_MINGGU', $urutanMgg)
            // ->where('aset.BAGIAN', '2')
            ->groupBy('MS_52W_ID')
            ->having(DB::raw('count(MS_4w.ID)'), '>', 1)
            ->get();
// dd($data);
        $totalFiles = sizeof($data);
    // dd($totalFiles);
        $bar = $this->output->createProgressBar($totalFiles);
        $bar->start();

        // Lets Loop
        foreach ($data as $row) {
            // dd($row);
            $arrId = explode(",", $row->ids);

            $foo = Ms4w::where('ms_52w_id', $row->ms_52w_id)
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
                $foobar = Ms4w::where('ms_52w_id', $row->ms_52w_id)
                    ->where('urutan_minggu', $urutanMgg)
                    ->whereIn('id', $arrId)
                    ->where('id', '<>', $done->id)->get();

                if (count($foobar) > 0) {
                    foreach ($foobar as $foo) {
                        $del = Ms4w::find($foo->id);
                        $del->delete();
                    }
                }
            }else{
                // keep one
                $keep = Ms4w::where('ms_52w_id', $row->ms_52w_id)
                    ->where('urutan_minggu', $urutanMgg)
                    ->whereIn('id', $arrId)
                    ->orderBy('id', 'desc')
                    ->take(1)
                    ->pluck('id');
    
                $foobar = Ms4w::where('ms_52w_id', $row->ms_52w_id)
                    ->where('urutan_minggu', $urutanMgg)
                    ->whereIn('id', $arrId)
                    ->where('id', '<>', $keep)->get();       
                
                // delete one
                $arrTemp = [];
                if (count($foobar) > 0) {
                    foreach ($foobar as $foo) {
                        $del = Ms4w::find($foo->id);
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
