<?php

namespace Asset\Console\Commands;

use Illuminate\Console\Command;

use Asset\Models\Aset;
use Asset\RoleUser;
use Asset\Models\MasterFm;
use Asset\Models\MasterJab;
use Asset\Models\Master;
use Asset\Models\MsPrwrutin;

use DB;
use Storage;
use Image;

class PrwRutin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prw:rutin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eksekusi ke database sesuai frekuensi perawatan rutin';

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

        $this->info ("Listing Frekuensi from database...\n");

        // Frekuensi yg masuk dalam minggu ini
        $x = weekNumber(date('Y-m-d')); //MINGGU NYA
        $y = 2; //FREKUENSI
        $factors = array();
        $factorsVal = array('W1');
        do {            
            if ( $x % $y == 0) {
                $factors[] = $y;
                $factorsVal[] = 'W'.$y;
            }
            $y++;
        }while ($y <= 100);
        //End Frekuensi 

        // List kegiatan perawatan
        $master = Master::Prwrutin()->get();
        $arrPerawatan = [];
        foreach ($master as $row) {
            $arrPerawatan[] = $row->name;
        }
        // end List kegiatan perawatan

        $arrRutin = MsPrwrutin::with(['komponen', 'part'])
            ->where(function ($query) use($factors){
                $query->where('ms_prwrutin.perawatan', 'like', '%"W1"%');
                foreach ($factors as $fq) {
                    $query->orWhere('ms_prwrutin.perawatan', 'like', '%"W'.$fq.'"%');
                }
            })
            // ->where('ms_prwrutin.komponen_id', '15348')
            ->get();
// dd($arrRutin);
        $this->info ("Preparing to Insert...\n");

        $arrData = [];
        $arrToSpv = [];
        foreach ($arrRutin as $row) {
            $prw = [];
            $arrTemp = json_decode($row->perawatan, true);
            
            foreach ($arrTemp as $key => $tmp) {
                if (in_array($tmp, $factorsVal)) {
                    $prw[] = $key;
                }
            }

            $arrData[$row->komponen_id][] = [
                'id_ms' => $row->id,
                'komponen_id' => $row->komponen_id,
                'nama_aset' => $row->komponen->nama_aset,
                'instalasi_id' => $row->komponen->instalasi_id,
                'instalasi' => $row->komponen->instalasi->name,
                'bagian_id' => $row->komponen->bagian,
                'perawatan' => json_encode($prw),
                'kode_part' => $row->kode_part,
                'kode_fm' => $row->komponen->kode_fm,
                'part' => !empty($row->part->part)?$row->part->part:""
            ];

            if (!empty($arrToSpv[$row->komponen->instalasi_id])) {
                if (!in_array($row->komponen->bagian, $arrToSpv[$row->komponen->instalasi_id])) {
                    $arrToSpv[$row->komponen->instalasi_id][] = $row->komponen->bagian;
                }
            } else {
                $arrToSpv[$row->komponen->instalasi_id][] = $row->komponen->bagian;
            }
        }
// dd($arrData);
        // Ready to insert
        $this->info ("Inserting Data...\n");
        DB::beginTransaction();
        try {
            $arrSpv = [];
            $totalData = count($arrData);

            $bar = $this->output->createProgressBar($totalData);
            $bar->start();
            // dd($arrData);
            foreach ($arrData as $row) {
                // GET SPV NIP FROM instalasi_id and bagian_id
                $temp = MasterJab::with(['tu_roleuser' => function($query) use($row){
                        $query->where('is_manajer', '1');
                        $query->with(['jabatan' => function($q) use($row){
                            $tempBag = ['1', '2', '3'];

                            $q->where('leveljab', '3');
                            if (in_array($row[0]['bagian_id'], $tempBag)) {
                                $q->where('parentjab', '030 ');    
                            } else {
                                $q->where('parentjab', '031 ');
                            } 
                        }]);
                    }])
                    ->whereNotIn('nip', adminNip())
                    ->where('lokasi', 'like', '%'.$row[0]['instalasi_id'].'%')
                    ->where('bagian', 'like', '%'.$row[0]['bagian_id'].'%')
                    ->get();
                $arrTemp = $temp->toArray();
                $resTemp = null;
                foreach ($arrTemp as $rowTmp) {
                    if (!empty($rowTmp['tu_roleuser'])) {
                        if (!empty($rowTmp['tu_roleuser']['jabatan'])) {
                            $resTemp = $rowTmp['tu_roleuser']['nip'];
                        }
                    }
                }
                // ./GET SPV NIP FROM instalasi_id and bagian_id

                $spv = trim($resTemp);
                $urutan = prwRutinUrutan();
                $gen = generateKodeWo('prwRutin',
                    $urutan,
                    substr($row[0]['kode_fm'], 0, 1),
                    $row[0]['instalasi_id'],
                    date('Y-m-d')
                );

                $data = DB::table('prw_rutin')->insertGetId([
                    'komponen_id' => $row[0]['komponen_id'],
                    'bagian_id' => $row[0]['bagian_id'],
                    'instalasi_id' => $row[0]['instalasi_id'],
                    'tanggal' => DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')"),
                    'spv' => $spv,
                    'kode_wo' => $gen,
                    'manajer' => manajer($spv),
                    'urutan' => $urutan
                ]);
                
                foreach ($row as $value) {
                    DB::table('prw_rutin_detail')->insert([
                        'prw_rutin_id' => $data,
                        'kode_part' => $value['kode_part'],
                        'perawatan' => $value['perawatan']
                    ]);
                }

                DB::commit();

                if (!in_array($spv, $arrSpv)){
                    $arrSpv[] = $spv;
                }

                $bar->setMessage (' ' . $row[0]['nama_aset']);
                $bar->advance ();
            }

            // end insert data
            $bar->finish ();               
            unset ($bar);
            
            // broadcast notifikasi
            $i = 1;
            foreach ($arrSpv as $rowSpv) {
                $notif = kirimnotif($rowSpv,
                    [
                        'title' => 'Pemberitahuan WO Perawatan Rutin',
                        'text' => 'Pemberitahuan WO Perawatan Rutin',
                        'sound' => 'default',
                        'click_action' => 'OPEN_ACTIVITY_NOTIF',
                        'tipe' => '261', 
                        'id' => $i
                    ]
                );
                $i++;
            }
        } catch (Exception $e) {
            DB::rollback();
        }
    }
}
