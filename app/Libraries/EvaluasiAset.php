<?php
namespace Asset\Libraries;

use Asset\Models\MasterKodeFm;

use DB;
use Cache;

class EvaluasiAset
{
	
	function __construct()
	{
		# code...
	}

	public static function getB1($kodeBobot, $umurBerjalan, $jmlPrw, $jmlPrbRingan, $jmlPrbBerat, $id = "")
	{
		$result = 0;
		$bobotPrw = 0;
		$bobotPrbRingan = 0;
		$bobotPrbBerat = 0;
		$bobotumurBerjalan = 0;

		// perawatan
		switch (true) {
			case $jmlPrw == 0:
				$bobotPrw = 0;
				break;
			case ($jmlPrw >= 1) && ($jmlPrw <= 3):
				$bobotPrw = 1;
				break;
			case ($jmlPrw >= 4) && ($jmlPrw <= 6):
				$bobotPrw = 2;
				break;
			case ($jmlPrw >= 7) && ($jmlPrw <= 10):
				$bobotPrw = 3;
				break;
			case ($jmlPrw >= 11) && ($jmlPrw <= 12):
				$bobotPrw = 4;
				break;
			case $jmlPrw > 12:
				$bobotPrw = 5;
				break;
		}

		// perbaikan Ringan
		switch ($jmlPrbRingan) {
			case 0:
				$bobotPrbRingan = 0;
				break;
			case 1:
				$bobotPrbRingan = 1;
				break;
			case 2:
				$bobotPrbRingan = 2;
				break;
			case 3:
				$bobotPrbRingan = 3;
				break;
			case 4:
				$bobotPrbRingan = 4;
				break;
			default:
				$bobotPrbRingan = 5;
		}

		// Perbaikan Berat
		switch ($jmlPrbBerat) {
			case 0:
				$bobotPrbBerat = 0;
				break;
			case 1:
				$bobotPrbBerat = 3;
				break;
			case 2:
				$bobotPrbBerat = 5;
				break;
		}

		// Umur Ekonomis
		switch (true) {
			case $umurBerjalan <= 8:
				$bobotumurBerjalan = 0;
				break;
			case ($umurBerjalan >= 9) && ($umurBerjalan <= 13):
				$bobotumurBerjalan = 1;
				break;
			case ($umurBerjalan >= 14) && ($umurBerjalan <= 18):
				$bobotumurBerjalan = 2;
				break;
			case ($umurBerjalan >= 19) && ($umurBerjalan <= 23):
				$bobotumurBerjalan = 3;
				break;
			case ($umurBerjalan >= 24) && ($umurBerjalan <= 28):
				$bobotumurBerjalan = 4;
				break;
			case $umurBerjalan > 28:
				$bobotumurBerjalan = 5;
				break;
		}

		/*if ($id = '7524') {
			dd($umurBerjalan);
			dd([$bobotPrw , $bobotPrbRingan , $bobotPrbBerat , $bobotumurBerjalan]);
		}*/

		$result = ($bobotPrw + $bobotPrbRingan + $bobotPrbBerat + $bobotumurBerjalan) / 4;
		return $result;
	}

	public static function getB2($umurBerjalan, $jmlPrw, $jmlPrb)
	{
		$result = 0;
		$bobotPrw = 0;
		$bobotPrb = 0;
		$bobotumurBerjalan = 0;

		// perawatan
		switch (true) {
			case $jmlPrw == 0:
				$bobotPrw = 0;
				break;
			case ($jmlPrw >= 1 && $jmlPrw <= 2):
				$bobotPrw = 1;
				break;
			case ($jmlPrw >= 3 && $jmlPrw <= 4):
				$bobotPrw = 2;
				break;
			case $jmlPrw == 5:
				$bobotPrw = 3;
				break;
			case $jmlPrw == 6:
				$bobotPrw = 4;
				break;
			case $jmlPrw > 6:
				$bobotPrw = 5;
				break;
		}

		switch ($jmlPrb) {
			case 0:
				$bobotPrb = 0;
				break;
			case 1:
				$bobotPrb = 1;
				break;
			case 2:
				$bobotPrb = 2;
				break;
			case 3:
				$bobotPrb = 3;
				break;
			case 4:
				$bobotPrb = 4;
				break;
			default:
				$bobotPrb = 5;
		}

		switch (true) {
			case $umurBerjalan <= 4:
				$bobotumurBerjalan = 0;
				break;
			case $umurBerjalan == 5:
				$bobotumurBerjalan = 1;
				break;
			case $umurBerjalan == 6:
				$bobotumurBerjalan = 2;
				break;
			case $umurBerjalan == 7:
				$bobotumurBerjalan = 3;
				break;
			case $umurBerjalan == 8:
				$bobotumurBerjalan = 4;
				break;
			case $umurBerjalan > 8:
				$bobotumurBerjalan = 5;
				break;
		}

		$result = ($bobotPrw + $bobotPrb + $bobotumurBerjalan) / 3;
		return $result;
	}

	public static function getB3($kondisi, $umurBerjalan)
	{
		$bobotKondisi = 0;
		$bobotumurBerjalan = 0;

		if (strtolower($kondisi) == 'normal') {
			$bobotKondisi = 0;
		} else {
			$bobotKondisi = 2;
		}

		switch (true) {
			case ($umurBerjalan >= 1 && $umurBerjalan <= 4):
				$bobotumurBerjalan = 0;
				break;
			case ($umurBerjalan > 4):
				$bobotumurBerjalan = 2;
				break;
			default:
				$bobotumurBerjalan = 0;
				break;
		}

		$result = ($bobotKondisi + $bobotumurBerjalan) / 2;
		return $result;
	}

	public static function getB4($umurBerjalan, $jmlPrw, $jmlPrb)
	{
		$result = 0;
		$bobotPrw = 0;
		$bobotPrb = 0;
		$bobotumurBerjalan = 0;

		// perawatan
		switch (true) {
			case ($jmlPrw == 0):
				$bobotPrw = 0;
				break;
			case $jmlPrw == 1:
				$bobotPrw = 2;
				break;
			case $jmlPrw == 2:
				$bobotPrw = 3;
				break;
			case $jmlPrw == 3:
				$bobotPrw = 4;
				break;
			case $jmlPrw > 3:
				$bobotPrw = 5;
				break;
		}

		switch (true) {
			case $jmlPrb == 0:
				$bobotPrb = 0;
				break;
			case ($jmlPrb >= 1 && $jmlPrb <= 5):
				$bobotPrb = 1;
				break;
			case ($jmlPrb >= 6 && $jmlPrb <= 10):
				$bobotPrb = 2;
				break;
			case ($jmlPrb >= 11 && $jmlPrb <= 15):
				$bobotPrb = 3;
				break;
			case ($jmlPrb >= 16 && $jmlPrb <= 20):
				$bobotPrb = 4;
				break;
			case $jmlPrb > 20:
				$bobotPrb = 5;
		}

		switch (true) {
			case $umurBerjalan <= 20:
				$bobotumurBerjalan = 0;
				break;
			case ($umurBerjalan >= 21 && $umurBerjalan <= 30):
				$bobotumurBerjalan = 1;
				break;
			case ($umurBerjalan >= 31 && $umurBerjalan <= 40):
				$bobotumurBerjalan = 2;
				break;
			case ($umurBerjalan >= 41 && $umurBerjalan <= 50):
				$bobotumurBerjalan = 3;
				break;
			case ($umurBerjalan >= 51 && $umurBerjalan <= 60):
				$bobotumurBerjalan = 4;
				break;
			case $umurBerjalan > 60:
				$bobotumurBerjalan = 5;
				break;
		}

		$result = ($bobotPrw + $bobotPrb + $bobotumurBerjalan) / 3;
		return $result;
	}

	public static function getRata($kodeBobot, $umurBerjalan, $jmlPrw, $jmlPrb, $kondisi = "")
	{
		$rata = 0;
		
		switch ($kodeBobot) {
			case 'B2':
				$rata = self::getB2($umurBerjalan, $jmlPrw, $jmlPrb);
				break;
			case 'B3':
				$rata = self::getB3($umurBerjalan, $kondisi);
				break;
			case 'B4':
				$rata = self::getB4($umurBerjalan, $jmlPrw, $jmlPrb);
				break;
		}

		return $rata;
	}

	public static function getHasilB1($rata)
	{
		$result = 0;

		switch (true) {
			case ($rata < 1.5):
				$result = 'Normal';
				break;
			case ($rata >= 1.5) && ($rata <= 3):
				$result = 'Perbaikan (Refurbish)';
				break;
			case $rata > 3:
				$result = 'Penggantian (Replace)';
				break;
		}

		return $result;
	}

	public static function getHasilB2($rata)
	{
		$result = 0;

		switch (true) {
			case ($rata < 1):
				$result = 'Normal';
				break;
			case ($rata >= 1) && ($rata <= 2):
				$result = 'Perbaikan (Refurbish)';
				break;
			case $rata > 2:
				$result = 'Penggantian (Replace)';
				break;
		}

		return $result;
	}

	public static function getHasilB3($rata)
	{
		$result = 0;

		switch (true) {
			case ($rata == 0):
				$result = 'Normal';
				break;
			case ($rata < 2):
				$result = 'Perbaikan (Refurbish)';
				break;
			case $rata == 2:
				$result = 'Penggantian (Replace)';
				break;
		}

		return $result;
	}

	public static function getHasilB4($rata)
	{
		$result = 0;

		switch (true) {
			case ($rata < 1):
				$result = 'Normal';
				break;
			case ($rata >= 1) && ($rata <= 3):
				$result = 'Perbaikan (Refurbish)';
				break;
			case $rata > 3:
				$result = 'Penggantian (Replace)';
				break;
		}

		return $result;
	}

	public static function bobotOperasional($param = '1')
	{
		$ret = 1;

		switch ($param) {
			case '1':
				$ret = 1;
				break;
			case '2':
				$ret = 2;
				break;
			case '3':
				$ret = 3;
				break;
			case '4':
				$ret = 4;
				break;
			case '5':
				$ret = 5;
				break;
		}

		return $ret;
	}

	public static function bobotKeuangan($param = 'NPV  < 1')
	{
		$ret = 1;

		switch ($param) {
			case 'NPV > 3':
				$ret = 5;
				break;
			case 'NPV 2 - 3':
				$ret = 4;
				break;
			case 'NPV 1 - 2':
				$ret = 3;
				break;
			case 'NPV = 1':
				$ret = 2;
				break;
			case 'NPV  < 1':
				$ret = 1;
				break;
		}

		return $ret;
	}

	public static function bobotWaktu($param = 'Nice to Have (>1 tahun)')
	{
		$ret = 1;

		switch ($param) {
			case 'Urgent (<1 tahun)':
				$ret = 5;
				break;
			case '4':
				$ret = 4;
				break;
			case 'Important (1 tahun)':
				$ret = 3;
				break;
			case '2':
				$ret = 2;
				break;
			case 'Nice to Have (>1 tahun)':
				$ret = 1;
				break;
		}

		return $ret;
	}

	public static function getUmurBerjalan($tahunPasang)
	{
		$ret = "xxxx";
		
		if (!empty($tahunPasang)) {
			$ret = date('Y') - $tahunPasang;
		}

		return $ret;
	}

	public static function getKondisi($start, $end, $instalasi, $bagian)
	{
		$result = Cache::remember('users', 30, function() use($start, $end, $instalasi, $bagian){
		    return DB::select("select b.id, b.kode_aset, b.nama_aset, b.kode_fm, b.tanggal, b.ms_4w_id, b.operasional
			from aset a,
			     table(cast(F_BOBOT_FM_E7(to_date($start,'YYYYMMDD'),to_date($end,'YYYYMMDD'),a.id) as CT_BOBOT_FM_E7)) b
			where a.id = b.id 
			      and a.kode_fm = 'E7' 
			      and a.kondisi_id <> '12'
			      and a.instalasi_id = $instalasi
			      and a.bagian = $bagian
			union 
			select b.id, b.kode_aset, b.nama_aset, b.kode_fm, b.tanggal, b.ms_4w_id, b.operasional
			from aset a,
			     table(cast(F_BOBOT_FM_E8(to_date($start,'YYYYMMDD'),to_date($end,'YYYYMMDD'),a.id) as CT_BOBOT_FM_E8)) b
			where a.id = b.id 
			      and a.kode_fm = 'E8' 
			      and a.kondisi_id <> '12'
			      and a.instalasi_id = $instalasi
			      and a.bagian = $bagian
			union
			select b.id, b.kode_aset, b.nama_aset, b.kode_fm, b.tanggal, b.ms_4w_id, b.pintu_air as operasional
			from aset a,
			     table(cast(F_BOBOT_FM_S7(to_date($start,'YYYYMMDD'),to_date($end,'YYYYMMDD'),a.id) as CT_BOBOT_FM_S7)) b
			where a.id = b.id 
			      and a.kode_fm = 'S7' 
			      and a.kondisi_id <> '12'
			      and a.instalasi_id = $instalasi
			      and a.bagian = $bagian
			union
			select b.id, b.kode_aset, b.nama_aset, b.kode_fm, b.tanggal, b.ms_4w_id, b.tandon as operasional
			from aset a,
			     table(cast(F_BOBOT_FM_S8(to_date($start,'YYYYMMDD'),to_date($end,'YYYYMMDD'),a.id) as CT_BOBOT_FM_S8)) b
			where a.id = b.id 
			      and a.kode_fm = 'S8' 
			      and a.kondisi_id <> '12'
			      and a.instalasi_id = $instalasi
			      and a.bagian = $bagian
			union
			select b.id, b.kode_aset, b.nama_aset, b.kode_fm, b.tanggal, b.ms_4w_id, b.operasional
			from aset a,
			     table(cast(F_BOBOT_FM_E9(to_date($start,'YYYYMMDD'),to_date($end,'YYYYMMDD'),a.id) as CT_BOBOT_FM_E9)) b
			where a.id = b.id 
			      and a.kode_fm = 'E9' 
			      and a.kondisi_id <> '12'
			      and a.instalasi_id = $instalasi
			      and a.bagian = $bagian
			union
			select b.id, b.kode_aset, b.nama_aset, b.kode_fm, b.tanggal, b.ms_4w_id, b.operasional
			from aset a,
			     table(cast(F_BOBOT_FM_E10(to_date($start,'YYYYMMDD'),to_date($end,'YYYYMMDD'),a.id) as CT_BOBOT_FM_E10)) b
			where a.id = b.id 
			      and a.kode_fm = 'E10' 
			      and a.kondisi_id <> '12'
			      and a.instalasi_id = $instalasi
			      and a.bagian = $bagian
			union
			select b.id, b.kode_aset, b.nama_aset, b.kode_fm, b.tanggal, b.ms_4w_id, b.operasional
			from aset a,
			     table(cast(F_BOBOT_FM_M7(to_date($start,'YYYYMMDD'),to_date($end,'YYYYMMDD'),a.id) as CT_BOBOT_FM_M7)) b
			where a.id = b.id 
			      and a.kode_fm = 'M7' 
			      and a.kondisi_id <> '12'
			      and a.instalasi_id = $instalasi
			      and a.bagian = $bagian
			union
			select b.id, b.kode_aset, b.nama_aset, b.kode_fm, b.tanggal, b.ms_4w_id, b.operasional
			from aset a,
			     table(cast(F_BOBOT_FM_M8(to_date($start,'YYYYMMDD'),to_date($end,'YYYYMMDD'),a.id) as CT_BOBOT_FM_M8)) b
			where a.id = b.id 
			      and a.kode_fm = 'M8' 
			      and a.kondisi_id <> '12'
			      and a.instalasi_id = $instalasi
			      and a.bagian = $bagian
			order by id");
		});
		
		return $result;
	}
}