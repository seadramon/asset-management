<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class SubKategori extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'SUB_KATEGORI';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function kategori() {
        return $this->belongsTo('Asset\Models\Kategori', 'kategori_id', 'id');
    }

    public function subsubkategori() {
        return $this->hasMany('Asset\Models\SubSubKategori', 'sub_kategori_id', 'id');
    }

    public function aset() {
        return $this->hasMany('Asset\Models\Aset', 'sub_kategori_id', 'id');
    }

//    
//    public function jabatan() {
//        return $this->hasOne('App\Jabatan', 'recidjabatan', 'roleuserid');
//    }
//    public function subsubkategori() {
//        return $this->hasMany('App\SubKategori', 'kategori_id', 'id');
//    }
}
