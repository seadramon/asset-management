<?php 
namespace Asset\Http\Controllers\Api;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\User,
    Asset\Models\Aset,
    Asset\Models\PmlKeluhan,
    Asset\Models\Master,
    Asset\Models\MasterFm,
    Asset\Models\MasterJab,
    Asset\Models\Perawatan,
    Asset\Models\Perbaikan,
    Asset\Models\Ms4w;


use Illuminate\Support\Facades\File;
use DB;
use Html;
use Validator;
use Redirect;
use Session;
use Image;
use Storage;

class AduanController extends Controller
{

	public function index(Request $request)
	{
		$start = $request->get('start'); //offset
        $limit = $request->get('limit');

        $nip = $request->header('nip');
        $result = [];
// dd('a');
        try {
        	$data = Perbaikan::with('komponen', 'bagian', 'instalasi', 'sukucadang')
                ->where('prb_data.petugas_id', $nip)
        		->where('prb_data.tipe', 'aduan')
                ->whereNotIn('prb_data.status', config('custom.skipStatus'))
                ->orderBy('prb_data.id', 'desc');
// dd($data->get());
        	if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            /*$arr = $data->get();
            $arrD = "";
            foreach ($arr as $row) {
                if (!in_array($row->bagian_id, ['1', '2', '3', '4'])) {
                    $arrD .= $row->komponen_id.', ';
                    // dd($row);
                }
            }
dd($arrD);*/
            $data = $data->get()->toArray();
            
            // dd(implode(", ", $a));
            // dd($data);
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $isMasaPemeliharaan = "no";
                    $masaPemeliharaan = "";
                    $period = date("Y-m", strtotime($row['tanggal']));
                    
                    if (!empty($row['pemeliharaan_start']) && !empty($row['pemeliharaan_end'])) {
                        $masaPemeliharaan = $row['pemeliharaan_start'].' s/d '.$row['pemeliharaan_end'];

                        $isMasaPemeliharaan = cekMasaPemeliharaan($row['pemeliharaan_start'], $row['pemeliharaan_end']);
                    }

                    $status = statusTindakanManajer($row);
                    $result[] = [
                        'id' 				=> $row['id'],
                        'komponen_id'       => $row['komponen_id'],
                        'no_registrasi'		=> $row['kode_wo'],
                        'komponen'          => $row['komponen']['nama_aset'],
                        'kode_barcode'      => $row['komponen']['kode_barcode'],
                        'instalasi_id'      => $row['instalasi']['id'],
                        'instalasi'  		=> $row['instalasi']['name'],
                        'bagian_id'  		=> $row['bagian_id'],
                        'bagian'  			=> $row['bagian']['name'],
                        'petugas_id'  		=> $row['petugas_id'],
                        'status_web'        => $row['status'],
                        'statusMsg'  		=> $status,
                        'tanggal'  			=> $row['tanggal'],
                        'aduan_id'  		=> $row['aduan_id'],
                        'perkiraan'  		=> $row['perkiraan'],
                        'tgl_start'  		=> $row['tgl_start'],
                        'tgl_finish'  		=> $row['tgl_finish'],
                        'metode'  			=> $row['metode'],
                        'kondisi'  			=> $row['kondisi'],
                        'uraian'  			=> $row['uraian'],
                        'foto_investigasi'  => !empty($row['foto_investigasi'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto_investigasi'])):"",
                        'foto_investigasi2' => !empty($row['foto_investigasi2'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto_investigasi2'])):"",
                        'foto'              => !empty($row['foto'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto'])):"",
                        'foto2'             => !empty($row['foto2'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto2'])):"",
                        'foto_kondisi'      => !empty($row['aduan_kondisi'])?url('pic-api/gambar/aduan&'.$period.'&'.$row['aduan_kondisi']):"",
                        'penyebab'          => $row['penyebab'],
                        'jenis_penanganan'	=> $row['jenis_penanganan'],

                        'judul'  			=> $row['aduan_judul'],
                        /*'kode_barcode'  	=> $row['kode_barcode'],*/
                        'catatan'  			=> $row['aduan_catatan'],
                        'indikasi' 			=> $row['aduan_indikasi'],
                        'status_mobile'     => '',

                        'm_catatan'         => $row['m_catatan'],
                        'ms_ppp_catatan'    => $row['ms_ppp_catatan'],
                        'dalpro_catatan'    => $row['dalpro_catatan'],
                        'petugas_catatan'   => $row['petugas_catatan'],
                        
                        'masaPemeliharaan'   => $masaPemeliharaan,
                        'isMasaPemeliharaan'   => $isMasaPemeliharaan,
                    ];
                }
            }

            $return = ['data' => $result, 'next' => 'false'];

            if ($limit !== null) {
                $return = ['data' => array_slice($result, 0, $limit), 'next' => (count($result) > $limit)];    
            }

            return response()->json($return)->setStatusCode(200, 'OK');
        } catch(Exception $e) {
        	return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
	}

    public function tambah(Request $request)
    {
        $nip = $request->header('nip');

        DB::beginTransaction();

        try {
            $asetTemp = Aset::where('kode_barcode', $request->kode_barcode)->first();

            $cekPrb = Perbaikan::whereNotIn('status', config('custom.skipStatus'))
                ->where('komponen_id', $asetTemp->id)
                ->where(function($sql) {
                    $sql->where('bagian_id', '<>', '4')
                        ->orWhere('bagian_id', null);
                })
                ->count();
// dd($cekPrb);
            $cekPrw = Perawatan::whereNotIn('status', config('custom.skipStatus'))
                ->where('komponen_id', $asetTemp->id)
                ->where(function($sql) {
                    $sql->where('bagian_id', '<>', '4')
                        ->orWhere('bagian_id', null);
                })
                ->count();

            /*if ($cekPrw > 0) {
                Perawatan::where('komponen_id', $asetTemp->id)
                    ->whereNotIn('status', config('custom.skipStatus'))
                    ->update([
                        'status' => '98',
                        'last_action' => 'Digantikan Perbaikan Aduan',
                        'updated_at' => getNow()
                    ]);
            }*/

            if ($cekPrb > 0 || $cekPrw > 0) {
                return response()->json([
                    'result'  => 'Forbidden',
                    'message' => 'Aset sudah diadukan atau sedang dalam perawatan/perbaikan'])->setStatusCode(403, 'FORBIDDEN');
            }

            // $sessRole = User::where('trim(userid)', trim($request->nip))->first()->role[0]->recidroleuser;  
            $sessRole = User::where('trim(userid)', trim($request->nip))->first()->role->recidroleuser;

            $urutan = Perbaikan::select(DB::raw('max(urutan) as urutan'))
                ->where('tipe', 'aduan')
                ->whereRaw("TO_CHAR(TANGGAL, 'MM') = ".date('m', strtotime(date('Y-m-d'))))
                ->first();
            $urutnya = !empty($urutan->urutan)?$urutan->urutan+1:1;

            // generate kode
            $gen = generateKodeWo('aduan', 
                    $urutnya,
                    $asetTemp->bagian,
                    $asetTemp->instalasi_id,
                    date('Y-m-d'));
            // ./generate kode 

            if ($request->sifat == 1) {
                $sifat = 'Darurat';
                $optsifat = $request->optdarurat;
            } else {
                $sifat = 'Biasa';
                $optsifat = 'kosong';
            }

            $data = [
                'komponen_id' => $asetTemp->id,
                'bagian_id' => $asetTemp->bagian,
                'instalasi_id' => $asetTemp->instalasi_id,
                'tanggal' => getNow(),
                'status' => '0',
                'tipe' => 'aduan',
                'sifat' => $sifat,
                'kode_wo' => $gen,
                'urutan' => $urutnya,
                'aduan_pelapor' => str_pad(trim($request->nip), 30, ' '),
                'aduan_judul' => $request->judulkerusakan,
                'aduan_catatan' => $request->catatankerusakan,
                'aduan_indikasi' => $request->indikasikerusakan,
                'last_action' => 'Baru',
                'updated_at' => getNow()
            ];

            $idWo = Perbaikan::insertGetId($data);

            $path = '';
            if ($request->hasFile('filekondisi')) {
                $file = $request->file('filekondisi');
                $extension = $file->getClientOriginalExtension();

                $dir = 'aduan/'.date('Y-m');
                cekDir($dir);
                // $dir = 'aduan';

                \Storage::disk('sftp')->put($dir.'/path_kondisi_aduan_'.trim($request->nip) . '_' . $idWo . '.' . $extension, Image::make(\File::get($file))->encode('jpg', 75));
                $path = 'path_kondisi_aduan_'.trim($request->nip) . '_' . $idWo . '.' . $extension;
            }
            Perbaikan::where('id', $idWo)->update([
                'ADUAN_KONDISI' => $path,
                'ADUAN_ID' => $idWo
            ]);

            /*Update Ms4w Monitoring*/
            $aset = Aset::select('id')->where('kode_barcode', $request->kode_barcode)->first();
            $date = new \DateTime(date('Y-m-d'));
            $week = $date->format("W");
            $ms = Ms4w::whereHas('ms52w', function($query) use($aset) {
                    $query->where('komponen_id', $aset->id);
                    $query->where('tahun', date('Y'));
                })
                ->where('urutan_minggu', $week)
                ->where('status', '0');

            $exist = $ms->get();
            if (count($exist) > 0) {
                $ms->update(['status' => '99']);
            }
            /*End Update Ms4w Manajemen Aset*/

            DB::commit();

            // Notif
            /*$aset = Aset::where('kode_barcode', $request->kode_barcode)->first();
            kirimnotif(trim($data['DISPOSISI_M_NIP']),
                [
                    'title' => 'Pemberitahuan WO Perbaikan Aduan',
                    'text' => sprintf('Pemberitahuan WO Perbaikan untuk %s', $aset->nama_aset),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '213', 
                    'id' => $recid
                ] 
            );*/
            // End Notif
            return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

	public function investigasi(Request $request)
	{
		$nip = $request->header('nip');

		DB::beginTransaction();
		try {
            // if null then 'new' else 'normal process' 
            /*$isNew = PmlKeluhan::where('kode_barcode', $request->kode_barcode)
                ->where('recidkeluhan', $request->aduan_id)->first();*/

            // Dev
            $assetTemp = Aset::where('kode_barcode', $request->kode_barcode)->first();
            $isNew = Perbaikan::where('komponen_id', $assetTemp->id)
                ->whereNotIn('status', ['99', '10'])
                ->where('id', $request->id)
                ->first();
            // end:Dev

            if (empty($isNew)) {
                // Ketika ganti aset------------------------------
                // Dev
                $data = Perbaikan::where('id', $request->id)->update([
                    'aduan_pelapor' => substr($nip, 0, 30),
                    'komponen_id' => $assetTemp->id,
                    'instalasi_id' => $assetTemp->instalasi_id,
                    'bagian_id' => $assetTemp->bagian,
                    'tanggal' => getNow(),
                    'petugas_id' => null,
                    'manajer' => null,
                    'tgl_foto_investigasi' => null,
                    'foto_investigasi' => null,
                    'status' => 0,
                    'last_action' => 'Aset digantikan',
                    'updated_at' => getNow()
                ]);

                DB::commit();
                // end:Dev
                // end:Ketika ganti aset---------------------------
            } else {
                // Normal Process
                $status = '1';
                $data = [];

                if ($request->isKerusakan == 'no') {
                    $status = '99';

                    $data = [
                        'penyebab' => $request->penyebab,
                        'tgl_foto_investigasi' => getNow(),
                        'status' => $status,
                        'tgl_start' => getNow(),
                        'last_action' => 'ditandai Bukan Kerusakan',
                        'updated_at' => getNow()
                    ];
                }

                // dd($data);
                $filename = '';
                $filename_lok = '';
    
                if ($request->hasFile('foto_investigasi')) {
                    $file = $request->file('foto_investigasi');
                    $extension = $file->getClientOriginalExtension();

                    $dir = 'perbaikan/'.date('Ym');
                    cekDir($dir);

                    $filename = trim($nip) . '_' . $request->id . '_kerusakan.' . $extension;
                    $pathname = $dir.'/'.$filename;
                    Storage::disk('sftp')->put($dir.'/'.$filename, \File::get($file));

                    $data = [
                        'penyebab' => $request->penyebab,
                        'tgl_foto_investigasi' => getNow(),
                        'status' => $status,
                        'tgl_start' => getNow(),
                        'last_action' => 'investigasi',
                        'updated_at' => getNow()
                    ];
                    
                    $data['foto_investigasi'] = $pathname;
                }
                
                if ($request->hasFile('foto_investigasi2')) {
                    $file = $request->file('foto_investigasi2');
                    $extension = $file->getClientOriginalExtension();

                    $dir = 'perbaikan/'.date('Ym');
                    cekDir($dir);

                    $filename = trim($nip) . '_' . $request->id . '_kerusakan_2.' . $extension;
                    $pathname = $dir.'/'.$filename;
                    Storage::disk('sftp')->put($dir.'/'.$filename, \File::get($file));
                    
                    $data['foto_investigasi2'] = $pathname;
                }

                if ($request->hasFile('lok_kerusakan')) {
                    $file = $request->file('lok_kerusakan');
                    $extension = $file->getClientOriginalExtension();

                    $dir = 'perbaikan/'.date('Ym');
                    cekDir($dir);

                    $filename_lok = trim($nip) . '_' . $request->id . '_lokasi_kerusakan.' . $extension;
                    $pathname = $dir.'/'.$filename_lok;
                    Storage::disk('sftp')->put($dir.'/'.$filename_lok, \File::get($file));

                    $data['aduan_lok_kerusakan'] = $pathname;
                }
                // update PRB_DATA
                $simpan = Perbaikan::where('id', $request->id)->update($data);
                DB::commit();

                // Update pml_keluhan web-portal pemeliharaan
                    //---removed
                // ./Update pml_keluhan web-portal pemeliharaan

                // Notif
                $perbaikan = Perbaikan::with('komponen')->where('id', $request->id)->first();
                kirimnotif(spv($nip),
                    [
                        'title' => 'Pemberitahuan WO Perbaikan Aduan',
                        'text' => sprintf('Pemberitahuan Hasil Investigasi untuk %s', $perbaikan->komponen->nama_aset),
                        'sound' => 'default',
                        'click_action' => 'OPEN_ACTIVITY_NOTIF',
                        'tipe' => '211', 
                        'id' => $request->id
                    ]
                );
                // End Notif
                // ./End Normal Process
            }

            return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
		} catch(Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
		}
	}

    public function analisa(Request $request) //Penanganan
    {
        $nip = $request->header('nip');

        DB::beginTransaction();
        try {
            $status = '2';

            if ($request->revisi == 'yes') {
                $status = '3.2';
                $data = [
                    'status' => $status,
                    'petugas_catatan' => $request->petugas_catatan,
                    'tgl_petugas_catatan' => getNow(),
                    'last_action' => 'Revisi input metode dari Petugas',
                    'updated_at' => getNow()
                ];
            }/* else {
                $data = [
                    'uraian' => $request->uraian,
                    'status' => $status
                ];
            }*/

            if ($request->revisi != 'yes') {
                $filename = '';
                if ($request->hasFile('foto')) {
                    $file = $request->file('foto');
                    $extension = $file->getClientOriginalExtension();

                    $dir = 'perbaikan/'.date('Ym');
                    cekDir($dir);

                    $filename = trim($nip) . '_' . $request->id . '_analisa.' . $extension;
                    $pathname = $dir.'/'.$filename;
                    Storage::disk('sftp')->put($dir.'/'.$filename, \File::get($file));
                    
                    $data = [
                        'uraian' => $request->uraian,
                        'status' => $status,
                        'foto' => $pathname,
                        'tgl_foto_analisa' => getNow(),
                        'last_action' => 'Penanganan',
                        'updated_at' => getNow()
                    ];
                    /*$data['foto'] = $filename;
                    $data['tgl_foto_analisa'] = DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')");*/
                }

                $filename2 = '';
                if ($request->hasFile('foto2')) {
                    $file = $request->file('foto2');
                    $extension = $file->getClientOriginalExtension();

                    $dir = 'perbaikan/'.date('Ym');
                    cekDir($dir);

                    $filename2 = trim($nip) . '_' . $request->id . '_analisa_2.' . $extension;
                    $pathname = $dir.'/'.$filename2;
                    Storage::disk('sftp')->put($dir.'/'.$filename2, \File::get($file));
                    
                    $data['foto2'] = $pathname;
                }    
            }
            
            // update PRB_DATA
            $simpan = Perbaikan::where('id', $request->id)->update($data);
            DB::commit();

            // Notif
            $tempNotif = Perbaikan::find($request->id);

            $title = 'Pemberitahuan WO Perbaikan Aduan';
            $tipe = 212;
            if ($request->revisi == 'yes') {
                $title = 'Pemberitahuan Revisi Input Metode dari Penanganan';
                $tipe = 217;
            }
            
            kirimnotif(spv($nip),
                [
                    'title' => $title,
                    'text' => sprintf('Hasil Penanganan WO Perbaikan untuk %s', $tempNotif->komponen->nama_aset),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => $tipe, 
                    'id' => $request->id
                ] 
                // ['tipe' => '2', 'id' => $request->id]
            );
            // End Notif            

            return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }
}