<?php

namespace Asset;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {
    
    use Authenticatable,
        CanResetPassword;

    protected $connection = 'oraclesecman';
    protected $table = 'usrtab';
    protected $primaryKey = 'userid';
    public $timestamps = false;

    protected $fillable = ['userid', 'username', 'passw'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /* public function getAuthPassword() {
      return $this->PASSWORD; // default return $this->password;
      } */

    public function role() {
        return $this->hasOne('Asset\Role', 'nip', 'userid');
    }

    public function masterjab() {
        return $this->hasMany('Asset\Models\MasterJab', 'nip', 'userid');
    }

    public function rolebaru(){
        return $this->belongsToMany('Asset\Models\Role','ru_role_user');
    }
}
