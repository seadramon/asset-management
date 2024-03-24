<?php

namespace Asset;

use Illuminate\Database\Eloquent\Model;
use DB;

class RoleUser extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi_dbout';
    protected $table = 'TU_ROLEUSER';
    protected $primaryKey = 'recidroleuser';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function user() {
        return $this->belongsTo('Asset\User', 'nip', 'userid');
    }

    public function jabatan() {
        return $this->belongsTo('Asset\Jabatan', 'roleuserid', 'recidjabatan');
    }
}
