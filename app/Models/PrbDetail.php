<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class PrbDetail extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'PRB_DETAIL';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function perbaikan(){
    	return $this->belongsTo('Asset\Models\Perbaikan', 'prb_data_id', 'id');
    }
}
