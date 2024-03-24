<?php

namespace Asset\Console\Commands;

use Illuminate\Console\Command;

use Asset\Models\Prw4w;

use DB;

class DeleteUndisposed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prwrutin:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus data yang tidak terdisposisi';

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
// dd();
        $data = Prw4w::whereNull('petugas')
            ->whereIn('urutan_minggu', get4weeks())
            ->orderBy('id')
            ->get();

        $totalFiles = sizeof($data);
    
        $bar = $this->output->createProgressBar($totalFiles);
        $bar->start();

        foreach ($data as $row) {
            // dd($row->id);
            $del = Prw4w::find($row->id);
            $del->delete();

            $bar->advance();
        }

        $bar->finish ();               
        unset ($bar);
    }
}
