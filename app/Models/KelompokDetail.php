<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class KelompokDetail extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'MS_KELOMPOK_DETAIL';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function kelompok() {
        return $this->belongsTo('Asset\Models\Kelompok', 'ms_kelompok_id', 'id');
    }
}
