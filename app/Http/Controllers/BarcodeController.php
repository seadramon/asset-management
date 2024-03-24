<?php

namespace Asset\Http\Controllers;

use Illuminate\Http\Request;

use Asset\Http\Requests;
use Asset\Http\Controllers\Controller;
use Datatables;
use Asset\Models\Aset,
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
use Excel;

class BarcodeController extends Controller
{
    public function index(Request $request)
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
        
        return view('pages.barcode.index',[
            'TAG' => 'aset',
            'TAG2' => 'data',
            'instalasi' => $instalasi,
            'lokasi' => $lokasi,
            'ruang' => $ruang,
            'bagian' => $bagian,
            'kategori' => $kategori,
            'subkategori' => $subKategori,
            'subsubkategori' => $subSubKategori,
            'kondisi' => $kondisi,]
        );
    }

    public function data(Request $request)
    {
    	$query = Aset::with('kategori', 'subkategori', 'subsubkategori', 'instalasi', 'lokasi', 'ruangan', 'type', 'kondisi')
                ->select('aset.id', 'aset.kode_aset', 'aset.nama_aset', 'aset.kategori_id', 'aset.sub_kategori_id', 'aset.sub_sub_kategori_id', 'aset.instalasi_id', 'aset.lokasi_id', 'aset.ruang_id', 'aset.jenis_id', 'aset.kondisi_id', 'aset.availability');

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
            $query->where('aset.nama_aset', 'like', '%'.$request->nama_aset.'%');
        }

        if ($request->kode_aset) {
            $query->where('aset.kode_aset', $request->kode_aset);
        }

        if ($request->spesifikasi) {
            $query->where('aset.spesifikasi', 'like', '%'.$request->spesifikasi.'%');
        }
        // end:filter
        // 
        return Datatables::of($query)->addColumn('subsubkategori_name', function($m) {
            return $m->subsubkategori ? $m->subsubkategori->name : '-';
        })
        ->addColumn('ruangan_name', function($m) {
            return $m->ruangan ? $m->ruangan->name : '-';
        })
        ->addColumn('select_orders', static function ($row) {
            return '<input type="checkbox" name="download_barcode" value="'.$row->id.'"/>';
        })
        ->make(true);
    }

    public function download($strid)
    {   
        $arrData = [];
        $arrId = explode(",", $strid);

        $data = Aset::with('kategori', 'subkategori', 'subsubkategori', 'instalasi', 'lokasi', 'ruangan', 'type', 'kondisi', 'bagiannya')
        ->whereIn('id', $arrId)
        ->get();

        $i = 1;
        foreach ($data as $row) {
            $arrData[] = [
                'no' => $i,
                'kode_barcode' => $row->kode_barcode,
                'nama' => $row->nama_aset,
                'kode_aset' => $row->kode_aset,
                'bagian' => $row->bagiannya? $row->bagiannya->name : '-',
                'kategori' => $row->kategori? $row->kategori->name : '-',
                'subkategori' => $row->subkategori? $row->subkategori->name : '-',
                'subsubkategori' => $row->subsubkategori? $row->subsubkategori->name : '-',
                'no_aktiva' => $row->aktiva,
                'no_spk' => $row->no_spk,
                'no_spmu' => $row->no_spmu,
                'no_bk' => $row->no_bk,
                'tahun_pasang' => $row->tahun_pasang,
                'instalasi' => $row->instalasi ? $row->instalasi->name : '-',
                'lokasi' => $row->lokasi ? $row->lokasi->name : '-',
                'ruangan' => $row->ruangan ? $row->ruangan->name : '-'
            ];
        }
// dd($arrData);
        Excel::create("Barcode", function($excel) use($arrData) {
            $excel->sheet('first', function($sheet) use($arrData) {
                $sheet->fromArray($arrData);
            });
        })->download('xlsx');
    }
}
