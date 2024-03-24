<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class komponen extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'MS_KOMPONEN';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function komponendetail() {
        return $this->hasMany('Asset\Models\KomponenDetail', 'ms_komponen_id', 'id');
    }

    public function template(){
    	return $this->belongsTo('Asset\Models\Template', 'ms_template_id', 'id');
    }
}
