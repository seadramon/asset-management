<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Kelompok extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'MS_KELOMPOK';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function kelompokdetail() {
        return $this->hasMany('Asset\Models\KelompokDetail', 'ms_kelompok_id', 'id');
    }

    public function template(){
    	return $this->belongsTo('Asset\Models\Template', 'ms_template_id', 'id');
    }
}
