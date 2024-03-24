<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class BarangAlias extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'koneksigudang';
    protected $table = 'M_BARANG_ALIAS';
    protected $primaryKey = 'recid';
    public $timestamps = false;
}
