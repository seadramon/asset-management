<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Perawatan extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'PRW_DATA';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function komponen(){
    	return $this->belongsTo('Asset\Models\Aset', 'komponen_id', 'id');
    }

    public function ms4w(){
    	return $this->belongsTo('Asset\Models\Ms4w', 'ms_4w_id', 'id');
    }

    public function bagian() {
        return $this->belongsTo('Asset\Models\Master', 'bagian_id', 'id');
    }

    public function instalasi() {
        return $this->belongsTo('Asset\Models\Instalasi', 'instalasi_id', 'id');
    }

    public function sukucadang()
    {
        return $this->hasMany('Asset\Models\PermohonanSc', 'prw_data_id', 'id');
    }

    public function proposals()
    {
        return $this->belongsTo('Asset\Models\Proposal', 'proposal_id', 'id');
    }
}
