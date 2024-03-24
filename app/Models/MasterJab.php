<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class MasterJab extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'MASTER_JAB';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    public function bagian() {
    	return $this->belongsTo('Asset\Models\Master', 'bagian', 'id');
    }

    public function user() {
        return $this->belongsTo('Asset\User', 'nip', 'userid');
    }

    public function tu_roleuser() 
    {
        return $this->hasOne('Asset\RoleUser', 'nip', 'nip');
    }
}
