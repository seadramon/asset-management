<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ms4wdev extends Model
{
    use SoftDeletes;
    
    protected $connection = 'oracleaplikasi';
    protected $table = 'MS_4W_DEV';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function ms52w(){
    	return $this->belongsTo('Asset\Models\Ms52w', 'ms_52w_id', 'id');
    }

    public function petugas(){
        return $this->belongsTo('Asset\User', 'petugas', 'userid');
    }

    public function scopeKoneksi52w($query, $equipment, $tahun)
    {
        return $query->select('ms_4w_dev.*', 'ms_52w.komponen_id')
            ->join('ms_52w', 'ms_52w.id', '=', 'ms_4w_dev.ms_52w_id')
            ->where('ms_52w.equipment_id', $equipment)
            ->where('ms_52w.tahun', $tahun);
    }

    public function scopeKoneksi52wKomponen($query, $komponen, $tahun)
    {
        return $query->select('ms_4w_dev.*', 'ms_52w.komponen_id')
            ->join('ms_52w', 'ms_52w.id', '=', 'ms_4w_dev.ms_52w_id')
            ->where('ms_52w.komponen_id', $komponen)
            ->where('ms_52w.tahun', $tahun);   
    }
}
