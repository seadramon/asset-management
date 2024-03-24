<?php
namespace Asset\Libraries;

use DB;

class ParamChecker
{
	function __construct()
	{
		
	}

	static $kodefm;
	static $data;
	static $waspada;
	static $bahaya;
	static $revdata;
	static $nilaiOri; //Nilai asli yg sudah diproses
	static $batasBahaya;
	static $batasWaspada;

	public static function cekHitungan($data)
	{
		$ret = [];
		self::$kodefm = $data['kode_fm'];
		self::$data = $data;

		$fmException = ['E2'];
		$fieldException = ['suhu_h', 'heater'];
		$skipProcess = ['null', null, '', '-'];
		
		$master = DB::table('master_fm')
				->where('kode_fm', self::$kodefm)
				->where('aktif', 'Y')
				->orderBy('recid', 'asc')
				->get();
		$arrMaster = self::getMaster($master); 
// dd($arrMaster);
		foreach ($arrMaster as $key => $value) {
			$params = explode(";", $value->param); 

			// set default to prevent error
			if ($value->tipe == 0) {
				$data[$key] = 0;
			} 

			// auto set default to skipped value
			if (in_array($data[$key], $skipProcess) && $value->tipe != 0) {
				self::$nilaiOri[$key] = 0;
			} else {
				// auto set default to exceptional value
				if (in_array($key, $fieldException) && in_array($value->kode_fm, $fmException)) {
					self::$nilaiOri[$key] = isset($data[$key])?$data[$key]:0;
				} else {
					switch ($params[0]) {
						case 'calc':
							$subresult = self::getCalc($key, isset($data[$key])?$data[$key]:0 ,$value);
							break;
						case 'calc2':
							$subresult = self::getCalc2($key, isset($data[$key])?$data[$key]:0 ,$value);
							break;
						case 'avg':
							$subresult = self::getAvg($key, isset($data[$key])?$data[$key]:0 ,$value);
							break;
						case 'comp':
							$subresult = self::getComp($key, isset($data[$key])?$data[$key]:0 ,$value);
							break;
						case 'range':
							$subresult = self::getRange($key, isset($data[$key])?$data[$key]:0, $value);
							break;
						case 'range2':
							$subresult = self::getRangeDua($key, isset($data[$key])?$data[$key]:0, $value);
							break;
						case 'range3':
							$subresult = self::getRangeTiga($key, isset($data[$key])?$data[$key]:0, $value);
							break;
						case 'val':
							$subresult = self::getVal($key, isset($data[$key])?$data[$key]:0, $value);
							break;
						case 'max':
							$subresult = self::getMax($key, isset($data[$key])?$data[$key]:0, $value);
							break;
					}
				}
			}
		}

		self::$data['waspada'] = self::$waspada;
		self::$data['bahaya'] = self::$bahaya;
		self::$data['nilaiOri'] = self::$nilaiOri;
		self::$data['batasBahaya'] = self::$batasBahaya;
		self::$data['batasWaspada'] = self::$batasWaspada;

		return self::$data;
	}

	private static function getMaster($data)
	{
		$result = [];

		foreach ($data as $row) {
			$result[$row->nama_field] = $row;
		}

		return $result;
	}

	private static function getCalc($key, $val = 0, $rumus)
	{
		$params = explode(";", $rumus->param);
		switch ($params[1]) {
			case 'plus':
				$res = self::calcPlus($key, $val, $rumus);
				break;
			case 'plusRange':
				$res = self::calcPlusRange($key, $val, $rumus);
				break;
			case 'mult':
				$res = self::calcMult($key, $val, $rumus);
				break;
		}
	}

	private static function calcPlus($key, $val =0 , $rumus)
	{
		$waspada = false;
		$bahaya = false;

		// save nilai Asli
		self::$nilaiOri[$key] = $val;

		// bahaya
		if ($rumus->bahaya) {
			$tempBahaya1 = explode(";", $rumus->bahaya);
			$tempBahaya2 = explode("@", $tempBahaya1[1]);

			$temp = 0;
			foreach ($tempBahaya2 as $row) {
				$tempBahaya3 = explode("#", $row);

				if (is_numeric($tempBahaya3[1])) {
					$temp = hitung($temp, $tempBahaya3[0], $tempBahaya3[1]);
				} else {
					$temp = hitung($temp, $tempBahaya3[0], self::$data[$tempBahaya3[1]]);
				}
			}

			$bahaya = perbandingan($val, $tempBahaya1[0], $temp);

			if ($bahaya) {
				self::$bahaya .= $key.'#';
				
				// save batas bahaya
				self::$batasBahaya[$key] = $tempBahaya1[0].$temp;
			}
		}

		// waspada
		if ($rumus->waspada && !$bahaya) {
			$tempWaspada1 = explode(";", $rumus->waspada);
			$tempWaspada2 = explode("@", $tempWaspada1[1]);

			$temp = 0;
			foreach ($tempWaspada2 as $row) {
				$tempWaspada3 = explode("#", $row);

				if (is_numeric($tempWaspada3[1])) {
					$temp = hitung($temp, $tempWaspada3[0], $tempWaspada3[1]);
				} else {
					$temp = hitung($temp, $tempWaspada3[0], self::$data[$tempWaspada3[1]]);
				}
			}
			// dd($val);

			if (perbandingan($val, $tempWaspada1[0], $temp)) {
				self::$waspada .= $key.'#';

				// save batas waspada
				self::$batasWaspada[$key] = $tempWaspada1[0].$temp;
			}
		}
	}

	private static function calcPlusRange($key, $val =0 , $rumus)
	{
		$waspada = false;
		$bahaya = false;

		// save nilai Asli
		self::$nilaiOri[$key] = $val;

		// bahaya
		if ($rumus->bahaya) {
			$tempBahaya1 = explode(";", $rumus->bahaya);
			$tempBahaya2 = explode("@", $tempBahaya1[1]);

			$temp = 0;
			foreach ($tempBahaya2 as $row) {
				$tempBahaya3 = explode("#", $row);

				if (is_numeric($tempBahaya3[1])) {
					$temp = hitung($temp, $tempBahaya3[0], $tempBahaya3[1]);
				} else {
					$temp = hitung($temp, $tempBahaya3[0], self::$data[$tempBahaya3[1]]);
				}
			}

			$bahaya = perbandingan($val, $tempBahaya1[0], $temp);

			if ($bahaya) {
				self::$bahaya .= $key.'#';
				
				// save batas bahaya
				self::$batasBahaya[$key] = $tempBahaya1[0].$temp;
			}
		}

		// waspada
		if ($rumus->waspada && !$bahaya) {
			$tempWaspada1 = explode(";", $rumus->waspada);
			$tempWaspada2 = explode("@", $tempWaspada1[1]);

			$temp = 0;
			foreach ($tempWaspada2 as $row) {
				$tempWaspada3 = explode("#", $row);

				if (is_numeric($tempWaspada3[1])) {
					$temp = hitung($temp, $tempWaspada3[0], $tempWaspada3[1]);
				} else {
					$temp = hitung($temp, $tempWaspada3[0], self::$data[$tempWaspada3[1]]);
				}
			}
			// dd($val);

			if (perbandingan($val, $tempWaspada1[0], $temp)) {
				self::$waspada .= $key.'#';

				// save batas waspada
				self::$batasWaspada[$key] = $tempWaspada1[0].$temp;
			}
		}
	}

	private static function calcMult($key, $val = 0, $rumus)
	{
		$waspada = false;
		$bahaya = false;

		// save nilai asli
		self::$nilaiOri[$key] = $val;

		// bahaya
		if ($rumus->bahaya) {
			$tempBahaya1 = explode(";", $rumus->bahaya);
			$tempBahaya2 = explode("@", $tempBahaya1[1]);

			$temp = 0;
			foreach ($tempBahaya2 as $row) {
				$tempBahaya3 = explode("#", $row);

				if (is_numeric($tempBahaya3[1])) {
					$temp = hitung($temp, $tempBahaya3[0], $tempBahaya3[1]);
				} else {
					$temp = hitung($temp, $tempBahaya3[0], self::$data[$tempBahaya3[1]]);
				}
			}

			$bahaya = perbandingan($val, $tempBahaya1[0], $temp);

			if ($bahaya) {
				self::$bahaya .= $key.'#';

				// save batas bahaya
				self::$batasBahaya[$key] = $tempBahaya1[0].$temp;
			}
		}

		// waspada
		if ($rumus->waspada && !$bahaya) {
			$tempWaspada1 = explode(";", $rumus->waspada);
			$tempWaspada2 = explode("@", $tempWaspada1[1]);

			$temp = 0;
			foreach ($tempWaspada2 as $row) {
				$tempWaspada3 = explode("#", $row);

				if (is_numeric($tempWaspada3[1])) {
					$temp = hitung($temp, $tempWaspada3[0], $tempWaspada3[1]);
				} else {
					$temp = hitung($temp, $tempWaspada3[0], self::$data[$tempWaspada3[1]]);
				}
			}

			if (perbandingan($val, $tempWaspada1[0], $temp)) {
				self::$waspada .= $key.'#';

				// save batas waspada
				self::$batasWaspada[$key] = $tempWaspada1[0].$temp;
			}
		}
	}

	private static function getCalc2($key, $val = 0, $rumus)
	{
		$waspada = false;
		$bahaya = false;

		$arrParam = explode(";", $rumus->param);
		$val = self::$data[$arrParam[1]];
		
		// save nilai asli
		self::$nilaiOri[$key] = $val;

		// bahaya
		if ($rumus->bahaya) {
			$tempBahaya1 = explode(";", $rumus->bahaya);
			$tempBahaya2 = explode("@", $tempBahaya1[1]);

			$temp = 0;
			foreach ($tempBahaya2 as $row) {
				$tempBahaya3 = explode("#", $row);

				if (is_numeric($tempBahaya3[1])) {
					$temp = hitung($temp, $tempBahaya3[0], $tempBahaya3[1]);
				} else {
					$temp = hitung($temp, $tempBahaya3[0], self::$data[$tempBahaya3[1]]);
				}
			}

			$bahaya = perbandingan($val, $tempBahaya1[0], $temp);

			if ($bahaya) {
				self::$bahaya .= $key.'#';

				// save batas bahaya
				self::$batasBahaya[$key] = $tempBahaya1[0].$temp;
			}
		}

		// waspada
		if ($rumus->waspada && !$bahaya) {
			$tempWaspada1 = explode(";", $rumus->waspada);
			$tempWaspada2 = explode("@", $tempWaspada1[1]);

			$temp = 0;
			foreach ($tempWaspada2 as $row) {
				$tempWaspada3 = explode("#", $row);

				if (is_numeric($tempWaspada3[1])) {
					$temp = hitung($temp, $tempWaspada3[0], $tempWaspada3[1]);
				} else {
					$temp = hitung($temp, $tempWaspada3[0], self::$data[$tempWaspada3[1]]);
				}
			}

			if (perbandingan($val, $tempWaspada1[0], $temp)) {
				self::$waspada .= $key.'#';

				// save batas waspada
			self::$batasWaspada[$key] = $tempWaspada1[0].$temp;
			}
		}
	}

	private static function getAvg($key, $val = 0, $rumus)
	{		
		$ret = 0;
		if ($rumus->param) {
			$jumlah = 0;

			$arr1 = explode(";", $rumus->param);
			$arr2 = explode("@", $arr1[1]);
			$n = count($arr2);

			foreach ($arr2 as $row) {
				if (is_numeric(self::$data[$row])) {
					$jumlah += self::$data[$row];
				} else {
					$jumlah += 0;
				}
			}

			$ret = round($jumlah/$n, 2);

			// save nilai Asli
			self::$nilaiOri[$key] = $ret;

			self::$data[$key] = $ret;
		}
	}

	private static function getComp($key, $val = 0, $rumus)
	{
		// dd($key);
		// if ($key == 'arus_cu_r') {
		// 	dd(self::$data);
		// }
		if ($rumus->param) {
			$arr1 = explode(";", $rumus->param);
			$arr2 = explode("@", $arr1[1]);
// dd(self::$data);
			$temp = 0;
			foreach ($arr2 as $row) {
				$arr3 = explode("#", $row);

				if (is_numeric($arr3[1])) {
					$temp = hitung($temp, $arr3[0], $arr3[1]);
				} else {
					$temp = hitung($temp, $arr3[0], strlen(self::$data[$arr3[1]]) > 0 ? self::$data[$arr3[1]] : 0);
				}
			}

			$temp = round($temp);

			self::$data[$key] = $temp;
			// save nilai Asli
			self::$nilaiOri[$key] = $temp;

			$bahaya = false;
			if ($rumus->bahaya) {
				$arrB = explode("#", $rumus->bahaya);
				$bahaya = perbandingan($temp, $arrB[0], $arrB[1]);

				if ($bahaya) {
					self::$bahaya .= $key.'#';

					// save batas bahaya
					self::$batasBahaya[$key] = $arrB[0].$arrB[1];
				}
			}

			if ($rumus->waspada && !$bahaya) {
				$arrW = explode("#", $rumus->waspada);
				$waspada = perbandingan($temp, $arrW[0], $arrW[1]);

				if ($waspada) {
					self::$waspada .= $key.'#';

					// save batas waspada
					self::$batasWaspada[$key] = $arrW[0].$arrW[1];
				}
			}
		}
	}

	private static function getRange($key, $val = 0, $rumus)
	{
		$bahaya = false;
		$waspada = false;

		// save nilai Asli
		self::$nilaiOri[$key] = self::$data[$key];

		if ($rumus->bahaya) {
			$arr1 = explode("#", $rumus->bahaya);
			$bahaya = perbandingan(self::$data[$key], $arr1[0], $arr1[1]);
// if ($key == 'getaran_h_nde') dd($bahaya);
			if ($bahaya) {
				self::$bahaya .= $key.'#';

				// save batas bahaya
				self::$batasBahaya[$key] = $arr1[0].$arr1[1];
			}
		}

		if ($rumus->waspada && !$bahaya) {
			$arr1 = explode("#", $rumus->waspada);
			$waspada = perbandingan(self::$data[$key], $arr1[0], $arr1[1]);

			if ($waspada) {
				self::$waspada .= $key.'#';

				// save batas waspada
				self::$batasWaspada[$key] = $arr1[0].$arr1[1];
			}
		}
	}

	private static function getRangeDua($key, $val = 0, $rumus)
	{
		$bahaya = false;
		$waspada = false;

		// save nilai Asli
		self::$nilaiOri[$key] = self::$data[$key];

		if ($rumus->bahaya) {
			$arr = explode(";", $rumus->bahaya);

			if (count($arr) > 1) {
				$arr1 = explode("#", $arr[0]);
				$arr2 = explode("#", $arr[1]);

				$bahaya1 = perbandingan(self::$data[$key], $arr1[0], $arr1[1]); 
				$bahaya2 = perbandingan(self::$data[$key], $arr2[0], $arr2[1]); 

				if ($bahaya1 || $bahaya2) {
					self::$bahaya .= $key.'#';

					// save batas bahaya
					if ($bahaya1) {
						self::$batasBahaya[$key] = $arr1[0].$arr1[1];
					} else {
						self::$batasBahaya[$key] = $arr2[0].$arr2[1];
					}
				}
			} else {
				$arr1 = explode("#", $rumus->bahaya);
				$bahaya = perbandingan(self::$data[$key], $arr1[0], $arr1[1]);
	
				if ($bahaya) {
					self::$bahaya .= $key.'#';

					// save batas bahaya
					self::$batasBahaya[$key] = $arr1[0].$arr1[1];
				}
			}
		}

		if ($rumus->waspada && !$bahaya) {
			$arr = explode(";", $rumus->waspada);

			$arr1 = explode("#", $arr[0]);
			$arr2 = explode("#", $arr[1]);

			$waspada1 = perbandingan(self::$data[$key], $arr1[0], $arr1[1]); 
			$waspada2 = perbandingan(self::$data[$key], $arr2[0], $arr2[1]); 

			if ($waspada1 || $waspada2) {
				self::$waspada .= $key.'#';

				// save batas waspada
				if ($waspada1) {
					self::$batasWaspada[$key] = $arr1[0].$arr1[1];
				} else {
					self::$batasWaspada[$key] = $arr2[0].$arr2[1];
				}
			}
		}
	}

	private static function getRangeTiga($key, $val = 0, $rumus)
	{
		$bahaya = false;
		$waspada = false;

		// save nilai Asli
		// dd(self::$data[$key]);
		self::$nilaiOri[$key] = self::$data[$key];

		if ($rumus->bahaya) {
			$arr = explode(";", $rumus->bahaya);

			if (count($arr) > 1) {
				$arr1 = explode("#", $arr[0]);
				$arr2 = explode("#", $arr[1]);

				$bahaya1 = perbandingan(self::$data[$key], $arr1[0], $arr1[1]); 
				$bahaya2 = perbandingan(self::$data[$key], $arr2[0], $arr2[1]); 

				if ($bahaya1 && $bahaya2) {
					self::$bahaya .= $key.'#';

					// save batas bahaya
					if ($bahaya1) {
						self::$batasBahaya[$key] = $arr1[0].$arr1[1];
					} else {
						self::$batasBahaya[$key] = $arr2[0].$arr2[1];
					}
				}
			} else {
				$arr1 = explode("#", $rumus->bahaya);
				$bahaya = perbandingan(self::$data[$key], $arr1[0], $arr1[1]);
	
				if ($bahaya) {
					self::$bahaya .= $key.'#';

					// save batas bahaya
					self::$batasBahaya[$key] = $arr1[0].$arr1[1];
				}
			}
		}

		if ($rumus->waspada && !$bahaya) {
			$arr = explode(";", $rumus->waspada);

			$arr1 = explode("#", $arr[0]);
			$arr2 = explode("#", $arr[1]);

			$waspada1 = perbandingan(self::$data[$key], $arr1[0], $arr1[1]); 
			$waspada2 = perbandingan(self::$data[$key], $arr2[0], $arr2[1]); 

			if ($waspada1 && $waspada2) {
				self::$waspada .= $key.'#';

				// save batas waspada
				if ($waspada1) {
					self::$batasWaspada[$key] = $arr1[0].$arr1[1];
				} else {
					self::$batasWaspada[$key] = $arr2[0].$arr2[1];
				}
			}
		}
	}

	private static function getVal($key, $val = 0, $rumus)
	{		
		$bahaya = false;
		$waspada = false;

		// save nilai Asli
		self::$nilaiOri[$key] = $val;

		if ($rumus->bahaya) {
			if (strtoupper($val) == $rumus->bahaya) {				
				$bahaya = true;
				self::$bahaya .= $key.'#';

				// save batas bahaya
				self::$batasBahaya[$key] = $rumus->bahaya;
			}
		}

		if ($rumus->waspada && !$bahaya) {
			if (strtoupper($val) == $rumus->waspada) {
				$waspada = true;
				self::$waspada .= $key.'#';

				// save batas waspada
				self::$batasWaspada[$key] = $rumus->waspada;
			}
		}
	}

	private static function getMax($key, $val = 0, $rumus)
	{
		$ret = 0;
		if ($rumus->param) {
			$arrCompare = [];

			$arr1 = explode(";", $rumus->param);
			$arr2 = explode("@", $arr1[1]);
			$n = count($arr2);

			foreach ($arr2 as $row) {
				$arrCompare[$row] = self::$data[$row];
			}

			$ret = max($arrCompare);

			// save nilai Asli
			self::$nilaiOri[$key] = $ret;

			self::$data[$key] = $ret;
		}
	}
}