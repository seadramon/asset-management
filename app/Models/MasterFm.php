<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class MasterFm extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'MASTER_FM';
    protected $primaryKey = 'RECID';
    public $timestamps = false;
}
