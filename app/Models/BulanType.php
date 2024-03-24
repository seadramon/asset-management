<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class BulanType extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'BULAN_TYPE';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
