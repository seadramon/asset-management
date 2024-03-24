<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Master extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'MASTER';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    public function template() {
    	return $this->hasMany('Asset\Models\Template', 'bagian_id', 'id');
    }

    public function scopeBagian($query) {
        return $query->where('kelompok', 'BAGIAN');
    }

    public function scopeSistem($query) {
        return $query->where('kelompok', 'SISTEM');
    }

    public function scopePrwrutin($query) {
        return $query->where('kelompok', 'PRWRUTIN');
    }
}
