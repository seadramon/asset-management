<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'KATEGORI';
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
    public function subkategori() {
        return $this->hasMany('Asset\Models\SubKategori', 'kategori_id', 'id');
    }

    public function aset() {
        return $this->hasMany('Asset\Models\Aset', 'kategori_id', 'id');
    }
}
