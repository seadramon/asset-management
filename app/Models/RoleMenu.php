<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class RoleMenu extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'ru_menu_role';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    public function scopeSingleMenuRole($query, $role, $menu){
        $query->where('role_id',$role)
                ->where('menu_id',$menu);
    }

    public function scopeRoleMenus($query, $role){
        $query->where('role_id',$role);
    }
}
