<?php

namespace Asset;

use Illuminate\Database\Eloquent\Model;
use DB;

class Jabatan extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi_dbout';
    protected $table = 'TU_JABATAN';
    protected $primaryKey = 'recidjabatan';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function role() {
        return $this->belongsTo('Asset\Role', 'recidjabatan', 'roleuserid');
    }
    
    public function jabatan() {
        return $this->hasOne('Asset\Jabatan', 'recidjabatan', 'parentjab');
    }

}
