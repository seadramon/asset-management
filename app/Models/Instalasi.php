<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Instalasi extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'INSTALASI';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
//    public function user() {
//        return $this->belongsTo('App\User', 'nip', 'userid');
//    }
//    
//    public function jabatan() {
//        return $this->hasOne('App\Jabatan', 'recidjabatan', 'roleuserid');
//    }
    public function lokasi() {
        return $this->hasMany('Asset\Models\Lokasi', 'instalasi_id', 'id');
    }

    public function aset() {
        return $this->hasMany('Asset\Models\Aset', 'instalasi_id', 'id');
    }
}
