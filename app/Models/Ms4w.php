<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ms4w extends Model
{
    use SoftDeletes;
    
    protected $connection = 'oracleaplikasi';
    protected $table = 'MS_4W';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function ms52w(){
    	return $this->belongsTo('Asset\Models\Ms52w', 'ms_52w_id', 'id');
    }

    public function petugas(){
        return $this->belongsTo('Asset\User', 'petugas', 'userid');
    }

    // public function form(){
    // 	return $this->hasMany('Asset\Models\MasterFm', 'kode_fm', 'id');
    // }

    public function scopeKoneksi52w($query, $equipment, $tahun)
    {
        return $query->select('ms_4w.*', 'ms_52w.komponen_id')
            ->join('ms_52w', 'ms_52w.id', '=', 'ms_4w.ms_52w_id')
            ->where('ms_52w.equipment_id', $equipment)
            ->where('ms_52w.tahun', $tahun);
    }

    public function scopeKoneksi52wKomponen($query, $komponen, $tahun)
    {
        return $query->select('ms_4w.*', 'ms_52w.komponen_id')
            ->join('ms_52w', 'ms_52w.id', '=', 'ms_4w.ms_52w_id')
            ->where('ms_52w.komponen_id', $komponen)
            ->where('ms_52w.tahun', $tahun);   
    }
}
