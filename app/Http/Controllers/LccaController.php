<?php
namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Aset;
use Asset\Models\Ms4w;
use Asset\Models\MasterFm;
use Asset\Models\BiayaOperasional;
use Asset\Models\BiayaPemeliharaan;
use Asset\Models\Aktifitas;
use Asset\Models\Perawatan;
use Asset\Models\Perbaikan;
use Asset\Models\Prw4w;
use Asset\Models\Barang;

use DB;
use Datatables;
use Session;
use Validator;

/**
 * 
 */
class LccaController extends Controller
{
	
	public function index($id)
	{
		$aset = Aset::find($id);

        $totalOperasional = BiayaOperasional::where('aset_id', $aset->id)->sum('biaya');

        $totalPemeliharaan = BiayaPemeliharaan::where('aset_id', $aset->id)->sum('total_biaya');

        $arrTotal = [
            'akusisi' => !empty($aset->harga)?$aset->harga:0,
            'penghapusan' => !empty($aset->penghapusan_biaya)?$aset->penghapusan_biaya:0,
            'operasional' => !empty($totalOperasional)?$totalOperasional:0,
            'pemeliharaan' => !empty($totalPemeliharaan)?$totalPemeliharaan:0,
        ];

        $totalLcc = array_sum($arrTotal);

		return view('pages.aset.lcc_main', [
			'aset' => $aset,
            'totalOperasional' => $totalOperasional,
            'totalPemeliharaan' => $totalPemeliharaan,
            'arrTotal' => $arrTotal,
            'totalLcc' => $totalLcc
		]);
	}

	public function akuisisiStore(Request $request)
	{
		DB::beginTransaction();

        try {
            $data = Aset::where('id', $request->id)->update([
                    'akuisisi_spk' => $request->akuisisi_spk,
                    'akuisisi_berita_acara' => $request->akuisisi_berita_acara,
               ]);

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('lcca::index', ['id' => $request->id]);
	}

	public function penghapusanStore(Request $request)
	{
		DB::beginTransaction();

        try {
            $data = Aset::where('id', $request->id)->update([
                    'penghapusan_biaya' => $request->penghapusan_biaya,
                    'penghapusan_spk' => $request->penghapusan_spk,
                    'penghapusan_berita_acara' => $request->penghapusan_berita_acara,
               ]);

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('lcca::index', ['id' => $request->id]);
	}

    public function operasionalData(Request $request)
    {
        
    }

	public function operasionalStore(Request $request)
	{
		DB::beginTransaction();
// dd($request->all());
        try {
        	if (!empty($request->id)) {
                $data = BiayaOperasional::find($request->id);
            } else {
                $data = new BiayaOperasional;
            }

            $data->tanggal = $request->tanggal;
            $data->pemakaian = $request->pemakaian;
            $data->harga = $request->harga;
            $data->biaya = $request->harga * $request->pemakaian;
            $data->aset_id = $request->aset_id;

            $data->save();

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('lcca::index', ['id' => $request->aset_id]);
	}

    public function operasionalDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = BiayaOperasional::find($request->id);

            $data->delete();

            DB::commit();
            Session::flash('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal dihapus');
        }

        return redirect()->route('lcca::index', ['id' => $request->aset_id]);
    }

    public function pemeliharaanEntri(Request $request)
    {
        $aset_id = $request->aset_id;
        $wo = $request->wo;
        $wo_id = $request->wo_id;
// dd($wo);
        switch ($wo) {
            case 'Perbaikan dari Aduan':
                $fkey = 'prb_data_id';
                break;
            case 'Perbaikan dari Monitoring':
                $fkey = 'prb_data_id';
                break;
            case 'Perawatan dari Monitoring':
                $fkey = 'prw_data_id';
                break;
            case 'Perawatan Rutin':
                $fkey = 'prw_rutin_id';
                break;
        }

        $id = $request->id;
        $data = null;
        $aktifitas = [];
        $dataSc = null;
        
        if ($id) {
            $data = BiayaPemeliharaan::find($id);

            $aktifitas = $data->aktifitas;
        }

        // Get Existing Suku cadang
        $dataSc = listSukucadang($fkey, $wo_id, false, true);
        $dataScWait = listSukucadang($fkey, $wo_id, true);

        $tmpKdAlias = $dataSc->pluck('kode_alias')->toArray();
        $tmpKdAliasWait = $dataScWait->pluck('kode_alias')->toArray();
        $arrKdAlias = array_merge($tmpKdAlias, $tmpKdAliasWait);

        $pairKodeAlias = sukucadangSaldo($arrKdAlias);

        $keyPairKodeAlias = [];
        if (count($pairKodeAlias) > 0) {
            foreach ($pairKodeAlias as $key => $value) {
                $keyPairKodeAlias[] = $key;
            }
        }

        return view('pages.aset.pemeliharaan', [
            'data' => $data,
            'aktifitas' => $aktifitas,
            'aset_id' => $aset_id,
            'wo' => $wo,
            'wo_id' => $wo_id,
            'dataSc' => $dataSc,
            'dataScWait' => $dataScWait,
            'pairKodeAlias' => $pairKodeAlias,
            'keyPairKodeAlias' => $keyPairKodeAlias
        ]);
    }

    public function pemeliharaanData(Request $request)
    {
        $komponen = $request->komponen;

        $query = collect(DB::select(
            DB::raw("SELECT
                *
            FROM(
                SELECT
                    BIAYA_PEMELIHARAAN.id AS recid, PRB_DATA.tanggal, BIAYA_PEMELIHARAAN.spk, BIAYA_PEMELIHARAAN.BERITA_ACARA, PRb_DATA.id AS WO_ID, PRB_DATA.JENIS_PENANGANAN, 
                    (case PRB_DATA.TIPE
                        when 'aduan' THEN 'Perbaikan dari Aduan'
                        when 'monitoring' THEN 'Perbaikan dari Monitoring'
                    end) as WO, 
                    PRB_DATA.METODE,
                    BIAYA_PEMELIHARAAN.TOTAL_BIAYA
                FROM
                    BIAYA_PEMELIHARAAN
                    RIGHT JOIN PRB_DATA ON BIAYA_pemeliharaan.WO_ID = prb_data.ID
                    -- left JOIN PERMOHONAN_SC ON prb_data.ID = permohonan_sc.PRB_DATA_ID
                WHERE
                    prb_data.KOMPONEN_ID = :komponen
                    and prb_data.metode <> :metode
                UNION ALL
                SELECT
                    BIAYA_PEMELIHARAAN.id AS recid, PRB_DATA.tanggal, BIAYA_PEMELIHARAAN.spk, BIAYA_PEMELIHARAAN.BERITA_ACARA, PRb_DATA.id AS WO_ID, PRB_DATA.JENIS_PENANGANAN, (case PRB_DATA.TIPE
                        when 'aduan' THEN 'Perbaikan dari Aduan'
                        when 'monitoring' THEN 'Perbaikan dari Monitoring'
                    end) as WO, PRB_DATA.METODE,
                    BIAYA_PEMELIHARAAN.TOTAL_BIAYA
                FROM
                    BIAYA_PEMELIHARAAN
                    RIGHT JOIN PRB_DATA ON BIAYA_pemeliharaan.WO_ID = prb_data.ID
                    inner JOIN PERMOHONAN_SC ON prb_data.ID = permohonan_sc.PRB_DATA_ID
                WHERE
                    prb_data.KOMPONEN_ID = :komponen
                    and prb_data.metode = :metode
                UNION ALL
                SELECT
                    BIAYA_PEMELIHARAAN.id AS recid, PRW_DATA.tanggal, BIAYA_PEMELIHARAAN.spk, BIAYA_PEMELIHARAAN.BERITA_ACARA, PRW_DATA.id AS WO_ID, PRW_DATA.JENIS_PENANGANAN, 'Perawatan dari Monitoring' AS WO, PRW_DATA.METODE,
                    BIAYA_PEMELIHARAAN.TOTAL_BIAYA
                    -- ,permohonan_sc.ID, sc_detail.group_kode_alias, sc_detail.group_nama_barang
                FROM
                    BIAYA_PEMELIHARAAN
                    RIGHT JOIN PRW_DATA ON BIAYA_pemeliharaan.WO_ID = prW_data.ID
                    --left JOIN PERMOHONAN_SC ON prW_data.ID = permohonan_sc.PRW_DATA_ID
                WHERE
                    prW_data.KOMPONEN_ID = :komponen
                    and prW_data.metode <> :metode
                UNION ALL
                SELECT
                    BIAYA_PEMELIHARAAN.id AS recid, PRW_DATA.tanggal, BIAYA_PEMELIHARAAN.spk, BIAYA_PEMELIHARAAN.BERITA_ACARA, PRW_DATA.id AS WO_ID, PRW_DATA.JENIS_PENANGANAN, 'Perawatan dari Monitoring' AS WO, PRW_DATA.METODE,
                    BIAYA_PEMELIHARAAN.TOTAL_BIAYA
                    -- ,permohonan_sc.ID, sc_detail.group_kode_alias, sc_detail.group_nama_barang
                FROM
                    BIAYA_PEMELIHARAAN
                    RIGHT JOIN PRW_DATA ON BIAYA_pemeliharaan.WO_ID = prW_data.ID
                    -- left JOIN PERMOHONAN_SC ON prW_data.ID = permohonan_sc.PRW_DATA_ID
                WHERE
                    prW_data.KOMPONEN_ID = :komponen
                    and prW_data.metode = :metode
                UNION ALL
                SELECT 
                    BIAYA_PEMELIHARAAN.id AS recid, PRW_4W.TANGGAL_MONITORING AS TANGGAL, BIAYA_PEMELIHARAAN.spk, BIAYA_PEMELIHARAAN.BERITA_ACARA, PRW_4W.id AS WO_ID, 
                    PRW_52W.PERAWATAN AS JENIS_PENANGANAN, 'Perawatan Rutin' AS WO, 'metode' AS METODE,
                    BIAYA_PEMELIHARAAN.TOTAL_BIAYA
                    -- ,permohonan_sc.ID, sc_detail.group_kode_alias, sc_detail.group_nama_barang
                FROM
                    BIAYA_PEMELIHARAAN
                    RIGHT JOIN PRW_4W ON BIAYA_pemeliharaan.WO_ID = PRW_4W.ID
                    INNER JOIN PRW_52W ON PRW_4W.PRW_52W_ID = PRW_52W.ID
                    INNER JOIN PERMOHONAN_SC ON prW_4W.ID = permohonan_sc.PRW_rutin_ID
                    /*LEFT JOIN (
                        SELECT 
                            permohonan_sc_id, listagg(kode_alias, ',') AS group_kode_alias, listagg(nama_barang, ',') AS group_nama_barang
                        FROM
                            PERMOHONAN_SC_DETAIL
                        GROUP BY permohonan_sc_id
                    ) sc_detail ON permohonan_sc.ID = sc_detail.PERMOHONAN_SC_ID*/
                WHERE
                    PRW_52W.KOMPONEN_ID = :komponen
            )"
        ), [
            'komponen' => $request->komponen, 
            'metode' => 'internal'
        ]));

        return Datatables::of($query)
            ->addColumn('menu', function ($model) use($komponen){
                    if ( !empty( $model->recid ) ) {
                        // $link = '<button type="button" class="btn btn-outline bg-primary border-primary text-primary-800 btn-icon editPemeliharaan" data-toggle="modal" data-target="#edit_pemeliharaan"><i class="icon-pencil"></i> </button>';
                        $link = '<a href="' . route('lcca::pemeliharaan-entri', ['aset_id' => $komponen, 'id' => $model->recid, 'wo' => $model->wo, 'wo_id' => $model->wo_id]) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Edit </a>';
                    } else {
                        $link = '<a href="' . route('lcca::pemeliharaan-entri', ['aset_id' => $komponen, 'wo' => $model->wo, 'wo_id' => $model->wo_id]) . '" class="btn btn-sm purple"><i class="fa fa-edit"></i> Add </a>';
                    }
                    
                    return $link;
                })
            ->make(true);
    }

    public function pemeliharaanStore(Request $request)
    {
        DB::beginTransaction();

        try {
            if (!empty($request->id)) {
                $data = BiayaPemeliharaan::find($request->id);

                $data->spk = $request->spk;
                $data->berita_acara = $request->berita_acara;
                // $data->jumlah_biaya = $request->jumlah * $request->biaya;

                $data->save();
                DB::commit();

                // reset
                Aktifitas::where('biaya_pemeliharaan_id', $request->id)->delete();

                // insert ulang
                $tmpTotal = [];
                if (count($request->arrAktifitas) > 0) {
                    $arrDetail = [];
                    foreach ($request->arrAktifitas as $detail) {
                        if (!empty($detail['name'])) {
                            $jmlBiaya = $detail['jumlah'] * $detail['biaya'];

                            $arrDetail[] = [
                                'biaya_pemeliharaan_id' => $request->id,
                                'name' => $detail['name'],
                                'jumlah' => $detail['jumlah'],
                                'satuan' => $detail['satuan'],
                                'biaya' => $detail['biaya'],
                                'jumlah_biaya' => $jmlBiaya
                            ];

                            $tmpTotal[] = $jmlBiaya;
                        }
                    }

                    Aktifitas::insert($arrDetail);

                    if ( count($tmpTotal) > 0 ) {
                        $dataTotalBiaya = BiayaPemeliharaan::find($request->id);
                        $dataTotalBiaya->total_biaya = array_sum($tmpTotal);

                        $dataTotalBiaya->save();
                    }
                }
            } else {
                $data = new BiayaPemeliharaan;

                $data->aset_id = $request->aset_id;
                $data->wo = $request->wo;
                $data->wo_id = $request->wo_id;
                $data->spk = $request->spk;
                $data->berita_acara = $request->berita_acara;

                $data->save();

                $idBiaya = $data->id;
                DB::commit();

                $tmpTotal = [];
                if (count($request->arrAktifitas) > 0) {
                    $arrDetail = [];
                    foreach ($request->arrAktifitas as $detail) {
                        if (!empty($detail['name'])) {
                            $jmlBiaya = $detail['jumlah'] * $detail['biaya'];

                            $arrDetail[] = [
                                'biaya_pemeliharaan_id' => $idBiaya,
                                'name' => $detail['name'],
                                'jumlah' => $detail['jumlah'],
                                'satuan' => $detail['satuan'],
                                'biaya' => $detail['biaya'],
                                'jumlah_biaya' => $jmlBiaya
                            ];

                            $tmpTotal[] = $jmlBiaya;
                        }
                    }

                    Aktifitas::insert($arrDetail);

                    if ( count($tmpTotal) > 0 ) {
                        $dataTotalBiaya = BiayaPemeliharaan::find($idBiaya);
                        $dataTotalBiaya->total_biaya = array_sum($tmpTotal);

                        $dataTotalBiaya->save();
                    }
                }
            }

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('lcca::index', ['id' => $request->aset_id]);
    }

    public function analisis()
    {
        $instalasi = selectInstalasi();

        $bagian = selectBagian();

        $opsi_a = ["" => "- Pilih Aset A -"];
        $opsi_b = ["" => "- Pilih Aset B -"];
        $opsi_c = ["" => "- Pilih Aset C -"];

        return view('pages.aset.lcca', [
            'instalasi' => $instalasi,
            'bagian' => $bagian,
            'opsi_a' => $opsi_a,
            'opsi_b' => $opsi_b,
            'opsi_c' => $opsi_c
        ]);
    }

    public function analisisData(Request $request)
    {
        
    }

    public function assetSelect(Request $request)
    {
        $instalasi = $request->instalasi;
        $bagian = $request->bagian;

        $temp = Aset::where('instalasi_id', $instalasi)
            ->where('availability', '1');

        if ( !empty($bagian) ) {
            $temp = $temp->where('bagian', $bagian);
        }

        $data = $temp->get();
        $result = "";

        foreach ($data as $row) {
            $result .= '<option value="' . $row->id . '">' . $row->nama_aset . '</option>';
        }

        return response()->json([
            'data' => $result
        ]);
    }

    public function comparison(Request $request)
    {
        $arrTmp = [
            'opsi_a' => $request->opsi_a,
            'opsi_b' => $request->opsi_b,
            'opsi_c' => $request->opsi_c
        ];

        $arrData = [];
        $arrCostElemen = [];

        foreach ($arrTmp as $key => $aset_id) {
            if ( !empty($aset_id) ) {
                $aset = Aset::find($aset_id);

                $totalOperasional = BiayaOperasional::where('aset_id', $aset->id)->sum('biaya');

                $totalPemeliharaan = BiayaPemeliharaan::where('aset_id', $aset->id)->sum('total_biaya');

                $arrTotal = [
                    'akusisi' => !empty($aset->harga)?$aset->harga:0,
                    'penghapusan' => !empty($aset->penghapusan_biaya)?$aset->penghapusan_biaya:0,
                    'operasional' => !empty($totalOperasional)?$totalOperasional:0,
                    'pemeliharaan' => !empty($totalPemeliharaan)?$totalPemeliharaan:0,
                ];

                $totalLcc = array_sum($arrTotal);

                $arrTotal['totalLcc'] = $totalLcc;

                if ( $key != "opsi_a" ) {
                    $arrTotal['delta'] = $totalLcc - $arrData['opsi_a']['totalLcc'];

                    $arrCost = [
                        'akusisi' => self::costElemen($arrData['opsi_a']['akusisi'], $arrTotal['akusisi'], $arrData['opsi_a']['akusisi']),
                        'penghapusan' => self::costElemen($arrData['opsi_a']['penghapusan'], $arrTotal['penghapusan'], $arrData['opsi_a']['penghapusan']),
                        'operasional' => self::costElemen($arrData['opsi_a']['operasional'], $arrTotal['operasional'], $arrData['opsi_a']['operasional']),
                        'pemeliharaan' => self::costElemen($arrData['opsi_a']['pemeliharaan'], $arrTotal['pemeliharaan'], $arrData['opsi_a']['pemeliharaan']),
                        'totalLcc' => self::costElemen($arrData['opsi_a']['totalLcc'], $arrTotal['totalLcc'], $arrData['opsi_a']['totalLcc']),
                    ];
                } else {
                    $arrTotal['delta'] = "-";

                    $arrCost = [
                        'akusisi' => self::costElemen($arrTotal['akusisi'], $arrTotal['akusisi'], $arrTotal['akusisi']),
                        'penghapusan' => self::costElemen($arrTotal['penghapusan'], $arrTotal['penghapusan'], $arrTotal['penghapusan']),
                        'operasional' => self::costElemen($arrTotal['operasional'], $arrTotal['operasional'], $arrTotal['operasional']),
                        'pemeliharaan' => self::costElemen($arrTotal['pemeliharaan'], $arrTotal['pemeliharaan'], $arrTotal['pemeliharaan']),
                        'totalLcc' => self::costElemen($arrTotal['totalLcc'], $arrTotal['totalLcc'], $arrTotal['totalLcc']),
                    ];
                }

                $arrData[$key] = $arrTotal;
                $arrCostElemen[$key] = $arrCost;
            }
        }
// dd($arrCostElemen);
        return view('pages.aset.lcca_part', [
            'data' => $arrData,
            'reltolow' => $arrCostElemen
        ])->render();
    }

    private static function costElemen( $param1, $param2, $param3 )
    {
        $res = 0;

        if ($param3 > 0) {
            $res = round(1 - ( ( $param1 - $param2 ) / $param3 ), 2) * 100;
        }

        return (int)$res;
    }

    public function pemeliharaanSukucadang(Request $request){
        $kodeAlias = $request->kodealias;
        $recid = $request->recid;

        if ( !empty($recid) ) {

        } else {
            if ( !empty($kodeAlias) ) {
                $arrKodeAlias = explode(",", $kodeAlias);

                $data = Barang::whereIn('kd_barang_alias', $arrKodeAlias)->get();

                $jml = sizeof($data);

                return view('pages.aset.partsukucadang', [
                    'arrData' => $data,
                    'jmlSukucadang' => $jml])->render();
            }
        }
    }
}