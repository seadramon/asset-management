<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class BiayaOperasional extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'BIAYA_OPERASIONAL';
    protected $primaryKey = 'id';
    public $timestamps = true;

    public function aset()
    {
    	return $this->belongsTo('Asset\Models\Aset', 'aset_id', 'id');
    }
}
