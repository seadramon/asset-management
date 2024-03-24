<?php

namespace Asset\Http\Controllers\Api;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Libraries\ValidasiWo,
    Asset\Libraries\Proposal as ProLib,
    Asset\Models\Perawatan,
    Asset\Models\AduanNonOperasi,
    Asset\Models\Usulan,
    Asset\Models\Perbaikan;

use Asset\Models\Proposal as ProModel;

use PDF;

class ProposalController extends Controller
{
    
    public function simpan(Request $request)
    {
        $result = ProLib::store($request->all());

        if ($result) {
            return response()->json([
                'result' => 'success',
                'message' => 'Data Berhasil Disimpan'])->setStatusCode(200, 'OK');
        } else {
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    public function getJson($wo, $id) 
    {
        $data = ProModel::where($wo, $id)->first();
        switch ($wo) {
            case 'prb_data_id':
                $datawo = $data->perbaikan;
                break;
            case 'prw_data_id':
                $datawo = $data->perawatan;
                break;
            case 'aduan_non_op_id':
                $datawo = $data->aduannon;
                break;
            case 'usulan_id':
                $datawo = $data->usulan;
                break;
        }
        
        if ($data) {
            return response()->json([
                    'result' => 'success',
                    'proposal' => $data,
                    'datawo' => $datawo,
                    'pdf' => url('api/proposal/loadpdf/'.$wo.'/'.$id),
                    'imageProposal' => url('pic-api/gambar/'.str_replace('/', '&', $data->foto))
            ])->setStatusCode(200, 'OK');
        } else {
            return response()->json([
                        'result' => 'empty',
                        'message' => 'Data Kosong'])->setStatusCode(404, 'Not Found');
        }
    }

    public function getJson1($wo, $id) 
    {

        switch ($wo) {
            case 'prw_data_id':
                $datawo = Perawatan::find($id);
                break;
            case 'prb_data_id':
                $datawo = Perbaikan::find($id);
                break;
            case 'usulan_id':
                $datawo = Usulan::find($id);
                break;
            case 'aduan_non_op_id':
                $datawo = AduanNonOperasi::find($id);
                break;
        }

        if ($datawo) {
            $data = ProModel::find($datawo->proposal_id);
            if ($data) {
                switch ($wo) {
                    case 'prw_data_id':
                        $data->perawatan = $datawo;
                        break;
                    case 'prb_data_id':
                        $data->perbaikan = $datawo;
                        break;
                    case 'usulan_id':
                        $data->usulan = $datawo;
                        break;
                    case 'aduan_non_op_id':
                        $data->aduannon = $datawo;
                        break;
                }

                return response()->json([
                    'result' => 'success',
                    'proposal' => $data,
                    'datawo' => $datawo,
                    'pdf' => url('api/proposal/loadpdf/'.$wo.'/'.$id),
                    'imageProposal' => url('pic-api/gambar/'.str_replace('/', '&', $data->foto))
                ])->setStatusCode(200, 'OK');
                
            } else {
                return response()->json([
                        'result' => 'empty',
                        'message' => 'Data Kosong'])->setStatusCode(404, 'Not Found');
            }
        } else {
            return response()->json([
                        'result' => 'empty',
                        'message' => 'Data Kosong'])->setStatusCode(404, 'Not Found');
        }
    }

    public function loadPdf($wo, $id)
    {
        $arrData = ValidasiWo::getWo($wo, $id);

        if ($arrData) {
            $data = ProModel::find($arrData['data']['proposal_id']);

            if ($data) {
                switch ($wo) {
                    case 'prw_data_id':
                        $data['perawatan'] = $arrData['data'];
                        break;
                    case 'prb_data_id':
                        $data['perbaikan'] = $arrData['data'];
                        break;
                    case 'usulan_id':
                        $data['usulan'] = $arrData['data'];
                        break;
                    case 'aduan_non_op_id':
                        $data['aduannon'] = $arrData['data'];
                        break;
                }


                $pdf = PDF::loadView('pages.proposal.pekerjaan-report', [
                    'wo' => $wo,
                    'data' => $data,
                    'wo_id' => $id,
                    'datawo' => $arrData['data'],
                    'title' => $arrData['title']
                ]);

                return $pdf->stream(sprintf('Proposal-%s.pdf', date('Y-m')));
                
            }
        }

        // $data = ProModel::where($wo, $id)->first();

        // // dd($data);

        // $pdf = PDF::loadView('pages.proposal.pekerjaan-report', [
        //     'wo' => $wo,
        //     'data' => $data,
        //     'wo_id' => $id,
        //     'datawo' => $arrData['data'],
        //     'title' => $arrData['title']
        // ]);

        // return $pdf->stream(sprintf('Proposal-%s.pdf', date('Y-m')));
    }
}
