<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class MsData extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'MS_DATA';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function kelompokdetail() {
        return $this->belongsTo('Asset\Models\KelompokDetail', 'ms_kelompok_detail_id', 'id');
    }

    public function komponendetail(){
    	return $this->belongsTo('Asset\Models\KomponenDetail', 'ms_komponen_detail_id', 'id');
    }
}
