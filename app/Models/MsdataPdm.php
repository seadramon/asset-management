<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class MsdataPdm extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'MS_DATAPDM';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function komponen(){
    	return $this->belongsTo('Asset\Models\Aset', 'komponen_id', 'id');
    }

    public function equipment(){
    	return $this->belongsTo('Asset\Models\Aset', 'equipment_id', 'id');
    }
}
