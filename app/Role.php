<?php

namespace Asset;

use Illuminate\Database\Eloquent\Model;
use DB;

class Role extends Model {

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
	
	public function roleuser() {
        return $this->hasMany('Asset\Models\RoleUser', 'role_id', 'id');
    }
	
    public function jabatan() {
        return $this->hasOne('Asset\Jabatan', 'recidjabatan', 'roleuserid');
    }

    public function scopeLevel($query, $nip)
    {
        $levelJab = "petugas";

        $temp = $query->select('is_manajer')
            ->where('nip', $nip)->first();
        
        if ($temp->is_manajer == '1') {
            dd('aa');
        }

        return $levelJab;
    }

    public function masterjab() {
        return $this->hasOne('Asset\Models\MasterJab', 'nip', 'nip');
    }
}
