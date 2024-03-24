<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Kondisi extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'KONDISI';
    protected $primaryKey = 'ID';
    public $timestamps = false;
}
