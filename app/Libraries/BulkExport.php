<?php
namespace Asset\Libraries;

use Asset\Models\Perbaikan;
use DB;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * 
 */
class BulkExport implements FromQuery,WithHeadings
{
	
	public function headings(): array
    {
        return [
            'Kode',
            'Aset',
            'Bagian',
            'Instalasi',
            'Minggu Ke',
            'Petugas',
            'Tanggal',
            'Status'
        ];
    }
    public function query()
    {
        return $query = Perbaikan::with(['komponen', 'bagian', 'instalasi', 'ms4w'])
            ->select('prb_data.*')
            ->whereIn('prb_data.instalasi_id', lokasi())
            ->whereIn('prb_data.bagian_id', bagian())
            // ->whereNotIn('prb_data.status', ['10', '99'])
            ->whereRaw("TO_CHAR(prb_data.tanggal, 'YYYY') = $tahun")
            ->whereNotIn('prb_data.status', config('custom.hideStatus')) //tampilkan semua status kecuali 99
            ->where('prb_data.tipe', 'monitoring');
    }
    public function map($prb): array
    {
        return [
            $prb->id,
            $prb->komponen->nama_aset,
            $prb->bagian->name,
            $prb->instalasi->name,
            $prb->ms4w->hari,
            $prb->petugas_id,
            $prb->tanggal,
            $prb->tanggal,
            self::getStatus($prb)
        ];
    }

    private static function getStatus($model)
    {
    	$edit = '';
    	switch (true) {
    	    case ($model->status == '0' && empty($model->petugas_id)):
    	        $edit = '<a href="#" class="badge badge-primary"> Baru </a>';
    	        break;
    	    case ($model->status == '0' && !empty($model->petugas_id)):
    	        $edit = '<a href="#" class="badge badge-warning"> Investigasi </a>';
    	        break;
    	    case ($model->status == '1' && !empty($model->tgl_foto_investigasi)):
    	        $edit = '<a href="#" class="badge badge-info"> Sudah diinvestigasi </a>';
    	        break;
    	    case ($model->status == '1.1' && $model->bagian->id != '3'):
    	        $edit = '<a href="#" class="badge badge-info"> Menunggu Approval Manajer Pemeliharaan </a>';
    	        break;
    	    case ($model->status == '1.1' && $model->bagian->id == '3'):
    	        $edit = '<a href="#" class="badge badge-info"> Menunggu Approval Manajer TSI </a>';
    	        break;
    	    case ($model->status == '3.1' && $model->bagian->id != '3'):
    	        $edit = '<a href="#" class="badge badge-info"> Revisi Input Metode dari Manajer Pemeliharaan </a>';
    	        break;
    	    case ($model->status == '3.1' && $model->bagian->id == '3'):
    	        $edit = '<a href="#" class="badge badge-info"> Revisi Input Metode dari Manajer TSI </a>';
    	        break;
    	    case ($model->status == '1.2' && $model->metode == 'eksternal emergency'):
    	        $edit = '<a href="#" class="badge badge-info"> Menunggu Approval Manajer DalOps(Optional) </a>';
    	        break;
    	    case ($model->status == '1.2' && $model->metode != 'eksternal emergency'):
    	        $edit = '<a href="#" class="badge badge-info"> Menunggu Approval Manajer DalOps </a>';
    	        break;
    	    case ($model->status == '4.0'):
    	        $edit = '<a href="#" class="badge badge-danger"> Proses DED (Baru) </a>';
    	        break;
    	    case ($model->status == '4.1'):
    	        $edit = '<a href="#" class="badge badge-danger"> Proses DED (Proses) </a>';
    	        break;
    	    case ($model->status == '4.2'):
    	        $edit = '<a href="#" class="badge badge-danger"> Proses DED (Revisi) </a>';
    	        break;
    	    case ($model->status == '4.3'):
    	        $edit = '<a href="#" class="badge badge-danger"> Proses DED (Selesai) </a>';
    	        break;
    	    case ($model->status == '2' && empty($model->foto)):
    	        $edit = '<a href="#" class="badge badge-success"> Penanganan </a>';
    	        break;
    	    case ($model->status == '3.2'):
    	        $edit = '<a href="#" class="badge badge-info"> Revisi Input Metode dari Penanganan </a>';
    	        break;
    	    case ($model->status == '3.3'):
    	        $edit = '<a href="#" class="badge badge-info"> Revisi Input Metode dari Manajer DalOps </a>';
    	        break;
    	    // case ($model->status == '2' && (!empty($model->foto) && empty($model->approve_dalops))):
    	    //     $edit = '<a href="#" class="badge badge-success"> Menunggu Approval Manajer DalOps </a>';
    	    //     break;
    	    case ($model->status == '2' && (!empty($model->foto) && empty($model->approve_dalops)) && ($model->metode != "internal" || count($model->sukucadang) > 0)):
    	        $edit = '<a href="#" class="badge badge-success"> Menunggu Approval Manajer DalPro </a>';
    	        break;
    	    case ($model->status == '2' && (!empty($model->foto) && empty($model->approve_dalops)) && ($model->metode == "internal" && count($model->sukucadang) < 1)):
    	        $edit = '<a href="#" class="badge badge-success"> Closing </a>';
    	        break;
    	    case ($model->status == '2' && (!empty($model->foto) && !empty($model->approve_dalops))):
    	        $edit = '<a href="#" class="badge badge-success"> Sudah ditangani </a>';
    	        break;
    	    case $model->status == '10':
    	        $edit = '<a href="#" class="badge badge-success"> Selesai </a>';
    	        break;
    	}
    	return $edit;
    }
}