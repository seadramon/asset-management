<?php

namespace Asset\Http\Controllers\Api;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Aset,
    Asset\Models\Instalasi,
    Asset\Models\Ms52w,
    Asset\Models\Ms4w,
    Asset\Models\Master,
    Asset\Models\MasterFm,
    Asset\Models\MasterJab,
    Asset\Models\Perawatan,
    Asset\Models\Perbaikan,
    Asset\Models\PrbDetail,
    Asset\Models\PrwDetail;

use DB;
use Html;
use Validator;
use Redirect;
use Session;
use Storage;
use File;
use Image;

class WoController extends Controller
{
    public function perawatan(Request $request)
    {
        $start = $request->get('start'); //offset
        $limit = $request->get('limit');
        $nip = $request->header('nip');

        try {
            $result = [];

            $data = Perawatan::with(['ms4w', 'sukucadang'])
                ->select('aset.kode_barcode', 'aset.nama_aset', 'aset.pemeliharaan_start', 'aset.pemeliharaan_end', 'prw_data.*', 'instalasi.name as instalasi')
                ->join('aset', 'aset.id', '=', 'prw_data.komponen_id')
                ->join('instalasi', 'instalasi.id', '=', 'aset.instalasi_id')
                ->where('prw_data.petugas_id', $nip)
                ->whereNotIn('prw_data.status', config('custom.skipStatus'));

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            $data = $data->get()->toArray();
            
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $isMasaPemeliharaan = "no";
                    $masaPemeliharaan = "";
                    
                    if (!empty($row['pemeliharaan_start']) && !empty($row['pemeliharaan_end'])) {
                        $masaPemeliharaan = $row['pemeliharaan_start'].' s/d '.$row['pemeliharaan_end'];

                        $isMasaPemeliharaan = cekMasaPemeliharaan($row['pemeliharaan_start'], $row['pemeliharaan_end']);
                    }

                    $result[] = [
                        'nama_aset' => $row['nama_aset'],
                        'kode_barcode' => $row['kode_barcode'],
                        'id' => $row['id'],
                        'komponen_id' => $row['komponen_id'],
                        'bagian_id' => $row['bagian_id'],
                        'instalasi_id' => $row['instalasi_id'],
                        'instalasi' => $row['instalasi'],
                        'status' => $row['status'],
                        'statusMsg' => statusTindakanManajer($row),
                        'tanggal' => $row['tanggal'],
                        'hari' => $row['ms4w']['hari'],
                        'minggu' => $row['ms4w']['urutan_minggu'],
                        'tgl_start' => $row['tgl_start'],
                        'tgl_finish' => $row['tgl_finish'],
                        'metode' => $row['metode'],
                        'sifat' => !empty($row['sifat'])?$row['sifat']:"",
                        'tingkat' => !empty($row['tingkat'])?$row['tingkat']:"",
                        'perkiraan' => $row['perkiraan'],
                        'kondisi' => $row['kondisi'],
                        'foto_investigasi'  => !empty($row['foto_investigasi'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto_investigasi'])):"",
                        'foto_investigasi2' => !empty($row['foto_investigasi2'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto_investigasi2'])):"",
                        'foto'              => !empty($row['foto'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto'])):"",
                        'foto2'             => !empty($row['foto2'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto2'])):"",
                        'penyebab' => $row['penyebab'],
                        'jenis_penanganan' => $row['jenis_penanganan'],
                        'uraian' => $row['uraian'],
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
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function perawatanDetail(Request $request)
    {
        try {
            $nip = $request->header('nip');
            $id = $request->get('id');

            if (empty($id)) {
                return response()->json(['error' => 'ID Perawatan kosong'])->setStatusCode(500, 'Error');
            }

            $result = [];

            $query = PrwDetail::select('prw_detail.*', 
                DB::raw("(select pengukuran from master_fm where kode_fm = prw_detail.kode_fm and nama_field = prw_detail.nama_field) as pengukuran"))
                ->where('prw_detail.prw_data_id', $id)->get();

            $data = $query->toArray();
            
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $result[] = [
                        'pengukuran' => $row['pengukuran'],
                        'nilai_asli' => $row['nilai_asli'],
                        'nilai_batas' => $row['nilai_batas'],
                        'kode_fm' => $row['kode_fm']
                    ];
                }
            }

            $return = ['data' => $result];

            return response()->json($return)->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function perbaikan(Request $request)
    {
        $start = $request->get('start'); //offset
        $limit = $request->get('limit');
        $nip = $request->header('nip');

        try {
            $result = [];

            $data = Perbaikan::with(['ms4w', 'sukucadang'])
                ->select('aset.kode_barcode', 'aset.nama_aset', 'aset.pemeliharaan_start', 'aset.pemeliharaan_end', 'prb_data.*', 'instalasi.name as instalasi')
                ->join('aset', 'aset.id', '=', 'prb_data.komponen_id')
                ->join('instalasi', 'instalasi.id', '=', 'aset.instalasi_id')
                ->where('prb_data.petugas_id', $nip)
                ->where('prb_data.tipe', 'monitoring')
                ->whereNotIn('prb_data.status', config('custom.skipStatus'));

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            $data = $data->get()->toArray();
            
            if (count($data) > 0) {
                foreach ($data as $row) {
                	$isMasaPemeliharaan = "no";
                    $masaPemeliharaan = "";
                    
                    if (!empty($row['pemeliharaan_start']) && !empty($row['pemeliharaan_end'])) {
                        $masaPemeliharaan = $row['pemeliharaan_start'].' s/d '.$row['pemeliharaan_end'];

                        $isMasaPemeliharaan = cekMasaPemeliharaan($row['pemeliharaan_start'], $row['pemeliharaan_end']);
                    }

                    $result[] = [
                        'nama_aset' => $row['nama_aset'],
                        'kode_barcode' => $row['kode_barcode'],
                        'id' => $row['id'],
                        'komponen_id' => $row['komponen_id'],
                        'bagian_id' => $row['bagian_id'],
                        'instalasi_id' => $row['instalasi_id'],
                        'instalasi' => $row['instalasi'],
                        'status' => $row['status'],
                        'statusMsg' => statusTindakanManajer($row),
                        'tanggal' => $row['tanggal'],
                        'hari' => $row['ms4w']['hari'],
                        'minggu' => $row['ms4w']['urutan_minggu'],
                        'tgl_start' => $row['tgl_start'],
                        'tgl_finish' => $row['tgl_finish'],
                        'metode' => $row['metode'],
                        'perkiraan' => $row['perkiraan'],
                        'kondisi' => $row['kondisi'],
                        'foto_investigasi'  => !empty($row['foto_investigasi'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto_investigasi'])):"",
                        'foto_investigasi2' => !empty($row['foto_investigasi2'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto_investigasi2'])):"",
                        'foto'              => !empty($row['foto'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto'])):"",
                        'foto2'             => !empty($row['foto2'])?url('pic-api/gambar/'.str_replace('/', '&', $row['foto2'])):"",
                        'penyebab' => $row['penyebab'],
                        'uraian' => $row['uraian'],
                        'jenis_penanganan' => $row['jenis_penanganan'],
                        'm_catatan'         => $row['m_catatan'],
                        'ms_ppp_catatan'    => $row['ms_ppp_catatan'],
                        'dalpro_catatan'    => $row['dalpro_catatan'],
                        'petugas_catatan'   => $row['petugas_catatan'],
                        'masaPemeliharaan'   => $masaPemeliharaan,
                        'isMasaPemeliharaan'   => $isMasaPemeliharaan
                    ];
                }
            }

            $return = ['data' => $result, 'next' => 'false'];

            if ($limit !== null) {
                $return = ['data' => array_slice($result, 0, $limit), 'next' => (count($result) > $limit)];    
            }

            return response()->json($return)->setStatusCode(200, 'OK');
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function perbaikanDetail(Request $request)
    {
        try {
            $nip = $request->header('nip');
            $id = $request->get('id');

            if (empty($id)) {
                return response()->json(['error' => 'ID Perbaikan kosong'])->setStatusCode(500, 'Error');
            }

            $result = [];

            $query = PrbDetail::select('prb_detail.*', 
                DB::raw("(select pengukuran from master_fm where kode_fm = prb_detail.kode_fm and nama_field = prb_detail.nama_field) as pengukuran"))
                ->where('prb_detail.prb_data_id', $id)->get();

            $data = $query->toArray();
            
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $result[] = [
                        'pengukuran' => $row['pengukuran'],
                        'nilai_asli' => $row['nilai_asli'],
                        'nilai_batas' => $row['nilai_batas'],
                        'kode_fm' => $row['kode_fm']
                    ];
                }
            }

            $return = ['data' => $result];

            return response()->json($return)->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function perawatanInvSimpan(Request $request)
    {
        $nip = $request->header('nip');

        DB::beginTransaction();
        try {
            $filename = '';
            if ($request->hasFile('foto_investigasi')) {
                /*$file = $request->file('foto_investigasi');
                $extension = $file->getClientOriginalExtension();
                $path = public_path('uploads/perawatan/');
                $filename = trim($nip) . '_' . $request->id . '_perawatan_investigasi.' . $extension;
                $test = Image::make($file)->save($path.$filename);*/

                // upload file
                $file = $request->file('foto_investigasi');
                $extension = $file->getClientOriginalExtension();

                $dir = 'perawatan/'.date('Ym');
                cekDir($dir);

                $filename = trim($nip) . '_' . $request->id . '_perawatan_investigasi.' . $extension;
                $pathname = $dir.'/'.$filename;
                \Storage::disk("sftp")->put($dir.'/'.$filename, \File::get($file));
                // end:upload file

                $data = [
                    'penyebab' => $request->penyebab,
                    'tgl_foto_investigasi' => getNow(),
                    'status' => '1',
                    'tgl_start' => getNow(),
                    'foto_investigasi' => $pathname,
                    'last_action' => 'Investigasi',
                    'updated_at' => getNow()
                ];
            }

            $filename2 = '';
            if ($request->hasFile('foto_investigasi2')) {
                /*$file = $request->file('foto_investigasi2');
                $extension = $file->getClientOriginalExtension();
                $path = public_path('uploads/perawatan/');
                $filename2 = trim($nip) . '_' . $request->id . '_perawatan_investigasi_2.' . $extension;
                $test = Image::make($file)->save($path.$filename2);*/

                // upload file
                $file = $request->file('foto_investigasi2');
                $extension = $file->getClientOriginalExtension();

                $dir = 'perawatan/'.date('Ym');
                cekDir($dir);

                $filename2 = trim($nip) . '_' . $request->id . '_perawatan_investigasi_2.' . $extension;
                $pathname2 = $dir.'/'.$filename2;
                \Storage::disk("sftp")->put($dir.'/'.$filename2, \File::get($file));
                // end:upload file
                
                $data['foto_investigasi2'] = $pathname2;
            }

            // update PRB_DATA
            $simpan = Perawatan::where('id', $request->id)->update($data);
            DB::commit();

            // Notif
            $prwNotif = Perawatan::where('id', $request->id)->first();
            $notif = kirimnotif(spv($nip),
                [
                    'title' => 'Pemberitahuan Hasil Investigasi Perawatan',
                    'text' => sprintf('Pemberitahuan Hasil Investigasi Perawatan untuk %s', $prwNotif->komponen->nama_aset),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '232', 
                    'id' => $request->id
                ]
                // ['tipe' => '1', 'id' => $request->id]
            );
            // End Notif

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

    public function perawatanSimpan(Request $request)
    {
        $nip = $request->header('nip');

        DB::beginTransaction();
        try {
            $status = '2';
            $data = [];

            // jika petugas memilih utk revisi input metode
            if ($request->revisi == 'yes') {
                $status = '3.2';

                $data = [
                    'uraian' => $request->uraian,
                    'status' => $status,
                    // 'tgl_foto_analisa' => getNow(),
                    'tgl_petugas_catatan' => getNow(),
                    'petugas_catatan' => $request->petugas_catatan,
                    'approve_dalops' => null,
                    'last_action' => 'Revisi Input metode dari petugas',
                    'updated_at' => getNow()
                ];
            }

            if ($request->revisi != 'yes') {
                $filename = '';
                if ($request->hasFile('foto')) {
                    /*$file = $request->file('foto');
                    $extension = $file->getClientOriginalExtension();
                    $path = public_path('uploads/perawatan/');
                    $filename = trim($nip) . '_' . $request->id . '_perawatan.' . $extension;
                    $test = Image::make($file)->save($path.$filename);*/

                    // upload file
                    $file = $request->file('foto');
                    $extension = $file->getClientOriginalExtension();

                    $dir = 'perawatan/'.date('Ym');
                    cekDir($dir);

                    $filename = trim($nip) . '_' . $request->id . '_perawatan.' . $extension;
                    $pathname = $dir.'/'.$filename;
                    \Storage::disk("sftp")->put($dir.'/'.$filename, \File::get($file));
                    // end:upload file

                    $data = [
                        'uraian' => $request->uraian,
                        'status' => $status,
                        'tgl_foto_analisa' => getNow(),
                        //'petugas_catatan' => $request->petugas_catatan, 
                        'foto' => $pathname,
                        'last_action' => 'Penanganan',
                        'updated_at' => getNow()
                    ];
                }

                $filename2 = '';
                if ($request->hasFile('foto2')) {
                    /*$file = $request->file('foto2');
                    $extension = $file->getClientOriginalExtension();
                    $path = public_path('uploads/perawatan/');
                    $filename2 = trim($nip) . '_' . $request->id . '_perawatan_2.' . $extension;
                    $test = Image::make($file)->save($path.$filename2);*/

                    // upload file
                    $file = $request->file('foto2');
                    $extension = $file->getClientOriginalExtension();

                    $dir = 'perawatan/'.date('Ym');
                    cekDir($dir);

                    $filename2 = trim($nip) . '_' . $request->id . '_perawatan_2.' . $extension;
                    $pathname2 = $dir.'/'.$filename2;
                    \Storage::disk("sftp")->put($dir.'/'.$filename2, \File::get($file));
                    // end:upload file
                    
                    $data['foto2'] = $pathname2;
                }
            }

            // update PRB_DATA
            $simpan = Perawatan::where('id', $request->id)->update($data);
            DB::commit();

            // Notif
            $prwNotif = Perawatan::where('id', $request->id)->first();

            $title = 'Pemberitahuan Hasil Penanganan Perawatan';
            $tipe = 233;
            if ($request->revisi == 'yes') {
                $title = 'Pemberitahuan Revisi Input Metode dari Penanganan';
                $tipe = 235;
            }

            $notif = kirimnotif(spv($nip),
                [
                    'title' => $title,
                    'text' => sprintf('Pemberitahuan Hasil Penanganan Perawatan untuk %s', $prwNotif->komponen->nama_aset),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => $tipe, 
                    'id' => $request->id
                ]
                // ['tipe' => '1', 'id' => $request->id]
            );
            // End Notif

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

    public function perbaikanInvSimpan(Request $request)
    {
        $nip = $request->header('nip');

        DB::beginTransaction();
        try {
            $filename = '';

            if ($request->hasFile('foto_investigasi')) {
                /*$file = $request->file('foto_investigasi');
                $extension = $file->getClientOriginalExtension();
                $path = public_path('uploads/perbaikan/monitoring/');
                $filename = trim($nip) . '_' . $request->id . '_perbaikan_monitoring_investigasi.' . $extension;
                $test = Image::make($file)->save($path.$filename);*/

                // upload file
                $file = $request->file('foto_investigasi');
                $extension = $file->getClientOriginalExtension();

                $dir = 'perbaikan/'.date('Ym');
                cekDir($dir);

                $filename = trim($nip) . '_' . $request->id . '_perbaikan_monitoring_investigasi.' . $extension;
                $pathname = $dir.'/'.$filename;
                \Storage::disk("sftp")->put($dir.'/'.$filename, \File::get($file));
                // end:upload file
                
                $data = [
                    'penyebab' => $request->penyebab,
                    'tgl_foto_investigasi' => getNow(),
                    'status' => '1',
                    'tgl_start' => getNow(),
                    'foto_investigasi' => $filename,
                    'last_action' => 'Investigasi',
                    'updated_at' => getNow()
                ];
            }

            $filename2 = '';
            if ($request->hasFile('foto_investigasi2')) {
                /*$file = $request->file('foto_investigasi2');
                $extension = $file->getClientOriginalExtension();
                $path = public_path('uploads/perbaikan/monitoring/');
                $filename2 = trim($nip) . '_' . $request->id . '_perbaikan_monitoring_investigasi_2.' . $extension;
                $test = Image::make($file)->save($path.$filename2);*/

                // upload file
                $file = $request->file('foto_investigasi2');
                $extension = $file->getClientOriginalExtension();

                $dir = 'perbaikan/'.date('Ym');
                cekDir($dir);

                $filename2 = trim($nip) . '_' . $request->id . '_perbaikan_monitoring_investigasi_2.' . $extension;
                $pathname2 = $dir.'/'.$filename2;
                \Storage::disk("sftp")->put($dir.'/'.$filename2, \File::get($file));
                // end:upload file
                
                $data['foto_investigasi2'] = $filename2;
            }

            // update PRB_DATA
            $simpan = Perbaikan::where('id', $request->id)->update($data);
            DB::commit();

            // Notif
            $prbNotif = Perbaikan::where('id', $request->id)->first();
            $notif = kirimnotif(spv($nip),
                [
                    'title' => 'Pemberitahuan Hasil Investigasi Perbaikan',
                    'text' => sprintf('Pemberitahuan Hasil Investigasi Perbaikan untuk %s', $prbNotif->komponen->nama_aset),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '222', 
                    'id' => $request->id
                ]
                // ['tipe' => '1', 'id' => $request->id]
            );
            // End Notif

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

    public function perbaikanSimpan(Request $request)
    {
        $nip = $request->header('nip');
// dd($request->all());
        DB::beginTransaction();
        try {
            $status = '2';
            $data = [];

            if ($request->revisi == 'yes') {
                $status = '3.2';

                $data = [
                    'uraian' => $request->uraian,
                    'status' => $status,
                    'tgl_petugas_catatan' => getNow(),
                    // 'tgl_foto_analisa' => getNow(),
                    'petugas_catatan' => $request->petugas_catatan,
                    'approve_dalops' => null,
                    'last_action' => 'Revisi Input metode dari petugas',
                    'updated_at' => getNow()
                ];
            }

            if ($request->revisi != 'yes') {
                $filename = '';
                if ($request->hasFile('foto')) {
                    /*$file = $request->file('foto');
                    $extension = $file->getClientOriginalExtension();
                    $path = public_path('uploads/perbaikan/monitoring/');
                    $filename = $nip . '_' . $request->id . '_perbaikan_monitoring.' . $extension;
                    $test = Image::make($file)->save($path.$filename);*/

                    // upload file
                    $file = $request->file('foto');
                    $extension = $file->getClientOriginalExtension();

                    $dir = 'perbaikan/'.date('Ym');
                    cekDir($dir);

                    $filename = $nip . '_' . $request->id . '_perbaikan_monitoring.' . $extension;
                    $pathname = $dir.'/'.$filename;
                    \Storage::disk("sftp")->put($dir.'/'.$filename, \File::get($file));
                    // end:upload file

                    $data = [
                        'uraian' => $request->uraian,
                        'status' => $status,
                        'tgl_foto_analisa' => getNow(),
                        //'petugas_catatan' => $request->petugas_catatan,
                        'foto' => $pathname,
                        'last_action' => 'Penanganan',
                        'updated_at' => getNow()
                    ];
                }
                
                $filename2 = '';
                if ($request->hasFile('foto2')) {
                    /*$file = $request->file('foto2');
                    $extension = $file->getClientOriginalExtension();
                    $path = public_path('uploads/perbaikan/monitoring/');
                    $filename2 = $nip . '_' . $request->id . '_perbaikan_monitoring_2.' . $extension;
                    $test = Image::make($file)->save($path.$filename2);*/

                    // upload file
                    $file = $request->file('foto2');
                    $extension = $file->getClientOriginalExtension();

                    $dir = 'perbaikan/'.date('Ym');
                    cekDir($dir);

                    $filename2 = $nip . '_' . $request->id . '_perbaikan_monitoring_2.' . $extension;
                    $pathname2 = $dir.'/'.$filename2;
                    \Storage::disk("sftp")->put($dir.'/'.$filename2, \File::get($file));
                    // end:upload file
                    
                    $data['foto2'] = $pathname2;
                }
            }

            // update PRB_DATA
            $simpan = Perbaikan::where('id', $request->id)->update($data);
            DB::commit();

            // Notif
            $prbNotif = Perbaikan::where('id', $request->id)->first();

            $title = 'Pemberitahuan Hasil Penanganan Perbaikan';
            $tipe = 223;
            if ($request->revisi == 'yes') {
                $title = 'Pemberitahuan Revisi Input Metode dari Penanganan';
                $tipe = 225;
            }

            $notif = kirimnotif(spv($nip),
                [
                    'title' => $title,
                    'text' => sprintf('Pemberitahuan Hasil Penanganan Perbaikan untuk %s', $prbNotif->komponen->nama_aset),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => $tipe,
                    'id' => $request->id
                ]
                // ['tipe' => '1', 'id' => $request->id]
            );
            // End Notif

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
}
