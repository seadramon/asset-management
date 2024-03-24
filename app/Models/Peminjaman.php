<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'PEMINJAMAN_ASET';
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function aset() {
        return $this->belongsTo('Asset\Models\Aset', 'aset_id', 'id');
    }
}
