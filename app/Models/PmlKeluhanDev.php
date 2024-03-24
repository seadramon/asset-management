<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class PmlKeluhanDev extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi_dbout';
    // protected $connection = 'oracleaplikasi';
    protected $table = 'pml_keluhan';
    protected $primaryKey = 'recidkeluhan';
    public $timestamps = false;

    public function aset(){
        return $this->belongsTo('Asset\Models\Aset', 'kode_barcode', 'kode_barcode');
    }

    public function perbaikan(){
    	return $this->hasOne('Asset\Models\Perbaikan', 'aduan_id', 'recidkeluhan');
    }

    public function pelapor(){
        return $this->belongsTo('Asset\RoleUser', 'nip', 'nip');
    }

    public function spv(){
        return $this->belongsTo('Asset\RoleUser', 'disposisi_m_nip', 'nip');
    }
}
