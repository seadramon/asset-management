<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Pemindahan extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'PEMINDAHAN_ASET';
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function aset() {
        return $this->belongsTo('Asset\Models\Aset', 'aset_id', 'id');
    }

    public function instalasi_baru() {
        return $this->belongsTo('Asset\Models\Instalasi', 'instalasi_baru_id', 'id');
    }

    public function lokasi_baru() {
        return $this->belongsTo('Asset\Models\Lokasi', 'lokasi_baru_id', 'id');
    }

    public function ruangan_baru() {
        return $this->belongsTo('Asset\Models\Ruangan', 'ruang_baru_id', 'id');
    }

    public function instalasi_lama() {
        return $this->belongsTo('Asset\Models\Instalasi', 'instalasi_lama_id', 'id');
    }

    public function lokasi_lama() {
        return $this->belongsTo('Asset\Models\Lokasi', 'lokasi_lama_id', 'id');
    }

    public function ruangan_lama() {
        return $this->belongsTo('Asset\Models\Ruangan', 'ruang_lama_id', 'id');
    }
}
