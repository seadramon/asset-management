<?php

// use Asset\Libraries\ParamChecker;

if (!function_exists('hitung')) {

	function hitung($var1, $op, $var2)
	{
		$res = 0;

		if (is_numeric($var1) && is_numeric($var2)) {
			if ($op == '/' && $var2 == 0) {
				$res = 0;
			} else {
				switch ($op) {
					case '+':
						$res = $var1 + $var2;
						break;
					case '-':
						$res = $var1 - $var2;
						break;
					case '*':
						$res = $var1 * $var2;
						break;
					case '/':
						$res = $var1 / $var2;
						break;
				}
			}
		}

		return $res;
	}
}

if (!function_exists('perbandingan')) {
	function perbandingan($var1, $op, $var2) 
	{
		$res = false;
		switch ($op) {
			case '>=':
				$res = $var1 >= $var2;
				break;
			case '<=':
				$res = $var1 <= $var2;
				break;
			case '==':
				$res = $var1 == $var2;
				break;
			case '>':
				$res = $var1 > $var2;
				break;
			case '<':
				$res = $var1 < $var2;
				break;
		}

		return $res;
	}
}

if (!function_exists('getMinggu')) {
	function getMinggu()
	{
		$week = [0 => "- Pilih Minggu-"];
    	for ($i=1; $i <= 52; $i++) { 
    		$week[$i] = "Minggu ke ".$i;
    	}

    	return $week;
	}
}

if (!function_exists('hitungdev')) {

	function hitungdev($var1, $op, $var2)
	{
		$res = 0;
		if ($op == '/' && $var2 == 0) {
			$res = 0;
		} else {
			switch ($op) {
				case '+':
					$res = $var1 + $var2;
					break;
				case '-':
					$res = $var1 - $var2;
					break;
				case '*':
					$res = $var1 * $var2;
					break;
				case '/':
					$res = $var1 / $var2;
					break;
			}
		}

		return $res;
	}
}

if (!function_exists('pembagian')) {
	function pembagian($var1, $var2)
	{
		if ($var1 == 0) {
			return 0;
		} else {
			return $var1/$var2;
		}
	}
}
