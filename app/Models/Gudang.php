<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Gudang extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'koneksigudang';
    protected $table = 'M_GUDANG';
    protected $primaryKey = 'recid';
    public $timestamps = false;
}
