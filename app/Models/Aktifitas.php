<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Aktifitas extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'BIAYA_AKTIFITAS';
    protected $primaryKey = 'id';
    public $timestamps = true;

    public function header()
    {
    	return $this->belongsTo('Asset\Models\BiayaPemeliharaan', 'biaya_pemeliharaan_id', 'id');
    }
}
