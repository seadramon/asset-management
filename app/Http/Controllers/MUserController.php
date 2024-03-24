<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\User,
    Asset\Models\Role,
    Asset\Models\RoleUser;

use DB;
use Datatables;
use Session;
use Validator;

class MUserController extends Controller
{
    public function index()
    {
        $data = '';
        return view('pages.manajemenuser.user.index', [
            'data' => $data,
            'TAG'=>'m_user',
            'TAG2'=>'mnjuser'
        ]);
    }

    public function userData()
    {
        $query = User::with('rolebaru')
            ->select('usrtab.*');
// dd($query->get());
        return Datatables::of($query)
                ->addColumn('role', function ($model) {
                    $html = '';
                    foreach ($model->rolebaru as $row) {
                        $html.=$row->name;
                    }

                    return $html;
                })->addColumn('menu', function ($model) {
                    $edit = '<a href="' . route('mnjuser::mnjuser-entri', ['id' => $model->userid]) . '" class="btn btn-xs purple"><i class="fa fa-edit"></i> Edit </a>';
                    return $edit;
                })
                ->make(true);
    }

    public function entri($id = null)
    {
        $data = null;

        if ($id != null) {
            $data = RoleUser::where('user_id', $id)->first();
        }

        $role = Role::get()->pluck('name', 'id')->toArray();
        $labelRole = ["" => "-             Pilih Role             -"];
        $role = $labelRole + $role;

        $user = User::where('userid', $id)->first();        

        return view('pages.manajemenuser.user.entri', [
            'data' => $data,
            'role' => $role,
            'user' => $user,
        ]);
    }

    public function simpan(Request $request)
    {
        DB::beginTransaction();

        try {
            if ($request->tipe == 0) {
                $data = RoleUser::insert([
                    'user_id' => $request->user_id,
                    'role_id' => $request->role_id
                ]);
            } else {
                /*$data = Role::where('id', $request->id)
                    ->update(['ru_role.role' => $request->role]);*/
                $data = RoleUser::find($request->id);
                $data->user_id = $request->user_id;
                $data->role_id = $request->role_id;

                $data->save();
            }

            DB::commit();
            Session::flash('success', 'Data berhasil disimpan');
        } catch(Exception $e) {
            DB::rollback();
            Session::flash('error', 'Data gagal disimpan');
        }

        return redirect()->route('mnjuser::mnjuser-index');
    }
}
