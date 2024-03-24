<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Datatables;
use Image;
use DB;
use Storage;
use Hash;
use Auth;
use Excel;
use File;
use URL;
use Response;
use Validator;
use Asset\Models\Petugas;
use Asset\Models\Team;
use Asset\Models\Role;
use Asset\Models\User;
use Asset\Models\RolesUser;
use Asset\Models\RoleMenu;
use Asset\Models\Menu;
use Asset\Models\UserTab;

class UserController extends Controller {

    
    public function dataPetugasLink() {
        return view('petugas.datapetugas');
    }

    public function dataPetugasJson() {
        $query = Petugas::with('user_login')->select('*')->where('active','T');

        return Datatables::of($query)
                        ->editColumn('user_login.username', function($model) {
                            if($model->user_login == null){
                                return '<span class="m-badge m-badge--brand m-badge--wide">
                                            Not Found
                                        </span>' ;
                            }else{
                                return $model->user_login->username;
                            }
                        })
                        ->editColumn('alamat', function($model) {
                            return $model->alamat . '~ ' . $model->gang . '~ ' . $model->nomor . '~ ' . $model->notamb;
                        })
                        ->addColumn('menu', function($model) {
                            $edit = '<a href="Edit-DataPetugas/'.$model->kdcater.'" class="btn btn-sm btn-warning margin-bottom-5" style="color:#fff;">edit</a>';
                            $checkLogin = User::SinglePetugasCater($model->kdcater)->first();
                            if($checkLogin == null){
                                $manage = '<a href="SetUpLogin-DataUserLogin/'.$model->kdcater.'" class="btn btn-sm btn-primary margin-bottom-5">Setting User Login</a>';
                            }else{
                                $manage = '<a href="ResetPassword-DataUserLogin/'.$model->kdcater.'" 
                                        class="btn btn-sm btn-success margin-bottom-5">Reset Password</a>';
                            }
                            $delete = '<a href="Delete-DataPetugas/'.$model->kdcater.'" onClick="return confirm(\'Apakah anda yakin akan menghapus data ini ?\')" class="btn btn-sm btn-danger margin-bottom-5" style="color:#fff;">delete</a>';
                            
                            return $edit.'&nbsp'.$delete.'&nbsp'.$manage;
                        })
                        ->make(true);
    }

    public function add(){
        // $team = Team::all();
        $roles = Role::all();
        return view('petugas.add_datapetugas', ['roles' => $roles]);
    }

    public function post(Request $request){

        $rules = [
            // 'kdcater' => 'required',
            'namacater' => 'required',
            // 'role' => 'required',
            // 'kdteam' => 'required',
            // 'kdarea' => 'required',
            // 'kdwil' => 'required',
            // 'imei' => 'required|numeric'
        ];

        $messages = [
            // 'kdcater.required' => 'Kode Cater harus diisi',
            'namacater.required' => 'Nama harus disi',
            // 'role.required' => 'role petugas harus diisi',
            // 'kdteam.required' => 'Kode team harus diisi',
            // 'kdarea.required' => 'Kode area harus diisi',
            // 'kdwil.required' => 'Kode Wilayah harus diisi',
            // 'imei.required' => 'Imei harus diisi',
            // 'imei.numeric' => 'Imei harus angka'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator)->with(array('status' => 'Data kurang lengkap', 'alert' => 'danger')); 
        }else{
            $namacater = $request->namacater;
            $role = $request->role;
            $username = $request->username;
            $pass_1 = $request->pass_1;
            $pass_2 = $request->pass_2;
          
            // ADD & EDIT
            if($request->action_form == 'edit'){
                $petugas = Petugas::SingleCater($request->kdcater)->first();
            }else if($request->action_form == 'add'){
                $petugas = new Petugas();
                $user_login = new User();
            }else{
                return redirect()->route('datapetugas-link')->with(array('status' => 'Error !', 'alert' => 'danger')); 
            }

            // Apabila post Edit Data Petugas (Mobile)
            if(empty($request->role)){
                $kdcater = $request->kdcater;
                // $kdteam = $request->kdteam;
                $kdarea = $request->kdarea;
                $kdwil = $request->kdwil;
                $imei = $request->imei;

                // post to database table t_cater
                $petugas->kdcater = $kdcater;
                $petugas->nama = $namacater;
                // $petugas->kdteam = $kdteam;
                $petugas->kdarea = $kdarea;
                $petugas->kdwil = $kdwil;
                $petugas->imei = $imei;
                $petugas->save();

            }else if(in_array(1, $request->role)){ // apabila Post Role Memilih petugas cater juga (lebih dari 1 role)
                // variabel khusus cater
                $cc = (Petugas::count()) + 1;

                $kdcater = date('Y').$this->createRegNumber($cc,4);
                // $kdteam = $request->kdteam;
                $kdarea = $request->kdarea;
                $kdwil = $request->kdwil;
                $imei = $request->imei;

                // post to database table t_cater
                $petugas->kdcater = $kdcater;
                $petugas->nama = $namacater;
                // $petugas->kdteam = $kdteam;
                $petugas->kdarea = $kdarea;
                $petugas->kdwil = $kdwil;
                $petugas->imei = $imei;

                // post to database table user
                $user_login->username = $username;
                $user_login->nama = $namacater;
                $user_login->kdcater = $kdcater;
                $this->CheckingMatchPassword($pass_1,$pass_2);
                $user_login->password = Hash::make($pass_1);
                
                $petugas->save();
                $user_login->save();
               
            }else{
                // post to database    
                $user_login->nama = $namacater;
                $user_login->username = $username;
                $this->CheckingMatchPassword($pass_1,$pass_2);
                $user_login->password = Hash::make($pass_1);
                $user_login->save();

            }

            if($request->action_form == 'add'){ // post to RolesUser dengan asumsi lebih dari 1 role
                if(count($role)>0){
                    foreach($role as $row){
                        $dataRole = new RolesUser();
                        $dataRole->user_id = $user_login->id;
                        $dataRole->role_id = $row;
                        $dataRole->save();
                    }
                }
                // tidak ada else karena hanya untuk edit petugas android
            }

            return redirect()->route('datapetugas-link')->with(array('status' => 'Data berhasil diinputkan / diperbarui', 'alert' => 'success'));

        } // end of else
    }

    function createRegNumber($number, $maxLength){
        $numberLength = strlen($number);
        $zeroLength = $maxLength - $numberLength;
        $zero = "";
        for($i = 1 ; $i <= $zeroLength ; $i++){
            $zero .= "0";
        }
        return $zero.$number;
    }

    // create user login
    function CheckingMatchPassword($pass_1,$pass_2){
        if($pass_1 != $pass_2){
            return redirect()->route('datapetugas-link')->with(array('status' => 'Error ! Password Tidak Sama', 'alert' => 'danger'));
        }
    }

    // untuk edit role
    function CheckingPostRoles($user_id,$role){
        foreach($role as $row){
            $data = RolesUser::Single($user_id,$row)->first();
            $data->user_id = $user_id;
            $data->role_id = $row;
            $data->save();
        }
    }

    public function edit($kdcater){
        $data = Petugas::find($kdcater);
        $user = User::SinglePetugasCater($kdcater)->first();
        // $team = Team::all();
        return view('petugas.edit_datapetugas', ['data' => $data, 'user' => $user]);
    }
    
    public function delete($kdcater){
        $data  = Petugas::find($kdcater);
        $data->active = 'F';
        $data->save();

        User::SinglePetugasCater($kdcater)->delete();

        return redirect()->route('datapetugas-link')->with(array('status' => 'Data berhasil dihapus ! (softdelete)', 'alert' => 'warning')); 
    }

    public function reset_password($kdcater){
        $user = User::SinglePetugasCater($kdcater)->first();
        $user->password = Hash::make('12345');
        $user->save();
        return redirect()->route('datapetugas-link')->with(array('status' => 'Password berhasil direset ke "12345" !!', 'alert' => 'warning')); 
    }


    public function pquery(){
        $data = Petugas::with('team')->find('YRS001');
        return Response::json($data);
    }

// Kategori User
    public function dataKategoriPetugasLink(){
        return view('petugas.kategori.datakategori');
    }

    public function dataKategoriPetugasJson() {
        $query = Role::select('*');

        return Datatables::of($query)
                        ->addColumn('menu', function($model) {
                            $edit = '<a href="Edit-DataKategoriPetugas/'.$model->id.'" 
                                class="btn btn-primary margin-bottom-5"><i class="fa fa-gear"></i>Setting Zona</a>';
                            return $edit;
                        })
                        ->make(true);
    }

    public function kategoriedit($id){
        $data = Role::find($id);
        $zona = explode(",",$data->zona);
        return view('petugas.kategori.edit_datakategori', ['data' => $data,'zona'=>$zona]);

    }

    public function kategoripost(Request $request){

        $rules = [
            'role' => 'required',
        ];

        $messages = [
            'role.required' => 'Kategori harus di isi',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator)->with(array('status' => 'Data kurang lengkap', 'alert' => 'danger')); 

        }else{
            $id = $request->id;
            $role = $request->role;
            $zona = $request->zona;
            $device = $request->device;

            if($request->action_form == 'edit'){
                $kategori = Role::find($id);
            }else if($request->action_form == 'add'){
				$cek = Role::where('kode', '<>', 99)->orderBy('kode','desc')->first();
                $kategori = new Role();
				$kategori->kode = $cek->kode + 10;
            }else{
                return redirect()->route('datakategoripetugas-link')->with(array('status' => 'Error !', 'alert' => 'danger')); 
            }

            $kategori->role = $role;
            $kategori->device = $device;
            if($zona){
                $kategori->zona = implode(',', $zona);
            }
            $kategori->save();

            return redirect()->route('datakategoripetugas-link')->with(array('status' => 'Data berhasil diinputkan', 'alert' => 'success')); 

        }
        
    }
// End of Kategori


// Data User
    public function dataUserLink() {
        return view('petugas.user.datauser');
    }

    public function dataUserJson() {
        $query = UserTab::select('*');

        return Datatables::of($query)
                        ->addColumn('role', function($model) {
                            $ff = null;
                            foreach($model->roles as $role){
                                $ff .= '- '.$role->role.'<br>'; 
                            }
                            return $ff;
                        })
                        ->addColumn('menu', function($model) {
                            $edit = '<a href="View-DataUser/'.$model->userid.'"" class="btn btn-sm btn-primary margin-bottom-5">Setting Roles</a>';
                            // $setup = '<a href="View-DataUser/'.$model->userid.'"" class="btn btn-sm btn-primary margin-bottom-5">edit</a>';
                            if($model->username == 'admin'){
                                $delete = null;
                            }else{
                                $delete = '<a href="Del-DataUser/'.$model->userid.'"
                                onClick="return confirm(\'Menghapus User ini berarti anda juga akan menghapus semua hak askes menu dan kategori petugas ini !! Apakah anda yakin ?\')" 
                                class="btn btn-sm btn-danger margin-bottom-5">delete</a>';
                            }
                            
                            return $edit;
                        })
                        ->make(true);
    }

    public function deleteDataUser($id){
        DB::transaction(function() use ($id){
            User::find($id)->delete();
            RolesUser::SingleUser($id)->delete();
        });
        return redirect()->route('datauser-link')->with(array('status' => 'Data berhasil dihapus !', 'alert' => 'success')); 
    }

    public function viewDataUser($id){        
        $data = UserTab::with('roles')
                    ->select(DB::raw('TRIM(userid) as userid'), 'username')
                    ->where(DB::raw('TRIM(userid)'),$id)->first();
        $roles = Role::whereHas('roleuser',function($sql) use ($data){
            $sql->where('user_id',$data->userid);
        },'=',0)->get();

        // foreach($data->roles as $row){
        //     $role_user = $row->role;
        // }
        //return response()->json($roles);
        return view('petugas.user.edit_userlogin', ['data' => $data, 'roles' => $roles]);
    }

    public function addroleslogin(Request $request){
        $role = $request->role;
        $userid = $request->userid;
        if(count($role)>0){
            RolesUser::SingleUser($userid)->delete();
            foreach($role as $row){
                $ss = RolesUser::Single($userid,$row)->first();
                if($ss == null){
                    $dataRole = new RolesUser();
                    $dataRole->user_id = $userid;
                    $dataRole->role_id = $row;
                    $dataRole->save();
                }
               
            }
        }
        return redirect()->route('datauser-link')->with(array('status' => 'Berhasil menambahkan role !', 'alert' => 'success')); 
    }

    public function manageDataUserLogin($kdcater){
        $data =  User::SinglePetugasCater($kdcater)->first();
        $petugas = Petugas::find($kdcater);
        $roles = Role::all();
        return view('petugas.user.edit_userlogin', ['data' => $data, 'petugas' => $petugas, 'roles' => $roles]);
    }

    public function SetUpLoginDataUserLogin($kdcater){
        return view('petugas.user.set_login',['kdcater' => $kdcater]);
    }

    public function postEditDataUser(Request $request){
        $user_id = $request->id;
        $user_login =  User::find($user_id);
        $user_login->nama = $request->nama;
        $user_login->username = $request->username;

        if(count($request->role)>0){
            RolesUser::SingleUser($user_id)->delete();
            foreach($request->role as $row){
                $dataRole = new RolesUser();
                $dataRole->user_id = $user_login->id;
                $dataRole->role_id = $row;
                $dataRole->save();
            }
        }

        if($request->pass_1 == ''){
            $user_login->save();
            return redirect()->route('datauser-link')->with(array('status' => 'Berhasil memperbarui data petugas tanpa mengganti password anda', 'alert' => 'success')); 
        }else{
            $this->CheckingMatchPassword($request->pass_1,$request->pass_2);
            $user_login->password = Hash::make($request->pass_1);
            $user_login->save();
            
            return redirect()->route('datauser-link')->with(array('status' => 'Berhasil memperbarui data petugas dan mengganti password anda', 'alert' => 'success'));
        }

        

    }

    public function postDataUserLogin(Request $request){
        $user_login = new User();
        $user_login->nama = $request->nama;
        $user_login->username = $request->username;
        $user_login->kdcater = $request->kdcater;

        if($request->pass_1 != ''){
            $this->CheckingMatchPassword($request->pass_1,$request->pass_2);
            $user_login->password = Hash::make($request->pass_1);
            $user_login->save();
    
            $dataRole = new RolesUser();
            $dataRole->user_id = $user_login->id;
            $dataRole->role_id = $request->role;
            $dataRole->save();
            return redirect()->route('datapetugas-link')->with(array('status' => 'Berhasil menambahakan login petugas', 'alert' => 'success')); 
        }else{
            return redirect()->route('datapetugas-link')->with(array('status' => 'Gagal ! Password harus diisi', 'alert' => 'danger')); 
        }
    }

// End of Data User

    public function datarolemenus(){
		$data = '';
        return view('pages.user.rolemenu',[
            'data'=>$data,
            'TAG'=>'m_user',
            'TAG2'=>'rolemenus']);
    }

    public function datarolemenus_json(){
        $query = Role::select('*');

        return Datatables::of($query)
                        ->editColumn('name', function($model) {
                            $edit = '<a href="View-DataRoleMenus/role='.$model->id.'" 
                            class="btn btn-sm btn-primary margin-bottom-5" style="color:#fff;"><i class="fa fa-gears"></i> Setting</a>';

                            return $model->name.'<br>'.$edit;
                        })
                        ->addColumn('menus', function($model) {
                            $ff = null;
                            foreach($model->menus as $menu){
                                $btn = '<a class="pull-right" href="Del-DataRoleMenus/role='.$model->id.'/menu='.$menu->id.'" onClick="return confirm(\' Apakah anda yakin akan menghapus data ini ??\')"><i class="fa fa-times text-danger"></i></a>';
                                $btn_header = '<a class="pull-right btn btn-primary btn-sm" href="Del-DataRoleHeadMenus/role='.$model->id.'/menu='.$menu->id.'" 
                                        onClick="return confirm(\' Apakah anda yakin akan menghapus data ini ?? Menghapus menu header berarti anda akan menghapus semua sub menu nya !!\')">
                                        Hard Delete</a>';
                                $btn_sub_header = '<a class="pull-right btn btn-warning btn-sm m--font-light" href="Del-DataRoleSubHeadMenus/role='.$model->id.'/menu='.$menu->id.'" 
                                        onClick="return confirm(\' Apakah anda yakin akan menghapus data ini ?? Menghapus menu header berarti anda akan menghapus semua sub menu nya !!\')">
                                        Sub Head Delete</a>';
                                
                                if($menu->tipe == 3){
                                    $ff .= '<br><b>'.$menu->nama.'</b>'.$btn_sub_header.'</br>';
                                }else if($menu->tipe == 1){
                                    $ff .= '<br><b>'.$menu->nama.'</b>'.$btn_header.'</br>';
                                }
                                else{
                                    $ff .= '<br>- '.$menu->nama.' '.$btn.'</br>'; 
                                }
                                
                            }
                            return $ff;
                        })
                        ->addColumn('action', function($model) {
                            $delete = '<a href="Del-DataRoleAllMenus/role='.$model->id.'" 
                            onClick="return confirm(\'Apakah anda yakin akan menghapus data menu untuk kategori petugas ini ?\')" 
                            class="btn btn-sm btn-danger margin-bottom-5" style="color:#fff;"><i class="fa fa-times"></i> Hapus Semua Menu</a>';
                            return $delete;
                        })
                        ->make(true);
    }


    public function add_datarolemenus(){
        $role = Role::all();
        $single_menu = Menu::SingleMenu()->get();
        $head_menu = Menu::HeadMenu()->get();
        $sub_head = Menu::SubHead()->get();
        $subhead_menu = Menu::SubHeadMenu()->get();
        $sub_menu = Menu::SubMenu()->get();
        return view('petugas.menu.add_rolemenus' , ['role' => $role,'single_menu' => $single_menu ,'head_menu' => $head_menu ,'sub_head' => $sub_head,'subhead_menu' => $subhead_menu,'sub_menu' =>$sub_menu]);
    }

    public function post_datarolemenus(Request $request){
        $role = $request->role;
        $menu = $request->menu;
        foreach($menu as $row){

            $aa = explode('||',$row);
            $head_menu = $aa[0];
            $sub_menu = $aa[1];

            // head menu
            $checking_data = RoleMenu::SingleMenuRole($role,$head_menu)->get();
            if(count($checking_data) == 0){
                $data = new RoleMenu();
                $data->menu_id = $head_menu;
                $data->role_id = $role;
                $data->save();
            }

            // submenu
            $checking_data_ = RoleMenu::SingleMenuRole($role,$sub_menu)->get();
            if(count($checking_data_) == 0){
                $data = new RoleMenu();
                $data->menu_id = $sub_menu;
                $data->role_id = $role;
                $data->save();
            }

        }   
        return redirect()->route('datarolemenus-link')->with(array('status' => 'Berhasil menambahakan menu untuk role terpilih', 'alert' => 'success')); 
    }

    public function del_dataroleallmenus($role){   
        $data = RoleMenu::RoleMenus($role)->delete();
        return redirect()->back()->with(array('status' => 'Berhasil menghapus semua menu terpilih', 'alert' => 'warning')); 
    }

    public function del_datarolemenus($role,$menu){   
        $data = RoleMenu::SingleMenuRole($role,$menu)->delete();
        return redirect()->back()->with(array('status' => 'Berhasil menghapus menu terpilih', 'alert' => 'warning')); 
    }

    public function del_dataroleheadmenus($role,$menu){
        DB::transaction(function() use ($role, &$menu){
            $data = RoleMenu::SingleMenuRole($role,$menu)->delete(); // delete single submenu
            $head_urut_menu = Menu::find($menu);
            $submenu = Menu::SelectSubMenu(substr($head_urut_menu->urut,0,1))->lists('id');
            RoleMenu::where('role_id',$role)->whereIn('menu_id',$submenu)->delete();
        });
        return redirect()->back()->with(array('status' => 'Berhasil menghapus head menu terpilih', 'alert' => 'success')); 
    }

    public function del_datarolesubheadmenus($role,$menu){
        DB::transaction(function() use ($role, &$menu){
            $data = RoleMenu::SingleMenuRole($role,$menu)->delete(); // delete single submenu
            $head_urut_menu = Menu::find($menu);
            $submenu = Menu::SelectSubMenu(substr($head_urut_menu->urut,0,2))->lists('id');
            RoleMenu::where('role_id',$role)->whereIn('menu_id',$submenu)->delete();
        });
        return redirect()->back()->with(array('status' => 'Berhasil menghapus sub head menu terpilih', 'alert' => 'success')); 
    }

    public function view_datarolemenus($role){
        $role = Role::find($role);
        $single_menu = Menu::SingleMenu()->get();
        $head_menu = Menu::HeadMenu()->get();
        $sub_head = Menu::SubHead()->get();
        $subhead_menu = Menu::SubHeadMenu()->get();
        $sub_menu = Menu::SubMenu()->get();

        // $data = Role::with('menus')->where('id',$role)->first();
        // $menu = Menu::whereHas('roles',function($sql) use ($data){
        //     $sql->where('id',$data->id);
        // },'=',0)->get();

        // return response()->json($data); 
        return view('pages.user.view_rolemenus' , ['role' => $role,'single_menu' => $single_menu ,'head_menu' => $head_menu ,'sub_head' => $sub_head,'subhead_menu' => $subhead_menu,'sub_menu' =>$sub_menu]);
    }

    // Manage Roles
    public function dataroles(){
        return view('petugas.role.dataroles');
    }

    public function dataroles_json(){
        $query = Role::select('*');

        return Datatables::of($query)
                        ->addColumn('menus', function($model) {
                            $ff = null;
                            foreach($model->menus as $menu){
                                $btn = '<a class="pull-right" href="Del-DataRoleMenus/role='.$model->id.'/menu='.$menu->id.'" onClick="return confirm(\' Apakah anda yakin akan menghapus data ini ??\')"><i class="fa fa-times text-danger"></i></a>';
                                if($menu->tipe == 1){
                                    $ff .= '<br><b>'.$menu->nama.'</b></br>';
                                }else{
                                    $ff .= '<br>- '.$menu->nama.' '.$btn.'</br>'; 
                                }
                                
                            }
                            return $ff;
                        })
                        ->addColumn('action', function($model) {
                            $edit = '<a href="View-DataRoleMenus/role='.$model->id.'" class="btn btn-sm btn-success margin-bottom-5" style="color:#fff;"><i class="fa fa-gears"></i> Setting</a>';
                            //$delete = '<a href="Delete-DataPetugas/'.$model->kdcater.'" 
                            // onClick="return confirm(\'Apakah anda yakin akan menghapus data ini ?\')" 
                            // class="btn btn-sm btn-danger margin-bottom-5" style="color:#fff;">delete</a>';
                            // return $edit;
                        })
                        ->make(true);
    }

    public function dataroles_add(){
        return view('petugas.role.add_datarole');
    }
}
