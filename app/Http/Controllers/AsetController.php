<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;
use Datatables;
use Asset\Models\Aset,
    Asset\Models\AsetFoto,
    Asset\Models\SpekGroup,
    Asset\Models\Kondisi,
    Asset\Models\Instalasi,
    Asset\Models\Lokasi,
    Asset\Models\Ruangan,
    Asset\Models\Kategori,
    Asset\Models\SubKategori,
    Asset\Models\SubSubKategori,
    Asset\Models\Master
;
use DB;
use Storage;

class AsetController extends Controller
{
    public function entri(Request $request,$recid = null)
    {
        $kategori = Kategori::all();
        $instalasi = Instalasi::all();
        $kondisi = Kondisi::all();
        $speks = SpekGroup::with(['item' => function($q) {
                        $q->orderBy('urutan');
                    }])->orderBy('urutan')->get();
        $arrSpekData = null;

        $bagian = Master::where('kelompok', 'BAGIAN')->get()->pluck('name', 'id')->toArray();
        $labelBagian = ["" => "-            Pilih Bagian            -"];
        $bagian = $labelBagian + $bagian;

        $dateDefault = date("Y-m-d", strtotime("0000-00-00"));

        if ($recid == null) {
            $data = '';
            $sub = '';
            $subsub = '';
            $lokasi = '';
            $ruangan = '';
            $pemeliharaan = '';
        } else {
            $data = Aset::find($recid);
// dd($data->foto->foto_file);
            $sub = SubKategori::where('kategori_id', $data->kategori_id)->get();
            $subsub = SubSubKategori::where('sub_kategori_id', $data->sub_kategori_id)->get();
            $lokasi = Lokasi::where('instalasi_id', $data->instalasi_id)->get();
            $ruangan = Ruangan::where('lokasi_id', $data->lokasi_id)->get();
            $sp = explode("\n", $data->spesifikasi);
            foreach ($sp as $row) {
                $aa = explode('#', $row);
                if (count($aa) == 3) {
                    $intKelId = $aa[0];
                    $intSpekId = $aa[1];
                    $kelompok = substr($aa[2], 0, strpos($aa[2], "|") - 1);
                    $content = substr($aa[2], strpos($aa[2], "|") + 1);
                    $pos_colon = strpos($content, ":");
                    $spek = substr($content, 0, $pos_colon);
                    $pos_left_bracket = strpos($content, "[");
                    $pos_right_bracket = strpos($content, "]");
                    $value = trim(substr($content, $pos_colon + 1, $pos_left_bracket - $pos_colon - 1));
                    $unit = trim(substr($content, $pos_left_bracket + 1, $pos_right_bracket - $pos_left_bracket - 1));
                    if ($spek) {
                        $arrSpekData[$intKelId][$intSpekId]['kelompok'] = $kelompok;
                        $arrSpekData[$intKelId][$intSpekId]['spek'] = $spek;
                        $arrSpekData[$intKelId][$intSpekId]['val'] = $value;
                        $arrSpekData[$intKelId][$intSpekId]['unit'] = $unit;
                    }
                }
            }
            // dd($data->pemeliharaan_start);
            $arrPemeliharaan['start'] = !empty($data->pemeliharaan_start)?date("Y-m-d", strtotime($data->pemeliharaan_start)):"";
            $arrPemeliharaan['end'] = !empty($data->pemeliharaan_end)?date("Y-m-d", strtotime($data->pemeliharaan_end)):"";

            if (empty($arrPemeliharaan['start']) || empty($arrPemeliharaan['end'])) {
                $pemeliharaan = "";
            } else {
                $pemeliharaan = implode(" - ", $arrPemeliharaan);
                // $pemeliharaan = $arrPemeliharaan;
            }
            // dd($pemeliharaan);
        }
        // dd();
        return view('pages.aset.entri',[
            'data' => $data,
            'kategori' => $kategori,
            'subkategori' => $sub,
            'subsubkategori' => $subsub,
            'kondisi' => $kondisi,
            'instalasi' => $instalasi,
            'lokasi' => $lokasi,
            'ruangan' => $ruangan,
            'spesifikasi' => $speks,
            'spekdata' => $arrSpekData,
            'bagian' => $bagian,
            'pemeliharaan' => $pemeliharaan,
            'TAG'=>'aset',
            'TAG2'=>'entri'
        ]);
    }

    public function data(Request $request)
    {
        $data['TAG'] = 'aset';
        $data['TAG2'] = 'data';
        $instalasi = listInstalasi();
        $lokasi = [];
        $ruang = [];
        $bagian = [
            '' => "-    Pilih Bagian    -",
            '1' => 'Mekanikal',
            '2' => 'Elektrikal',
            '3' => 'Instrumentasi',
            '4' => 'Sipil',
        ];
        $kategori = listKategori();
        $subKategori = [];
        $subSubKategori = [];
        $kondisi = listKondisi();

        return view('pages.aset.data', [
            'TAG' => 'aset',
            'TAG2' => 'data',
            'instalasi' => $instalasi,
            'lokasi' => $lokasi,
            'ruang' => $ruang,
            'bagian' => $bagian,
            'kategori' => $kategori,
            'subkategori' => $subKategori,
            'subsubkategori' => $subSubKategori,
            'kondisi' => $kondisi,
        ]);
    }

    public function dataData(Request $request)
    {
    	$query = Aset::with(['kategori', 'subkategori', 'subsubkategori', 'instalasi', 'lokasi', 'ruangan', 'type', 'kondisi', 'pemindahan' => function($query) {
                    $query->orderBy('id', 'DESC');
                }])
                ->select('aset.id', 'aset.kode_aset', 'aset.nama_aset', 'aset.kategori_id', 'aset.sub_kategori_id', 'aset.sub_sub_kategori_id', 'aset.instalasi_id', 'aset.lokasi_id', 'aset.ruang_id', 'aset.jenis_id', 'aset.kondisi_id', 'aset.availability', 'aset.ts_create');

        // filter
        if ($request->instalasi_id) {
            $query->where('aset.instalasi_id', $request->instalasi_id);
        }

        if ($request->lokasi) {
            $query->where('aset.lokasi_id', $request->lokasi);
        }

        if ($request->ruang) {
            $query->where('aset.ruang_id', $request->ruang);
        }

        if ($request->bagian) {
            $query->where('aset.bagian', $request->bagian);
        }

        if ($request->kategori) {
            $query->where('aset.kategori_id', $request->kategori);
        }

        if ($request->subkategori) {
            $query->where('aset.sub_kategori_id ', $request->subkategori);
        }

        if ($request->subsubkategori) {
            $query->where('aset.sub_sub_kategori_id ', $request->subsubkategori);
        }

        if ($request->kondisi) {
            $query->where('aset.kondisi_id ', $request->kondisi);
        }

        /*if ($request->aset_id) {
            $query->where('aset.id', $request->aset_id);
        }*/

        if ($request->tahun) {
            $query->where('aset.tahun_pasang', $request->tahun);
        }

        if ($request->nama_aset) {
            $query->where(DB::raw('LOWER(aset.nama_aset)'), 'like', '%' . strtolower($request->nama_aset) .'%');
        }

        if ($request->kode_aset) {
            $query->where('aset.kode_aset', 'like', '%' . $request->kode_aset . '%');
        }

        if ($request->no_spk) {
            $query->where('aset.no_spk', 'like', '%' . $request->no_spk . '%');
        }

        if ($request->spesifikasi) {
            $query->where('aset.spesifikasi', 'like', '%'.$request->spesifikasi.'%');
        }
        // end:filter
        return Datatables::of($query)->addColumn('subsubkategori_name', function($m) {
            return $m->subsubkategori ? $m->subsubkategori->name : '-';
        })
        ->addColumn('ruangan_name', function($m) {
            return $m->ruangan ? $m->ruangan->name : '-';
        })
        ->addColumn('pindah_tgl_pindah', function($m) {
            if (!empty($m->pemindahan)) {
                if (!empty($m->pemindahan[0])) {
                    $res= $m->pemindahan[0]->tgl_pindah;    
                } else {
                    $res = "-";
                }
            } else {
                $res = '-';
            }
            return $res;
        })
        ->addColumn('menu', function ($model) {
            $lcca = "";
            // $edit = '<a href="' . url('aset/entri/'.$model->id) . '" class="btn alpha-blue text-blue-800 border-blue-600 legitRipple btn-sm"><i class="fa fa-edit"></i> Edit </a>';
            $edit = editMenu(url('aset/entri/'.$model->id));

            if ( $model->availability == '1' ) {
                $lcca = ' &nbsp; <a href="' . route('lcca::index', ['id' => $model->id]) . '" class="btn btn-outline alpha-success text-success-800 border-success-600 legitRipple btn-sm"><i class="icon-history"></i> LCCA </a>';
            }

            $view = '<a href="' . route('aset::aset-view', ['id' => $model->id]) . '" class="btn btn-sm purple" target="_blank"><i class="fa fa-eye"></i> View </a>'.'&nbsp;';

            return $view.$edit.$lcca;
        })
        ->make(true);
    }

    public function simpanAset(Request $req)
    {
        // dd($req->all());
        if ($req->tipe == 0) {
            $tmp = new Aset();
            $tmp->kategori_id = $req->kategori;
            $tmp->sub_kategori_id = $req->subkategori;
            $tmp->sub_sub_kategori_id = $req->subsubkategori;
            $tmp->no_aktiva = $req->no_aktiva;
            $tmp->no_spk = $req->no_spk;
            $tmp->no_bk = $req->no_bk;
            $tmp->no_spmu = $req->no_spmu;
            $tmp->nama_aset = $req->nama_aset;
            $tmp->jumlah = $req->jumlah;
            $tmp->satuan = $req->satuan;
            $tmp->lat = $req->lat;
            $tmp->lon = $req->lon;
            $tmp->instalasi_id = $req->instalasi;
            $tmp->lokasi_id = $req->lokasi;
            $tmp->ruang_id = $req->ruangan;
            $tmp->keterangan = $req->keterangan;
            $tmp->tahun_pasang = $req->tahun_pasang;
            $tmp->kondisi_id = $req->kondisi;
            $tmp->kode_fm = strtoupper($req->kode_fm);
            $tmp->bagian = $req->bagian;
            $tmp->penyedia = $req->penyedia;
            $tmp->nomor_urut = $req->nomor_urut;
            $tmp->ppk = $req->ppk;
            $tmp->harga = $req->harga;
            if ($req->tgl_pasang != '')
                $tmp->tgl_pasang = DB::raw("TO_DATE('" . date('dmY', strtotime($req->tgl_pasang)) . "','DDMMYYYY')");
            if ($req->tgl_operasi != '')
                $tmp->tgl_operasi = DB::raw("TO_DATE('" . date('dmY', strtotime($req->tgl_operasi)) . "','DDMMYYYY')");
            if ($req->tgl_perkiraan != '')
                $tmp->tgl_perkiraan_sut = DB::raw("TO_DATE('" . date('dmY', strtotime($req->tgl_perkiraan)) . "','DDMMYYYY')");
            if ($req->has('sukucadang')) {
                $tmp->is_sukucadang = '0';
            }

            if (!empty($req->masapemeliharaan)) {
                $arrPemeliharaan = explode(" - ", $req->masapemeliharaan);
                
                $tmp->pemeliharaan_start = $arrPemeliharaan[0];
                $tmp->pemeliharaan_end = $arrPemeliharaan[1];
            }
            //generate string spek
            $kode = $req->kode_spek;
            $nama = $req->nama_spek;
            $nilai = $req->nilai_spek;
            $satuan = $req->satuan_spek;
            $spek = [];
//            echo count($kode) . count($nama) . count($nilai) . count($satuan);
            for ($i = 0; $i < count($kode); $i++) {
                if (trim($nilai[$i]) != '') {
                    $spek[] = $kode[$i] . '# ' . $nama[$i] . ': ' . $nilai[$i] . ' [' . trim($satuan[$i]) . ']';
                }
            }
            $newspek = implode("\n", $spek);
            $tmp->spesifikasi = $newspek;

            $data->creator_id = 1;
            $data->updater_id = 1;
            $data->ts_create = getNow();
            $data->ts_update = getNow();

            $tmp->save();

            $idAset = $tmp->id;
            $instalasiId = $tmp->instalasi_id;          
        } else {
            $data = Aset::find($req->kode);

            $data->kategori_id = $req->kategori;
            $data->sub_kategori_id = $req->subkategori;
            $data->sub_sub_kategori_id = $req->subsubkategori;
            $data->no_aktiva = $req->no_aktiva;
            $data->no_spk = $req->no_spk;
            $data->no_bk = $req->no_bk;
            $data->no_spmu = $req->no_spmu;
            $data->nama_aset = $req->nama_aset;
            $data->jumlah = $req->jumlah;
            $data->satuan = $req->satuan;
            $data->lat = $req->lat;
            $data->lon = $req->lon;
            $data->instalasi_id = $req->instalasi;
            $data->lokasi_id = $req->lokasi;
            $data->ruang_id = $req->ruangan;
            $data->keterangan = $req->keterangan;
            $data->tahun_pasang = $req->tahun_pasang;
            $data->kondisi_id = $req->kondisi;
            $data->kode_fm = strtoupper($req->kode_fm);
            $data->bagian = $req->bagian;
            $data->penyedia = $req->penyedia;
            $data->nomor_urut = $req->nomor_urut;
            $data->ppk = $req->ppk;
            $data->harga = $req->harga;
            if ($req->tgl_pasang != '')
                $data->tgl_pasang = DB::raw("to_date('$req->tgl_pasang','MM/DD/YYYY')");
            if ($req->tgl_operasi != '')
                $data->tgl_operasi = DB::raw("TO_DATE('" . date('dmY', strtotime($req->tgl_operasi)) . "','DDMMYYYY')");
            if ($req->tgl_perkiraan != '')
                $data->tgl_perkiraan_sut = DB::raw("TO_DATE('" . date('dmY', strtotime($req->tgl_perkiraan)) . "','DDMMYYYY')");
            if ($req->has('sukucadang')) {
                $data->is_sukucadang = '0';
            }

            if (!empty($req->masapemeliharaan)) {                
                $arrPemeliharaan = explode(" - ", $req->masapemeliharaan);
                
                $data->pemeliharaan_start = $arrPemeliharaan[0];
                $data->pemeliharaan_end = $arrPemeliharaan[1];
            }
            //generate string spek
            $kode = $req->kode_spek;
            $nama = $req->nama_spek;
            $nilai = $req->nilai_spek;
            $satuan = $req->satuan_spek;
            $spek = [];
            for ($i = 0; $i < count($kode); $i++) {
                if (trim($nilai[$i]) != '') {
                    $spek[] = $kode[$i] . '# ' . $nama[$i] . ': ' . $nilai[$i] . ' [' . trim($satuan[$i]) . ']';
                }
            }
            $newspek = implode("\n", $spek);
            $data->spesifikasi = $newspek;

            $data->updater_id = 1;
            $data->ts_update = getNow();

            $data->save();
            // Aset::where('id', $req->kode)->update($data);

            $idAset = $req->kode;
            $instalasiId = $req->instalasi;
        }

        if ($req->hasFile('image')) {
            $file = $req->file('image');
            $extension = $file->getClientOriginalExtension();

            $dir = date('Y').'/'.$instalasiId.'/';
            cekDir($dir);

            $filename = $idAset .'_'. date('Ymd') .'.' . $extension;
            $fullpath = $dir.$filename;
            Storage::disk('sftp-aset-img')->put($fullpath, \File::get($file));
            
            $data = new AsetFoto;
            $data->foto_file = $fullpath;
            $data->aset_id = $idAset;
            $data->creator_id = 1;
            $data->updater_id = 1;
            $data->ts_create = getNow();
            $data->ts_update = getNow();

            $data->save();
        }

        return redirect('/aset/data');
    }

    public function show($recid)
    {
        $data = Aset::find($recid);
        $text = "";
        if (!empty($data->spesifikasi)) {
            $speks = explode("|", $data->spesifikasi);
            foreach ($speks as $key => $row) {
                $n = explode("\n", $row);

                if (count($n) > 1) {
                    $splitN = explode("\n", $row);
                    $text .= $splitN[0];

                    $temp = explode("#", $splitN[1]);
                    if (isset($temp[2])) $text .= $temp[2] . "<br>";
                } else {
                    $temp = explode("#", $row);
                    if (isset($temp[2])) $text .= $temp[2] . "<br>";
                }
            }
        }

        return view('pages.aset.view', [
            'data' => $data,
            'spek' => $text,
            'TAG'=>'aset',
            'TAG2'=>'data'
        ]);
    }
}
