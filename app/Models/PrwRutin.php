<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class PrwRutin extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'PRW_RUTIN';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function detail()
    {
    	return $this->hasMany('Asset\Models\PrwRutinDetail', 'prw_rutin_id', 'id');
    }

    public function instalasi()
    {
    	return $this->belongsTo('Asset\Models\Instalasi', 'instalasi_id', 'id');
    }

    public function aset()
    {
    	return $this->belongsTo('Asset\Models\Aset', 'komponen_id', 'id');
    }
}
