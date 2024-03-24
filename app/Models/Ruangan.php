<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'RUANGAN';
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function lokasi() {
        return $this->belongsTo('Asset\Models\Lokasi', 'lokasi_id', 'id');
    }
    
    public function aset() {
        return $this->hasMany('Asset\Models\Aset', 'ruang_id', 'id');
    }
}
