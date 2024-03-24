<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class BiayaPemeliharaan extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'BIAYA_PEMELIHARAAN';
    protected $primaryKey = 'id';
    public $timestamps = true;

    public function aset()
    {
    	return $this->belongsTo('Asset\Models\Aset', 'aset_id', 'id');
    }

    public function prwRutin()
    {
    	return $this->belongsTo('Asset\Models\Prw4w', 'wo_id', 'id');
    }

    public function perawatan()
    {
    	return $this->belongsTo('Asset\Models\Perawatan', 'wo_id', 'id');
    }

    public function perbaikan()
    {
    	return $this->belongsTo('Asset\Models\Prw4w', 'wo_id', 'id');
    }

    public function aktifitas()
    {
        return $this->hasMany('Asset\Models\Aktifitas', 'biaya_pemeliharaan_id', 'id');
    }
}
