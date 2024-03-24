<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Aset,
    Asset\Models\SpekGroup,
    Asset\Models\Kondisi,
    Asset\Models\Instalasi,
    Asset\Models\Lokasi,
    Asset\Models\Ruangan,
    Asset\Models\Kategori,
    Asset\Models\SubKategori,
    Asset\Models\SubSubKategori,
    Asset\Models\Master;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

use Excel;
use Log;
use DB;
use Session;
use Config;
use Uuid;

class UploadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $instalasi = Instalasi::get()->pluck('name', 'id')->toArray();
        $labelInstalasi = ["" => "-             Pilih Instalasi             -"];
        $instalasi = $labelInstalasi + $instalasi;

        return view('pages.aset.uploadform', [
            'instalasi' => $instalasi
        ]);
    }

    public function simpan(Request $request)
    {
        try {
            $validation_rules = [
                'excelnya' => 'required',
                'instalasi_id' => 'required'
            ];

            $validator = Validator::make($request->all(), $validation_rules);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            /* TODO */
            // Storing File
            $instalasiId = $request->instalasi_id;
            $file = $request->file('excelnya');
            Config::set('excel.import.startRow', 16);

            /*$extension = $file->getClientOriginalExtension();
            $dir = 'data_aset/'.date('m-Y');
            cekDir($dir);
            $filename = str_replace(" ", "_", $file->getClientOriginalName());
            $fileFullpath = $dir.'/'.$filename;

            Storage::disk('sftp-doc')->put($fileFullpath, \File::get($file));*/
            // end:Storing file
            
            $reader = Excel::selectSheetsByIndex(0)->load($file)->skipRows(1)->get();
            $totalRows = $reader->count();
            $arrData = [];

            foreach ($reader as $row) {

                if (!empty($row->nama_aset)) {

                    $kat = null;
                    if (!empty($row->kategori)) {
                        $kategori = Kategori::where('name', trim($row->kategori))->first();

                        if (empty($kategori)) {
                            $maxQuery = Kategori::max('kode') + 1;
                            $kategori = new Kategori;
                            $kategori->kode = $maxQuery;
                            $kategori->name = trim($row->kategori);

                            $kategori->save();
                        }

                        $kat = $kategori->id;
                    }

                    $subKat = null;
                    if (!empty($row->sub_kategori)) {
                        $subKategori = SubKategori::where('kategori_id', $kat)
                            ->where('name', trim($row->sub_kategori))
                            ->first();

                        if (empty($subKategori)) {
                            $maxQuery = SubKategori::where('kategori_id', $kat)->max('kode') + 1;

                            $subKategori = new SubKategori;
                            $subKategori->kode = $maxQuery;
                            $subKategori->name = trim($row->sub_kategori);
                            $subKategori->kategori_id = $kat;

                            $subKategori->save();
                        }

                        $subKat = $subKategori->id;
                    }

                    $subSubKat = null;
                    if (!empty($row->sub_sub_kategori)) {
                        $subSubKategori = SubSubKategori::where('kategori_id', $kat)
                                ->where('sub_kategori_id', $subKat)
                                ->where('name', trim($row->sub_sub_kategori))
                                ->first();

                        if (empty($subSubKategori)) {
                            $maxQuery = SubSubKategori::where('kategori_id', $kat)
                                ->where('sub_kategori_id', $subKat)
                                ->max('kode') + 1;

                            $subSubKategori = new SubSubKategori;
                            $subSubKategori->kode = $maxQuery;
                            $subSubKategori->name = trim($row->sub_sub_kategori);
                            $subSubKategori->kategori_id = $kat;
                            $subSubKategori->sub_kategori_id = $subKat;

                            $subSubKategori->save();
                        }

                        $subSubKat = $subSubKategori->id;
                    }

                    $lok = null;
                    if (!empty($row->lokasi)) {
                        $lokasi = Lokasi::where('instalasi_id', $instalasiId)
                            ->where('name', trim($row->lokasi))
                            ->first();

                        if (empty($lokasi)) {
                            $maxQuery = Lokasi::where('instalasi_id', $instalasiId)->max('kode') + 1;

                            $lokasi = new Lokasi;
                            $lokasi->kode = $maxQuery;
                            $lokasi->name = trim($row->lokasi);
                            $lokasi->instalasi_id = $instalasiId;

                            $lokasi->save();   
                        }

                        $lok = $lokasi->id;
                    }

                    $ruang = null;
                    if (!empty($row->ruang)) {
                        $ruangan = Ruangan::where('instalasi_id', $instalasiId)
                            ->where('lokasi_id', $lok)
                            ->where('name', trim($row->ruang))
                            ->first();

                        if (empty($ruangan)) {
                            $maxQuery = Ruangan::where('instalasi_id', $instalasiId)->where('lokasi_id', $lok)->max('kode') + 1;
// dd($lok.'-'.$instalasiId.'-'.$maxQuery);
                            $ruangan = new Ruangan;
                            $ruangan->kode = $maxQuery;
                            $ruangan->name = trim($row->ruang);
                            $ruangan->instalasi_id = $instalasiId;
                            $ruangan->lokasi_id = $lok;

                            $ruangan->save();   
                        }

                        $ruang = $ruangan->id;
                    }

                    $nomorUrut = !empty($row->no)?(int)$row->no:'';
                    $hash = Uuid::generate()->string;

                    $data = new Aset;

                    $data->jenis_id              = 1;
                    $data->nama_aset             = !empty($row->nama_aset)?$row->nama_aset:'';
                    $data->kategori_id           = $kat;
                    $data->sub_kategori_id       = $subKat;
                    $data->sub_sub_kategori_id   = $subSubKat;
                    $data->no_aktiva             = !empty($row->no_aktiva)?$row->no_aktiva:'';
                    $data->no_spk                = !empty($row->no_spk)?$row->no_spk:'';
                    $data->no_spmu               = !empty($row->no_spmu)?$row->no_spmu:'';
                    $data->no_bk                 = !empty($row->no_bk)?$row->no_bk:'';
                    $data->kode_aset             = !empty($row->kode_aset)?$row->kode_aset:'';
                    $data->kode_barcode          = !empty($row->kode_barcode)?$row->kode_barcode:'';
                    $data->jumlah                = !empty($row->jumlah)?$row->jumlah:'';
                    $data->satuan                = !empty($row->satuan)?$row->satuan:'';
                    $data->instalasi_id          = $instalasiId;
                    $data->lokasi_id             = $lok;
                    $data->ruang_id              = $ruang;
                    $data->tahun_pasang          = !empty($row->tahun_pasang)?$row->tahun_pasang:'';
                    $data->umur                  = !empty($row->umur)?$row->umur:'';
                    $data->umur_aktiva           = !empty($row->umur_aktiva)?$row->umur_aktiva:'';
                    $data->sisa_umur_aktiva      = !empty($row->sisa_umur_aktiva)?$row->sisa_umur_aktiva:'';
                    $data->keterangan            = !empty($row->keterangan)?$row->keterangan:'';
                    $data->nomor_urut            = $nomorUrut;
                    $data->hashid                = $hash;
                    $data->kode_barcode          = str_replace("-", "", $hash);
                    $data->kondisi_id            = 2;

                    $data->save();        
                }
            }

            if (count($arrData) > 0 ) {
                $data = Aset::insert($arrData);
            }

            Session::flash('success', 'Upload Asset berhasil');
        } catch(Exception $e) {
            Session::flash('error', 'Upload Asset gagal.');
        }

        return redirect()->route('uploadaset::index');
    }
}
