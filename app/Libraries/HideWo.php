<?php
namespace Asset\Libraries;

use Asset\Models\Aset,
    Asset\Models\Perawatan,
    Asset\Models\Perbaikan,
    Asset\Models\Ms52w,
    Asset\Models\Ms4w,
    Asset\Models\Prw4w,
    Asset\Models\Prw52w;

use DB;

class HideWo
{
	
	function __construct()
	{
		# code...
	}

	public static function init()
	{
		self::closeMonitoring($asetId);
		self::closePrwRutin($asetId);
	}

	public static function closeMonitoring($asetId)
	{

	}

	public static function closePrwRutin($asetId)
	{

	}

	public static function closePerawatan($asetId)
	{

	}

	public static function closePerbaikan($asetId)
	{

	}


}