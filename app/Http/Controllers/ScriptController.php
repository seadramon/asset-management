<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;

use Asset\Models\Ms4w;
use Asset\Models\MasterFm;

use DB;
use Datatables;
use Session;
use Validator;

class ScriptController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /*$data = Ms4w::whereNull('tanggal_monitoring')->get();
        dd(count($data));*/

        $data = MasterFm::where('kode_fm', 'E5')->get();
        dd($data);
    }
}
