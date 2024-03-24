<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalLiburPompa extends Model
{
	use SoftDeletes;
	
    protected $connection = 'oracleaplikasi';
    protected $table = 'JADWAL_LIBUR_POMPA';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $dates = ['deleted_at'];

    public function aset() {
    	return $this->belongsTo('Asset\Models\Aset', 'equipment_id', 'id');
    }

    public function scopeMinggu($query, $equipment)
    {
        return $query->where('equipment_id', $equipment)->first();
    }
}
