<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'ru_role';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function users(){
        return $this->belongsToMany('Asset\User','ru_role_user');
    }

    public function roleuser() {
        return $this->hasMany('Asset\Models\RoleUser', 'role_id', 'id');
    }

    // public function users_tab(){
        // return $this->belongsToMany('App\UserTab','abm_role_user','role_id','user_id');
    // }

    public function menus(){
        return $this->belongsToMany('Asset\Models\Menu','ru_menu_role')->orderBy('urut','ASC');
    }
}
