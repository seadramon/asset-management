<?php

namespace Asset\Console\Commands;

use Illuminate\Console\Command;

use Asset\Models\Ms4w;

use Asset\Models\Ms52w;
use Asset\Models\JadwalLibur;

use Asset\Models\Perawatan;
use Asset\Models\Perbaikan;

use DB;

class RemoveMsYear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:removeyear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove Duplicate Ms 52 week';

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

        // get duplicate 52w
        $tmps = Ms52w::select('komponen_id', DB::raw("count(*) as jml"))
            ->where('tahun', date('Y'))
            // ->where('komponen_id', '16756') // tester 
            ->groupBy('komponen_id')
            ->having(DB::raw('count(id)'), '>', 1)
            ->get();

        $totalFiles = sizeof($tmps);
    // dd($tmps);
        $bar = $this->output->createProgressBar($totalFiles);
        $bar->start();

        // Lets Loop
        foreach ($tmps as $tmp) {
            $tmpDetail = Ms52w::where('tahun', date('Y'))
                ->where('komponen_id', $tmp->komponen_id)
                ->orderBy('id', 'desc')
                ->get();
            $idyear = [];

            // collecting 52w id, dg dobel komponenid
            foreach ($tmpDetail as $row) {
                $idyear[] = $row->id;
            }
            /*dd($idyear);*/
            // 52wid yg digunankan
            $idyearUse = $idyear[0];

            // dd($idyearUse);
            // update relate 4w dg 52wid yg ditentukan diatas
            Ms4w::whereIn('ms_52w_id', $idyear)->update([
                'ms_52w_id' => $idyearUse
            ]);

            $tmpMonths = Ms4w::select('urutan_minggu', DB::raw("count(*) as jml"))
                ->where('ms_52w_id', $idyearUse)
                ->groupBy('urutan_minggu')
                ->having(DB::raw('count(id)'), '>', '1')
                ->orderBy('urutan_minggu', 'asc')
                ->get();
    // dd(sizeof($tmpMonths));
            foreach ($tmpMonths as $row) {
                $foo = Ms4w::where('ms_52w_id', $idyearUse)
                    ->where('urutan_minggu', $row->urutan_minggu);

                // jika ada status = 1
                $done = $foo->where('status', '1')
                    ->whereNotNull('foto_lokasi')
                    ->first();

                if ($done) {
                    // dd($row->urutan_minggu);
                    $foobar = Ms4w::where('ms_52w_id', $idyearUse)
                        ->where('urutan_minggu', $row->urutan_minggu)
                        ->where('id', '<>', $done->id)->get();

                    if (count($foobar) > 0) {
                        foreach ($foobar as $foo) {
                            $del = Ms4w::find($foo->id);
                            $del->delete();
                        }
                    }
                } else {
                    // dd('masuk else');
                    $keep = Ms4w::where('ms_52w_id', $idyearUse)
                        ->where('urutan_minggu', $row->urutan_minggu)
                        ->orderBy('id', 'desc')
                        ->take(1)
                        ->pluck('id');

                    $foobar = Ms4w::where('ms_52w_id', $idyearUse)
                        ->where('urutan_minggu', $row->urutan_minggu)
                        ->where('id', '<>', $keep)->get();

                    // delete one
                    $arrTemp = [];
                    if (count($foobar) > 0) {
                        foreach ($foobar as $foo) {
                            $del = Ms4w::find($foo->id);
                            $del->delete();
                        }
                    }
                    // end:delete one
                }
            }

    /*dd('wait');*/
            unset($idyear[0]);
            Ms52w::whereIn('id', $idyear)->delete();

            $bar->advance ();
        }
        // End Loop

        $bar->finish ();               
        unset ($bar);
    }
}
