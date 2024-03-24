<?php
namespace Asset\Libraries;

use Asset\Models\Proposal as ProModel,
    Asset\Models\Perawatan,
    Asset\Models\AduanNonOperasi,
    Asset\Models\Usulan,
    Asset\Models\Perbaikan;

use Illuminate\Support\Facades\File;
use DB;
use Html;
use Validator;
use Redirect;
use Session;
use Image;
use Storage;

/**
 * 
 */
class Proposal
{
	
	public static function get()
	{

	}

	// buat baru
	public static function store($param)
	{
		DB::beginTransaction();
		try {
			$wo = $param['wo'];
			$foreignId = $param[$wo]; //ID wo yg bersangkutan
			// $cekWo = ProModel::where($wo, $foreignId)->first();
			$filename = "";

			/*if ($cekWo) {
				if (!empty($param['id'])) {
					$data = ProModel::find($param['id']);
				} else {
					$data = $cekWo;
				}
			} else {
				$data = new ProModel;				
			}*/
			if (!empty($param['id'])) {
				$data = ProModel::find($param['id']);
			} else {
				$data = new ProModel;
			}

			// $data->$wo = $foreignId;
			$data->nama = $param['nama'];
			$data->lokasi = $param['lokasi'];
			$data->gambaran = $param['gambaran'];
			$data->kondisi = $param['kondisi'];
			$data->manfaat_teknis = $param['manfaat_teknis'];
			$data->manfaat_ekonomis = $param['manfaat_ekonomis'];
			$data->tgl_mulai = $param['tgl_mulai'];
			$data->spesifikasi = $param['spesifikasi'];
			$data->kesimpulan = $param['kesimpulan'];
			$data->deskripsi = $param['deskripsi'];
			$data->waktu = $param['waktu'];

			// if (isset($param['perkiraan_proposal'])) {
			// 	$data->perkiraan = $param['perkiraan_proposal'];
			// } else {
			// 	$data->perkiraan = $param['perkiraan'];
			// }
			if (isset($param['perkiraan_anggaran_proposal'])) {
				$data->perkiraan = $param['perkiraan_proposal'];
				$data->perkiraan_anggaran = $param['perkiraan_anggaran_proposal'];
				$data->tahun_anggaran = $param['tahun_anggaran_proposal'];
			}

			if (isset($param['spv'])) {
				$data->spv = $param['spv'];
			} else {
				if (isset($param['nip_spv_mobile'])) {
					$data->spv = getRecidJabatan($param['nip_spv_mobile']);
				}
			}

			if (isset($param['nip_spv'])) {
				$data->nip_spv = $param['nip_spv'];
			}

			if (isset($param['nip_spv_mobile'])) {
				$nip = "";
				if ($wo == "prb_data_id") {
					$nip = $param['nip_spv_mobile'];
				} else {
					$nip = str_pad(strtoupper($param['nip_spv_mobile']), 30, ' ');
				}
				$param['nip_spv_mobile'] = $nip;
				$data->nip_spv = $param['nip_spv_mobile'];
			}
			// $data->perkiraan_anggaran = $param['perkiraan_anggaran_proposal'];				
			// $data->tahun_anggaran = $param['tahun_anggaran_proposal'];
			

			if (!empty($param['foto'])) {
				$file = $param['foto'];
	            $extension = $file->getClientOriginalExtension();
	            $filename = $wo .'_'.str_random('5').'.'. $extension;

	            $dir = 'proposal_pekerjaan/'.date('Y-m');
	            cekDir($dir);
	            $pathname = $dir .'/'. $filename;
	            Storage::disk('sftp')->put($pathname, \File::get($file));

				$data->foto = $pathname;
			}

			// Update table WO
			switch ($wo) {
				case 'prw_data_id':
					$updateWo = Perawatan::find($foreignId);
					$data->prw_data_id =  $foreignId;
					break;
				case 'prb_data_id':
					$updateWo = Perbaikan::find($foreignId);
					$data->prb_data_id =  $foreignId;
					break;
				case 'usulan_id':
					$updateWo = Usulan::find($foreignId);
					$data->usulan_id =  $foreignId;
					break;
				case 'aduan_non_op_id':
					$updateWo = AduanNonOperasi::find($foreignId);
					$data->aduan_non_op_id =  $foreignId;
					break;
			}

			$data->save();
			$idProposal = $data->id;

			DB::commit();

			$updateWo->proposal_id = $idProposal;
			$updateWo->save();

			DB::commit();
			// end:Update Table WO

			return true;
		} catch(Exception $e) {
			DB::rollback();

			return false;
		}
	}
}