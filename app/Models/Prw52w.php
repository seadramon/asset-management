<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Prw52w extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'PRW_52W';
    protected $primaryKey = 'id';
    public $timestamps = true;

    public static function boot() {
        parent::boot();

        static::deleting(function($parent) {
            // dd('hello world');
            //remove related rows prw 4w
            // $parent->prw4w()->withTrashed()->delete();//
            return true;
        });
    }

    public function instalasi(){
    	return $this->belongsTo('Asset\Models\Instalasi', 'instalasi_id', 'id');
    }

    public function aset(){
    	return $this->belongsTo('Asset\Models\Aset', 'aset_id', 'id');
    }

    public function komponen(){
        return $this->belongsTo('Asset\Models\Aset', 'komponen_id', 'id');
    }

    public function template(){
    	return $this->belongsTo('Asset\Models\Template', 'template_id', 'id');
    }

    public function komponendetail() {
        return $this->belongsTo('Asset\Models\KomponenDetail', 'part', 'id');
    } 

    public function prw4w() {
        return $this->hasMany('Asset\Models\Prw4w', 'prw_52w_id', 'id');
    }  
}
