<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class PrwDetail extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'PRW_DETAIL';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function perawatan(){
    	return $this->belongsTo('Asset\Models\Perawatan', 'prw_data_id', 'id');
    }
}
