<?php

namespace Asset\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Asset\Models\Prw4w;
use Asset\Models\Ms4w;
use Asset\Models\Prw52w;
use Asset\Models\PrwrutinPdm;
use Asset\Models\JadwalLiburPompa;
use Asset\Models\LogArtisan;
use Asset\Models\PermohonanScDetail;
use Asset\Models\Perbaikan;
use Asset\Models\Perawatan;
use Asset\Models\AduanNonOperasi;
use Asset\Models\Usulan;
use Asset\Models\Proposal;
use Asset\Models\Aset;


use Storage;
use DB;
use Excel;

class CustomTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pindah:dir';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat dan memasukkan ke direktori';

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
        $arr = [];

        $this->info ("Listing test\n");

        $arrData = Proposal::get();
        
        $totalFiles = sizeof($arrData);

        $bar = $this->output->createProgressBar($totalFiles);
        $bar->start();

        $tmpProposal = [];

        foreach ($arrData as $row) {
            switch (true) {
                case !empty($row->prw_data_id):
                    $data = Perawatan::find($row->prw_data_id);
                    break;
                case !empty($row->prb_data_id):
                    $data = Perbaikan::find($row->prb_data_id);
                    break;
                case !empty($row->aduan_non_op_id):
                    $data = AduanNonOperasi::find($row->aduan_non_op_id);
                    break;
                case !empty($row->usulan_id):
                    $data = Usulan::find($row->usulan_id);
                    break;
            }

            // $data->proposal_id = $row->id;
            $tmpProposal = [
                'spv' => $data->spv,
                'nip_spv' => $data->nip_spv,
            ];

            // $data->save();

            Proposal::where('id', $row->id)->update($tmpProposal);

            $bar->advance();
        }

        $bar->finish (); 
    }

    private static function getStatus($model)
    {
        $edit = '';
        switch (true) {
            case ($model->status == '0' && empty($model->petugas_id)):
                $edit = 'Baru';
                break;
            case ($model->status == '0' && !empty($model->petugas_id)):
                $edit = 'Investigasi';
                break;
            case ($model->status == '1' && !empty($model->tgl_foto_investigasi)):
                $edit = 'Sudah diinvestigasi';
                break;
            case ($model->status == '1.1' && $model->bagian->id != '3'):
                $edit = 'Menunggu Approval Manajer Pemeliharaan';
                break;
            case ($model->status == '1.1' && $model->bagian->id == '3'):
                $edit = 'Menunggu Approval Manajer TSI';
                break;
            case ($model->status == '3.1' && $model->bagian->id != '3'):
                $edit = 'Revisi Input Metode dari Manajer Pemeliharaan';
                break;
            case ($model->status == '3.1' && $model->bagian->id == '3'):
                $edit = 'Revisi Input Metode dari Manajer TSI';
                break;
            case ($model->status == '1.2' && $model->metode == 'eksternal emergency'):
                $edit = 'Menunggu Approval Manajer DalOps(Optional)';
                break;
            case ($model->status == '1.2' && $model->metode != 'eksternal emergency'):
                $edit = 'Menunggu Approval Manajer DalOps';
                break;
            case ($model->status == '4.0'):
                $edit = 'Proses DED (Baru)';
                break;
            case ($model->status == '4.1'):
                $edit = 'Proses DED (Proses)';
                break;
            case ($model->status == '4.2'):
                $edit = 'Proses DED (Revisi)';
                break;
            case ($model->status == '4.3'):
                $edit = 'Proses DED (Selesai)';
                break;
            case ($model->status == '2' && empty($model->foto)):
                $edit = 'Penanganan';
                break;
            case ($model->status == '3.2'):
                $edit = 'Revisi Input Metode dari Penanganan';
                break;
            case ($model->status == '3.3'):
                $edit = 'Revisi Input Metode dari Manajer DalOps';
                break;
            // case ($model->status == '2' && (!empty($model->foto) && empty($model->approve_dalops))):
            //     $edit = 'Menunggu Approval Manajer DalOps';
            //     break;
            case ($model->status == '2' && (!empty($model->foto) && empty($model->approve_dalops)) && ($model->metode != "internal" || count($model->sukucadang) > 0)):
                $edit = 'Menunggu Approval Manajer DalPro';
                break;
            case ($model->status == '2' && (!empty($model->foto) && empty($model->approve_dalops)) && ($model->metode == "internal" && count($model->sukucadang) < 1)):
                $edit = 'Closing';
                break;
            case ($model->status == '2' && (!empty($model->foto) && !empty($model->approve_dalops))):
                $edit = 'Sudah ditangani';
                break;
            case $model->status == '10':
                $edit = 'Selesai';
                break;
        }
        return $edit;
    }
}
