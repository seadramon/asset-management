<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class KomponenDetail extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'MS_KOMPONEN_DETAIL';
    protected $primaryKey = 'id';
    public $timestamps = false;

    /*public function komponen() {
        return $this->belongsTo('Asset\Models\Komponen', 'ms_komponen_id', 'id');
    }*/
}
