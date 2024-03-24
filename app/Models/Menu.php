<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'ru_menu';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    public function scopeHeadMenu($query){
        $query->where('tipe',1)->orderBy('urut','ASC');
    }
	
	public function scopeSingleMenu($query){
        $query->where('tipe',0)->orderBy('urut','ASC');
    }

    public function scopeSubHead($query){
        $query->where('tipe',2)->orderBy('urut','ASC');
    }

    public function scopeSubHeadMenu($query){
        $query->where('tipe',3)->orderBy('urut','ASC');
    }

    public function scopeSubMenu($query){
        $query->where('tipe',4)->orderBy('urut','ASC');
    }

    public function scopeSelectSubMenu($query,$menu_urut){
        $query->where('urut','LIKE',$menu_urut.'%')->orderBy('urut','ASC');
    }
    
    public function roles(){
        return $this->belongsToMany('Asset\Models\Role','ru_menu_role');
    }
}
