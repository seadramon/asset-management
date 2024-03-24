<?php

if (!function_exists('petugas')) {

    function petugas($idlokasi, $bagian = "")
    {
        $nip = DB::table('master_jab');
            
        if (is_array($idlokasi)){
            $nip = $nip->whereIn('master_jab.lokasi', $idlokasi);
        } else {
            $nip = $nip->where('master_jab.lokasi', $idlokasi);
        }

        if ($bagian!="") {
            $nip->where('master_jab.bagian', $bagian);
        }

        $nip = $nip->get();
        $arrNip = [];

        foreach ($nip as $row) {
            $arrNip[] = $row->nip;
        }

        $arrUser = ["" => "-             Pilih Petugas             -"];
        $users = DB::connection('oraclesecman')->table('usrtab')
            ->select('userid', 'username')
            ->whereIn('userid', $arrNip)
            ->get();
        foreach ($users as $row) {
            $arrUser[trim($row->userid)] = trim($row->username);
        }

        return $arrUser;
    }
}

    function getPetugas($lokasi, $withNip = false, $spv = null, $isTrim = true) 
    {
        if ( empty($spv) ) {
            $initUser = \Auth::user();
        }else {
            $initUser = Asset\User::whereRaw("TRIM(userid) = '$spv'")->first();
        }
        $arrLokasi = explode(",", $lokasi);

        $nipLokasi = Asset\Models\MasterJab::whereIn('lokasi', $arrLokasi)
            ->orWhere('lokasi', 'like', '%'.$lokasi.'%')
            ->get()->pluck('nip')->toArray();

        $petugas = ["" => "-             Pilih Petugas             -"];
        $users = Asset\Role::select('nip', 'nama')
            ->whereNull('is_manajer')
            ->where('recidrole', $initUser->role->jabatan->recidjabatan)
            ->get();

        foreach ($users as $row) {
            if (in_array($row->nip, $nipLokasi)) {
                if ( $isTrim ) {
                    if ($withNip == true) {
                        $petugas[trim($row->nip)] = trim($row->nip).' - '.trim($row->nama);
                    } else {
                        $petugas[trim($row->nip)] = trim($row->nama);
                    }
                } else {
                    if ($withNip == true) {
                        $petugas[$row->nip] = trim($row->nip).' - '.trim($row->nama);
                    } else {
                        $petugas[$row->nip] = trim($row->nama);
                    }
                }
            }
        }

        return $petugas;
    }

    function getUserPengolahan($arrLokasi)
    {
        $arrNip = [];
        $lokasi = implode(",", $arrLokasi);

        $tmpData = Asset\Models\Role::with('roleuser')
            ->whereIn('name', config('custom.rolePengolahan'))
            ->get();

        if (count($tmpData)) {
            foreach ($tmpData as $rolenya) {
                if (!empty($rolenya->roleuser)) {
                    foreach ($rolenya->roleuser as $row) {
                        $arrNip[] = trim($row->user_id);
                    }
                }
            }
        }
// dd($arrNip);
        $nipLokasi = Asset\Models\MasterJab::where(function($q) use($arrLokasi, $lokasi){
            $q->whereIn('lokasi', $arrLokasi)
                ->orWhere('lokasi', 'like', '%'.$lokasi.'%');
        })
        ->select(DB::raw("TRIM(NIP) AS NIP"))
        ->whereIn(DB::raw("TRIM(nip)"), $arrNip)
        ->get()->pluck('nip')->toArray();

        return $nipLokasi;
    }

    if (!function_exists('lokasi')) {
        function lokasi($nip = "", $tipe = 'arr')
        {
            $arrLokasi = null;

            if ($nip == "") {
                $jab = DB::table('master_jab')
                    ->where('trim(nip)', trim(\Auth::user()->userid))
                    ->first();
            } else {
                $jab = DB::table('master_jab')
                    ->where('trim(nip)', trim($nip))
                    ->first();
                // dd($jab);
            }
                // dd(trim(\Auth::user()->userid));
            if (strlen($jab->lokasi) > 0) {
                $arrLokasi = explode(',', $jab->lokasi);

                if (count($arrLokasi) == 1) {
                    $arrLokasi = [$jab->lokasi];
                }

                if ($tipe == 'str') {
                    $arrLokasi = $jab->lokasi;
                }
            }

            return $arrLokasi;
        }         
    }

    if (!function_exists('bagian')) {
        function bagian($nip = "", $tipe = 'arr')
        {
            $bagian = null;

            if ($nip == "") {
                $jab = DB::table('master_jab')
                    ->where('trim(nip)', trim(\Auth::user()->userid))
                    ->first();
            } else {
                $jab = DB::table('master_jab')
                    ->where('trim(nip)', trim($nip))
                    ->first();
                // dd($jab);
            }
            
            if (strlen($jab->bagian) > 0) {
                $bagian = explode(',', $jab->bagian);
                if (count($bagian) == 1) {
                    $bagian = [$jab->bagian];
                }

                if ($tipe == 'str') {
                    $bagian = $jab->bagian;
                }
            }

            return $bagian;
        }
    }

    if (!function_exists('namaRole')) {
        function namaRole($nip = null)
        {
            if (!empty($nip)) {
                $data = Asset\User::with(['rolebaru'])
                    ->where('userid', 'like', $nip.'%')->first();
                if (sizeof($data->rolebaru) > 0) {
                    $ret = $data->rolebaru[0]->name;
                }
            } else {
                if (!empty(\Auth::user()->rolebaru()->first())) {
                    $ret = \Auth::user()->rolebaru()->first()->name;
                } else {
                    abort('404', 'Role Not Found');
                }
            }
            return $ret;
        }
    }

    if (!function_exists('idRole')) {
        function idRole($nip = null)
        {
            if (!empty($nip)) {
                $data = Asset\User::with(['rolebaru'])
                    ->where('userid', 'like', $nip.'%')->first();
                if (sizeof($data->rolebaru) > 0) {
                    $ret = $data->rolebaru[0]->id;
                }
            } else {
                if (!empty(\Auth::user()->rolebaru()->first())) {
                    $ret = \Auth::user()->rolebaru()->first()->id;
                } else {
                    abort('404', 'Role Not Found');
                }
            }
            return $ret;
        }
    }

    if (!function_exists('tgl_indo')) {
        // usage : echo tgl_indo(date('Y-m-d')); // 21 Oktober 2017
        function tgl_indo($tanggal){
            $bulan = array (
                1 =>   'Januari',
                'Februari',
                'Maret',
                'April',
                'Mei',
                'Juni',
                'Juli',
                'Agustus',
                'September',
                'Oktober',
                'November',
                'Desember'
            );
            $pecahkan = explode('-', $tanggal);
         
            return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
        }
    }

    if (!function_exists('weekNumber')) {
        function weekNumber($date)
        {
            $date = new DateTime($date);
            $week = $date->format("W");
            
            return $week;
        }
    }

    if (!function_exists('lastWeekNumber')) {
        function lastWeekNumber()
        {
            $year = date('Y');
            $date = new DateTime;
            $date->setISODate($year, 53);

            return $date->format("W") === "53" ? 53 : 52;
        }
    }

    if (!function_exists('weekInMonth')) {
        function weekInMonth($date)
        {
            $lastDate = date('Y-m-t', strtotime($date));
// dd($lastDate);
            $date = new DateTime($date);
            $firstWeek = $date->format("W");
            // dd($date);
            $date = new DateTime($lastDate);
            $lastWeek = $date->format("W");

            if ($lastWeek == "01") {
                $lastWeek = "52";
            }

            if ($firstWeek == "53" or $firstWeek == "52") {
                $firstWeek = "1";
            }

            $result = [];
            for ($i=(int)$firstWeek; $i <= $lastWeek ; $i++) { 
                $result[] = $i;
            }
            
            return $result;
        }
    }

    function hoursInMonth($period)
    {
        $lastDate = DateTime::createFromFormat('Ym', $period)->format('t-m-Y 23:59');
        $firstDate = DateTime::createFromFormat('Ym', $period)->format('01-m-Y 00:00');

        $foo = DB::connection('oracleaplikasi_dbout')->select("select round(24 * (to_date('$lastDate', 'dd-mm-yyyy hh24:mi') - to_date('$firstDate', 'dd-mm-yyyy hh24:mi'))) as diff_hours
from dual");

        return $foo[0]->diff_hours;
    }

    if (!function_exists('statusTindakan')) {
        function statusTindakan($kode)
        {
            switch ($kode) {
                case '0':
                    $res = 'disposisi';
                    break;
                case '1':
                    $res = 'investigasi';
                    break;
                case '2':
                    $res = 'analisa';
                    break;
                case '10':
                    $res = 'close';
                    break;
                default:
                    $res = 'disposisi';
                    break;
            }

            return $res;
        }
    }

    if (!function_exists('base64_to_jpeg')) {
        function base64_to_jpeg($base64_string, $output_file) {
            // open the output file for writing
            $ifp = fopen( $output_file, 'wb' ); 

            // split the string on commas
            // $data[ 0 ] == "data:image/png;base64"
            // $data[ 1 ] == <actual base64 string>
            $data = explode( ',', $base64_string );

            // we could add validation here with ensuring count( $data ) > 1
            fwrite( $ifp, base64_decode( $data[ 1 ] ) );

            // clean up the file resource
            fclose( $ifp ); 

            return $output_file; 
        }
    }

    // Notifikasi
    if (!function_exists('kirimnotif')) {
        function kirimnotif($nip, $message)
        {
            $arrDev = [];
            $dev = \DB::connection('oracleaplikasi_dbout')->table('devices')->where(\DB::raw('trim(nip)'), $nip)->get();
            // dd($dev);
            if (!empty($dev)) {
                foreach ($dev as $row) {
                    $arrDev[] = $row->device_id;
                }
            }
            $url = 'https://fcm.googleapis.com/fcm/send';
            $tokens = array();

            if (!empty($dev)) {
                $fields = array (
                    // 'to' => 'cFRp4oUhaV8:APA91bGfmMn1w91INEn623o-b9UZpwcu55SSkFUZe_jOmwtA4kKywG7EISzyGyCSbye9tBFb7f6kKUkb1hR1a7jmkPGfPrF4c8Q_WW2Q32QLXi2MGsmIYPmnfenhQzKqTGLZDO6JYNNh',
                    'registration_ids' => $arrDev,
                    'data' => $message,
                    // 'notification' => $notif,
                );

                $ch = curl_init($url);
                $header = array('Content-Type: application/json',
                    "Authorization: key=AAAAOdKiD80:APA91bGfY5TrQ-s7PQDO1UobVBNAxOQIfaw1BX13tybPzg0F-_5gRaU2wuOMLsmZmfBfLp8BmaeL1lL2nBNwkZk12pvvFyssHyGa032_ub5cWojyXS7RMRazDuMhaCdC_TERrzEnXbXH");
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

                $result = curl_exec($ch);
                
                if ($result === FALSE) {
                    die('Curl failed: ' . curl_error($ch));
                    return false;
                }

                return true;
            }

            return false;
        }
    }

    if (!function_exists('manajer')) {
        function manajer($spv)
        {
            $ssAksesSpv = ['11001579'];
            $nip = trim($spv);

            $data = Asset\RoleUser::select('nip', 'nama')
                ->whereRaw("ROLEUSERID = (SELECT 
                            PARENTJAB 
                    FROM 
                        TU_JABATAN 
                    WHERE RECIDJABATAN = (SELECT ROLEUSERID FROM TU_ROLEUSER WHERE TRIM(nip) = '$nip'))")
                ->where('is_manajer', '1')
                ->first();

            if (!empty($data)) {
                return trim($data->nip);
            } else {
                return null;
            }
        }
    }

    if (!function_exists('manajerDev')) {
        function manajerDev($spv)
        {
            $ssAksesSpv = ['11001579'];
            $nip = trim($spv);

            $data = Asset\RoleUser::select('nip', 'nama')
                ->whereRaw("ROLEUSERID = (SELECT 
                            PARENTJAB 
                    FROM 
                        TU_JABATAN 
                    WHERE RECIDJABATAN = (SELECT ROLEUSERID FROM TU_ROLEUSER WHERE TRIM(nip) = '$nip'))")
                ->where('is_manajer', '1')
                ->first();
                
            if (in_array($spv, $ssAksesSpv)) {
                $temp = \Asset\RoleUser::where('roleuserid', '30')
                    ->where('is_manajer', '1')
                    ->first();

                if (!empty($temp)) {
                    return trim($temp->nip);
                } else {
                    return null;
                }
            } else {
                if (!empty($data)) {
                    return trim($data->nip);
                } else {
                    return null;
                }
            }
        }
    }

    if (!function_exists('spv')) {
        function spv($petugas)
        {            
            $nip = trim($petugas);

            // Diubah oleh Nafi 18/08/2020 (TRIM(NIP) diubah ke NIP like)
            // pake TRIM(NIP) ada error pas approval aduan non operasi
            $data = Asset\RoleUser::whereRaw("NIP like '%$petugas%'")->first();

            $spv = Asset\RoleUser::where('roleuserid', $data->roleuserid)
                ->where('is_manajer', '1')
                ->first();

            return trim($spv->nip);
        }
    }

    // Add by Nafi (18/03/2021)
    if (!function_exists('roleid')) {
        function roleid($petugas)
        {            
            $nip = trim($petugas);

            // Diubah oleh Nafi 18/08/2020 (TRIM(NIP) diubah ke NIP like)
            // pake TRIM(NIP) ada error pas approval aduan non operasi
            $data = Asset\RoleUser::whereRaw("NIP like '%$petugas%'")->first();

            $roleid =  $data->roleuserid;

            return trim($roleid);
        }
    }

    if (!function_exists('namaPegawai')) {
        function namaPegawai($petugas)
        {            
            $nip = trim($petugas);

            // Diubah oleh Nafi 18/08/2020 (TRIM(NIP) diubah ke NIP like)
            // pake TRIM(NIP) ada error pas approval aduan non operasi
            $data = Asset\RoleUser::whereRaw("NIP like '%$petugas%'")->first();

            $nama =  $data->nama;

            return trim($nama);
        }
    }
    //

    if (!function_exists('statusPerencanaan')) {
        function statusPerencanaan($status)
        {
            if (namaRole() == 'SPV PERENCANAAN OPERASI') {
                switch ($status) {
                    case '4.0':
                        $ret = 'Baru';
                        break;
                    case '4.1':
                        $ret = 'Proses';
                        break;
                    case '4.2':
                        $ret = 'Revisi';
                        break;
                    case '4.3':
                        $ret = 'Selesai';
                        break;
                    default:
                        $ret = 'Proses DED';
                        break;
                }
            } else {
                $ret = "Proses DED";
            }
        }
    }

    if (!function_exists('statusTindakanManajer')) {
        function statusTindakanManajer($row = null)
        {
            if (empty($row)) {
                $status = 'Baru';
            } else {
                switch (true) {
                    case ($row['status'] == '0' && empty($row['petugas_id'])):
                        $status = 'Baru';
                        break;
                    case ($row['status'] == '0' && !empty($row['petugas_id'])):
                        $status = 'Investigasi';
                        break;
                    case ($row['status'] == '1' && !empty($row['tgl_foto_investigasi'])):
                        $status = 'Sudah diinvestigasi';
                        break;
                    case ($row['status'] == '1.1'):
                        $status = 'Menunggu Approval Manajer Pemeliharaan';
                        break;
                    case ($row['status'] == '3.1'):
                        $status = 'Revisi Input Metode dari Manajer Pemeliharaan';
                        break;
                    case ($row['status'] == '1.2' && $row['metode'] == 'eksternal emergency'):
                        $status = 'Menunggu Approval Manajer DalOps (Optional)';
                        break;
                    case ($row['status'] == '1.2' && $row['metode'] != 'eksternal emergency'):
                        $status = 'Menunggu Approval Manajer DalOps';
                        break;
                    case ($row['status'] == '1.3' && $row['metode'] != 'eksternal emergency'):
                        $status = 'Menunggu Approval Manajer PPP';
                        break;
                    case ($row['status'] == '3.3'):
                        $status = 'Revisi Input Metode dari Manajer DalOps';
                        break;
                    case ($row['status'] == '3.4'):
                        $status = 'Revisi Input Metode dari Manajer PPP';
                        break;
                    case ($row['status'] == '4.0'):
                        $status = 'Proses DED (Baru)';
                        break;
                    case ($row['status'] == '4.1'):
                        $status = 'Proses DED (Proses)';
                        break;
                    case ($row['status'] == '4.2'):
                        $status = 'Proses DED (Revisi)';
                        break;
                    case ($row['status'] == '4.3'):
                        $status = 'Proses DED (Selesai)';
                        break;
                    case ($row['status'] == '2' && empty($row['foto'])):
                        $status = 'Penanganan';
                        break;
                    case ($row['status'] == '3.2'):
                        $status = 'Revisi Input Metode dari Penanganan';
                        break;
                    case ($row['status'] == '2' && (!empty($row['foto']) && empty($row['approve_dalops']))  && ($row['metode'] != "internal" || count($row['sukucadang']) > 0)):
                        $status = 'Menunggu Approval Manajer DalOps';
                        break;
                    case ($row['status'] == '2' && (!empty($row['foto']) && empty($row['approve_dalops'])) && ($row['metode'] == "internal" && count($row['sukucadang']) < 1)):
                        $status = 'Sudah ditangani';
                        break;
                    case ($row['status'] == '2' && (!empty($row['foto']) && !empty($row['approve_dalops']))):
                        $status = 'Sudah ditangani';
                        break;
                    case $row['status'] == '10':
                        $status = 'Selesai';
                        break;
                    default:
                        $status = 'Undefined';
                }
            }
// dd($status);
            return $status;
        }
    }

    if (!function_exists('aksiTindakan')) {
        function aksiTindakan($row = null)
        {
            if (empty($row)) {
                $action = 'Disposisi';
            } else {
                switch (true) {
                    case ($row['status'] == '0' && empty($row['petugas_id'])):
                        $action = 'Disposisi';
                        break;
                    case ($row['status'] == '0' && !empty($row['petugas_id'])):
                        $action = '';
                        break;
                    case ($row['status'] == '1' && !empty($row['tgl_foto_investigasi'])):
                        $action = 'Input Metode';
                        break;
                    case ($row['status'] == '1.1'):
                        $action = 'Konfirmasi';
                        break;
                    case ($row['status'] == '3.1'):
                        $action = 'Revisi Input Metode';
                        break;
                    case ($row['status'] == '1.2' && $row['metode'] == 'eksternal emergency'):
                        $action = 'Approval Manajer DalOps (Optional)';
                        break;
                    case ($row['status'] == '1.2' && $row['metode'] != 'eksternal emergency'):
                        $action = 'Approval Manajer DalOps';
                        break;
                    case ($row['status'] == '1.3' && $row['metode'] != 'eksternal emergency'):
                        $action = 'Approval Manajer PPP';
                        break;
                    case ($row['status'] == '3.3'):
                        $action = 'Revisi Input Metode';
                        break;
                    case ($row['status'] == '3.4'):
                        $action = 'Revisi Input Metode';
                        break;
                    case ($row['status'] == '4.0'):
                        $action = 'Proses DED';
                        break;
                    case ($row['status'] == '4.1'):
                        $action = 'Proses DED';
                        break;
                    case ($row['status'] == '4.2'):
                        $action = 'Proses DED';
                        break;
                    case ($row['status'] == '4.3'):
                        $action = 'Proses DED';
                        break;
                    case ($row['status'] == '2' && empty($row['foto'])):
                        $action = '';
                        break;
                    case ($row['status'] == '3.2'):
                        $action = 'Revisi Input Metode';
                        break;
                    case ($row['status'] == '2' && (!empty($row['foto']) && empty($row['approve_dalops']))  && ($row['metode'] != "internal" || count($row['sukucadang']) > 0)):
                        $action = 'Approval Manajer DalOps';
                        break;
                    case ($row['status'] == '2' && (!empty($row['foto']) && empty($row['approve_dalops'])) && ($row['metode'] == "internal" && count($row['sukucadang']) < 1)):
                        $action = 'Closing';
                        break;
                    case ($row['status'] == '2' && (!empty($row['foto']) && !empty($row['approve_dalops']))):
                        $action = 'Closing';
                        break;
                    case $row['status'] == '10':
                        $action = '';
                        break;
                }
            }

            return $action;
        }
    }

    if (!function_exists('aksiTindakanWeb')) {
        function aksiTindakanWeb($row = null)
        {
            if (empty($row)) {
                $action = 'Disposisi';
            } else {
                switch (true) {
                    case ($row['status'] == '0' && empty($row['petugas_id'])):
                        $action = 'Disposisi';
                        break;
                    case ($row['status'] == '0' && !empty($row['petugas_id'])):
                        $action = 'Investigasi';
                        break;
                    case ($row['status'] == '1' && !empty($row['tgl_foto_investigasi'])):
                        $action = 'Input Metode';
                        break;
                    case ($row['status'] == '1.1'):
                        $action = 'Konfirmasi';
                        break;
                    case ($row['status'] == '3.1'):
                        $action = 'Revisi Input Metode';
                        break;
                    case ($row['status'] == '1.2' && $row['metode'] == 'eksternal emergency'):
                        $action = 'Approval Manajer DalOps (Optional)';
                        break;
                    case ($row['status'] == '1.2' && $row['metode'] != 'eksternal emergency'):
                        $action = 'Approval Manajer DalOps';
                        break;
                    case ($row['status'] == '1.3' && $row['metode'] != 'eksternal emergency'):
                        $action = 'Approval Manajer PPP';
                        break;
                    case ($row['status'] == '4.0'):
                        $action = '';
                        break;
                    case ($row['status'] == '4.1'):
                        $action = '';
                        break;
                    case ($row['status'] == '4.2'):
                        $action = '';
                        break;
                    case ($row['status'] == '4.3'):
                        $action = '';
                        break;
                    case ($row['status'] == '2' && empty($row['foto'])):
                        $action = 'Penanganan';
                        break;
                    case ($row['status'] == '3.2'):
                        $action = 'Revisi Input Metode';
                        break;
                    case ($row['status'] == '3.3'):
                        $action = 'Revisi Input Metode';
                        break;
                    case ($row['status'] == '3.4'):
                        $action = 'Revisi Input Metode';
                        break;
                    case ($row['status'] == '2' && (!empty($row['foto']) && empty($row['approve_dalops']))  && ($row['metode'] != "internal" || count($row['sukucadang']) > 0)):
                        $action = 'Approval Manajer DalOps';
                        break;
                    case ($row['status'] == '2' && (!empty($row['foto']) && empty($row['approve_dalops'])) && ($row['metode'] == "internal" && count($row['sukucadang']) < 1)):
                        $action = 'Closing';
                        break;
                    case ($row['status'] == '2' && (!empty($row['foto']) && !empty($row['approve_dalops']))):
                        $action = 'Closing';
                        break;
                    default:
                        $action = '';
                        break;
                }
            }

            return $action;
        }
    }

    function manajerStatusCaption($wo = null, $instalasi = null, $spvbag = null)
    {
        if (in_array($wo, ['usulan', 'aduan_non_op'])) {
            if ($spvbag == '231') {
                return "Manajer Trandist";
            } else {
                return "Manajer Pemeliharaan";
            }
        } else {
            return "Manajer Pemeliharaan";
        }
    }

    if (!function_exists('statusTindakanWeb')) {
        function statusTindakanWeb($row = null, $wo = null)
        {
            if (empty($row)) {
                $status = '<a href="#" class="badge badge-primary"> Baru </a>';
            } else {
                switch (true) {
                    case ($row['status'] == '0' && empty($row['petugas_id'])):
                        $status = '<a href="#" class="badge badge-primary"> Baru </a>';
                        break;
                    case ($row['status'] == '0' && !empty($row['petugas_id'])):
                        $status = '<a href="#" class="badge badge-warning"> Investigasi </a>';
                        break;
                    case ($row['status'] == '1' && !empty($row['tgl_foto_investigasi'])):
                        $status = '<a href="#" class="badge badge-info"> Sudah diinvestigasi </a>';
                        break;
                    case ($row['status'] == '1.1'):
                        $status = '<a href="#" class="badge badge-info"> Menunggu Approval '. manajerStatusCaption($wo, $row['instalasi_id'], $row['spv']) .' </a>';
                        break;
                    case ($row['status'] == '3.1'):
                        $status = '<a href="#" class="badge badge-danger"> Revisi Input Metode dari '. manajerStatusCaption($wo, $row['instalasi_id'], $row['spv']) .' </a>';
                        break;
                    case ($row['status'] == '3.3'):
                        $status = '<a href="#" class="badge badge-danger"> Revisi Input Metode dari Dalpro </a>';
                        break;
                    case ($row['status'] == '3.4'):
                        $status = '<a href="#" class="badge badge-danger"> Revisi Input Metode dari Manajer PPP </a>';
                        break;
                    case ($row['status'] == '1.2' && $row['metode'] == 'eksternal emergency'):
                        $status = '<a href="#" class="badge badge-info"> Menunggu Approval Manajer DalPro(Optional) </a>';
                        break;
                    case ($row['status'] == '1.2' && $row['metode'] != 'eksternal emergency'):
                        $status = '<a href="#" class="badge badge-info"> Menunggu Approval Manajer DalPro </a>';
                        break;
                    case ($row['status'] == '1.3' && $row['metode'] != 'eksternal emergency'):
                        $status = '<a href="#" class="badge badge-info"> Menunggu Approval Manajer PPP </a>';
                        break;
                    case ($row['status'] == '4.0'):
                        $status = '<a href="#" class="badge badge-danger"> Proses DED (Baru) </a>';
                        break;
                    case ($row['status'] == '4.1'):
                        $status = '<a href="#" class="badge badge-danger"> Proses DED (Proses) </a>';
                        break;
                    case ($row['status'] == '4.2'):
                        $status = '<a href="#" class="badge badge-danger"> Proses DED (Revisi) </a>';
                        break;
                    case ($row['status'] == '4.3'):
                        $status = '<a href="#" class="badge badge-danger"> Proses DED (Selesai) </a>';
                        break;
                    case ($row['status'] == '2' && empty($row['foto'])):
                        $status = '<a href="#" class="badge badge-success"> Penanganan </a>';
                        break;
                    case ($row['status'] == '3.2'):
                        $status = '<a href="#" class="badge badge-danger"> Revisi Input Metode dari Penanganan </a>';
                        break;
                    case ($row['status'] == '2' && (!empty($row['foto']) && empty($row['approve_dalops'])) && ($row['metode'] != "internal" || count($row['sukucadang']) > 0)):
                        $status = '<a href="#" class="badge badge-success"> Menunggu Approval Manajer DalPro </a>';
                        break;
                    case ($row['status'] == '2' && (!empty($row['foto']) && empty($row['approve_dalops'])) && ($row['metode'] == "internal" && count($row['sukucadang']) < 1)):
                        $status = '<a href="#" class="badge badge-success"> Closing </a>';
                        break;
                    case ($row['status'] == '2' && (!empty($row['foto']) && !empty($row['approve_dalops']))):
                        $status = '<a href="#" class="badge badge-success"> Sudah ditangani </a>';
                        break;
                    case ($row['status'] == '10'):
                        $status = '<a href="#" class="badge badge-dark"> Selesai </a>';
                        break;
                    case ($row['status'] == '98'):
                        $status = '<a href="#" class="badge badge-dark"> Digantikan </a>';
                        break;
                    case ($row['status'] == '99'):
                        $status = '<a href="#" class="badge badge-dark"> Bukan Kerusakan </a>';
                        break;
                    default:
                        $status = '<a href="#" class="badge badge-secondary"> Undefined </a>';
                        break;
                }
            }
// dd($status);
            return $status;
        }
    }

    function toObject($Array)
    {
        // Create new stdClass object 
        $object = new \stdClass(); 
          
        // Use loop to convert array into 
        // stdClass object 
        foreach ($Array as $key => $value) { 
            if (is_array($value)) { 
                $value = ToObject($value); 
            } 
            $object->$key = $value; 
        } 
        return $object; 
    }

    function generateKodeWo($wo, $urutan, $bagian, $instalasi_id, $tanggal)
    {
        switch ($wo) {
            case 'monitoring':
                $noWO = 1;
                break;
            case 'prwRutin':
                $noWO = 2;
                break;
            case 'prw':
                $noWO = 3;
                break;
            case 'prb':
                $noWO = 4;
                break;
            case 'aduan':
                $noWO = 5;
                break;
        }

        if (is_numeric($bagian)) {
            switch ($bagian) {
                case '1':
                    $noBagian = 'M';
                    break;
                case '2':
                    $noBagian = 'E';
                    break;
                case '3':
                    $noBagian = 'I';
                    break;
                case '4':
                    $noBagian = 'I';
                    break;
            }
        } else {
            $noBagian = strtoupper($bagian);
        }

        $gen = sprintf("%02d/WO-%d/%s/%s/%d/%d", $urutan, 
                                                $noWO, 
                                                $noBagian, 
                                                $instalasi_id,
                                                date('m', strtotime($tanggal)),
                                                date('Y', strtotime($tanggal)));
        return $gen;
    }

    function prwUrutan()
    {
        $urutan = Asset\Models\Perawatan::select(DB::raw('max(urutan) as urutan'))
            ->whereRaw("TO_CHAR(TANGGAL, 'MM') = ".date('m'))
            ->first();
        $urutnya = !empty($urutan->urutan)?$urutan->urutan+1:1;

        return $urutnya;
    }

    function prwRutinUrutan()
    {
        $urutan = Asset\Models\Prw4w::select(DB::raw('max(urutan) as urutan'))
            ->whereRaw("TO_CHAR(TANGGAL, 'MM') = ".date('m'))
            ->first();
        $urutnya = !empty($urutan->urutan)?$urutan->urutan+1:1;

        return $urutnya;
    }

    function prwRutinUrutanDev()
    {
        $urutan = Asset\Models\Prw4wDev::select(DB::raw('max(urutan) as urutan'))
            ->whereRaw("TO_CHAR(TANGGAL, 'MM') = ".date('m'))
            ->first();
        $urutnya = !empty($urutan->urutan)?$urutan->urutan+1:1;

        return $urutnya;
    }

    function prbUrutan($tipe = 'monitoring')
    {
        $urutan = Asset\Models\Perbaikan::select(DB::raw('max(urutan) as urutan'))
            ->where('tipe', $tipe)
            ->whereRaw("TO_CHAR(TANGGAL, 'MM') = ".date('m'))
            ->first();
        $urutnya = !empty($urutan->urutan)?$urutan->urutan+1:1;

        return $urutnya;
    }

    function monitoringUrutan()
    {
        $urutan = Asset\Models\Ms4w::select(DB::raw('max(urutan) as urutan'))
            ->whereRaw("TO_CHAR(TANGGAL, 'MM') = ".date('m'))
            ->first();
        $urutnya = !empty($urutan->urutan)?$urutan->urutan+1:1;

        return $urutnya;
    }

    function cekDir($dir, $disk = "local")
    {
        /*if (!\Storage::disk('sftp')->exists($dir)) {
            Storage::disk('sftp')->makeDirectory($dir);
        }*/
// dd($dir);
        if (!\Storage::disk($disk)->exists($dir)) {
            Storage::disk($disk)->makeDirectory($dir, 0777, true);
        }
    }

    function aduanNonOperasiStatus($status)
    {
        $ret = null;
        switch ($status) {
            case '0':
                $ret = 'Disposisi';
                break;
            case '1':
                $ret = 'Disposisi';
                break;
            default:
                # code...
                break;
        }
    }

    function cekJabatan($nip)
    {
        $levelJab = 'petugas';

        $user = \Asset\Role::where('trim(nip)', trim($nip))->first();        
        if (!empty($user->is_manajer)) {
            $jab = \Asset\Jabatan::where('recidjabatan', $user->roleuserid)->first();

            switch ($jab->leveljab) {
                case '1':
                    $levelJab = "manajer senior";
                    break;
                case '2':
                    $levelJab = "manajer";
                    break;
                case '3':
                    $levelJab = "spv";
                    break;
            }            
        }

        return $levelJab;
    }

    function getJabatan($nip)
    {
        $user = \Asset\Role::where('trim(nip)', trim($nip))->first();        
        if (!empty($user->is_manajer)) {
            $jab = \Asset\Jabatan::where('recidjabatan', $user->roleuserid)->first();

            return $jab;   
        }

        return false;
    }

    function getProfile($nip)
    {
        $user = \Asset\Role::where('trim(nip)', trim($nip))->first();        

        return $user;
    }

    function bagianCaption($str)
    {
        $arr = explode(",", $str);
        $result = "";

        if (count($arr) > 0) {
            foreach ($arr as $row) {
                switch ($row) {
                    case '1':
                        $result .= "Mekanikal ";
                        break;
                    case '2':
                        $result .= "Elektrikal ";
                        break;
                    case '3':
                        $result .= "Instrumentasi ";
                        break;
                    case '4':
                        $result .= "Sipil ";
                        break;
                }
            }
            $result = str_replace(" ", ",", trim($result));
        }

        return $result;
    }

    function filterMenu($jabatan, $menu)
    {
        if ($jabatan == "petugas") {
            if (in_array($menu, petugasMenu())) {
                return $menu;
            }
        }

        if ($jabatan == "spv") {
            if (in_array($menu, spvMenu())) {
                return $menu;
            }
        }

        if ($jabatan == "manajer") {
            if (in_array($menu, manajerMenu())) {
                return $menu;
            }
        }

        if ($jabatan == "dalops") {
            if (in_array($menu, dalopsMenu())) {
                return $menu;
            }
        }

        return "";
    }

    function rupiah($angka, $numBehind = 0, $decimalDelimiter = ",", $delimiter = ".", $rupiah = true)
    {
        $hasil_rupiah = "Rp -" ;
        
        if (is_numeric($angka)) {
            if ($rupiah) {
                $hasil_rupiah = "Rp " . number_format($angka, $numBehind, $decimalDelimiter, $delimiter);
            } else {
                $hasil_rupiah = number_format($angka, $numBehind, $decimalDelimiter, $delimiter);
            }
        }
        
        return $hasil_rupiah;
    }


    function petugasMenu()
    {
        return [
            'Investigasi',
            'Penanganan'
        ];
    }

    function spvMenu()
    {
        return [
            'Disposisi',
            'Input Metode',
            'Revisi Input Metode',
            'Closing'
        ];
    }

    function manajerMenu()
    {
        return [
            'Konfirmasi'
        ];
    }

    function dalopsMenu()
    {
        return [
            'Approval Manajer DalOps',
            'Approval Manajer DalOps (Optional)'
        ];
    }

    function adminNip()
    {
        return [
            '10601344                      ',
            '10601366                      ',
            '10801498                      ',
            '10901554                      '
        ];
    }

    function cekMasaPemeliharaan($start, $end)
    {
        $isMasaPemeliharaan = "no";

        $curDate = date('Y-m-d');
        $curDate=date('Y-m-d', strtotime($curDate));

        $dateBegin = date('Y-m-d', strtotime($start));
        $dateEnd = date('Y-m-d', strtotime($end));

        if (($curDate >= $dateBegin) && ($curDate <= $dateEnd)){
            $isMasaPemeliharaan = "yes";
        }

        return $isMasaPemeliharaan;
    }

    function cekSpvPengolahan()
    {
        $ret = null;

        $temp = Asset\Role::with('jabatan')
            ->where('nip', \Auth::user()->userid)
            ->first();

        if (!empty($temp->jabatan)) {
            // dd($temp->jabatan->namajabatan);
            if (((strpos($temp->jabatan->namajabatan, 'SPV Pengolahan') !== false) || (strpos($temp->jabatan->namajabatan, 'Sistem Distribusi Utama') !== false)) || (strpos($temp->jabatan->namajabatan, 'Manajer Produksi') !== false)) {
                $ret = $temp->jabatan->namajabatan;
            }
        }

        return $ret;
    }

    function getInstalasiPengolahan() 
    {
        $arr = [];

        $jabatan = cekSpvPengolahan();

        if ($jabatan) {
            $str = str_replace('SPV', '', $jabatan);
            $str = trim(str_replace('Pengolahan', '', $str));

            if ($str == 'Sistem Distribusi Utama') {
                $instalasi = Asset\Models\Instalasi::where('name', 'like', '%Pompa%')->get();
            } else {
                $instalasi = Asset\Models\Instalasi::where('name', 'like', '%'.$str)->get();
            }
            
            if (!empty($instalasi)) {
                foreach ($instalasi as $row) {
                    $arr[] = $row->id;
                }
            }
        }
        
        return $arr;        
    }

    function getLibur($weeks = array())
    {
        $libur = "";

        if (count($weeks) > 0) {
            for ($i=1; $i <= 52 ; $i++) { 
                if (!in_array($i, $weeks)) {
                    $libur .= ",".$i;
                }
            }
        }

        return $libur.',';
    }

    function convertDateTime($date, $format = 'Y-m-d H:i:s')
    {
        $tz1 = 'UTC';
        $tz2 = 'Asia/Jakarta'; // UTC +7

        $d = new DateTime($date, new DateTimeZone($tz1));
        $d->setTimeZone(new DateTimeZone($tz2));

        return $d->format($format);
    }

    function changeDateFormat($date = null, $from = null, $to = null)
    {
        if (empty($date)) dd('kosong date');
        if (empty($from)) dd('kosong start');
        if (empty($to)) dd('kosong to');

        return DateTime::createFromFormat($from, $date)->format($to);
    }

    function getNow()
    {
        $data = DB::select("SELECT TO_CHAR(SYSDATE, 'YYYY-MM-DD HH24:MI:SS') AS saiki FROM dual");
        
        return $data[0]->saiki;
    }

    function getPeriode($period)
    {
        $temp = explode("-", $period);

        switch (strtolower($temp[0])) {
            case 'january':
                $bulan = 'Januari';
                break;
            case 'february':
                $bulan = 'Februari';
                break;
            case 'march':
                $bulan = 'Maret';
                break;
            case 'april':
                $bulan = 'April';
                break;
            case 'may':
                $bulan = 'Mei';
                break;
            case 'june':
                $bulan = 'Juni';
                break;
            case 'july':
                $bulan = 'Juli';
                break;
            case 'august':
                $bulan = 'Agustus';
                break;
            case 'september':
                $bulan = 'September';
                break;
            case 'october':
                $bulan = 'Oktober';
                break;
            case 'november':
                $bulan = 'November';
                break;
            case 'december':
                $bulan = 'Desember';
                break;
        }

        return $bulan.' '.$temp[1];
    }

    function selectInstalasi()
    {
        $instalasi = Asset\Models\Instalasi::whereIn('id', lokasi())
            ->get()->pluck('name', 'id')->toArray(); 
        $labelInstalasi = ["" => "- Pilih Instalasi -"];
        $instalasi = $labelInstalasi + $instalasi;

        return $instalasi;
    }

    function selectBagian()
    {
        $bagian = Asset\Models\Master::Bagian()
                ->get()->pluck('name', 'id')->toArray();
        $label = ["" => "- Pilih Bagian -"];
        $bagian = $label + $bagian;

        return $bagian;
    }

    function getWeekInMonth($curWeek)
    {
        // $curWeek = date('W');
        // $curWeek = 4;
        $week = [];

        if ($curWeek == '52') {
            $b_atas = (int)0;
            $b_bawah = (int)4;
        } else {
            $b_atas = (int)floor($curWeek / 4) * 4;
            $b_bawah = (int)floor($curWeek / 4) + 1;
            $b_bawah *= 4;
        }

        if ( lastWeekNumber() == '53' ) {
            $week['53'] = 53;
        }

        for ($i = $b_atas + 1; $i <= $b_bawah; $i++) { 
            $week[$i] = $i;
        }

        return $week;
    }

    function getFromWeeknumber($week, $year)
    {
        $dto = new DateTime();
        $dto->setISODate($year, $week);
        $ret['week_start'] = $dto->format('Y-m-d');
        $dto->modify('+6 days');
        $ret['week_end'] = $dto->format('Y-m-d');
        
        return $ret;
    }

    function formatIn(array $data)
    {
        $test = "";
        foreach ($data as $row) {
            $test .= "'$row',";
        }

        return $test;
    }

    function get4weeks($curWeek = null)
    {
        if (empty($curWeek)) {
            $curWeek = date('W');
        }
        // $curWeek = 48;

        if ($curWeek == '52') {
            $b_atas = (int)0;
            $b_bawah = (int)4;
        } else {
            $b_atas = (int)floor($curWeek / 4) * 4;
            $b_bawah = (int)floor($curWeek / 4) + 1;
            $b_bawah *= 4;
        }

        // if ($curWeek % 4 == 0 || in_array(trim(\Auth::user()->userid), $nipception)) {
        for ($i = $b_atas + 1; $i <= $b_bawah; $i++) { 
            $week[] = $i;
        }

        return $week;
    }

    function getMsPpp()
    {
        $data = null;

        $id = config('custom.id_ms_ppp');
        //$id = 201; //PJS (temporary)
        $data = Asset\Role::where('roleuserid', $id)->where('is_manajer', '1')->first();

        // dd($data);

        return $data;
    }

    function captionWo($foreignKey)
    {
        switch ($foreignKey) {
            case 'prb_data_id':
                $ret = 'perbaikan';
                break;
            case 'prw_data_id':
                $ret = 'perawatan';
                break;
            case 'aduan_non_op_id':
                $ret = 'aduan';
                break;
            case 'usulan_id':
                $ret = 'usulan';
                break;
        }

        return $ret;
    }

    function formatNip($nip)
    {
        if (strlen(trim($nip)) == 8) {
            $format = "";
            for ($i=0; $i < strlen($nip); $i++) { 
                if (in_array($i, [0, 2])) {
                    $format .= $nip[$i] . '.';
                } else {
                    $format .= $nip[$i];
                }
            }

            return $format;
        }

        return $nip;
    }

    function pangkat($a, $b)
    {
        $bil = $a;
        for($i=0;$i<($b-1);$i++) {
            $bil = $bil * $a;
        }
        
        return $bil;
    }


    //====================================================================================================================
    //====================================================================================================================
    //====================================================================================================================
    // USING MODELS
    // 

    function getListProposal($recidJab)
    {
        $labelProposal = ["" => "-             Pilih Proposal             -"];
        $proposals = [];

        $usulan = Asset\Models\Proposal::where('spv', $recidJab)->get();
        foreach ($usulan as $row) {
            $proposals[$row->id] = trim($row->nama);
        }

        $proposals = $labelProposal + $proposals;
        return $proposals;
    }


    function getRecidJabatan($nip)
    {
        $data = Asset\Role::where(DB::raw("TRIM(NIP)"), trim($nip))->first();

        if ($data) {
            return $data->jabatan->recidjabatan;
        }

        return null;
    }    

    function listInstalasi($filter = null)
    {
        $instalasi = Asset\Models\Instalasi::get()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Pilih Instalasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        return $instalasi;
    }

    function listLokasi($instalasi = null)
    {
        if ($instalasi) {
            $lokasi = Asset\Models\Lokasi::where('instalasi_id', $instalasi)
                ->get()->pluck('name', 'id')->toArray();
        } else{
            $lokasi = Asset\Models\Lokasi::get()->pluck('name', 'id')->toArray();    
        }
        
        $labelLokasi = ["" => "-             Pilih Lokasi             -"];
        $lokasi = $labelLokasi + $lokasi;

        return $lokasi;
    }

    function listRuang($lokasi = null)
    {
        if ($lokasi) {
            $ruang = Asset\Models\Ruangan::where('lokasi_id', $lokasi)
                ->get()->pluck('name', 'id')->toArray();
        }else{
            $ruang = Asset\Models\Ruangan::get()->pluck('name', 'id')->toArray();    
        }
        
        $labelRuang = ["" => "-             Pilih Ruangan             -"];
        $ruang = $labelRuang + $ruang;

        return $ruang;
    }

    function listAset($instalasi = null)
    {
        if ($instalasi) {
            $aset = Asset\Models\Aset::where('instalasi_id', $instalasi)
                ->get()->pluck('nama_aset', 'id')->toArray();
        } else {
            $aset = Asset\Models\Aset::get()->pluck('nama_aset', 'id')->toArray();    
        }
        
        $labelAset = ["" => "-             Pilih Aset             -"];
        $aset = $labelAset + $aset;

        return $aset;
    }

    function listKategori($filter = null)
    {
        $kategori = Asset\Models\Kategori::get()->pluck('name', 'id')->toArray();
        $labelKategori = ["" => "-             Pilih Kategori             -"];
        $kategori = $labelKategori + $kategori;

        return $kategori;
    }

    function listKondisi($filter = null)
    {
        $kondisi = Asset\Models\Kondisi::get()->pluck('name', 'id')->toArray();
        $labelKondisi = ["" => "-             Pilih Kondisi             -"];
        $kondisi = $labelKondisi + $kondisi;

        return $kondisi;
    }

    function listSukucadang($fKey, $fId, $waitlist = false, $all = false)
    {
        if ($waitlist) {
            $dataSc = Asset\Models\PermohonanSc::select('permohonan_sc.' . $fKey, 'permohonan_sc.status', 'permohonan_sc_detail.*')
                ->join('permohonan_sc_detail', 'permohonan_sc.id', '=', 'permohonan_sc_detail.permohonan_sc_id')
                ->where($fKey, $fId)
                ->where('status', 'waiting-list')
                ->get();
        } else {
            if ($all == true) {
                $status = ['permintaan', 'baru', 'disetujui', 'ditolak', 'dikeluarkan'];
            } else {
                $status = ['permintaan', 'baru'];
            }

            $dataSc = Asset\Models\PermohonanSc::select('permohonan_sc.' . $fKey,'permohonan_sc.status','permohonan_sc_detail.*')
                ->join('permohonan_sc_detail', 'permohonan_sc.id', '=', 'permohonan_sc_detail.permohonan_sc_id')
                ->whereIn('status', $status)
                ->where($fKey, $fId)
                ->get();
        }

        return $dataSc;
    }

    function sukucadangSaldo($tmpKdAlias)
    {
        $pairKodeAlias = [];
        $sc = ["" => "-  Pilih Suku Cadang  -"];
        $sqlSc = DB::connection('koneksigudang')->table('v_saldogdg')
            ->select('v_saldogdg.kd_barang', 'v_saldogdg.gudang', 'v_saldogdg.kelompok_barang', 'v_saldogdg.saldo', 'm_barang.kd_barang_alias', 'm_barang_alias.nama', 'm_barang.SATUAN', 'm_gudang.nama_gdg')
            ->join('m_barang', 'v_saldogdg.kd_barang', '=', 'm_barang.kd_barang')
            ->join('m_barang_alias', 'm_barang.kd_barang_alias', '=', 'm_barang_alias.recid')
            ->join('m_gudang', 'v_saldogdg.gudang', '=', 'm_gudang.kd_gdg')
            ->where('gudang', 'like', 'GSC%')
            ->get();
        if (count($sqlSc) > 0) {
            foreach ($sqlSc as $row) {
                $sc[$row->kd_barang_alias.'#'.$row->saldo.'#'.$row->gudang.'#'.$row->kelompok_barang] = $row->nama.' @ '.$row->nama_gdg;

                if (in_array($row->kd_barang_alias, $tmpKdAlias)) {
                    $pairKodeAlias[$row->kd_barang_alias] = $row->nama;
                }
            }
        }

        return $pairKodeAlias;
    }

?>