<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use DB;
use Datatables;
use Session;
use Validator;

use Asset\Models\Aset;
use Asset\Models\Kondisi;
use Asset\Models\Kategori;
use Asset\Models\SubKategori;
use Asset\Models\SubSubKategori;
use Asset\Models\SpekItem;
use Asset\Models\SpekGroup;
use Asset\Models\Instalasi;
use Asset\Models\Master;
use Asset\Models\Lokasi;
use Asset\Models\Ruangan;
use Asset\Models\Template;
use Asset\Models\Kelompok;
use Asset\Models\KelompokDetail;
use Asset\Models\Komponen;
use Asset\Models\KomponenDetail;
use Asset\Models\MasterFm;
use Asset\Models\PrwrutinPdm;
use Asset\Models\Prw52w;

use Artisan;
    

class MasterController extends Controller
{
    public function index()
    {

    }

    public function kondisiLink($recid = null) {
        if ($recid == null)
            $data = '';
        else
            $data = Kondisi::find($recid);
        return view('pages.master.kondisi', ['data' => $data]);
    }

    public function kondisiSimpan(Request $req) {
        try {
            if ($req->tipe == 0) {
                $tmp = new Kondisi();
                $tmp->kode = $req->nomor;
                $tmp->name = $req->nama;
                $tmp->nilai_level = $req->level;
                $tmp->tingkat_pemeliharaan = $req->pemeliharaan;
                $tmp->save();

                Session::flash('success', 'Data berhasil diinputkan');
            } else {
                Kondisi::where('id', $req->kode)->update([
                    'kode' => $req->nomor,
                    'name' => $req->nama,
                    'nilai_level' => $req->level,
                    'tingkat_pemeliharaan' => $req->pemeliharaan
                ]);

                Session::flash('success', 'Data berhasil diupdate');
            }
        } catch(\Exception $e) {
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('master::kondisi-link');
    }

    public function kondisiData()
    {
        $query = Kondisi::select('id', 'kode', 'name', 'nilai_level', 'tingkat_pemeliharaan');
        return Datatables::of($query)
                ->addColumn('menu', function ($model) {
                    $edit = '<a href="' . route('master::kondisi-link', ['recid' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                    return $edit;
                })
                ->make(true);
    }

    public function kategoriLink($recid = null) {
        if ($recid == null)
            $data = '';
        else
            $data = Kategori::find($recid);
        return view('pages.master.kategori', ['data' => $data]);
    }

    public function kategoriSimpan(Request $req) {
        try {
            if ($req->tipe == 0) {
                $tmp = new Kategori();
                $tmp->name = $req->nama;
                $tmp->save();

                Session::flash('success', 'Data berhasil diinputkan');
            } else {
                Kategori::where('id', $req->kode)->update([
                    'name' => $req->nama
                ]);

                Session::flash('success', 'Data berhasil diupdate');
            }
        } catch(\Exception $e) {
            Session::flash('error', 'Data gagal disimpan');
        }
        
        return redirect()->route('master::kategori-link');
    }

    public function kategoriData() {
        $query = Kategori::select('id', 'kode', 'name');
        return Datatables::of($query)
//                        ->editColumn('tgltu', function ($model) {
//                            if ($model->tgltu == '') {
//                                return 'Tidak Ada Tanggal';
//                            } else {
//                                return date('d-m-Y', strtotime($model->tgltu));
//                            }
//                        })
                        ->addColumn('menu', function ($model) {
                            $edit = '<a href="' . route('master::kategori-link', ['recid' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                            return $edit;
                        })
                        ->make(true);
    }

    public function subKategoriLink($recid = null) {
        if ($recid == null)
            $data = '';
        else
            $data = SubKategori::with('kategori')->find($recid);
        $kat = Kategori::all();
        return view('pages.master.subkategori', ['data' => $data, 'kategori' => $kat]);
    }

    public function subKategoriSimpan(Request $req) {
        try {
            if ($req->tipe == 0) {
                $kode = SubKategori::where('kategori_id', $req->kategori)->max(DB::raw('to_number(kode)'));
                $tmp = new SubKategori();
                $tmp->name = $req->nama;
                $tmp->kode = $kode + 1;
                $tmp->kategori_id = $req->kategori;
                $tmp->save();

                Session::flash('success', 'Data berhasil diinputkan');
            } else {
                $tmp = SubKategori::find($req->kode);
                $smpn = [
                    'name' => $req->nama,
                    'kategori_id' => $req->kategori
                ];
                if ($tmp->kategori_id != $req->kategori) {
                    $kode = SubKategori::where('kategori_id', $req->kategori)->max(DB::raw('to_number(kode)'));
                    $smpn['kode'] = $kode + 1;
                }
                SubKategori::where('id', $req->kode)->update($smpn);

                Session::flash('success', 'Data berhasil diupdate');
            }
        } catch(\Exception $e) {
            Session::flash('error', 'Data gagal disimpan');
        }
        
        return redirect()->route('master::subkategori-link');
    }

    public function subKategoriData() {
        $query = SubKategori::with('kategori')->select('sub_kategori.id', 'sub_kategori.kode', 'sub_kategori.name', 'sub_kategori.kategori_id');
        return Datatables::of($query)
//                        ->editColumn('tgltu', function ($model) {
//                            if ($model->tgltu == '') {
//                                return 'Tidak Ada Tanggal';
//                            } else {
//                                return date('d-m-Y', strtotime($model->tgltu));
//                            }
//                        })
                        ->addColumn('menu', function ($model) {
                            $edit = '<a href="' . route('master::subkategori-link', ['recid' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                            return $edit;
                        })
                        ->make(true);
    }

    public function subKategoriSelect($recid) {
        $aa = SubKategori::where('kategori_id', $recid)->get();
        $txt = '';
        foreach ($aa as $row) {
            $txt .= '<option value="' . $row->id . '">' . $row->name . '</option>';
        }
        return response()->json(['data' => $txt]);
    }

    public function subSubKategoriLink($recid = null) {
        if ($recid == null)
            $data = '';
        else
            $data = SubSubKategori::with('subkategori')->find($recid);
        $kat = Kategori::with('subkategori')->get();
        return view('pages.master.subsubkategori', ['data' => $data, 'kategori' => $kat]);
    }

    public function subSubKategoriSimpan(Request $req) {
        try {
            $sub = explode('#', $req->subkategori);
            if ($req->tipe == 0) {
                $kode = SubSubKategori::where('sub_kategori_id', $sub[1])->max(DB::raw('to_number(kode)'));
                $tmp = new SubSubKategori();
                $tmp->name = $req->nama;
                $tmp->kode = $kode + 1;
                $tmp->kategori_id = $sub[0];
                $tmp->sub_kategori_id = $sub[1];
                $tmp->save();

                Session::flash('success', 'Data berhasil diinputkan');
            } else {
                // $tmp = SubKategori::find($req->kode);
                $tmp = SubKategori::find($req->sub_kategori_id);
                $smpn = [
                    'name' => $req->nama,
                    'kategori_id' => $sub[0],
                    'sub_kategori_id' => $sub[1]
                ];
                
                if ($tmp->sub_kategori_id != $sub[1]) {
                    $kode = SubSubKategori::where('sub_kategori_id', $sub[1])->max(DB::raw('to_number(kode)'));
                    $smpn['kode'] = $kode + 1;
                }
                SubSubKategori::where('id', $req->kode)->update($smpn);

                Session::flash('success', 'Data berhasil diupdate');
            }
        } catch(\Exception $e) {
            Session::flash('error', $e->getMessage());
        }
        
        return redirect()->route('master::subsubkategori-link');
    }

    public function subSubKategoriData() {
        $query = SubSubKategori::with('subkategori.kategori')->select('sub_sub_kategori.id', 'sub_sub_kategori.kode', 'sub_sub_kategori.name', 'sub_sub_kategori.sub_kategori_id');
        return Datatables::of($query)
//                        ->editColumn('tgltu', function ($model) {
//                            if ($model->tgltu == '') {
//                                return 'Tidak Ada Tanggal';
//                            } else {
//                                return date('d-m-Y', strtotime($model->tgltu));
//                            }
//                        })
                        ->addColumn('menu', function ($model) {
                            $edit = '<a href="' . route('master::subsubkategori-link', ['recid' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                            return $edit;
                        })
                        ->make(true);
    }

    public function subSubKategoriSelect($recid) {
        $aa = SubSubKategori::where('sub_kategori_id', $recid)->get();
        $txt = '';
        foreach ($aa as $row) {
            $txt .= '<option value="' . $row->id . '">' . $row->name . '</option>';
        }
        return response()->json(['data' => $txt]);
    }

    public function instalasiLink($recid = null) {
        if ($recid == null)
            $data = '';
        else
            $data = Instalasi::find($recid);
        return view('pages.master.instalasi', ['data' => $data]);
    }

    public function instalasiSimpan(Request $req) {
        try {
            if ($req->tipe == 0) {
                $tmp = new Instalasi();
                $tmp->name = $req->nama;
                $tmp->kode = $req->kode;
                $tmp->save();

                Session::flash('success', 'Data berhasil diinputkan');
            } else {
                Instalasi::where('id', $req->id)->update([
                    'name' => $req->nama
                ]);

                Session::flash('success', 'Data berhasil diupdate');
            }
        } catch (\Exception $e) {
            Session::flash('error', 'Data gagal disimpan');
            // Session::flash('error', $e->getMessage());
        }
        
        return redirect()->route('master::instalasi-link');
    }

    public function instalasiData() {
        $query = Instalasi::select('id', 'kode', 'name');
        return Datatables::of($query)
//                        ->editColumn('tgltu', function ($model) {
//                            if ($model->tgltu == '') {
//                                return 'Tidak Ada Tanggal';
//                            } else {
//                                return date('d-m-Y', strtotime($model->tgltu));
//                            }
//                        })
                        ->addColumn('menu', function ($model) {
                            $edit = '<a href="' . route('master::instalasi-link', ['recid' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                            return $edit;
                        })
                        ->make(true);
    }

    public function lokasiLink($recid = null) {
        if ($recid == null)
            $data = '';
        else
            $data = Lokasi::with('instalasi')->find($recid);

        $kat = Instalasi::all();
        return view('pages.master.lokasi', ['data' => $data, 'instalasi' => $kat]);
    }

    public function lokasiSimpan(Request $req) {
        try {
            if ($req->tipe == 0) {
                $kode = Lokasi::where('instalasi_id', $req->instalasi)->max(DB::raw('to_number(kode)'));
                $tmp = new Lokasi();
                $tmp->name = $req->nama;
                // $tmp->kode = $kode + 1;
                $tmp->kode = $req->kode;
                $tmp->instalasi_id = $req->instalasi;
                $tmp->save();

                Session::flash('success', 'Data berhasil diinputkan');
            } else {
                $tmp = Lokasi::find($req->id);
                $smpn = [
                    'name' => $req->nama,
                    'instalasi_id' => $req->instalasi
                ];
                if ($tmp->instalasi_id != $req->instalasi) {
                    $kode = Lokasi::where('instalasi_id', $req->instalasi)->max(DB::raw('to_number(kode)'));
                    $smpn['kode'] = $kode + 1;
                }
                Lokasi::where('id', $req->id)->update($smpn);

                Session::flash('success', 'Data berhasil diupdate');
            }
        } catch(\Exception $e) {
            Session::flash('error', 'Data gagal disimpan');
        }
        
        return redirect()->route('master::lokasi-link');
    }

    public function lokasiData() {
        $query = Lokasi::with('instalasi')->select('lokasi.id', 'lokasi.kode', 'lokasi.name', 'lokasi.instalasi_id');
        return Datatables::of($query)
//                        ->editColumn('tgltu', function ($model) {
//                            if ($model->tgltu == '') {
//                                return 'Tidak Ada Tanggal';
//                            } else {
//                                return date('d-m-Y', strtotime($model->tgltu));
//                            }
//                        })
                        ->addColumn('menu', function ($model) {
                            $edit = '<a href="' . route('master::lokasi-link', ['recid' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                            return $edit;
                        })
                        ->make(true);
    }
    
    public function lokasiSelect($recid) {
        $aa = Lokasi::where('instalasi_id', $recid)->get();
        $txt = '';
        foreach ($aa as $row) {
            $txt .= '<option value="' . $row->id . '">' . $row->name . '</option>';
        }
        return response()->json(['data' => $txt]);
    }

    public function ruanganLink($recid = null) {
        if ($recid == null)
            $data = null;
        else
            $data = Ruangan::with('lokasi')->find($recid);
        $kat = Instalasi::with('lokasi')->get();
        return view('pages.master.ruangan', ['data' => $data, 'instalasi' => $kat]);
    }

    public function ruanganSimpan(Request $req) {
        try {
            $sub = explode('#', $req->instalasi);
            if ($req->tipe == 0) {
                $kode = Ruangan::where('lokasi_id', $sub[1])->max(DB::raw('to_number(kode)'));
                $tmp = new Ruangan();
                $tmp->name = $req->nama;
                // $tmp->kode = $kode + 1;
                $tmp->kode = $req->kode;
                $tmp->instalasi_id = $sub[0];
                $tmp->lokasi_id = $sub[1];
                $tmp->save();

                Session::flash('success', 'Data berhasil disimpan');
            } else {
                $tmp = Ruangan::find($req->id);
                $smpn = [
                    'name' => $req->nama,
                    'instalasi_id' => $sub[0],
                    'lokasi_id' => $sub[1]
                ];
                if ($tmp->lokasi_id != $sub[1]) {
                    $kode = Ruangan::where('lokasi_id', $sub[1])->max(DB::raw('to_number(kode)'));
                    $smpn['kode'] = $kode + 1;
                }
                Ruangan::where('id', $req->id)->update($smpn);

                Session::flash('success', 'Data berhasil diupdate');
            }
        } catch (\Exception $e) {
            Session::flash('error', 'Data gagal disimpan');
        }
        
        return redirect()->route('master::ruangan-link');
    }

    public function ruanganData() {
        $query = Ruangan::with('lokasi.instalasi')->select('ruangan.id', 'ruangan.kode', 'ruangan.name', 'ruangan.lokasi_id');
        return Datatables::of($query)
//                        ->editColumn('tgltu', function ($model) {
//                            if ($model->tgltu == '') {
//                                return 'Tidak Ada Tanggal';
//                            } else {
//                                return date('d-m-Y', strtotime($model->tgltu));
//                            }
//                        })
                        ->addColumn('menu', function ($model) {
                            $edit = '<a href="' . route('master::ruangan-link', ['recid' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                            return $edit;
                        })
                        ->make(true);
    }
    
    public function ruanganSelect($recid) {
        $aa = Ruangan::where('lokasi_id', $recid)->get();
        $txt = '';
        foreach ($aa as $row) {
            $txt .= '<option value="' . $row->id . '">' . $row->name . '</option>';
        }
        return response()->json(['data' => $txt]);
    }

    public function spekItemLink($recid = null) {
        if ($recid == null)
            $data = '';
        else
            $data = SpekItem::find($recid);
        return view('pages.master.spekitem', ['data' => $data]);
    }

    public function spekItemSimpan(Request $req) {
        try {
            if ($req->tipe == 0) {
                $kode = SpekItem::max(DB::raw('to_number(urutan)'));
                $tmp = new SpekItem();
                $tmp->name = $req->nama;
                $tmp->satuan = $req->satuan;
                $tmp->urutan = $kode;
                $tmp->save();

                Session::flash('success', 'Data berhasil disimpan');
            } else {
                SpekItem::where('id', $req->kode)->update([
                    'name' => $req->nama,
                    'satuan' => $req->satuan
                ]);

                Session::flash('success', 'Data berhasil diupdate');
            }
        } catch (\Exception $e) {
            Session::flash('error', 'Data gagal disimpan');
        }
        
        return redirect()->route('master::spekitem-link');
    }

    public function spekItemData() {
        $query = SpekItem::select('id', 'name', 'satuan');
        return Datatables::of($query)
//                        ->editColumn('tgltu', function ($model) {
//                            if ($model->tgltu == '') {
//                                return 'Tidak Ada Tanggal';
//                            } else {
//                                return date('d-m-Y', strtotime($model->tgltu));
//                            }
//                        })
                        ->addColumn('menu', function ($model) {
                            $edit = '<a href="' . route('master::spekitem-link', ['recid' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                            return $edit;
                        })
                        ->make(true);
    }

    public function spekGroupLink($recid = null) {
        $listSpekItem = SpekItem::orderBy('urutan', 'asc')
                            ->get();
        $spekItemSel = [];

        if ($recid == null) {
            $data = '';
        } else {
            $data = SpekGroup::find($recid);
            $arrItemSel = DB::table('spek_item_assn')
                        ->select('spek_item_id')
                        ->where('kelompok_spek_id', $recid)
                        ->get();

            if ($arrItemSel) {
                foreach ($arrItemSel as $row) {                    
                    $spekItemSel[] = $row->spek_item_id;
                }
            }
        }

        return view('pages.master.spekgroup', [
            'data' => $data,
            'list_spekitem' => $listSpekItem,
            'spekItemSel' => $spekItemSel
        ]);
    }

    public function spekGroupSimpan(Request $req) {
        DB::beginTransaction();

        try {
            if ($req->tipe == 0) {
                // input ke kelompok_spek
                $kode = SpekGroup::max(DB::raw('to_number(urutan)'));
                $tmp = new SpekGroup();
                $tmp->name = $req->nama;
                $tmp->urutan = $kode;
                $tmp->save();

                // input ke spek_item_assn
                if (is_array($req->spek_item_id) && count($req->spek_item_id) > 0) {
                    foreach ($req->spek_item_id as $row) {
                        $arrItemAssn[] = ['kelompok_spek_id' => $tmp->id,
                            'spek_item_id' => $row
                        ]; 
                    }

                    $spek_item_assn = DB::table('spek_item_assn')
                                ->insert($arrItemAssn);
                }

                DB::commit();
                Session::flash('success', 'Data berhasil disimpan');

            } else {
                // update ke kelompok_spek
                SpekGroup::where('id', $req->kode)->update([
                    'name' => $req->nama
                ]);

                // update ke spek_item_assn
                if (is_array($req->spek_item_id) && count($req->spek_item_id) > 0) {
                    DB::table('spek_item_assn')
                        ->where('kelompok_spek_id', $req->kode)
                        ->delete();

                    foreach ($req->spek_item_id as $row) {
                        $arrItemAssn[] = ['kelompok_spek_id' => $req->kode,
                            'spek_item_id' => $row
                        ]; 
                    }

                    $spek_item_assn = DB::table('spek_item_assn')
                                ->insert($arrItemAssn);
                }

                DB::commit();
                Session::flash('success', 'Data berhasil diupdate');
            }
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }
        
        return redirect()->route('master::spekgroup-link');
    }

    public function spekGroupData() {
        $query = SpekGroup::select('id', 'name');
        return Datatables::of($query)
//                        ->editColumn('tgltu', function ($model) {
//                            if ($model->tgltu == '') {
//                                return 'Tidak Ada Tanggal';
//                            } else {
//                                return date('d-m-Y', strtotime($model->tgltu));
//                            }
//                        })
                        ->addColumn('menu', function ($model) {
                            $edit = '<a href="' . route('master::spekgroup-link', ['recid' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                            return $edit;
                        })
                        ->make(true);
    }

    public function kelompokLink($recid = null) {
        if ($recid == null)
            $data = '';
        else
            $data = Kelompok::find($recid);

        $kat = Template::all();
        return view('pages.master.kelompok', ['data' => $data, 'template' => $kat]);
    }

    public function kelompokSimpan(Request $req) {
        try {
            if ($req->tipe == 0) {
                $tmp = new Kelompok();
                $tmp->nama = $req->nama;
                $tmp->ms_template_id = $req->template;
                $tmp->save();

                Session::flash('success', 'Data berhasil diinputkan');
            } else {
                Kelompok::where('id', $req->kode)->update([
                    'nama' => $req->nama,
                    'ms_template_id' => $req->template 
                ]);

                Session::flash('success', 'Data berhasil diupdate');
            }
        } catch(\Exception $e) {
            Session::flash('error', 'Data gagal disimpan');
        }
        
        return redirect()->route('master::kelompok-link');
    }

    public function kelompokData() {
        $query = Kelompok::with('template')->select('ms_kelompok.*');
        return Datatables::of($query)
//                        ->editColumn('tgltu', function ($model) {
//                            if ($model->tgltu == '') {
//                                return 'Tidak Ada Tanggal';
//                            } else {
//                                return date('d-m-Y', strtotime($model->tgltu));
//                            }
//                        })
                        ->addColumn('menu', function ($model) {
                            $edit = '<a href="' . route('master::kelompok-link', ['recid' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                            return $edit;
                        })
                        ->make(true);
    }

    public function kelompokSelect($recid) {
        $aa = Komponen::where('ms_template_id', $recid)->get();
        $txt = '';
        foreach ($aa as $row) {
            $txt .= '<option value="' . $row->id . '">' . $row->nama . '</option>';
        }
        return response()->json(['data' => $txt]);
    }

    public function kelompokDetailLink($recid = null) {
        if ($recid == null)
            $data = '';
        else
            $data = KelompokDetail::with('kelompok')->find($recid);

        // dd($data);
        $kat = Kelompok::all();
        return view('pages.master.kelompokdetail', ['data' => $data, 'kelompok' => $kat]);
    }

    public function kelompokDetailSimpan(Request $req) {
        try {
            if ($req->tipe == 0) {
                $tmp = new KelompokDetail();
                $tmp->nama = $req->nama;
                $tmp->ms_kelompok_id = $req->kelompok;
                $tmp->save();

                Session::flash('success', 'Data berhasil diinputkan');
            } else {
                $tmp = KelompokDetail::find($req->kode);
                $smpn = [
                    'nama' => $req->nama,
                    'ms_kelompok_id' => $req->kelompok
                ];

                KelompokDetail::where('id', $req->kode)->update($smpn);

                Session::flash('success', 'Data berhasil diupdate');
            }
        } catch(\Exception $e) {
            Session::flash('error', 'Data gagal disimpan');
        }
        
        return redirect()->route('master::kelompokdetail-link');
    }

    public function kelompokDetailData() {
        $query = KelompokDetail::with('kelompok.template')->select('ms_kelompok_detail.*');
        // dd($query->get());
        return Datatables::of($query)
//                        ->editColumn('tgltu', function ($model) {
//                            if ($model->tgltu == '') {
//                                return 'Tidak Ada Tanggal';
//                            } else {
//                                return date('d-m-Y', strtotime($model->tgltu));
//                            }
//                        })
                        ->addColumn('menu', function ($model) {
                            $edit = '<a href="' . route('master::kelompokdetail-link', ['recid' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                            return $edit;
                        })
                        ->make(true);
    }

    public function kelompokDetailSelect($recid) {
        $aa = KelompokDetail::where('ms_kelompok_id', $recid)->get();
        $txt = '';
        foreach ($aa as $row) {
            $txt .= '<option value="' . $row->id . '">' . $row->nama . '</option>';
        }
        return response()->json(['data' => $txt]);
    }

    // 
    public function templateLink($recid = null) {
        $aset = Aset::select('aset.*', DB::raw("aset.nama_aset || ' # ' || instalasi.name as fullname"))
            ->join('instalasi', 'aset.instalasi_id', '=', 'instalasi.id')
            ->where('aset.equipment', 'no')
            ->where('kondisi_id', '<>', '12')
            ->get()->pluck('fullname', 'id')->toArray();
        $labelAset = ["" => "-             Pilih Aset             -"];
        $aset = $labelAset + $aset;     

        $sistem = Master::where('kelompok', 'SISTEM')->get()->pluck('name', 'id')->toArray();
        $labelSistem = ["" => "-            Pilih Sistem            -"];
        $sistem = $labelSistem + $sistem;

        $bagian = Master::bagian()->get()->pluck('name', 'id')->toArray();
        $labelBagian = ["" => "-            Pilih Bagian            -"];
        $bagian = $labelBagian + $bagian;

        if ($recid == null) {
            $data = '';
            $namaaset = '';
        } else {
            $data = Aset::find($recid);
            $namaaset = Aset::select(DB::raw("aset.nama_aset || ' # ' || instalasi.name as fullname"))
            ->join('instalasi', 'aset.instalasi_id', '=', 'instalasi.id')
            ->where('aset.id', $recid)->first()->fullname;
        }


        return view('pages.master.template', [
            'data' => $data,
            'aset' => $aset,
            'namaaset' => $namaaset,
            'sistem' => $sistem,
            'bagian' => $bagian
        ]);
    }

    public function templateSimpan(Request $req) {
        DB::beginTransaction();
// dd($req->all());
        try {
            if ($req->tipe==0) {
                Aset::whereIn('id', $req->id_aset)->update([
                    'equipment' => 'yes',
                    'sistem_id' => $req->sistem_id,
                    'bagian' => $req->bagian,
                    'kode_fm' => $req->kode_fm,
                    'availability' => isset($req->availability)?$req->availability:0
                ]);
            } else {
                Aset::where('id', $req->id)->update([
                    'equipment' => 'yes',
                    'sistem_id' => $req->sistem_id,
                    'bagian' => $req->bagian,
                    'kode_fm' => $req->kode_fm,
                    'availability' => isset($req->availability)?$req->availability:0
                ]);
            }

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(\Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }
        
        return redirect()->route('master::template-link');
    }

    public function templateData() {
        $query = Aset::select('aset.*')
                    ->with('sistem')
                    ->with('instalasi')
                    ->where('equipment', 'yes')
                    ->whereNull('equipment_id')
                    ->where('kondisi_id', '<>', '12');
        return Datatables::of($query)
//                        ->editColumn('tgltu', function ($model) {
//                            if ($model->tgltu == '') {
//                                return 'Tidak Ada Tanggal';
//                            } else {
//                                return date('d-m-Y', strtotime($model->tgltu));
//                            }
//                        })
                        ->addColumn('menu', function ($model) {
                            $edit = '<a href="' . route('master::template-link', ['recid' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                            $delete = '<a href="' . route('master::template-pindah', ['recid' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-trash"></i> Hapus </a>';
                            return $edit.$delete;
                        })
                        ->make(true);
    }

    public function templatePindah($recid)
    {
        $data = Aset::select('aset.*', DB::raw("aset.nama_aset || ' # ' || instalasi.name as asetlama"))
                ->join('instalasi', 'aset.instalasi_id', '=', 'instalasi.id')
                ->where('aset.id', $recid)
                ->first();
        
        $equipment = Aset::select('aset.*', DB::raw("aset.nama_aset || ' # ' || instalasi.name as fullname"))
                ->join('instalasi', 'aset.instalasi_id', '=', 'instalasi.id')
                // ->where('aset.equipment', 'yes')
                ->where('aset.kondisi_id', '<>', '12')
                ->where('aset.id', '<>', $recid)
                ->get()->pluck('fullname', 'id')->toArray();
        $labelEquipment = ["" => "-             Pilih Equipment Baru            -"];
        $equipment = $labelEquipment + $equipment;

        return view('pages.master.templatePindah', [
            'data' => $data,
            'aset' => $equipment
        ]);
    }

    public function templatePindahSimpan(Request $request) {
        DB::beginTransaction();
// dd($request->all());
        try {
            // remove dari equipment dan nonaktif equipment
            $data = Aset::where('id', $request->id)
                ->update([
                    'equipment' => 'no',
                    'kondisi_id' => '12'
                ]);

            // ubah aset baru menjadi equipment
            Aset::where('id', $request->asetbaru)
                ->update(['equipment' => 'yes']);

            // assign komponen2 ke equipment baru 
            Aset::where('equipment_id', $request->id)
                ->update(['equipment_id' => $request->asetbaru]);

            DB::commit();
            Session::flash('success', 'Data berhasil dihapus');
        } catch (Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal dihapus');
        }
        
        return redirect()->route('master::template-link');
    }

    public function komponenLink($recid = null) {
        DB::connection()->enableQueryLog();

        $currentKomponen = null;
        $temp = null;

        if ($recid == null) {
            $data = [];
            $namaequipment = '';
            $komponens = [];
        } else {
            $data = Aset::find($recid);
            // dd(count((array)$data));
            $komponens = Aset::where('equipment_id', $recid)->get();
// dd(count($komponens));
            if (count($komponens) > 0) {
                foreach ($komponens as $row) {
                    $temp[] = $row->id;
                }
                $currentKomponen = implode(",", $temp);
            }

            $namaequipment = Aset::select(DB::raw("aset.nama_aset || ' # ' || instalasi.name as fullname"))
                ->join('instalasi', 'aset.instalasi_id', '=', 'instalasi.id')
                ->where('aset.id', $recid)->first()->fullname;
        }
        
        $equipment = Aset::select('aset.*', DB::raw("aset.nama_aset || ' # ' || instalasi.name as fullname"))
                ->join('instalasi', 'aset.instalasi_id', '=', 'instalasi.id')
                ->where('aset.equipment', 'yes')
                ->where('aset.kondisi_id', '<>', '12')
                ->get()->pluck('fullname', 'id')->toArray();
        $labelEquipment = ["" => "-             Pilih Equipment             -"];
        $equipment = $labelEquipment + $equipment;

        $komponen = Aset::select('aset.*', DB::raw("aset.nama_aset || ' # ' || instalasi.name as fullname"))
                    ->join('instalasi', 'aset.instalasi_id', '=', 'instalasi.id')
                    // ->join('aset equipment', 'aset.equipment_id', '=', 'equipment.id')
                    // ->where('aset.equipment', '=', 'no')
                    ->where('aset.kondisi_id', '<>', '12')
                    ->whereNull('aset.equipment_id')
                    ->get()->pluck('fullname', 'id')->toArray();
        $labelKomponen = ["" => "-             Pilih Komponen             -"];
        $komponen = $labelKomponen + $komponen;

        $bagian = Master::where('kelompok', 'BAGIAN')->get()->pluck('name', 'id')->toArray();
        $labelBagian = ["" => "-            Pilih Bagian            -"];
        $bagian = $labelBagian + $bagian;

        return view('pages.master.komponen', [
            'data' => $data,
            'komponens' => $komponens,
            'komponen' => $komponen,
            'equipment' => $equipment,
            'namaequipment' => $namaequipment,
            'bagian' => $bagian,
            'currentKomponen' => $currentKomponen
        ]);
    }

    public function komponenSimpan(Request $req) {
        DB::beginTransaction();
        try {            
            $validator = Validator::make($req->all(), [
                'equipment_id' => 'required'
            ], ['equipment_id.required' => 'Data gagal disimpan. Anda belum mengisi Equipment']);

            if ($validator->fails()) {
                return redirect()
                        ->route('master::komponen-link')
                        ->withErrors($validator)
                        ->withInput();
            }

            // reset to null
            if (strlen($req->currentId) > 0) {
                $arrCur = explode(",", $req->currentId);

                Aset::whereIn('id', $arrCur)->update([
                    'equipment_id' => null,
                    'bagian' => null,
                    'kode_fm' => null,
                    'availability' => 0
                ]);
            }
// dd($req->komponen);
            if (is_array($req->komponen) && count($req->komponen) > 0) {
                foreach ($req->komponen as $row) {
                    if (isset($row['komponen_id'])) {
                        // perawatan rutin condition
                        /*$foo = Aset::find($row['komponen_id']);
                        if ($row['kode_fm'] != $foo->kode_fm) {
                            PrwrutinPdm::where('komponen_id', $row['komponen_id'])->delete();

                            $arrTmp = Prw52w::where('komponen_id', $row['komponen_id'])
                                ->get()
                                ->pluck('id')
                                ->toArray();
                            if (sizeof($arrTmp) > 0) {
                                Prw52w::destroy($arrTmp);
                            }
                        }*/

                        Aset::where('id', $row['komponen_id'])->update([
                            'equipment_id' => $req->equipment_id,
                            'bagian' => $row['bagian'],
                            'kode_fm' => $row['kode_fm'],
                            'availability' => isset($row['availability'])?$row['availability']:0
                        ]);
                    }
                }
            }

            DB::commit();

            Artisan::call('komponen:refresh', ['--wo' => 'monitoring']);
            Artisan::call('komponen:refresh', ['--wo' => 'prwrutin']);

            Session::flash('success', 'Data berhasil diupdate');
        } catch(\Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan.<br>'.$e->getMessage());
        }
        
        return redirect()->route('master::komponen-link');
    }

    public function komponenData() {
    	DB::connection()->enableQueryLog();
    	
        $query = Aset::select('aset.*',
                    DB::raw("(select a.nama_aset from aset a where a.id = aset.equipment_id) as p_equipment"))
                    ->with('bagiannya')
                    ->with('instalasi')
                    ->where('aset.kondisi_id', '<>', '12');
                    // ->whereNotNull('aset.equipment_id')
                    // ->orWhere('aset.equipment', 'yes')->get();
        // dd(DB::getQueryLog());
                    // dd($query);
        return Datatables::of($query)
//                        ->editColumn('tgltu', function ($model) {
//                            if ($model->tgltu == '') {
//                                return 'Tidak Ada Tanggal';
//                            } else {
//                                return date('d-m-Y', strtotime($model->tgltu));
//                            }
//                        })
                        ->addColumn('menu', function ($model) {
                            if (!$model->equipment_id) {
                                $edit = '<a href="' . route('master::komponen-link', ['recid' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                                return $edit;
                            }
                        })
                        ->make(true);
    }

    public function komponenSelect($recid) {
        $aa = Komponen::where('ms_template_id', $recid)->get();
        $txt = '';
        foreach ($aa as $row) {
            $txt .= '<option value="' . $row->id . '">' . $row->nama . '</option>';
        }
        return response()->json(['data' => $txt]);
    }

    public function komponenDetailLink($recid = null) {
        if ($recid == null)
            $data = [];
        else
            $data = KomponenDetail::with('komponen')->find($recid);


// dd(count($data));
        $kodefm = MasterFm::distinct('kode_fm')->get()->pluck('kode_fm', 'kode_fm')->toArray();
        $labelKodefm = ["" => "-             Pilih Kode Form             -"];
        $kodefm = $labelKodefm + $kodefm;

        // dd($data);
        $attribut = [];
        $kat = Komponen::all();

        return view('pages.master.komponendetail', ['data' => $data, 
            'komponen' => $kat,
            'kodefm' => $kodefm,
            'attribut' => $attribut
        ]);
    }

    public function komponenDetailSimpan(Request $req) {
        try {
            DB::beginTransaction();

            $arrAttribut = [];
            if (is_array($req->komponen) && count($req->komponen) > 0) {
                $i = 0;
                foreach ($req->komponen as $row) {
                	if (isset($row['id'])) {
                		// Edit Previous Data
                		DB::table('ms_komponen_detail')->where('id', $row['id'])
                			->update($row);
                	} else {
                		// Insert New Data
                		$arrAttribut[$i] = $row;
	                    $arrAttribut[$i]['kode_fm'] = $req->kode_fm;

	                    $i++;
                	}
                }

                $smpn = DB::table('ms_komponen_detail')->insert($arrAttribut);
                DB::commit();
            }

            Session::flash('success', 'Data berhasil diinputkan');
        } catch(\Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }
        return redirect()->route('master::komponendetail-link');
    }

    public function komponenDetailData() {
        // $query = SubSubKategori::with('subkategori.kategori')->select('sub_sub_kategori.id', 'sub_sub_kategori.kode', 'sub_sub_kategori.name', 'sub_sub_kategori.sub_kategori_id');
        $query = KomponenDetail::with('komponen.template')->select('ms_komponen_detail.*');
        // dd($query->get());
        return Datatables::of($query)
//                        ->editColumn('tgltu', function ($model) {
//                            if ($model->tgltu == '') {
//                                return 'Tidak Ada Tanggal';
//                            } else {
//                                return date('d-m-Y', strtotime($model->tgltu));
//                            }
//                        })
                        ->addColumn('menu', function ($model) {
                            $edit = '<a href="' . route('master::komponendetail-link', ['recid' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                            return $edit;
                        })
                        ->make(true);
    }

    public function komponenDetailSelect($kodefm) {
        $data = KomponenDetail::where('kode_fm', $kodefm)->get();
        
        return view('pages.master.partdetailkomponen', [
        		'data' => $data
        ])->render();
    }

    public function sistemLink($recid = null) {
        if ($recid == null)
            $data = '';
        else
            $data = Master::find($recid);

        // dd($data);
        return view('pages.master.sistem', ['data' => $data]);
    }

    public function sistemSimpan(Request $req) {
        try {
            if ($req->tipe == 0) {
                $nomor = (int)Master::sistem()->max('kode') + 1;

                $tmp = new Master();
                $tmp->kode = $nomor;
                $tmp->name = $req->name;
                $tmp->kelompok = 'SISTEM';
                $tmp->save();

                Session::flash('success', 'Data berhasil diinputkan');
            } else {
                Master::where('id', $req->kode)->update([
                    'name' => $req->name
                ]);

                Session::flash('success', 'Data berhasil diupdate');
            }
        } catch(\Exception $e) {
            Session::flash('error', 'Data gagal disimpan '.$e->getMessage());
        }

        return redirect()->route('master::sistem-link');
    }

    public function sistemData()
    {
        $query = Master::sistem()
            ->select('id', 'kode', 'name', 'kelompok');

        return Datatables::of($query)
                ->addColumn('menu', function ($model) {
                    $edit = '<a href="' . route('master::sistem-link', ['recid' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                    return $edit;
                })
                ->make(true);
    }
}
