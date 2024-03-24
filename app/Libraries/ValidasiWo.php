<?php
namespace Asset\Libraries;

use Asset\Models\Aset,
    Asset\Models\PmlKeluhan,
    Asset\Models\PmlKeluhanDev,
    Asset\Models\Master,
    Asset\Models\MasterFm,
    Asset\Models\MasterJab,
    Asset\Models\Perawatan,
    Asset\Models\Perbaikan,
    Asset\Models\AduanNonOperasi,
    Asset\Models\Usulan,
    Asset\Models\Ms4w,
    Asset\Models\Prw4w,
    Asset\Models\Prw52w,
    Asset\Models\JadwalLibur,
    Asset\Models\Instalasi,
    Asset\Jabatan,
    Asset\Models\JadwalLiburPompa,
    Asset\Role as tuRoleUser;

use Illuminate\Support\Facades\File;
use DB;
use Html;
use Validator;
use Redirect;
use Session;
use Image;
use Storage;
use Route;

/**
 * 
 */
class ValidasiWo
{
	public static function tidakBeroperasi($komponenId)
    {
        // dd('test');
        // Perawatan
        $cekPrw = Perawatan::whereNotIn('status', config('custom.skipStatus'))
            ->where('komponen_id', $komponenId)
            ->where(function($sql) {
                $sql->where('bagian_id', '<>', '4')
                    ->orWhere('bagian_id', null);
            })
            ->count();

        if ($cekPrw > 0) {
            Perawatan::where('komponen_id', $komponenId)
                ->whereNotIn('status', config('custom.skipStatus'))
                ->update([
                    'status' => '98',
                    'last_action' => 'Digantikan Perbaikan aset tidak beroperasi',
                    'updated_at' => getNow()
                ]);
        }

        /*Update Ms4w Monitoring*/
        $aset = Aset::find($komponenId);
        $week = weekInMonth(date('Y-m-d'));

        $ms = Ms4w::whereHas('ms52w', function($query) use($aset) {
                $query->where('komponen_id', $aset->id);
                $query->where('tahun', date('Y'));
            })
            ->where('urutan_minggu', '>=', date('W'))
            ->where('status', '0');

        $exist = $ms->get();
        if (count($exist) > 0) {
            $ms->update(['status' => '99']);
        }
        /*End Update Ms4w Manajemen Aset*/

        // Perawatan Rutin
        $prw = Prw4w::whereHas('prw52w', function($query) use($aset) {
                $query->where('komponen_id', $aset->id);
                $query->where('tahun', date('Y'));
            })
            ->where('urutan_minggu', '>=', date('W'))
            ->where('status', '0');

        $exist = $prw->get();
        if (count($exist) > 0) {
            $prw->update(['status' => '99']);
        }

        return true;
    }

    public static function tidakBeroperasiRevive($komponenId)
    {
        /*Update Ms4w Monitoring*/
        $aset = Aset::find($komponenId);
        $week = weekInMonth(date('Y-m-d'));

        $ms = Ms4w::whereHas('ms52w', function($query) use($aset) {
                $query->where('komponen_id', $aset->id);
                $query->where('tahun', date('Y'));
            })
            ->where('urutan_minggu', '>=', date('W'))
            ->where('status', '99');

        $exist = $ms->get();
        if (count($exist) > 0) {
            $ms->update(['status' => '0']);
        }
        /*End Update Ms4w Manajemen Aset*/

        // Perawatan Rutin
        $prw = Prw4w::whereHas('prw52w', function($query) use($aset) {
                $query->where('komponen_id', $aset->id);
                $query->where('tahun', date('Y'));
            })
            ->where('urutan_minggu', '>=', date('W'))
            ->where('status', '99');

        $exist = $prw->get();
        if (count($exist) > 0) {
            $prw->update(['status' => '0']);
        }

        return true;
    }

    public static function filterStatus($query, $status)
    {
        switch ($status) {
            case ('baru'):
                return $query->where('status', '0')->whereNull('petugas_id');
                break;
            case ('investigasi'):                
                return $query->where('status', '0')->whereNotNull('petugas_id');
                break;
            case ('sudah diinvestigasi'):
                return $query->where('status', '1')->whereNotNull('tgl_foto_investigasi');
                break;
            case ('menunggu approval manajer pemeliharaan'):
                return $query->where('status', '1.1');
                break;
            case ('menunggu approval manajer trandist'):
                return $query->where('status', '1.1');
                break;
            case ('revisi input metode dari manajer pemeliharaan'):
                return $query->where('status', '3.1');
                break;
            case ('revisi input metode dari manajer trandist'):
                return $query->where('status', '3.1');
                break;
            case ('menunggu approval manajer dalops'):
                return $query->where('status', '1.2');
                break;
            case ('menunggu approval ms ppp'):
                return $query->where('status', '1.3');
                break;
            case ('revisi input metode dari manajer dalops'):
                return $query->where('status', '3.3');
                break;
            case ('revisi input metode dari ms ppp'):
                return $query->where('status', '3.4');
                break;
            case ('proses ded (baru)'):
                return $query->where('status', '4.0');
                break;
            case ('proses ded (proses)'):
                return $query->where('status', '4.1');
                break;
            case ('proses ded (revisi)'):
                return $query->where('status', '4.2');
                break;
            case ('proses ded (selesai)'):
                return $query->where('status', '4.3');
                break;
            case ('penanganan'):
                return $query->where('status', '2')->whereNull('foto');
                break;
            case ('selesai'):
                return $query->where('status', '10');
                break;
            case ('nonaktif'):
                return $query->where('status', '99');
                break;
            case ('digantikan'):
                return $query->where('status', '98');
                break;
            default:
                return false;
        }
    }

    public static function approveDalproNextStatus($metode, $sifat, $isDed)
    {
        $arrMetode = ['eksternal pp', 'overhaul'];
        $arrSifat = ['biasa', 'overhaul'];

        if ($isDed == 'no') {
            $nextStatus = '2';
        } else {
            if ( in_array($metode, $arrMetode) && in_array(strtolower($sifat), $arrSifat) ) {
                $nextStatus = '1.3';
            /*} elseif (in_array($metode, ['eksternal pp', 'overhaul']) && $sifat == "emergency") {
                $nextStatus = '2';*/
            } else {
                $nextStatus = '2';
            }
        }

        return $nextStatus;
    }               

    public static function cekDedRevisiPenanganan($status, $metode, $sifat, $tglded = null)
    {
        $isDed = 'no';

        if (!empty($tglded)) {
            $isDed = 'no';
        } else {
            if ( $metode == 'eksternal pp' && in_array($sifat, ['biasa', 'overhaul']) ) {
                $isDed = 'yes';
            } else {
                $isDed = 'no';
            }
        }

        return $isDed;
    }

    public static function selectJab($instalasi, $wo)
    {
        if (empty($instalasi)) {
            $arrJab = ['80', '82', '85', '81', '83', '86', '231', '218'];

            $jabatan = Jabatan::whereIn('recidjabatan', $arrJab)->get()->pluck('namajabatan', 'recidjabatan')->toArray();
        } else {
            $arrPompa = [];
            $rp = Instalasi::select('id')->whereNotIn('id', config('custom.distribusiExcept'))->get();
            foreach ($rp as $row) {
                $arrPompa[] = $row->id;
            }

            switch (true) {
                case (in_array($instalasi, ['17', '18', '19']) && $wo != "usulan"):
                    $arrJab = ['80', '82', '85', '218'];
                    break;
                case (in_array($instalasi, ['21', '22', '23']) && $wo != "usulan"):
                    $arrJab = ['81', '83', '86', '218'];
                    break;
                case (in_array($instalasi, ['17', '18', '19']) && $wo == "usulan"):
                    $arrJab = array_merge(['80', '82', '85', '218'], config('custom.jabatanProduksiIpamNgagel'));
                    break;
                case (in_array($instalasi, ['21', '22', '23']) && $wo == "usulan"):
                    $arrJab = array_merge(['81', '83', '86', '218'], config('custom.jabatanProduksiIpamKp'));
                    break;
                case (in_array($instalasi, ['1', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14']) && $wo == "usulan"):
                    $arrJab = ['223','224'];
                    break;
                case (in_array($instalasi, $arrPompa)):
                    $arrJab = ['231', '218'];
                    break;
                default:
                    $arrJab = ['80', '82', '85', '81', '83', '86', '231', '218','223','224','64','65','66', '67','68','69'];
                    break;
            }

            $jabatan = Jabatan::whereIn('recidjabatan', $arrJab)->get()->pluck('namajabatan', 'recidjabatan')->toArray();
        }

        $labelJabatan = ["" => "-             Pilih SPV Pelaksana            -"];
        $jabatan = $labelJabatan + $jabatan;

        return $jabatan;
    }

    public static function getWo($wo, $id)
    {
        $result = [];

        switch ($wo) {
            case 'prb_data_id':
                $data = Perbaikan::find($id);
                $title = 'Perbaikan dari '.ucwords($data->tipe);
                break;
            case 'prw_data_id':
                $data = Perawatan::find($id);
                $title = 'Perawatan dari Monitoring';
                break;
            case 'aduan_non_op_id':
                $data = AduanNonOperasi::find($id);
                $title = 'Aduan Non Operasi';
                break;
            case 'usulan_id':
                $data = Usulan::find($id);
                $title = 'Usulan';
                break;
            default:
                $data = null;
                $title = '';
                break;
        }

        $result = [
            'data' => $data,
            'title' => $title
        ];

        return $result;
    }

    public static function cekMasukProposal($metode, $sifat)
    {
        if ($metode == "eksternal pp" && in_array(strtolower($sifat), ['biasa', 'overhaul'])) {
            return true;
        }

        return false;
    }
}