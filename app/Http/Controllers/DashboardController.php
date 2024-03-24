<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;

use Asset\Models\Aset,
    Asset\Models\AsetType,
    Asset\Models\Kondisi,
    Asset\Models\Kategori;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function linkperawatanRutin(Request $request)
    {
        // dd($request->all());
        $week = [
            "" => "- Pilih Minggu-"
        ];

        $tahun = date('Y');
        $minggu_ke = date('W');

        if (!empty($request->minggu_ke)) {
            $minggu_ke = $request->minggu_ke;
        }

        for ($i = 1; $i <= lastWeekNumber(); $i++) { 
            $week[$i] = "Minggu ke ".$i;
        }

        $metabaseSiteUrl =  env('METABASE_URL');
        $metabaseSecretKey = env('METABASE_SECRET_KEY');

        $signer = new Sha256();
        $token = (new Builder())
            ->withClaim('resource', [
                'dashboard' => 94
            ])
            ->withClaim('params', (object)[
                'tahun' => $tahun,
                'minggu_ke' => $minggu_ke
            ])
            ->getToken($signer, new Key($metabaseSecretKey));
            
        $iframeUrl = "{$metabaseSiteUrl}/embed/dashboard/{$token}#bordered=true&titled=true&theme=night";
        
        return view('pages.dashboard.perawatan_rutin', [
            'week' => $week,
            'minggu_ke' => $minggu_ke,
            'defaultUrl'=> $iframeUrl
        ]);
    }

    public function linkAsset(Request $request)
    {
        $asetType = AsetType::where('id', '<>', '3')->orderBy('id')->get();

        $kondisi = Kondisi::orderBy('id')->get();
        $kondisiQuery = "";

        foreach ($asetType as $type) {
            foreach ($kondisi as $row) {
                $kondisiQuery .= sprintf(", SUM(CASE WHEN jenis_id = '%d' AND KONDISI_ID = '%d' THEN 1 ELSE 0 END) as %s", $type->id, $row->id, strtolower(str_replace(" ", "_", $type->name)).'_'.strtolower($row->kode));    
            }
        }

        $aset = Aset::with(['type'])
        ->selectRaw("SUM(CASE WHEN jenis_id = '1' THEN 1 ELSE 0 END) as typeAset, 
            SUM(CASE WHEN jenis_id = '2' THEN 1 ELSE 0 END) as typeNonAset,
            SUM(CASE WHEN jenis_id = '1' AND KATEGORI_ID = '1' THEN 1 ELSE 0 END) as aset_utama,
            SUM(CASE WHEN jenis_id = '1' AND KATEGORI_ID = '2' THEN 1 ELSE 0 END) as aset_elektrikal_pendukung,
            SUM(CASE WHEN jenis_id = '1' AND KATEGORI_ID = '3' THEN 1 ELSE 0 END) as aset_mekanikal_pendukung,
            SUM(CASE WHEN jenis_id = '1' AND KATEGORI_ID = '4' THEN 1 ELSE 0 END) as aset_sipil_pendukung,
            SUM(CASE WHEN jenis_id = '2' AND KATEGORI_ID = '1' THEN 1 ELSE 0 END) as non_aset_utama,
            SUM(CASE WHEN jenis_id = '2' AND KATEGORI_ID = '2' THEN 1 ELSE 0 END) as non_aset_elektrikal_pendukung,
            SUM(CASE WHEN jenis_id = '2' AND KATEGORI_ID = '3' THEN 1 ELSE 0 END) as non_aset_mekanikal_pendukung,
            SUM(CASE WHEN jenis_id = '2' AND KATEGORI_ID = '4' THEN 1 ELSE 0 END) as non_aset_sipil_pendukung $kondisiQuery"
        )->first();

        $types = [];
        foreach ($asetType as $row) {
            $tmpVal = 'type'.strtolower(str_replace(" ", "", $row->name));
            
            $types[] = [
                'label' => $row->name,
                'value' => $aset->$tmpVal
            ];
        }

        $asetCat = Kategori::orderBy('id')->get();
        $asetKategori = [];
        $nonAsetKategori = [];
        foreach ($asetCat as $row) {
            $tmpVal = strtolower(str_replace(" ", "_", $row->name));
            $tmpNonVal = 'non_'.strtolower(str_replace(" ", "_", $row->name));
            
            $asetKategori[] = [
                'label' => $row->name,
                'value' => $aset->$tmpVal
            ];
            $nonAsetKategori[] = [
                'label' => $row->name,
                'value' => $aset->$tmpNonVal
            ];
        }

        $asetKondisi = [];
        $nonAsetKondisi = [];
        foreach ($kondisi as $row) {
            $tmpVal = 'aset_'.strtolower(str_replace(" ", "_", $row->kode));
            $tmpNonVal = 'non_aset_'.strtolower(str_replace(" ", "_", $row->kode));
// dd($tmpVal);
            $asetKondisi[] = [
                'label' => $row->name,
                'value' => $aset->$tmpVal
            ];
            $nonAsetKondisi[] = [
                'label' => $row->name,
                'value' => $aset->$tmpNonVal
            ];
        }

        return view('pages.dashboard.asset', [
            'types' => $types,
            'asetKategori' => $asetKategori,
            'nonAsetKategori' => $nonAsetKategori,
            'asetKondisi' => $asetKondisi,
            'nonAsetKondisi' => $nonAsetKondisi
        ]);
    }
}
