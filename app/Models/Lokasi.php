<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'LOKASI';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function instalasi() {
        return $this->belongsTo('Asset\Models\Instalasi', 'instalasi_id', 'id');
    }

    public function ruangan() {
        return $this->hasMany('Asset\Models\Ruangan', 'lokasi_id', 'id');
    }

    public function aset() {
        return $this->hasMany('Asset\Models\Aset', 'lokasi_id', 'id');
    }
}
