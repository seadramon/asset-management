<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class template extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'MS_TEMPLATES';
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function komponen() {
        return $this->hasMany('Asset\Models\Komponen', 'ms_template_id', 'id');
    }

    public function kelompok() {
        return $this->hasMany('Asset\Models\Kelompok', 'ms_template_id', 'id');
    }

    public function instalasi() {
        return $this->belongsTo('Asset\Models\Instalasi', 'instalasi_id', 'id');
    }

    public function bagian() {
        return $this->belongsTo('Asset\Models\Master', 'bagian_id', 'id');
    }
}
