<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrwrutinPdm extends Model
{
    use SoftDeletes;
    
    protected $connection = 'oracleaplikasi';
    protected $table = 'PRW_RUTIN_PDM';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function komponen()
    {
    	return $this->belongsTo('Asset\Models\Aset', 'komponen_id', 'id');
    }

    public function part()
    {
    	return $this->belongsTo('Asset\Models\KomponenDetail', 'kode_part', 'id');
    }

    public function prw52w()
    {
        return $this->hasMany('Asset\Models\Prw52w', 'prw_rutin_id', 'id');
    }
}
