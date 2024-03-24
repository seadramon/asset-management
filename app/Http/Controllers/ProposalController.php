<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Proposal,
    Asset\Models\Perbaikan,
    Asset\Models\Perawatan,
    Asset\Models\AduanNonOperasi,
    Asset\Models\Usulan;

use Asset\Libraries\ValidasiWo,
    Asset\Libraries\Proposal as ProLib;

use Illuminate\Support\Facades\File;
use DB;
use PDF;
use Html;
use Validator;
use Redirect;
use Session;
use Image;
use Response;
use Storage;

class ProposalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function pekerjaan(Request $request, $wo, $id)
    {
        $arrData = ValidasiWo::getWo($wo, $id);

        if (!empty($request->idReady)) {
            $data = Proposal::find($request->idReady);
        } else {
            $data = Proposal::where($wo, $id)->first();
        }
// dd($data);
        if (!$request->report) {

            return view('pages.proposal.pekerjaan', [
                'wo' => $wo,
                'data' => $data,
                'wo_id' => $id,
                'datawo' => $arrData['data'],
                'title' => $arrData['title']
            ]);

        } else {

            if ( empty($data) ) {

                if ( empty($arrData['data']->proposal)) {
                    abort(404, "Proposal belum dibuat");
                } else  {
                    $filename = $arrData['data']->proposal;
                    $dir = in_array($wo, ['aduan_non_op_id', 'usulan_id'])?'non-operasi&'.captionWo($wo).'&'.$id:captionWo($wo) . '&proposal';
                    $path = url('doc-api/dokumen/'. $dir .'&'.$arrData['data']->proposal);

                    return Response::make(file_get_contents($path), 200, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="'.$filename.'"'
                    ]);
                }
            } else {
                $datawo = $arrData['data'];
                // dd(getProfile($datawo->manajer));
                $pdf = PDF::loadView('pages.proposal.pekerjaan-report', [
                    'wo' => $wo,
                    'data' => $data,
                    'wo_id' => $id,
                    'datawo' => $arrData['data'],
                ]);

                return $pdf->stream(sprintf('Proposal-%s.pdf', date('Y-m')));
            }
        }
    }

    public function getJson($wo, $id) 
    {
        $data = Proposal::where($wo, $id)->first();

        if ($data) {
            return response()->json([
                    'result' => 'success',
                    'proposal' => $data->toJson(),
                    'image' => url('pic-api/gambar/'.str_replace('/', '&', $data->foto)),
                    'message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
        }

        return response()->json([
                    'result' => 'empty',
                    'message' => 'Data Kosong'])->setStatusCode(200, 'OK');
    }

    public function storeTest(Request $request)
    {
        return redirect()->back();
    }

    public function store(Request $request)
    {
        $redirect = self::redirectRoute($request->wo);
        // Validation
        $validation_rules = [
            'nama' => 'required',
            'lokasi' => 'required',
            'gambaran' => 'required',
            'kondisi' => 'required',
            'manfaat_teknis' => 'required',
            'manfaat_ekonomis' => 'required',
            'tgl_mulai' => 'required',
            'spesifikasi' => 'required',
            'kesimpulan' => 'required',
            'deskripsi' => 'required',
            'waktu' => 'required'
        ];

        if (!$request->id) {
            $validation_rules['foto'] = 'required';
        }

        $validator = Validator::make($request->all(), $validation_rules);
        if ($validator->fails()) {
            return response()->json([
                'result' => 'error',
                'message' => 'yang diberi tanda bintang wajib diisi'])->setStatusCode(500, 'OK');
        }
        // end:validation

        $result = ProLib::store($request->all());

        if ($result) {
            return response()->json([
                    'result' => 'success',
                    'message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
        } else {
            return response()->json([
                'result' => 'error',
                'message' => 'Data Gagal Disimpan'])->setStatusCode(500, 'OK');
        }
    }

    private static function redirectRoute($caption)
    {
        $ret = "";

        switch ($caption) {
            case 'prb_data_id':
                $ret = 'perbaikan::perbaikan-index';
                break;
            case 'prw_data_id':
                $ret = 'perawatan::perawatan-index';
                break;
            case 'aduan_non_op_id':
                $ret = 'non-operasi::aduan-index';
                break;
            case 'usulan_id':
                $ret = 'non-operasi::usulan-index';
                break;
        }

        return $ret;
    }
}
