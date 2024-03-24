<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class MasterKodeFm extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'MASTER_KODEFM';
    protected $primaryKey = 'KODE';
    public $timestamps = false;
}
