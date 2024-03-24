<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class KpiSetting extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'KPI_CLOSING';
    protected $primaryKey = 'id';
    public $timestamps = true;
}
