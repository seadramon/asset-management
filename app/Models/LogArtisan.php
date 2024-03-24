<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class LogArtisan extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'LOG_ARTISANCALL';
    protected $primaryKey = 'ID';
    public $timestamps = true;
}
