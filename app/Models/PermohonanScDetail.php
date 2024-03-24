<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class PermohonanScDetail extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'PERMOHONAN_SC_DETAIL';
    // protected $primaryKey = 'id';
    public $timestamps = true;

    /*
    connect to m_barang_alias
    */
    public function myParent()
    {
        return $this->belongsTo('Asset\Models\PermohonanSc', 'permohonan_sc_id', 'id');
    }

    public function barang()
    {
        return $this->belongsTo('Asset\Models\BarangAlias', 'kode_alias', 'recid');
    }

    public function gudangRelasi()
    {
        return $this->belongsTo('Asset\Models\Gudang', 'gudang', 'kd_gdg');
    }
}
