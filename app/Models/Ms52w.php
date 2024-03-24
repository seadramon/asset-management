<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Ms52w extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'MS_52W';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function instalasi(){
    	return $this->belongsTo('Asset\Models\Instalasi', 'instalasi_id', 'id');
    }

    public function aset(){
    	return $this->belongsTo('Asset\Models\Aset', 'aset_id', 'id');
    }

    public function komponen(){
        return $this->belongsTo('Asset\Models\Aset', 'komponen_id', 'id');
    }

    public function template(){
    	return $this->belongsTo('Asset\Models\Template', 'template_id', 'id');
    }    
}
