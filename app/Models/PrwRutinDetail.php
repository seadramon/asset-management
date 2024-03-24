<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class PrwRutinDetail extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'PRW_RUTIN_DETAIL';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function part()
    {
    	return $this->belongsTo('Asset\Models\KomponenDetail', 'kode_part', 'id');
    }
}
