<?php

namespace Asset\Http\Controllers\Api;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Aset,
    Asset\Models\Instalasi,
    Asset\Models\PrwrutinPdm,
    Asset\Models\PermohonanSc,
    Asset\Models\PermohonanScDetail,
    Asset\Models\Prw52w,
    Asset\Models\Prw4w;

use DB;
use Html;
use Validator;
use Redirect;
use Session;
use Image;
use Storage;

class RutinController extends Controller
{
    protected $disk = "sftp";
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $start = $request->get('start'); //offset
        $limit = $request->get('limit');
        $nip = $request->header('nip');

        $lokasi = lokasi($nip);
        $bagian = bagian($nip);
        
        if (date('W') == '52') {
            $tahun = '2021';
        } else {
            $tahun = date('Y');
        }

        try {
            $result = [];
            $woGroup = Prw4w::select('wo_id')
                ->join('prw_52w', 'prw_4w.prw_52w_id', '=', 'prw_52w.id')
                ->join('aset', 'prw_52w.komponen_id', '=', 'aset.id')
                ->join('instalasi', 'prw_52w.instalasi_id', '=', 'instalasi.id')
                ->join('ms_komponen_detail', 'prw_52w.part', '=', 'ms_komponen_detail.id')
                ->whereIn('aset.bagian', $bagian)
                ->where('prw_4w.petugas', trim($nip))
                //->where('prw_4w.urutan_minggu', '41')
                 ->where('prw_4w.urutan_minggu', date('W'))
                ->whereRaw("TO_CHAR(prw_4w.TANGGAL_MONITORING, 'YYYY') = $tahun")
                ->where('aset.kondisi_id', '<>', '12')
                ->whereNotIn('prw_4w.status', ['99', '0.3', '0.6'])
                ->groupBy('wo_id')
                ->get('wo_id')->pluck('wo_id')->toArray();            

            $data = Prw4w::select('prw_4w.*', 'prw_52w.komponen_id', 'prw_52w.perawatan', 'prw_52w.instalasi_id', 'aset.id as aset_id', 'aset.nama_aset', 'aset.kode_barcode', 'aset.bagian', 'instalasi.name as instalasinya', 'ms_komponen_detail.part', 'aset.lokasi_id', 'lokasi.name as lokasi')
                ->join('prw_52w', 'prw_4w.prw_52w_id', '=', 'prw_52w.id')
                ->join('aset', 'prw_52w.komponen_id', '=', 'aset.id')
                ->join('instalasi', 'prw_52w.instalasi_id', '=', 'instalasi.id')
                ->join('lokasi', 'aset.lokasi_id', '=', 'lokasi.id')
                ->join('ms_komponen_detail', 'prw_52w.part', '=', 'ms_komponen_detail.id')
                ->whereIn('aset.bagian', $bagian)
                ->where('prw_4w.petugas', trim($nip))
                //->where('prw_4w.urutan_minggu', '41')
                 ->where('prw_4w.urutan_minggu', date('W'))
                ->whereRaw("TO_CHAR(prw_4w.TANGGAL_MONITORING, 'YYYY') = $tahun")
                ->where('aset.kondisi_id', '<>', '12')
                ->whereNotIn('prw_4w.status', ['99', '0.3', '0.6']);

            $dataSc = [];
            $sqlSc = PermohonanSc::with(['detail', 'namaSpv'])
                ->whereIn('prw_rutin_id', $woGroup)
                ->get();

            foreach ($sqlSc as $row) {
                $dataSc[$row->prw_rutin_id][$row->id] = [
                    'nama' => $row->nama,
                    'status' => $row->status,
                    'nipspv' => $row->nip,
                    'spv' => $row->namaSpv->nama,
                    'keterangan' => $row->keterangan,
                ];

                if (!empty($row->detail)) {
                    foreach ($row->detail as $det) {
                        $dataSc[$row->prw_rutin_id][$row->id]['detail_sukucadang'][] = [
                            'kode_alias' => $det->kode_alias,
                            'nama_barang' => $det->barang->nama,
                            'jumlah' => $det->jumlah,
                            'keterangan' => $det->keterangan,
                            'kelompok_barang' => $det->kelompok_barang,
                            'dibeli_by' => $det->dibeli_by,
                            'kode_gudang' => !empty($det->gudang)?$det->gudang:"",
                            'gudang' => !empty($det->gudangRelasi)?$det->gudangRelasi->nama_gdg:"",
                        ];        
                    }
                }
            }

            if ($start !== null) {
                $data = $data->offset($start);
            }

            if ($limit !== null) {
                $data = $data->limit($limit + 1);
            }

            $data = $data->get();
            // dd($data);
            if (count($data) > 0) {
                $tempWo = [];
                foreach ($data as $row) {
                    $period = date("Y-m", strtotime($row->tanggal_selesai));
                    
                    if (!in_array($row->wo_id, $tempWo)) {
                        $tempWo[] = $row->wo_id;

                        $result[$row->wo_id] = [
                            'wo_id' => $row->wo_id,
                            'aset_id' => $row->aset_id,
                            'nama_aset' => $row->nama_aset,
                            'instalasi_id' => $row->instalasi_id,
                            'instalasi' => $row->instalasinya,
                            'lokasi_id' => $row->lokasi_id,
                            'lokasi' => $row->lokasi,
                            'kode_barcode' => $row->kode_barcode,
                            'bagian' => $row->bagian,
                            'urutan_minggu' => $row->urutan_minggu,
                            'hari' => $row->hari,
                            'petugas' => $row->petugas,
                            'tanggal' => $row->tanggal_monitoring,
                            'tanggal_disposisi' => $row->tanggal,
                            'sukucadang' => !empty($dataSc[$row->wo_id])?$dataSc[$row->wo_id]:null
                        ];

                        $result[$row->wo_id]['detail_perawatan'][] = [
                                'id' => $row->id,
                                'part' => $row->part,
                                'perawatan' => $row->perawatan,
                                'foto' => !empty($row->foto)?url('pic-api/gambar/perawatan_rutin&'.$period.'&'.$row->foto):"",
                                //'foto' => !empty($row->foto)?$row->foto:"",
                                'status' => $row->status
                            ];
                    } else {
                        $result[$row->wo_id]['detail_perawatan'][] = [
                            'id' => $row->id,
                            'part' => $row->part,
                            'perawatan' => $row->perawatan,
                            'foto' => !empty($row->foto)?url('pic-api/gambar/perawatan_rutin&'.$period.'&'.$row->foto):"",
                            //'foto' => !empty($row->foto)?$row->foto:"",
                            'status' => $row->status
                        ];
                    }                    
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

    public function penanganan(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = [];
            $nip = $request->header('nip');
            $woId = $request->wo_id;

            $filename = null;
            if ($request->hasFile('foto')) {
                $id = $request->id;
                $file = $request->file('foto');
                $extension = $file->getClientOriginalExtension();
                $filename = $woId .'_'.$id.'.'. $extension;

                $dir = 'perawatan_rutin/'.date('Y-m');
                cekDir($dir);
                Storage::disk($this->disk)->put($dir .'/'. $filename, \File::get($file));

                $data = [
                    'status' => '1',
                    'tanggal_selesai' => DB::raw("TO_DATE('" . date('dmY H:i:s') . "','DDMMYYYY HH24:MI:SS')"),
                    'foto' => $filename
                ];
            }

            $data = Prw4w::where('id', $request->id)->update($data);

            DB::commit();

            // Notif
            // $perawatan = PrwRutin::with('aset')->where('id', $request->id)->first();
            /*$notif = kirimnotif($request->petugas_id,
                [
                    'title' => 'Pemberitahuan WO Perawatan Rutin',
                    'text' => sprintf('Pemberitahuan WO Perawatan Rutin untuk %s', $perawatan->aset->nama_aset),
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_NOTIF',
                    'tipe' => '262', 
                    'id' => $request->id
                ]
            );*/
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
