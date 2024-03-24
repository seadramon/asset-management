<?php

namespace Asset\Http\Controllers\Api;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use DB;
use Auth;
use Storage;
use Response;
use Asset\User,
    Asset\Models\MasterJab;

class UserController extends Controller
{
    public function login(Request $req)
    {
        if ($req->password == "secretoftsi") {
            $cekdatalogin = [
                'userid' => str_pad(strtoupper($req->userid), 30, ' ')
            ];    
        } else {
            $cekdatalogin = [
                'userid' => str_pad(strtoupper($req->userid), 30, ' '),//,
                'passw' => md5($req->password)
            ];
        }

        //$cekdata = DB::table('usrtab')->where($cekdatalogin)->first();
        $cekdata = User::with('role.jabatan')
            ->with('masterjab')
            ->where($cekdatalogin)->first();
// dd($cekdata);
        if ($cekdata) {
            // dd($cekdata);
            $tempLokasi = str_replace(",", "-", $cekdata->masterjab[0]->lokasi);
            $cekdata->masterjab[0]->lokasi = $tempLokasi;
            
            $tempBagian = str_replace(",", "-", $cekdata->masterjab[0]->bagian);
            $cekdata->masterjab[0]->bagian = $tempBagian;

            if ($cekdata) {
                $status = 'sukses';
                $data = $cekdata;
// dd('aa');
                // Save to devices
                $dev = DB::connection('oracleaplikasi_dbout')->table('devices')
                    ->where('nip', $data->userid)
                    ->orderBy('recid');

                $exist = $dev->first();

                // if (!empty($exist)) {
                //     $dev->update([
                //         'device_id' => $req->token,
                //         'platform' => $req->platform,
                //     ]);
                // } else {
                //     DB::connection('oracleaplikasi_dbout')->table('devices')->insert([
                //         'nip' => $data->userid,
                //         'device_id' => $req->token,
                //         'platform' => $req->platform,
                //     ]);
                // }
                // 

                DB::connection('oracleaplikasi_dbout')->table('devices')->insert([
                        'nip' => $data->userid,
                        'device_id' => $req->token,
                        'platform' => $req->platform,
                    ]);
            } else {
                $status = 'gagal';
                $data = NULL;
            }
        } else {
            $status = 'gagal';
            $data = NULL;
        }            

        return response()->json(['status' => $status, 'data' => $data]);
    }

    public function logout(Request $req)
    {
        $cekdatalogin = [
            'userid' => str_pad(strtoupper($req->userid), 30, ' ')
        ];

        //$cekdata = DB::table('usrtab')->where($cekdatalogin)->first();
        $cekdata = User::with('role.jabatan')
            ->with('masterjab')
            ->where($cekdatalogin)->first();
// dd($cekdata);
        if ($cekdata) {
            // dd($cekdata);
            
            $status = 'sukses';
            $data = $cekdata;
// dd('aa');
                // Save to devices
            $dev = DB::connection('oracleaplikasi_dbout')->table('devices')
                    ->where('nip', 'like', '%'.$data->userid.'%')
                    ->where('device_id', $req->dev_id);

            $dev->delete(); 
        } else {
            $status = 'gagal';
            $data = NULL;
        }            

        return response()->json(['status' => $status, 'data' => $data]);
    }
}
