<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Depresiasi extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'DEPRESIASI';
    protected $primaryKey = 'id';
    // public $timestamps = false;

    const CREATED_AT = 'TS_CREATE';
    const UPDATED_AT = 'TS_UPDATE';

    public function asetnya()
    {
    	return $this->belongsTo('Asset\Models\Aset', 'aset_id', 'id');
    }

    public function bulan()
    {
    	return $this->belongsTo('Asset\Models\BulanType', 'bulan_id', 'id');
    }
}
