<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Menu;

use DB;
use Datatables;
use Session;
use Validator;

class MMenuController extends Controller
{
    public function index()
    {
        $data = '';
        return view('pages.manajemenuser.menu.index', [
            'data' => $data,
            'TAG' => 'm_user',
            'TAG2' => 'menus'
        ]);
    }

    public function menuData()
    {
        $query = Menu::orderBy('urut');
// dd($query->get());
        return Datatables::of($query)
                ->addColumn('menu', function ($model) {
                    $edit = '<a href="' . route('mnjmenu::mnjmenu-entri', ['id' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                    return $edit;
                })
                ->make(true);
    }

    public function entri($id = "")
    {
        $data = null;
        $aset = null;

        if ($id != "") {
            $data = Menu::find($id);
        }

        $tipe = ["" => "- Pilih Tipe -",
            "0" => "0 - Single",
            "1" => "1 - Parent",
            "2" => "2 - Child",
            "3" => "3 - Child lv 2"
        ];

        return view('pages.manajemenuser.menu.entri', [
            'data' => $data,
            'tipe' => $tipe
        ]);
    }

    public function simpan(Request $request)
    {
        DB::beginTransaction();

        try {
            if ($request->tipeform == 0) {
                $data = Menu::insert([
                    'nama' => $request->nama,
                    'url' => $request->url,
                    'icon' => $request->icon,
                    'tipe' => $request->tipe,
                    'urut' => $request->urut
                ]);
            } else {
                // dd($request->all());
                $data = Menu::where('id', $request->id)
                    ->update([
                        'nama' => $request->nama,
                        'url' => $request->url,
                        'icon' => $request->icon,
                        'tipe' => $request->tipe,
                        'urut' => $request->urut
                    ]);
            }

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('mnjmenu::mnjmenu-index');
    }
}
