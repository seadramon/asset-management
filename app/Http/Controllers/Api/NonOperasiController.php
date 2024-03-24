<?php

namespace Asset\Http\Controllers\Api;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\AduanNonOperasi,
    Asset\Models\Usulan,
    Asset\Models\Instalasi,
    Asset\Models\Lokasi,
    Asset\Models\RoleUser,
    Asset\Models\Role,
    Asset\Role as tuRoleUser;

use Asset\Jabatan;

use DB;
use Datatables;
use Session;
use Validator;
use Storage;
use Image;

class NonOperasiController extends Controller
{
    protected $disk = "sftp";

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function aduan(Request $request)
    {
        $nip = $request->header('nip');

        $title = $request->get('judul');
        $id = $request->get('id');
        $filterStatus = $request->get('filterStatus');
        $start = $request->get('start'); //offset
        $limit = $request->get('limit'); 

        $dalops = [
            '10601344',
            config('custom.manajerDalops')
        ];
// dd(in_array(trim($nip), $dalops));
        try {
            $levelJab = cekJabatan($nip);
            $result = [];

            $data = AduanNonOperasi::with(['petugas', 'jabatan', 'bagian', 'sukucadang'])
                ->select('aduan_non_operasi.*', 'instalasi.name as instalasi')
                ->join('instalasi', 'aduan_non_operasi.instalasi_id', '=', 'instalasi.id')
                ->whereNotIn('aduan_non_operasi.status', config('custom.skipStatus'));

            if (!in_array(trim($nip), $dalops)) {
                if ( $nip == trim(getMsPpp()->nip) ) {
                    $data->where('aduan_non_operasi.status', '1.3'); // Manajer PKO
                } else {
                    switch ($levelJab) {
                        case 'petugas':
                            $data = $data->where('trim(petugas_id)', trim($nip));
                            break;
                        case 'spv':
                            $data = $data->where('trim(nip_spv)', trim($nip))->whereNotIn('manajer' , ['me                            ', '                            ']);
                            break;
                        case 'manajer':
                            $data = $data->where('trim(manajer)', trim($nip))
                                ->whereIn('aduan_non_operasi.status', ['1.1','1.2']);
                            break;
                        /*case 'manajer senior':
                            $data = $data->where('aduan_non_operasi.status', '1.3');
                            break;*/
                    }
                }
            }

            // if ($levelJab == 'manajer') {
            //     if (!in_array(trim($nip), $dalops)) { //if not dalops manajer
            //         $data = $data->where('aduan_non_operasi.status', '1.1');
            //     } else {
            //         $data = $data->whereIn('aduan_non_operasi.status', ['1.2', '2']);
            //             // ->whereNull('aduan_non_operasi.approve_dalops');
            //     }
            // }
// dd($levelJab);
            if (!empty($title)) {
                $data = $data->where('judul', 'like', '%'.$title.'%');
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
             //dd($data);
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $action = aksiTindakan($row);
                    $status = statusTindakanManajer($row);

                    if (!empty($filterStatus)) {
                        if ($status == $filterStatus) {
                            $result[] = self::aduanTransformer($row, $status, $action);    
                        }
                    } else {
                        $result[] = self::aduanTransformer($row, $status, $action);
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

    private static function aduanTransformer($row, $status = "", $action = "")
    {
        return [
            'id'                    => $row['id'],
            'judul'                 => $row['judul'],
            'instalasi'             => $row['instalasi'],
            'instalasi_id'          => $row['instalasi_id'],
            'lokasi'                => $row['lokasi'],
            'sifat'                 => $row['sifat'],
            'spv'                   => $row['spv'],
            'spv_tujuan'            => $row['jabatan']['namajabatan'],
            'bagian'                => bagianCaption($row['bagian']['bagian']),
            'foto_aduan'            => !empty($row['foto_aduan'])?url('pic-api/gambar/non-operasi&aduan&'.$row['id'].'&'.$row['foto_aduan']):"",
            'foto'                  => !empty($row['foto'])?url('pic-api/gambar/non-operasi&aduan&'.$row['id'].'&'.$row['foto']):"",
            'catatan'               => $row['catatan'],
            'indikasi'              => $row['indikasi'],
            'tanggal'               => $row['created_at'],
            'nip_spv'               => $row['nip_spv'],
            'manajer'               => $row['manajer'],
            'petugas_id'            => $row['petugas_id'],
            'petugas'               => $row['petugas']['nama'],
            'tingkat'               => !empty($row['tingkat'])?$row['tingkat']:"",
            'perkiraan'             => !empty($row['perkiraan'])?$row['perkiraan']:"",
            'perkiraan_revisi'      => !empty($row['perkiraan_revisi'])?$row['perkiraan_revisi']:"",
            'tgl_start'             => $row['tgl_start'],
            'tgl_finish'            => $row['tgl_finish'],
            'metode'                => $row['metode'],
            'kondisi'               => $row['kondisi'],
            'tgl_disposisi'         => $row['tgl_disposisi'],
            'penyebab'              => $row['penyebab'],
            'tgl_foto_investigasi'  => $row['tgl_foto_investigasi'],
            'foto_investigasi'      => !empty($row['foto_investigasi'])?url('pic-api/gambar/non-operasi&aduan&'.$row['id'].'&'.$row['foto_investigasi']):"",
            'foto_investigasi2'     => !empty($row['foto_investigasi2'])?url('pic-api/gambar/non-operasi&aduan&'.$row['id'].'&'.$row['foto_investigasi2']):"",
            'jenis_penanganan'      => $row['jenis_penanganan'],
            'tgl_input_metode'      => $row['tgl_input_metode'],
            'tgl_foto_analisa'      => $row['tgl_foto_analisa'],
            'uraian'                => $row['uraian'],
            'foto2'                 => !empty($row['foto2'])?url('pic-api/gambar/non-operasi&aduan&'.$row['id'].'&'.$row['foto2']):"",
            'approve_manajer'       => $row['approve_manajer'],
            'approve_dalops'        => $row['approve_dalops'],
            'catatan_m'             => $row['m_catatan'],
            'catatan_ms_ppp'        => $row['ms_ppp_catatan'],
            'catatan_dalpro'        => $row['dalpro_catatan'],
            'catatan_p'             => $row['petugas_catatan'],

             // Add by Nafi 16-04-2021
            'perkiraan_anggaran'    => !empty($row['perkiraan_anggaran'])?$row['perkiraan_anggaran']:"0",
            'tahun_anggaran'    => !empty($row['tahun_anggaran'])?$row['tahun_anggaran']:"",
            'proposal'              => !empty($row['proposal'])?url('doc-api/dokumen/non-operasi&aduan&'.$row['id'].'&'.$row['proposal']):"",
            //

            'status_web'    => $row['status'],
            'status'        => $status,
            'action'        => $action
        ];
    }

    public function usulan(Request $request)
    {
        $nip = $request->header('nip');

        $title = $request->get('nama');
        $id = $request->get('id');
        $filterStatus = $request->get('filterStatus');
        $start = $request->get('start'); //offset
        $limit = $request->get('limit'); 

        $dalops = [
            '10601344',
            // config('custom.manajerDalops')
        ];

        try {
            $levelJab = cekJabatan($nip);
            $result = [];

            $data = Usulan::with(['petugas', 'jabatan', 'bagian', 'instalasi', 'sukucadang', 'aset'])
                ->select('usulan_non_operasi.*', 'instalasi.name as instalasi')
                ->join('instalasi', 'usulan_non_operasi.instalasi_id', '=', 'instalasi.id')
                ->whereNotIn('usulan_non_operasi.status', ['10', '99']);


            if (!in_array(trim($nip), $dalops)) {
                // dd(getMsPpp()->nip);
                if ( $nip == trim(getMsPpp()->nip) ) {
                    $data->where('usulan_non_operasi.status', '1.3'); // Manajer PKO
                } else {
                    switch ($levelJab) {
                        case 'petugas':
                            $data = $data->where('trim(petugas_id)', trim($nip));
                            break;
                        case 'spv':
                            $data = $data->where('trim(nip_spv)', trim($nip));
                            break;
                        case 'manajer':
                            $data = $data->where('trim(manajer)', trim($nip))
                                ->whereIn('usulan_non_operasi.status', ['1.1','1.2']);
                            break;
                        /*case 'manajer senior':
                            $data = $data->where('usulan_non_operasi.status', '1.3');
                            break;*/
                    }
                }
            }

            // if ($levelJab == 'manajer') {
            //     if (!in_array(trim($nip), $dalops)) { //if not dalops manajer
            //         $data = $data->where('usulan_non_operasi.status', '1.1');
            //     } else {
            //         $data = $data->whereIn('usulan_non_operasi.status', ['1.2', '2'])
            //             ->whereNull('usulan_non_operasi.approve_dalops');
            //     }
            // }

            if (!empty($title)) {
                $data = $data->where('nama', 'like', '%'.$title.'%');
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
            // $kosong = array();
            // dd($data);
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $action = aksiTindakanWeb($row);
                    $status = statusTindakanManajer($row);

                    if (!empty($filterStatus)) {
                        if ($status == $filterStatus) {
                            $result[] = self::usulanTransformer($row, $status, $action);    
                        }
                    } else {
                        $result[] = self::usulanTransformer($row, $status, $action);
                    }


                    // if ($nip = '10601381' && $row['bagian'] == "" ) {
                    //     $kosong[] = $row['id'];
                    // }

                }
            }

            // if ($nip == '10601381') {
            //     dd(implode(",", $kosong));
            // }

            $return = ['data' => $result, 'next' => 'false'];

            if ($limit !== null) {
                $return = ['data' => array_slice($result, 0, $limit), 'next' => (count($result) > $limit)];    
            }

            return response()->json($return)->setStatusCode(200, 'OK');
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()])->setStatusCode(500, 'Error');
        }
    }

    private static function usulanTransformer($row, $status = "", $action = "")
    {
        $perencanaan = null;
        if (!empty($row['perencanaan'])) {
            $arrTemp = explode(";", $row['perencanaan']);

            foreach ($arrTemp as $tmp) {                
                $perencanaan[] = url('pic-api/gambar/non-operasi&usulan&'.$row['id'].'&'.$tmp);
            }
        }

         return [
            'id'                    => $row['id'],
            'nama'                  => $row['nama'],
            'instalasi_id'          => $row['instalasi_id'],
            'instalasi'             => $row['instalasi']['name'],
            'lokasi'                => $row['lokasi'],
            'spv'                   => $row['spv'],
            'spv_tujuan'            => $row['jabatan']['namajabatan'],
            'sifat'                 => !empty($row['sifat'])?$row['sifat']:"",
            'bagian'                => bagianCaption($row['bagian']['bagian']),
            'foto_kondisi'          => !empty($row['foto_kondisi'])?url('pic-api/gambar/non-operasi&usulan&'.$row['id'].'&'.$row['foto_kondisi']):"",
            // 'foto'                  => !empty($row['foto'])?url('pic-api/gambar/non-operasi&usulan&'.$row['id'].'&'.$row['foto']):"",
            'tujuan'                => $row['tujuan'],
            'keterangan'            => $row['keterangan'],
            'tanggal'               => $row['created_at'],
            'nip_spv'               => $row['nip_spv'],
            'manajer'               => $row['manajer'],
            'petugas_id'            => $row['petugas_id'],
            'petugas'               => $row['petugas']['nama'],
            'tgl_disposisi'         => $row['tgl_disposisi'],
            'tgl_start'             => $row['tgl_start'],
            'tgl_finish'            => $row['tgl_finish'],
            'hasil_investigasi'     => $row['hasil_investigasi'],
            'tgl_foto_investigasi'  => $row['tgl_foto_investigasi'],
            'foto_investigasi'      => !empty($row['foto_investigasi'])?url('pic-api/gambar/non-operasi&usulan&'.$row['id'].'&'.$row['foto_investigasi']):"",
            'foto_investigasi2'     => !empty($row['foto_investigasi2'])?url('pic-api/gambar/non-operasi&usulan&'.$row['id'].'&'.$row['foto_investigasi2']):"",

            'jenis_penanganan'      => $row['jenis_penanganan'],
            'perkiraan'             => !empty($row['perkiraan'])?$row['perkiraan']:"",
            'perkiraan_revisi'      => !empty($row['perkiraan_revisi'])?$row['perkiraan_revisi']:"",
            'metode'                => $row['metode'],            
            'material'              => $row['material'],            
            'perencanaan'           => $perencanaan,            
            'tgl_input_metode'      => $row['tgl_input_metode'],

            'tgl_foto_analisa'      => $row['tgl_foto_analisa'],
            'uraian'                => $row['uraian'],
            'foto'                 => !empty($row['foto'])?url('pic-api/gambar/non-operasi&usulan&'.$row['id'].'&'.$row['foto']):"",
            'foto2'                 => !empty($row['foto2'])?url('pic-api/gambar/non-operasi&usulan&'.$row['id'].'&'.$row['foto2']):"",
            'approve_manajer'       => $row['approve_manajer'],
            'approve_dalops'        => $row['approve_dalops'],
            'catatan_m'             => $row['catatan_rev_manajer'],
            'catatan_ms_ppp'        => $row['ms_ppp_catatan'],
            'catatan_dalpro'        => $row['dalpro_catatan'],
            'catatan_p'             => $row['petugas_catatan'],

            // Add by Nafi 16-04-2021
            'perkiraan_anggaran'    => !empty($row['perkiraan_anggaran'])?$row['perkiraan_anggaran']:"0",
            'tahun_anggaran'    => !empty($row['tahun_anggaran'])?$row['tahun_anggaran']:"",
            'proposal'              => !empty($row['proposal'])?url('doc-api/dokumen/non-operasi&usulan&'.$row['id'].'&'.$row['proposal']):"",
            //

            'aset_id'       => !empty($row['aset_id'])?$row['aset_id']:"",
            'nama_aset'     => !empty($row['aset_id'])?$row['aset']['nama_aset'] . ' ( '. $row['aset']['kode_aset'] .' )':"",

            'status_web'    => $row['status'],
            'status'        => $status,
            'action'        => $action
        ];
    }
}
