<?php

// use Asset\Libraries\ParamChecker;

if (!function_exists('hitung')) {

	function hitung($var1, $op, $var2)
	{
		$res = 0;
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
			case '=':
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