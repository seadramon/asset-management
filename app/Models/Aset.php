<?php

namespace Asset\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Aset extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'oracleaplikasi';
    protected $table = 'ASET';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public static function boot()
    {
        parent::boot();

        static::saving(function($model1)
        {
            $kodeAset = self::createKodeAset($model1);
            $model1->kode_aset = $kodeAset;
        });
    }

    private static function createKodeAset($data){
        $nomor_urut_aset = str_pad($data->nomor_urut,3,0,STR_PAD_LEFT);
        $jenis_aset = ( $data->jenis_id == 1 )?"A":"NA";
        $instalasi = ($data->instalasi_id)?str_pad($data->instalasi->kode,2,0,STR_PAD_LEFT):"00";
        $lokasi = ($data->lokasi_id)?str_pad($data->lokasi->kode,2,0,STR_PAD_LEFT):"00";
        $ruang = ($data->ruang_id)?str_pad($data->ruangan->kode,2,0,STR_PAD_LEFT):"00";
        $kategori = ($data->kategori_id)?str_pad($data->kategori->kode,2,0,STR_PAD_LEFT):"00";
        $subkategori = ($data->sub_kategori_id)?str_pad($data->subkategori->kode,2,0,STR_PAD_LEFT):"00";
        $subsubkategori = ($data->sub_sub_kategori_id)?str_pad($data->subsubkategori->kode,2,0,STR_PAD_LEFT):"00";
        $tahun_pengadaan = ($data->tahun_pasang)?round($data->tahun_pasang):"xxxx";
        $strKodeAset = "$jenis_aset/$instalasi-$lokasi-$ruang/$kategori-$subkategori-$subsubkategori-$nomor_urut_aset/$tahun_pengadaan";
// dd(str_replace("-",".",$strKodeAset));

        return str_replace("-",".",$strKodeAset);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
//    public function user() {
//        return $this->belongsTo('Asset\Models\User', 'nip', 'userid');
//    }
//    
    public function type() {
        return $this->hasOne('Asset\Models\AsetType', 'id', 'jenis_id');
    }
    public function foto() {
        return $this->hasOne('Asset\Models\AsetFoto', 'aset_id', 'id');
    }
    public function kondisi() {
        return $this->hasOne('Asset\Models\Kondisi', 'id', 'kondisi_id');
    }

    public function kategori() {
        return $this->belongsTo('Asset\Models\Kategori', 'kategori_id', 'id');
    }

    public function subkategori() {
        return $this->belongsTo('Asset\Models\SubKategori', 'sub_kategori_id', 'id');
    }

    public function subsubkategori() {
        return $this->belongsTo('Asset\Models\SubSubKategori', 'sub_sub_kategori_id', 'id');
    }
    public function instalasi() {
        return $this->belongsTo('Asset\Models\Instalasi', 'instalasi_id', 'id');
    }

    public function lokasi() {
        return $this->belongsTo('Asset\Models\Lokasi', 'lokasi_id', 'id');
    }

    public function ruangan() {
        return $this->belongsTo('Asset\Models\Ruangan', 'ruang_id', 'id');
    }

    public function sistem() {
        return $this->belongsTo('Asset\Models\Master', 'sistem_id', 'id');
    }

    public function bagiannya() {
        return $this->belongsTo('Asset\Models\Master', 'bagian', 'id');
    }

    public function ms52w_komponen() {
        return $this->hasMany('Asset\Models\Ms52w', 'komponen_id', 'id');
    }

    public function prw52w_komponen() {
        return $this->hasMany('Asset\Models\Prw52w', 'komponen_id', 'id');
    }

    public function pdm() {
        return $this->hasMany('Asset\Models\MsdataPdm', 'komponen_id', 'id');
    }

    public function investasi() {
        return $this->hasMany('Asset\Models\UsulanInvestasi', 'komponen_id', 'id');
    }

    public function perbaikan() {
        return $this->hasMany('Asset\Models\Perbaikan', 'komponen_id', 'id');
    }

    public function biayaOperasional()
    {
        return $this->hasMany('Asset\Models\BiayaOperasional', 'aset_id', 'id');
    }

    public function biayaPemeliharaan()
    {
        return $this->hasMany('Asset\Models\BiayaPemeliharaan', 'aset_id', 'id');
    }

    public function scopeBarcode($query, $barcode)
    {
        $query->where('kode_barcode', $barcode);
    }

    public function pemindahan()
    {
        return $this->hasMany('Asset\Models\Pemindahan', 'aset_id', 'id');
    }

//    public function subkategori() {
//        return $this->hasMany('Asset\Models\SubKategori', 'kategori_id', 'id');
//    }
}
