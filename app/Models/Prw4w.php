<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prw4w extends Model
{
    use SoftDeletes;

    protected $connection = 'oracleaplikasi';
    protected $table = 'PRW_4W';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function prw52w(){
    	return $this->belongsTo('Asset\Models\Prw52w', 'prw_52w_id', 'id');
    }

    public function petugas(){
        return $this->belongsTo('Asset\User', 'petugas', 'userid');
    }

    public function scopeKoneksi52w($query, $equipment, $tahun)
    {
        return $query->select('prw_4w.*', 'prw_52w.komponen_id')
            ->join('prw_52w', 'prw_52w.id', '=', 'prw_4w.prw_52w_id')
            ->where('prw_52w.equipment_id', $equipment)
            ->where('prw_52w.tahun', $tahun);
    }

    public function scopeKoneksi52wDev($query, $arrKomponen, $tahun)
    {
        return $query->select('prw_4w.*', 'prw_52w.komponen_id')
            ->join('prw_52w', 'prw_52w.id', '=', 'prw_4w.prw_52w_id')
            ->whereIn('prw_52w.komponen_id', $arrKomponen)
            ->where('prw_52w.tahun', $tahun);
    }

    public function scopeKoneksi52wKomponen($query, $komponen, $tahun)
    {
        return $query->select('prw_4w.*', 'prw_52w.komponen_id')
            ->join('prw_52w', 'prw_52w.id', '=', 'prw_4w.prw_52w_id')
            ->where('prw_52w.komponen_id', $komponen)
            ->where('prw_52w.tahun', $tahun);
    }
}
