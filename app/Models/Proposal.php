<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $connection = 'oracleaplikasi';
    protected $table = 'PROPOSAL';
    protected $primaryKey = 'id';
    public $timestamps = true;

    public function perbaikan()
    {
    	return $this->belongsTo('Asset\Models\Perbaikan', 'prb_data_id', 'id');
    }

    public function perawatan()
    {
    	return $this->belongsTo('Asset\Models\Perawatan', 'prw_data_id', 'id');
    }

    public function usulan()
    {
    	return $this->belongsTo('Asset\Models\Usulan');
    }

    public function aduannon()
    {
    	return $this->belongsTo('Asset\Models\AduanNonOperasi');
    }
}
