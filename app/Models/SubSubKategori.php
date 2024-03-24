<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class SubSubKategori extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'SUB_SUB_KATEGORI';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function subkategori() {
        return $this->belongsTo('Asset\Models\SubKategori', 'sub_kategori_id', 'id');
    }

    public function aset() {
        return $this->hasMany('Asset\Models\Aset', 'sub_sub_kategori_id', 'id');
    }
}
