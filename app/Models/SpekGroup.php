<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class SpekGroup extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'kelompok_spek';
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
//    public function instalasi() {
//        return $this->belongsTo('App\Instalasi', 'instalasi_id', 'id');
//    }
//    
//    public function ruangan() {
//        return $this->hasMany('App\Ruangan', 'lokasi_id', 'id');
//    }
//    
//    public function jabatan() {
//        return $this->hasOne('App\Jabatan', 'recidjabatan', 'roleuserid');
//    }
//    public function subsubkategori() {
//        return $this->hasMany('App\SubKategori', 'kategori_id', 'id');
//    }
    public function item() {
        return $this->belongsToMany('Asset\Models\SpekItem', 'spek_item_assn', 'kelompok_spek_id', 'spek_item_id');
    }
}
