<?php

namespace Asset\Http\Controllers\Api;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Aset,
    Asset\Models\PermohonanSc,
    Asset\Models\Master,
    Asset\Models\MasterFm,
    Asset\Models\MasterJab,
    Asset\Models\Perawatan,
    Asset\Models\Perbaikan,
    Asset\Models\AduanNonOperasi,
    Asset\Models\Usulan,
    Asset\Models\PrwRutin,
    Asset\Models\Barang,
    Asset\RoleUser;

use DB;
use Html;
use Validator;
use Redirect;
use Session;
use Image;
use Storage;
use Log;

class SukuCadangController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listPermohonan(Request $request)
    {
        $nip = $request->header('nip');
        $wo = $request->header('wo');
        $fid = $request->get('fid'); //wo id, ex:prw_data_id,prb_data_id, prw_rutin_id
        $status = $request->get('status');

        $start = $request->get('start'); //offset
        $limit = $request->get('limit');

        $result = [];

        try {
            if (empty($wo) || empty($fid)) {
                return response()->json(['result' => 'error',
                    'message' => "WO dan ID tidak boleh kosong"])->setStatusCode(500, 'Error');
            }

            if (cekJabatan($nip) == "petugas") {
                $nipResult = str_pad(spv($nip), 30);
                // dd($nipResult);
            } else {
                $nipResult = str_pad($nip, 30);
            }

            // dd(cekJabatan($nip));
            if (strpos(cekJabatan($nip), 'manajer') === false) {
            // if (cekJabatan($nip) != "manajer") {
                $data = PermohonanSc::with('detail')
                        ->whereNotIn('status', ['dikeluarkan', 'ditolak']);
            } else {
                $data = PermohonanSc::with('detail');
            }

            // if (cekJabatan($nip) != "manajer") {
            if (strpos(cekJabatan($nip), 'manajer') === false) {
                $data = $data->where('nip', $nipResult);
            }

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            if (!empty($status)) {
                $data = $data->where('status', $status);
            }

            $woFKey = self::getWo($wo);
            $data = $data->where($woFKey, $fid);

            $headers = $data->get();

            $i = 0;
            foreach ($headers as $header) {
                $arrData = !empty($header)?$header->detail:[];

                if (count($arrData) > 0) {
                    $result[$i]['nama'] = $header->nama;
                    $result[$i]['status'] = $header->status;
                    $result[$i]['bagian'] = !empty($header->bagian->name)?$header->bagian->name:"";
                    foreach ($arrData as $row) {
                        //if ($row->kode_alias == '1923') dd($row);
                        $result[$i][] = [
                            $woFKey => $fid,
                            'permohonan_sc_id' => $header->id,
                            'nama_barang' => $row->barang->nama,
                            'kode_alias' => $row->kode_alias,
                            'nama_gudang' => !empty($row->gudang)?$row->gudangRelasi->nama_gdg:"",
                            'jumlah' => $row->jumlah,
                            'keterangan' => $row->keterangan,
                        ];
                    }
                }
                $i++;
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

    public function permohonan(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip');
            $wo = $request->header('wo');
            $fid = $request->get('fid'); //wo id, ex:prw_data_id,prb_data_id, prw_rutin_id
            // dd('aa');
            if (empty($wo) || empty($fid)) {
                return response()->json(['result' => 'error',
                    'message' => "WO dan Foreign ID kosong"])->setStatusCode(500, 'Error ');
            }

            $woFKey = self::getWo($wo);
            $header = PermohonanSc::where($woFKey, $fid)
                ->whereIn('status', ['permintaan', 'baru'])
                ->first();

            // set name/title
            $table = str_replace('_id', '', $woFKey);

            if (!in_array( $table, ['aduan_non_operasi', 'usulan_non_operasi']) ) {
                $tempAset = DB::table($table)
                    ->select('aset.nama_aset')
                    ->join('aset', $table.'.komponen_id = aset.id')
                    ->where($table.'.id', $fid)
                    ->first(); 
                $title = sprintf("Permohonan Suku Cadang untuk %s %s", 
                    ucfirst(str_replace("-", " ", $wo)), 
                    !empty($tempAset)?$tempAset->nama_aset:"--");
            } else {
                $tempAset = DB::table($table)
                    ->where('id', $fid)
                    ->first();

                $title = sprintf("Permohonan Suku Cadang untuk %s %s", 
                    ucfirst(str_replace("-", " ", $wo)), 
                    !empty($tempAset->judul)?$tempAset->judul:$tempAset->nama);
            }

            if ($wo == "prw-rutin") {
                $title = $request->nama;
            }
            // end set name/title

            if (empty($header)) {
                // Insert Header Table
                $header = new PermohonanSc;

                $header->nama = $title;
                $header->kd_unitkrj = $request->kd_unitkrj;
                $header->bagian_id = !empty($request->bagian_id)?$request->bagian_id:null;
                $header->prw_data_id = !empty($request->prw_data_id)?$request->prw_data_id:null;
                $header->prb_data_id = !empty($request->prb_data_id)?$request->prb_data_id:null;
                $header->prw_rutin_id = !empty($request->prw_rutin_id)?$request->prw_rutin_id:null;

                $header->aduan_non_operasi_id = !empty($request->aduan_non_operasi_id)?$request->aduan_non_operasi_id:null;
                $header->usulan_non_operasi_id = !empty($request->usulan_non_operasi_id)?$request->usulan_non_operasi_id:null;

                $header->status = "permintaan";
                $header->nip = str_pad($nip, 30);

                $header->save();
            } else {
                DB::table('permohonan_sc_detail')->where('permohonan_sc_id', $header->id)->delete();
            }

            // Insert Detail table
            $arrDetail = (array)$request->detail;
            Log::debug(sizeof($arrDetail));
            if (sizeof($arrDetail) > 0) {
                foreach ($arrDetail as $row) {
                    $nmBarang = Barang::where('kd_barang_alias', $row['kode_alias'])->pluck('nama');

                    DB::table('permohonan_sc_detail')->insert([
                        'permohonan_sc_id' => $header->id,
                        'kode_alias' => $row['kode_alias'],
                        'jumlah' => $row['jumlah'],
                        'keterangan' => $row['keterangan'],
                        'dibeli_by' => $row['dibeli_by'],
                        'kelompok_barang' => $row['kelompok_barang'],
                        'nama_barang' => $nmBarang
                    ]);   
                }
            }

            DB::commit();

            return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => ' testtt'. $e->getMessage()])->setStatusCode(500, 'Error ending');
        }
    }

    public function listPermohonanAll(Request $request)
    {
        $nip = $request->header('nip');
        $wo = $request->header('wo');

        $start = $request->get('start'); //offset
        $limit = $request->get('limit');

        $status = $request->get('status');

        $result = [];
        $dataWo = [];
// dd('a');
        try {
            $data = PermohonanSc::with(['detail', 'bagian']);

            if (cekJabatan($nip) == "petugas") {                
                $data = $data->where('nip', str_pad(spv($nip), 30));

                $spvDetail = RoleUser::where('nip', str_pad(spv($nip), 30))->first();
            } else {
                $data = $data->where('nip', str_pad($nip, 30));

                $spvDetail = RoleUser::where('nip', str_pad($nip, 30))->first();
            }

            if (!empty($status)) {
                $data = $data->where('status', $status);
            }

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            if (!empty($wo)) {
                $woFKey = self::getWo($wo);
                $data = $data->whereNotNull($woFKey);
            }

            $headers = $data->get();
            // dd($headers);

            if (count($headers) > 0) {
                $i = 0;
                foreach ($headers as $header) {
                    switch (true) {
                        case !empty($header->prw_data_id):
                            $type = "perawatan";
                            $wo = "prw_data_id";
                            break;
                        case !empty($header->prb_data_id):
                            $type = "perbaikan";
                            $wo = "prb_data_id";
                            break;
                        case !empty($header->prw_rutin_id):
                            $type = "perawatan rutin";
                            $wo = "prw_rutin_id";
                            break;
                        case !empty($header->aduan_non_operasi_id):
                            $type = "aduan non operasi";
                            $wo = "aduan_non_operasi_id";
                            break;
                        case !empty($header->usulan_non_operasi_id):
                            $type = "usulan non operasi";
                            $wo = "usulan_non_operasi_id";
                            break;
                        default:
                            $type = "undefined";
                            $wo = "";
                            break;
                    }

                    $tmpWo = self::getTable($type, $header->$wo)->toArray();
                    $statusWo = statusTindakanManajer($tmpWo);

                    $result[$i] = [
                        'id' => $header->id,
                        'nama' => $header->nama,
                        'namaSpv' => !empty($spvDetail)?$spvDetail->nama:"",
                        'status' => $header->status,
                        $wo => $header->$wo,
                        'statusWo' => $statusWo,
                        'type' => $type,
                        'keterangan' => $header->keterangan
                    ];

                    if (count($header->detail) > 0) {
                        foreach ($header->detail as $row) {
                            $result[$i]['detail'][] = [
                                'permohonan_sc_id' => $header->id,
                                'nama_barang' => $row->barang->nama,
                                'kode_alias' => $row->kode_alias,
                                'nama_gudang' => !empty($row->gudang)?$row->gudangRelasi->nama_gdg:"",
                                'jumlah' => $row->jumlah,
                                'keterangan' => $row->keterangan
                            ];
                        }
                    }    
                
                    $i++;
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

    public function waitingList(Request $request)
    {
        DB::beginTransaction();

        try {
            $nip = $request->header('nip');
            $wo = $request->header('wo');
            $fid = $request->get('fid'); //wo id, ex:prw_data_id,prb_data_id, prw_rutin_id, aduan_non_operasi_id, usulan_non_operasi_id

            if (empty($wo) || empty($fid)) {
                return response()->json(['result' => 'error',
                    'message' => "WO dan Foreign ID kosong"])->setStatusCode(500, 'Error');
            }

            $woFKey = self::getWo($wo);
            $header = PermohonanSc::where($woFKey, $fid)
                ->where('status', 'waiting-list')
                ->first();

            // set name/title
            $table = str_replace('_id', '', $woFKey);

            if (!in_array( $table, ['aduan_non_operasi', 'usulan_non_operasi']) ) {
                $tempAset = DB::table($table)
                    ->select('aset.nama_aset')
                    ->join('aset', $table.'.komponen_id = aset.id')
                    ->where($table.'.id', $fid)
                    ->first(); 

                $title = sprintf("Permohonan Suku Cadang untuk %s %s", 
                    ucfirst(str_replace("-", " ", $wo)), 
                    !empty($tempAset)?$tempAset->nama_aset:"--");
            } else {
                $tempAset = DB::table($table)
                    ->where('id', $fid)
                    ->first();

                $title = sprintf("Permohonan Suku Cadang untuk %s %s", 
                    ucfirst(str_replace("-", " ", $wo)), 
                    !empty($tempAset->judul)?$tempAset->judul:$tempAset->nama);
            }
            // end set name/title

            if (empty($header)) {
                // Insert Header Table
                $header = new PermohonanSc;

                $header->nama = $title;
                $header->kd_unitkrj = $request->kd_unitkrj;
                $header->bagian_id = $request->bagian_id;
                $header->prw_data_id = !empty($request->prw_data_id)?$request->prw_data_id:null;
                $header->prb_data_id = !empty($request->prb_data_id)?$request->prb_data_id:null;
                $header->prw_rutin_id = !empty($request->prw_rutin_id)?$request->prw_rutin_id:null;

                $header->aduan_non_operasi_id = !empty($request->aduan_non_operasi_id)?$request->aduan_non_operasi_id:null;
                $header->usulan_non_operasi_id = !empty($request->usulan_non_operasi_id)?$request->usulan_non_operasi_id:null;

                $header->status = "waiting-list";
                $header->nip = str_pad($nip, 30);

                $header->save();
            } else {
                DB::table('permohonan_sc_detail')->where('permohonan_sc_id', $header->id)->delete();
            }

            // Insert Detail table
            if (count($request->detail) > 0) {
                foreach ($request->detail as $row) {
                    $nmBarang = Barang::where('kd_barang_alias', $row['kode_alias'])->pluck('nama');

                    DB::table('permohonan_sc_detail')->insert([
                        'permohonan_sc_id' => $header->id,
                        'kode_alias' => $row['kode_alias'],
                        'jumlah' => $row['jumlah'],
                        'keterangan' => $row['keterangan'],
                        'dibeli_by' => $row['dibeli_by'],
                        'kelompok_barang' => $row['kelompok_barang'],
                        'nama_barang' => $nmBarang
                    ]);   
                }
            }

            DB::commit();

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

    private static function getWo($wo)
    {
        switch ($wo) {
            case 'perawatan':
                $woFKey = 'prw_data_id';
                break;
            case 'perbaikan':
                $woFKey = 'prb_data_id';
                break;
            case 'prw-rutin':
                $woFKey = 'prw_rutin_id';
                break;
            case 'aduan-non-op':
                $woFKey = 'aduan_non_operasi_id';
                break;
            case 'usulan-non-op':
                $woFKey = 'usulan_non_operasi_id';
                break;
        }

        return $woFKey;
    }

    private static function getTable($wo, $id) {
        switch ($wo) {
            case 'perawatan':
                $data = Perawatan::with('komponen')
                    ->where('id', $id)
                    ->first();
                break;
            case 'perbaikan':
                $data = Perbaikan::with('komponen')
                    ->where('id', $id)
                    ->first();
                break;
            case 'prw-rutin':
                $data = PrwRutin::where('id', $id)->first();
                break;
            case 'aduan non operasi':
                $data = AduanNonOperasi::where('id', $id)
                    ->first();
                break;
            case 'usulan non operasi':
                $data = Usulan::where('id', $id)
                    ->first();
                break;
        }

        return $data;
    }

    public function stok(Request $request)
    {
        try {
            $result = [];
            $kd_barang = $request->kdbarang;

            $data = DB::connection('koneksigudang')->table('v_saldogdg')
            ->select('v_saldogdg.kd_barang', 'v_saldogdg.gudang', 'v_saldogdg.kelompok_barang', 'v_saldogdg.saldo', 'm_barang.kd_barang_alias', 'm_barang_alias.nama')
            ->join('m_barang', 'v_saldogdg.kd_barang', '=', 'm_barang.kd_barang')
            ->join('m_barang_alias', 'm_barang.kd_barang_alias', '=', 'm_barang_alias.recid')
            ->where('gudang', 'like', 'GSC%');

            $data = $data->get();

            if (!empty($data)) {
                foreach ($data as $row) {
                    $result[] = [
                        'kd_barang'         => $row->kd_barang,
                        'gudang'            => $row->gudang,
                        'kelompok_barang'   => $row->kelompok_barang,
                        'saldo'             => $row->saldo,
                        'kd_barang_alias'   => $row->kd_barang_alias,
                        'nama_alias'        => $row->nama,
                    ];
                }
            }

            $return = ['data' => $result];

            return response()->json($return)->setStatusCode(200, 'OK');
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function kirimWaitinglist(Request $request)
    {
        DB::beginTransaction();
        try {
            $nip = $request->header('nip');
            $id = $request->id;

            $data = PermohonanSc::where('id', $id)
                ->where('nip', str_pad($nip, 30))
                ->update([
                    'status' => 'baru'
                ]);

            DB::commit();
            return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Dihapus'])->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $nip = $request->header('nip');
            $id = $request->id;

            $data = PermohonanSc::find($id);

            if ($data->nip == $nip) {
                DB::table('PERMOHONAN_SC_DETAIL')->where('PERMOHONAN_SC_ID', $id)->delete();

                DB::table('PERMOHONAN_SC')->where('id', $id)->delete();

                DB::commit();
            } else {
                return response()->json([
                    'result' => 'error',
                    'message' => 'Nip Tidak Sesuai'])->setStatusCode(500, 'Error');    
            }

            return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Dihapus'])->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }
}
