<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class UsulanInvestasi extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'USULAN_INVESTASI';
    protected $primaryKey = 'id';
    // public $timestamps = true;

    public function aset() {
        return $this->belongsTo('Asset\Models\Aset', 'komponen_id', 'id');
    }
}
