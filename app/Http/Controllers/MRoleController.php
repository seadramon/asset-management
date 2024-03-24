<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Role;

use DB;
use Datatables;
use Session;
use Validator;

class MRoleController extends Controller
{
    public function index()
    {
        $data = '';
        return view('pages.manajemenuser.role.index', [
            'data' => $data,
            'TAG'=>'m_user',
            'TAG2'=>'mnjrole'
        ]);
    }

    public function roleData()
    {
        $query = Role::select('ru_role.*');
// dd($query->get());
        return Datatables::of($query)
                ->addColumn('menu', function ($model) {
                    $edit = '<a href="' . route('mnjrole::mnjrole-entri', ['id' => $model->id]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                    return $edit;
                })
                ->make(true);
    }

    public function entri($id = null)
    {
        $data = null;

        if ($id != null) {
            $data = Role::find($id);
        }

        return view('pages.manajemenuser.role.entri', [
            'data' => $data
        ]);
    }

    public function simpan(Request $request)
    {
        DB::beginTransaction();

        try {
            if ($request->tipe == 0) {
                $data = Role::insert([
                    'name' => $request->name,
                    'can_add' => !empty($request->can_add)?$request->can_add:"Y",
                    'can_edit' => !empty($request->can_edit)?$request->can_edit:"Y",
                    'can_delete' => !empty($request->can_delete)?$request->can_delete:"Y",
                ]);
            } else {
                /*$data = Role::where('id', $request->id)
                    ->update(['ru_role.role' => $request->role]);*/
                $data = Role::find($request->id);
                $data->name = $request->name;

                if (!empty($request->can_add)) $data->can_add = $request->can_add;
                if (!empty($request->can_edit)) $data->can_edit = $request->can_edit;
                if (!empty($request->can_delete)) $data->can_delete = $request->can_delete;

                $data->save();
            }

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('mnjrole::mnjrole-index');
    }
}
