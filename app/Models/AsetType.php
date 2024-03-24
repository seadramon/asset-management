<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class AsetType extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'ASET_TYPE';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
//    public function user() {
//        return $this->belongsTo('Asset\Models\User', 'nip', 'userid');
//    }
//    
//    public function jabatan() {
//        return $this->hasOne('Asset\Models\Jabatan', 'recidjabatan', 'roleuserid');
//    }
//    public function subkategori() {
//        return $this->hasMany('Asset\Models\SubKategori', 'kategori_id', 'id');
//    }

}
