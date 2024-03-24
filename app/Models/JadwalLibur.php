<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Asset\Models\JadwalLiburPompa;

class JadwalLibur extends Model
{
	use SoftDeletes;

    protected $connection = 'oracleaplikasi';
    protected $table = 'JADWAL_LIBUR';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $dates = ['deleted_at'];

    public static function boot()
	{
	    parent::boot();
	    /*static::deleted(function($model1)
	    {
	        JadwalLiburPompa::where('equipment_id', $model1->equipment_id)->delete();
	    });*/

	    static::deleting(function($model1)
	    {
	    	// dd(JadwalLiburPompa::withTrashed()->where('equipment_id', $model1->equipment_id)->delete());
	        $tmp = JadwalLiburPompa::where('equipment_id', $model1->equipment_id)->first();

	        $del = JadwalLiburPompa::find($tmp->id);
	        $del->delete();
	    });

	    static::created(function($model1)
	    {
	    	$data = JadwalLiburPompa::where('equipment_id', $model1->equipment_id);
	    	$arrWeek = explode(",", $model1->minggu);
	    	$libur = getLibur($arrWeek);

	    	if ($data->count() > 0) {
	    		JadwalLiburPompa::where('equipment_id', $model1->equipment_id)
	    			->update([
	    				'minggu' => $libur
	    			]);
	    	} else {
	    		$data = JadwalLiburPompa::insert([
                    'equipment_id' => $model1->equipment_id,
                    'minggu' => $libur
                ]);
	    	}
	    });

	    static::updated(function($model1)
	    {
	    	$data = JadwalLiburPompa::where('equipment_id', $model1->equipment_id);
	    	$arrWeek = explode(",", $model1->minggu);
	    	$libur = getLibur($arrWeek);

	    	if ($data->count() > 0) {
	    		JadwalLiburPompa::where('equipment_id', $model1->equipment_id)
	    			->update([
	    				'minggu' => $libur
	    			]);
	    	} else {
	    		$data = JadwalLiburPompa::insert([
                    'equipment_id' => $model1->equipment_id,
                    'minggu' => $libur
                ]);
	    	}
	    });
	}

    public function aset() {
    	return $this->belongsTo('Asset\Models\Aset', 'equipment_id', 'id');
    }

    public function scopeMinggu($query, $equipment)
    {	
    	return $query->where('equipment_id', $equipment)->first();
    }
}
