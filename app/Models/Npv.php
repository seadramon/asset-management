<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Npv extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'INVESTASI_NPV';
}
