<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Usulan extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'USULAN_NON_OPERASI';
    protected $primaryKey = 'id';
    public $timestamps = true;

    public function petugas() {
        return $this->belongsTo('Asset\RoleUser', 'petugas_id', 'nip');
    }

    public function instalasi() {
        return $this->belongsTo('Asset\Models\Instalasi', 'instalasi_id', 'id');
    }

    public function jabatan() {
        return $this->belongsTo('Asset\Jabatan', 'spv', 'recidjabatan');
    }

    public function bagian() {
        return $this->belongsTo('Asset\Models\MasterJab', 'nip_spv', 'nip');
    }

    public function pelapor() {
        return $this->belongsTo('Asset\RoleUser', 'pic', 'nip');
    }

    public function sukucadang()
    {
        return $this->hasMany('Asset\Models\PermohonanSc', 'usulan_non_operasi_id', 'id');
    }

    public function proposals()
    {
        return $this->belongsTo('Asset\Models\Proposal', 'proposal_id', 'id');
    }

    public function aset()
    {
        return $this->belongsTo('Asset\Models\Aset', 'aset_id', 'id');
    }
}
