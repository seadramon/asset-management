<?php

namespace Asset\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;

use Asset\Models\KpiSetting;
use Asset\Http\Controllers\KpiController;

use Asset\Libraries\Kpi;

use DateTime;
use DB;
use PDF;
use Session;
use Validator;
use Storage;

class CloseKpi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kpi:close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Closing kpi';

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

        $this->info ("Check and Close expected Period");
        // $lastDate = date('t-m-Y 23:00:00');
        $lastDate = '2022-02-27 23:00:00'; //DEVELOPMENT/CHANGE TO DEBUG
        $now = date('Y-m-d H:i:s');

        DB::beginTransaction();
        try {
            if ( $now > $lastDate ) {

                // $cek = KpiSetting::where('periode', date('Ym'))
                $cek = KpiSetting::where('periode', '202202') //DEVELOPMENT/CHANGE TO DEBUG
                    ->whereNotNull('start')
                    ->whereNotNull('end')
                    ->where('closed', '<>', '1')
                    ->first();
// dd($cek);
                if ($cek) {
                    // Close Data KPI this period
                    $data = KpiSetting::find($cek->id);
                    $data->closed = 1;

                    $data->save();
                    
                    DB::commit();
                    // end:Close Data KPI this period

                    $this->info ("Listing and Process KPI with particular Location\n");

                    $arrLokasi = ["231 #SPV Rumah Pompa#3#029 ","080 #SPV Pemeliharaan Mekanikal & Pompa Ngagel#3#209 ","082 #SPV Pemeliharaan Elektrikal Ngagel#3#209 ","085 #SPV Pemeliharaan Sipil Ngagel#3#209 ","081 #SPV Pemeliharaan Mekanikal & Pompa Karang Pilang#3#210 ","083 #SPV Pemeliharaan Elektrikal Karang Pilang#3#210 ","086 #SPV Pemeliharaan Sipil Karang Pilang#3#210 ","218 #SPV Kontrol Digital dan Instrumentasi#3#022 "];

                    $totalFiles = sizeof($arrLokasi);

                    // Declare start end

                    /*DEVELOPMENT/CHANGE TO DEBUG*/
                    $periode = 'February-2022';
                    $formatPeriode = date("Ym", strtotime($periode));
                    /*DEVELOPMENT/CHANGE TO DEBUG*/

                    /*$periode = date('F-Y');
                    $formatPeriode = date("Ym");*/

                    $setting = KpiSetting::wherePeriode($formatPeriode)->first();
                    if ( $setting ) {
                        if ($setting->closed == '1') {
                            $start = changeDateFormat($setting->start, 'Y-m-d H:i:s', 'Ymd');
                            $end = changeDateFormat($setting->end, 'Y-m-d H:i:s', 'Ymd');
                        }
                    }
                    // end:Declare start end

                    $bar = $this->output->createProgressBar($totalFiles);
                    $bar->start();

                    foreach ($arrLokasi as $row) {
                        $arrData = Kpi::cetak($periode, $row, $start, $end);

                        $tmpBag = explode("#", $row);
                        $period = date("Ym", strtotime($periode));
                        $filename = sprintf('%d-%s.pdf', (int)$tmpBag[0], $period);

                        $pdf = PDF::loadView('pages.laporan.kpi.reportPdf', $arrData)->setPaper('a4', 'landscape');

                        $dir = 'kpi/'.$period;
                        cekDir($dir);
                        $cekFile = Storage::disk('sftp-doc')->has($dir.'/'.$filename);

                        if ( !$cekFile ) {
                            Storage::disk('sftp-doc')->put($dir.'/'.$filename, $pdf->output());
                        }

                        $bar->advance();
                    }

                    $bar->finish (); 
                }
            } else {
                dd('aaa');
            }
        } catch(Exception $e) {
            DB::rollback();
        }
    }
}
