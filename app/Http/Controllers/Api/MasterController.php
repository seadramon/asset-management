<?php

namespace Asset\Http\Controllers\Api;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Aset,
    Asset\Models\Barang,
    Asset\Models\BarangAlias,
    Asset\Models\Instalasi,
    Asset\Models\Lokasi,
    Asset\Models\Kategori,
    Asset\Models\SubKategori,
    Asset\Models\SubSubKategori,
    Asset\Models\Ruangan;

use DB;
use Html;
use Validator;
use Redirect;
use Session;

class MasterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function instalasi(Request $request)
    {
        $title = $request->get('name');
        $id = $request->get('id');
        $start = $request->get('start'); //offset
        $limit = $request->get('limit'); 

        try {
            $result = [];

            $data = Instalasi::select('instalasi.*');

            if (!empty($title)) {
                $data = $data->where('name', 'like', '%'.$title.'%');
            }

            if (!empty($id)) {
                $data = $data->where('id', $id);
            }

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            $data = $data->get()->toArray();
            
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $result[] = [
                        'id'        => $row['id'],
                        'hashid'    => $row['hashid'],
                        'kode'      => $row['kode'],
                        'name'      => $row['name'],
                        'alamat'      => $row['alamat'],
                        'lat'      => $row['lat'],
                        'lon'      => $row['lon']
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

    public function lokasi(Request $request)
    {
        $title = $request->get('name');
        $id = $request->get('id');
        $instalasi_id = $request->get('instalasiId');
        $start = $request->get('start'); //offset
        $limit = $request->get('limit'); 

        try {
            $result = [];

            $data = Lokasi::select('lokasi.*');

            if (!empty($title)) {
                $data = $data->where('name', 'like', '%'.$title.'%');
            }

            if (!empty($id)) {
                $data = $data->where('id', $id);
            }

            if (!empty($instalasi_id)) {
                $data = $data->where('instalasi_id', $instalasi_id);
            }

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            $data = $data->get()->toArray();
            
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $result[] = [
                        'id'                  => $row['id'],
                        'instalasi_id'        => $row['instalasi_id'],
                        'hashid'              => $row['hashid'],
                        'kode'                => $row['kode'],
                        'name'                => $row['name'],
                        'lat'                 => $row['lat'],
                        'lon'                 => $row['lon']
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

    public function lokasiSelect($id)
    {
        $aa = Lokasi::where('instalasi_id', $id)->get();
        $txt = '';
        foreach ($aa as $row) {
            $txt .= '<option value="' . $row->id . '">' . $row->name . '</option>';
        }

        return response()->json([
            'data' => $txt
        ]);
    }

    public function ruangSelect($id, Request $request)
    {
        if (!empty($request->instalasi)) {
            $aa = Ruangan::where('lokasi_id', $id)
                ->where('instalasi_id', $request->instalasi)
                ->get();
        } else {
            $aa = Ruangan::where('lokasi_id', $id)->get();    
        }
        
        $txt = '';
        foreach ($aa as $row) {
            $txt .= '<option value="' . $row->id . '">' . $row->name . '</option>';
        }

        return response()->json([
            'data' => $txt
        ]);
    }

    public function asetSelect(Request $request)
    {
        $instalasi_id = !empty($request->instalasi)?$request->instalasi:"";
        $lokasi_id = !empty($request->lokasi)?$request->lokasi:"";
        $ruang_id = !empty($request->ruang)?$request->ruang:"";

        $query = Aset::where('instalasi_id', $instalasi_id);

        if ($lokasi_id) {
            $query->where('lokasi_id', $lokasi_id);
        }

        if ($ruang_id) {
            $query->where('ruang_id', $ruang_id);
        }
        
        $data = $query->get();

        $txt = '';
        foreach ($data as $row) {
            $txt .= '<option value="' . $row->id . '">[' . $row->kode_aset . '] ' .$row->nama_aset . '</option>';
        }

        return response()->json([
            'data' => $txt
        ]);
    }

    public function subkategoriSelect($id)
    {
        $aa = SubKategori::where('kategori_id', $id)->get();
        $txt = '';
        foreach ($aa as $row) {
            $txt .= '<option value="' . $row->id . '">' . $row->name . '</option>';
        }

        return response()->json([
            'data' => $txt
        ]);
    }

    public function subsubkategoriSelect($id, Request $request)
    {
        if (!empty($request->kategori)) {
            $aa = SubSubKategori::where('sub_kategori_id', $id)
                ->where('kategori_id', $request->kategori)
                ->get();
        } else {
            $aa = SubSubKategori::where('sub_kategori_id', $id)->get();    
        }
        
        $txt = '';
        foreach ($aa as $row) {
            $txt .= '<option value="' . $row->id . '">' . $row->name . '</option>';
        }

        return response()->json([
            'data' => $txt
        ]);
    }

    public function status(Request $request)
    {
        try {
            $nip = $request->header('nip');
            
            $status = [
                'Baru',
                'Investigasi',
                'Sudah diinvestigasi',
                'Menunggu Approval Manajer Pemeliharaan',
                'Menunggu Approval Manajer DalOps (Optional)',
                'Menunggu Approval Manajer DalOps',
                'Penanganan',
                'Menunggu Approval Manajer DalOps',
                'Sudah ditangani',
                'Selesai'
            ];

            return response()->json($status)->setStatusCode(200, 'OK');
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function barang(Request $request)
    {
        $title = $request->get('nama');
        $id = $request->get('recid');
        $start = $request->get('start'); //offset
        $limit = $request->get('limit'); 

        try {
            $result = [];

            /*$data = BarangAlias::select('M_BARANG.KD_BARANG', 'M_BARANG_ALIAS.RECID', 'M_BARANG_ALIAS.nama', 'M_BARANG.KELOMPOK_BARANG', 'm_barang.SATUAN')
                ->join('M_BARANG', 'm_barang_alias.RECID', '=', 'm_barang.KD_BARANG_ALIAS')
                ->where('m_barang.KELOMPOK_BARANG', 'like', 'SC%')
                ->groupBy('M_BARANG.KD_BARANG', 'M_BARANG_ALIAS.RECID', 'M_BARANG_ALIAS.nama', 'M_BARANG.KELOMPOK_BARANG', 'm_barang.SATUAN');*/

            $data = DB::connection('koneksigudang')->table('v_saldogdg')
                ->select('v_saldogdg.kd_barang', 'v_saldogdg.gudang', 'v_saldogdg.kelompok_barang', 'v_saldogdg.saldo', 'm_barang.kd_barang_alias', 'm_barang_alias.nama', 'm_barang.SATUAN', 'm_gudang.nama_gdg')
                ->join('m_barang', 'v_saldogdg.kd_barang', '=', 'm_barang.kd_barang')
                ->join('m_barang_alias', 'm_barang.kd_barang_alias', '=', 'm_barang_alias.recid')
                ->join('m_gudang', 'v_saldogdg.gudang', '=', 'm_gudang.kd_gdg')
                ->where('gudang', 'like', 'GSC%');
// dd($data->get()->toArray());
            if (!empty($title)) {
                $data = $data->where('m_barang_alias.nama', 'like', '%'.$title.'%');
            }

            if (!empty($id)) {
                $data = $data->where('m_barang_alias.recid', $id);
            }

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            $data = $data->get();
            
            if (count($data) > 0) {
                foreach ($data as $row) {
                    // dd($row->kd_barang);
                    $result[] = [
                        'kd_barang'         => $row->kd_barang,
                        'gudang'            => $row->gudang,
                        'kelompok_barang'   => $row->kelompok_barang,
                        'saldo'             => $row->saldo,
                        'kd_barang_alias'   => $row->kd_barang_alias,
                        'nama'              => $row->nama,
                        'satuan'            => $row->satuan,
                        'nama_gdg'          => $row->nama_gdg,
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

    public function unitKerja(Request $request)
    {
        $start = $request->get('start'); //offset
        $limit = $request->get('limit'); 

        try {
            $result = [];

            $data = DB::connection('koneksigudang')->table('unit_kerja')
                ->whereIn('TRIM(kd_unitkrj)', ['B32', 'B33']);

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            $data = $data->get();
            
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $result[] = [
                        'kd_unitkrj'        => $row->kd_unitkrj,
                        'nama'              => $row->nama,
                        'alamat'            => $row->alamat,
                        'nip_kepala'        => $row->nip_kepala,
                        'nama_kepala'        => $row->nama_kepala,
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

    public function gudang(Request $request)
    {
        $start = $request->get('start'); //offset
        $limit = $request->get('limit'); 

        try {
            $result = [];

            $data = DB::connection('koneksigudang')->table('m_gudang')
                ->where('sukucadang', '1');

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            $data = $data->get();
            
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $result[] = [
                        'recid'        => $row->recid,
                        'kd_gdg'       => $row->kd_gdg,
                        'nama_gdg'     => $row->nama_gdg,
                        'ket'          => $row->ket,
                        'sukucadang'   => $row->sukucadang,
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

    public function allAset(Request $request) {
        $start = $request->get('start'); //offset
        $limit = $request->get('limit'); 

        try {
            $result = []; 

            $data = Aset::select('aset.*');

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }
            
            $data = $data->get()->toArray();
            
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $result[] = [
                        'id'        => $row['id'],
                        'nama_aset'    => $row['nama_aset'],
                        'kode_barcode'      => $row['kode_barcode']
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
}
