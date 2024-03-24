<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class PermohonanSc extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'PERMOHONAN_SC';
    protected $primaryKey = 'id';
    public $timestamps = true;

    public static function boot() {
        parent::boot();

        static::deleting(function($parent) {
            //remove related rows SC Detail
            $parent->detail()->delete();//
            return true;
        });
    }

    public function detail() 
    {
    	return $this->hasMany('Asset\Models\PermohonanScDetail', 'permohonan_sc_id', 'id');
    }

    public function bagian()
    {
        return $this->belongsTo('Asset\Models\Master', 'bagian_id', 'id');
    }

    public function namaSpv() 
    {
        return $this->belongsTo('Asset\RoleUser', 'nip', 'nip');
    }
}
