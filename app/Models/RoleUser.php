<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'ru_role_user';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function users(){
        return $this->belongsTo('Asset\User','user_id', 'userid');
    }

    public function roles(){
        return $this->belongsTo('Asset\Models\Role','role_id');
    }
}
