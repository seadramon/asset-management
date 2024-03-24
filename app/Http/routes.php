<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
Route::get('pindah', function() {
	$filename = 'test.jpg';
	\Storage::disk('sftp')->put('temp/'.$filename, \File::get(public_path('uploads/temp/'.$filename)));
});

Route::get('clear', function() {
	\Cache::flush();
});

Route::get('test', function () {
	phpinfo();
	/*$aa = \Asset\Models\Menu::whereHas('roles.roleuser', function($sql){
                        $sql->where('trim(user_id)', '15312');
                    })
					//->where('tipe', '>', 0)
                    ->orderBy('urut')->get();
    return response()->json($aa);*/
});

Route::get('lost', function () {
	abort(404);
})->name('lost');
// Auth::routes();
Route::group(['namespace' => 'Auth'], function() {
    Route::get('auth/login', 'AuthController@getLogin');
    Route::post('auth/login', 'AuthController@postLogin');
    Route::get('auth/logout', 'AuthController@getLogout');
});

Route::group(['middleware' => 'auth'], function() {
	Route::get('/', 									['as' => 'index', 'uses' => 'HomeController@index']);
	Route::get('/dashboard-assetSelect/{id}', 			['as' => 'dashboard-assetSelect', 'uses' => 'HomeController@assetSelect']);
	Route::get('/dashboard-getAssetKodefm/{id}', 		['as' => 'dashboard-getAssetKodefm', 'uses' => 'HomeController@getAssetKodefm']);
	Route::post('/', 									['as' => 'index-dashboard', 'uses' => 'HomeController@index']);

	Route::group(['prefix'=>'aset', 'as' => 'aset::'],function(){
		Route::get('/',function(){
			return redirect('/aset/entri');
		});

		Route::get('/entri/{recid?}',	['uses' => 'AsetController@entri', 'as' => 'aset-entri']);
		Route::get('/view/{recid?}',	['uses' => 'AsetController@show', 'as' => 'aset-view']);
		Route::get('/data',				['uses' => 'AsetController@data', 'as' => 'aset-data']);
		Route::get('/data/data','AsetController@dataData');

		Route::post('/entri','AsetController@simpanAset');
	});

	Route::group(['prefix' => 'dashboard', 'as' => 'dashboard::'], function() {
		Route::get('prwrutin', 			['uses' => 'DashboardController@linkperawatanRutin', 'as' => 'link-prwrutin']);
		Route::get('asset', 			['uses' => 'DashboardController@linkAsset', 'as' => 'link-aset']);
	});

	Route::group(['prefix' => 'depresiasi', 'as' => 'depresiasi::'], function() {
		Route::get('/', 			['uses' => 'DepresiasiController@index', 'as' => 'index']);
		Route::get('data', 			['uses' => 'DepresiasiController@data', 'as' => 'data']);
		Route::post('simpan', 		['uses' => 'DepresiasiController@simpan', 'as' => 'simpan']);

		Route::get('entri/{id?}', 	['uses' => 'DepresiasiController@entri', 'as' => 'entri']);
		Route::get('view/{id}', 	['uses' => 'DepresiasiController@show', 'as' => 'view']);

		Route::get('delete/{id}',	['uses' => 'DepresiasiController@delete', 'as' => 'delete']);
	});

	Route::group(['prefix' => 'verifikasi', 'as' => 'verifikasi::'], function() {
		Route::get('/', 			['uses' => 'VerifikasiController@index', 'as' => 'index']);
		Route::get('data', 			['uses' => 'VerifikasiController@data', 'as' => 'data']);
		Route::post('simpan', 		['uses' => 'VerifikasiController@simpan', 'as' => 'simpan']);

		Route::get('entri/{id?}', 	['uses' => 'VerifikasiController@entri', 'as' => 'entri']);
		Route::get('view/{id}', 	['uses' => 'VerifikasiController@show', 'as' => 'view']);

		Route::get('delete/{id}',	['uses' => 'VerifikasiController@delete', 'as' => 'delete']);
	});

	Route::group(['prefix' => 'pengesahan', 'as' => 'pengesahan::'], function() {
		Route::get('/', 			['uses' => 'PengesahanController@index', 'as' => 'index']);
		Route::get('data', 			['uses' => 'PengesahanController@data', 'as' => 'data']);
		Route::post('simpan', 		['uses' => 'PengesahanController@simpan', 'as' => 'simpan']);

		Route::get('entri/{id?}', 	['uses' => 'PengesahanController@entri', 'as' => 'entri']);
		Route::get('view/{id}', 	['uses' => 'PengesahanController@show', 'as' => 'view']);

		Route::get('delete/{id}',	['uses' => 'PengesahanController@delete', 'as' => 'delete']);
	});

	Route::group(['prefix' => 'uploadaset', 'as' => 'uploadaset::'], function() {
		Route::get('index', 		['uses' => 'UploadController@index', 'as' => 'index']);
		Route::post('simpan', 		['uses' => 'UploadController@simpan', 'as' => 'simpan']);
	});
	
	Route::group(['prefix' => 'barcodeaset', 'as' => 'barcodeaset::'], function() {
		Route::get('index', 		['uses' => 'BarcodeController@index', 'as' => 'index']);
		Route::get('data',			['uses' => 'BarcodeController@data', 'as' => 'data']);
		Route::get('download/{strid}',['uses' => 'BarcodeController@download', 'as' => 'download']);
	});

	Route::group(['prefix' => 'lcca', 'as' => 'lcca::'], function(){
		Route::get('mc-data',			['uses' => 'LccaController@pemeliharaanData', 'as' => 'mc-data']);
		Route::get('oc-data',			['uses' => 'LccaController@operasionalData', 'as' => 'oc-data']);
		Route::get('lcca',				['uses' => 'LccaController@analisis', 'as' => 'analisis']);
		Route::get('assetSelect',		['uses' => 'LccaController@assetSelect', 'as' => 'assetSelect']);
		Route::get('comparison',		['uses' => 'LccaController@comparison', 'as' => 'comparison']);
		Route::get('sukucadang',		['uses' => 'LccaController@pemeliharaanSukucadang', 'as' => 'sukucadang']);
		Route::get('pemeliharaan',		['uses' => 'LccaController@pemeliharaanEntri', 'as' => 'pemeliharaan-entri']);
		Route::get('{id}',				['uses' => 'LccaController@index', 'as' => 'index']);

		Route::post('akuisisi',			['uses' => 'LccaController@akuisisiStore', 'as' => 'akuisisi-simpan']);
		Route::post('penghapusan',		['uses' => 'LccaController@penghapusanStore', 'as' => 'penghapusan-simpan']);
		Route::post('operasional',		['uses' => 'LccaController@operasionalStore', 'as' => 'operasional-simpan']);
		Route::post('pemeliharaan',		['uses' => 'LccaController@pemeliharaanStore', 'as' => 'pemeliharaan-simpan']);

		Route::delete('operasional-delete', ['uses' => 'LccaController@operasionalDelete', 'as' => 'operasional-delete']);
		Route::delete('pemeliharaan-delete', ['uses' => 'LccaController@pemeliharaanDelete', 'as' => 'pemeliharaan-delete']);
	});

	Route::group(['prefix' => 'master', 'as' => 'master::'], function(){
		//Kondisi
		Route::get('Kondisi/{recid?}', ['uses' => 'MasterController@kondisiLink', 'as' => 'kondisi-link']);
		Route::post('Kondisi', ['uses' => 'MasterController@kondisiSimpan', 'as' => 'kondisi-simpan']);
		Route::get('KondisiData', ['uses' => 'MasterController@kondisiData', 'as' => 'kondisi-data']);

		//Kategori
		Route::get('Kategori/{recid?}', ['uses' => 'MasterController@kategoriLink', 'as' => 'kategori-link']);
		Route::post('Kategori', ['uses' => 'MasterController@kategoriSimpan', 'as' => 'kategori-simpan']);
		Route::get('KategoriData', ['uses' => 'MasterController@kategoriData', 'as' => 'kategori-data']);

		//SubKategori
		Route::get('SubKategori/{recid?}', ['uses' => 'MasterController@subKategoriLink', 'as' => 'subkategori-link']);
		Route::post('SubKategori', ['uses' => 'MasterController@subKategoriSimpan', 'as' => 'subkategori-simpan']);
		Route::get('SubKategoriData', ['uses' => 'MasterController@subKategoriData', 'as' => 'subkategori-data']);
		Route::get('SubKategoriSelect/{recid}', ['uses' => 'MasterController@subKategoriSelect', 'as' => 'subkategori-select']);

		//SubSubKategori
		Route::get('SubSubKategori/{recid?}', ['uses' => 'MasterController@subSubKategoriLink', 'as' => 'subsubkategori-link']);
		Route::post('SubSubKategori', ['uses' => 'MasterController@subSubKategoriSimpan', 'as' => 'subsubkategori-simpan']);
		Route::get('SubSubKategoriData', ['uses' => 'MasterController@subSubKategoriData', 'as' => 'subsubkategori-data']);
		Route::get('SubSubKategoriSelect/{recid}', ['uses' => 'MasterController@subSubKategoriSelect', 'as' => 'subsubkategori-select']);

		//Instalasi
		Route::get('Instalasi/{recid?}', ['uses' => 'MasterController@instalasiLink', 'as' => 'instalasi-link']);
		Route::post('Instalasi', ['uses' => 'MasterController@instalasiSimpan', 'as' => 'instalasi-simpan']);
		Route::get('InstalasiData', ['uses' => 'MasterController@instalasiData', 'as' => 'instalasi-data']);

		//Lokasi
		Route::get('Lokasi/{recid?}', ['uses' => 'MasterController@lokasiLink', 'as' => 'lokasi-link']);
		Route::post('Lokasi', ['uses' => 'MasterController@lokasiSimpan', 'as' => 'lokasi-simpan']);
		Route::get('LokasiData', ['uses' => 'MasterController@lokasiData', 'as' => 'lokasi-data']);
		Route::get('LokasiSelect/{recid}', ['uses' => 'MasterController@lokasiSelect', 'as' => 'lokasi-select']);

		//Ruangan
		Route::get('Ruangan/{recid?}', ['uses' => 'MasterController@ruanganLink', 'as' => 'ruangan-link']);
		Route::post('Ruangan', ['uses' => 'MasterController@ruanganSimpan', 'as' => 'ruangan-simpan']);
		Route::get('RuanganData', ['uses' => 'MasterController@ruanganData', 'as' => 'ruangan-data']);
		Route::get('RuanganSelect/{recid}', ['uses' => 'MasterController@ruanganSelect', 'as' => 'ruangan-select']);

		//Spek Group
		Route::get('SpekGroup/{recid?}', ['uses' => 'MasterController@spekGroupLink', 'as' => 'spekgroup-link']);
		Route::post('SpekGroup', ['uses' => 'MasterController@spekGroupSimpan', 'as' => 'spekgroup-simpan']);
		Route::get('SpekGroupData', ['uses' => 'MasterController@spekGroupData', 'as' => 'spekgroup-data']);

		//Spek Item
		Route::get('SpekItem/{recid?}', ['uses' => 'MasterController@spekItemLink', 'as' => 'spekitem-link']);
		Route::post('SpekItem', ['uses' => 'MasterController@spekItemSimpan', 'as' => 'spekitem-simpan']);
		Route::get('SpekItemData', ['uses' => 'MasterController@spekItemData', 'as' => 'spekitem-data']);

		//Template
		Route::get('Template/{recid?}', ['uses' => 'MasterController@templateLink', 'as' => 'template-link']);
		Route::get('TemplatePindah/{recid}', ['uses' => 'MasterController@templatePindah', 'as' => 'template-pindah']);
		Route::post('Template', ['uses' => 'MasterController@templateSimpan', 'as' => 'template-simpan']);
		Route::post('TemplatePindah', ['uses' => 'MasterController@templatePindahSimpan', 'as' => 'template-pindah-simpan']);
		Route::get('TemplateData', ['uses' => 'MasterController@templateData', 'as' => 'template-data']);

		//Kelompok
		Route::get('Kelompok/{recid?}', ['uses' => 'MasterController@kelompokLink', 'as' => 'kelompok-link']);
		Route::post('Kelompok', ['uses' => 'MasterController@kelompokSimpan', 'as' => 'kelompok-simpan']);
		Route::get('KelompokData', ['uses' => 'MasterController@kelompokData', 'as' => 'kelompok-data']);

		//Kelompok Detail
		Route::get('KelompokDetail/{recid?}', ['uses' => 'MasterController@kelompokDetailLink', 'as' => 'kelompokdetail-link']);
		Route::post('KelompokDetail', ['uses' => 'MasterController@kelompokDetailSimpan', 'as' => 'kelompokdetail-simpan']);
		Route::get('KelompokDetailData', ['uses' => 'MasterController@kelompokDetailData', 'as' => 'kelompokdetail-data']);
		Route::get('KelompokDetailSelect/{recid}', ['uses' => 'MasterController@kelompokDetailSelect', 'as' => 'kelompokdetail-select']);

		//Kelompok
		Route::get('Komponen/{recid?}', ['uses' => 'MasterController@komponenLink', 'as' => 'komponen-link']);
		Route::post('Komponen', ['uses' => 'MasterController@komponenSimpan', 'as' => 'komponen-simpan']);
		Route::get('KomponenData', ['uses' => 'MasterController@komponenData', 'as' => 'komponen-data']);
		Route::get('KomponenSelect/{recid}', ['uses' => 'MasterController@komponenSelect', 'as' => 'komponen-select']);

		//Komponen Detail
		Route::get('KomponenDetail/{recid?}', ['uses' => 'MasterController@komponenDetailLink', 'as' => 'komponendetail-link']);
		Route::post('KomponenDetail', ['uses' => 'MasterController@komponenDetailSimpan', 'as' => 'komponendetail-simpan']);
		Route::get('KomponenDetailData', ['uses' => 'MasterController@komponenDetailData', 'as' => 'komponendetail-data']);
		Route::get('KomponenDetailSelect/{kodefm}', ['uses' => 'MasterController@komponenDetailSelect', 'as' => 'komponendetail-select']);

		//Sistem
		Route::get('Sistem/{recid?}', ['uses' => 'MasterController@sistemLink', 'as' => 'sistem-link']);
		Route::post('Sistem', ['uses' => 'MasterController@sistemSimpan', 'as' => 'sistem-simpan']);
		Route::get('SistemData', ['uses' => 'MasterController@sistemData', 'as' => 'sistem-data']);
	});

	// Manajemen Strategi
	Route::group(['prefix' => 'mstrategi', 'as' => 'mstrategi::'], function(){
		// init
		Route::get('Entri', 			['uses' => 'MStrategiController@entri', 'as' => 'mstrategi-entri']);
		Route::get('EntriPdm', 			['uses' => 'MStrategiController@entripdm', 'as' => 'mstrategi-entripdm']);
		Route::get('Entri52w', 			['uses' => 'MStrategiController@entri52w', 'as' => 'mstrategi-entri52w']);
		Route::get('Entri4w', 			['uses' => 'MStrategiController@entri4w', 'as' => 'mstrategi-entri4w']);
		Route::get('EntriPenugasan',    ['uses' => 'MStrategiController@entriPenugasan', 'as' => 'mstrategi-entriPenugasan']);
		Route::get('EntriOverhaul',    	['uses' => 'MStrategiController@entriOverhaul', 'as' => 'mstrategi-entriOverhaul']);

		// Perawatan Rutin
		Route::get('EntriPrwRutin',    	['uses' => 'MStrategiController@entriPrwRutin', 'as' => 'mstrategi-entriPrwRutin']);
		Route::get('rutin52w',    		['uses' => 'PrwRutinController@entri52w', 		'as' => 'mstrategi-rutin52w']);
		Route::get('rutin4w',    		['uses' => 'PrwRutinController@entri4w', 		'as' => 'mstrategi-rutin4w']);

		// save
		Route::post('Entri', 			['uses' => 'MStrategiController@simpan', 'as' => 'mstrategi-simpan']);
		Route::post('EntriPdm', 		['uses' => 'MStrategiController@simpanpdm', 'as' => 'mstrategi-simpanpdm']);
		Route::post('Entri52w', 		['uses' => 'MStrategiController@simpan52w', 'as' => 'mstrategi-simpan52w']);
		Route::post('Entri4w', 			['uses' => 'MStrategiController@simpan4w', 'as' => 'mstrategi-simpan4w']);
		Route::post('EntriPenugasan',   ['uses' => 'MStrategiController@simpanPenugasan', 'as' => 'mstrategi-simpanPenugasan']);
		Route::post('EntriOverhaul', 	['uses' => 'MStrategiController@simpanoverhaul', 'as' => 'mstrategi-simpanoverhaul']);
		Route::post('EntriPrwRutin', 	['uses' => 'MStrategiController@simpanPrwRutin', 'as' => 'mstrategi-simpanPrwRutin']);

		Route::post('rutin52w', 		['uses' => 'PrwRutinController@simpan52w', 'as' => 'mstrategi-simpanRutin52w']);
		Route::post('rutin4w', 			['uses' => 'PrwRutinController@simpanRutin4w', 'as' => 'mstrategi-simpanRutin4w']);

		Route::get('Lihat', 			['uses' => 'MStrategiController@lihat', 'as' => 'mstrategi-lihat']);

		// lookup
		Route::get('showPrwrutin', 		['uses' => 'PrwRutinController@showPrwrutin', 'as' => 'prwrutin-perawatan']);

		Route::get('Part/{recid}', 			['uses' => 'MStrategiController@part', 'as' => 'mstrategi-part']);
		Route::get('Partpdm/{recid}', 		['uses' => 'MStrategiController@partpdm', 'as' => 'mstrategi-partpdm']);
		Route::get('AssetSelect/{id}', 		['uses' => 'MStrategiController@assetSelect', 'as' => 'asset-select']);
		Route::get('AssetPrwSelect/{id}', 	['uses' => 'MStrategiController@assetPrwSelect', 'as' => 'asset-prwselect']);
		Route::get('OverhaulSelect/{id}', 	['uses' => 'MStrategiController@overhaulSelect', 'as' => 'overhaul-select']);

		Route::get('PrwrutinSelect/{id}', 			['uses' => 'MStrategiController@prwrutinSelect', 'as' => 'prwrutin-select']);
		Route::get('PrwrutinLokasiSelect/{id}', 	['uses' => 'PrwRutinController@lokasiSelect', 'as' => 'prwrutin-lokasi-select']);

		Route::get('KomponenSelectPrw/{id}', 	['uses' => 'PrwRutinController@komponenSelect', 'as' => 'komponen-select-prw']);

		Route::get('KomponenSelect/{id}/{tahun?}', 		['uses' => 'MStrategiController@komponenSelect', 'as' => 'komponen-select']);

		Route::get('WeekSelect/{id}', 	['uses' => 'MStrategiController@weekSelect', 'as' => 'week-select']);
		Route::get('PenugasanSelect/{id}', 	['uses' => 'MStrategiController@penugasanSelect', 'as' => 'penugasan-select']);

		Route::get('kodePartSelect/{id}/{kodepart}/{tahun?}', 	['uses' => 'PrwRutinController@kodepartSelect', 'as' => 'kodepart-select']);
		Route::get('WeekSelectNew/{week}/{bagian}/{tahun?}/{lokasi?}', 	['uses' => 'MStrategiController@weekSelectnewdev', 'as' => 'week-selectnew']);
	});

	Route::group(['prefix' => 'nonaktif', 'as' => 'nonaktif::'], function(){
		Route::get('/', 				['uses' => 'NonaktifController@index', 'as' => 'nonaktif-index']);
		Route::get('data', 				['uses' => 'NonaktifController@nonaktifData', 'as' => 'nonaktif-data']);
		Route::get('entri/{id?}', 		['uses' => 'NonaktifController@entri', 'as' => 'nonaktif-entri']);
		Route::post('simpan', 			['uses' => 'NonaktifController@simpan', 'as' => 'nonaktif-simpan']);
	});

	Route::group(['prefix' => 'pemindahan', 'as' => 'pemindahan::'], function(){
		Route::get('/', 			['uses' => 'PemindahanController@index', 'as' => 'pemindahan-index']);
		Route::get('data', 			['uses' => 'PemindahanController@pemindahanData', 'as' => 'pemindahan-data']);
		Route::get('entri/{id?}', 		['uses' => 'PemindahanController@pemindahanEntri', 'as' => 'pemindahan-entri']);
		Route::post('simpan', 		['uses' => 'PemindahanController@simpan', 'as' => 'pemindahan-simpan']);
		Route::get('AsetSelect/{id}', 	['uses' => 'PemindahanController@asetSelect', 'as' => 'pemindahan-aset']);
	});

	Route::group(['prefix' => 'peminjaman', 'as' => 'peminjaman::'], function(){
		Route::get('/', 	['uses' => 'PeminjamanController@index', 'as' => 'peminjaman-index']);
		Route::get('data', 	['uses' => 'PeminjamanController@peminjamanData', 'as' => 'peminjaman-data']);
		Route::get('entri/{id?}',	['uses' => 'PeminjamanController@peminjamanEntri', 'as' => 'peminjaman-entri']);
		Route::post('simpan', 		['uses' => 'PeminjamanController@simpan', 'as' => 'peminjaman-simpan']);
		Route::get('AsetSelect/{id}', 	['uses' => 'PeminjamanController@asetSelect', 'as' => 'peminjaman-aset']);
	});

	Route::group(['prefix' => 'monitoring', 'as' => 'monitoring::'], function(){
		Route::get('/', 			['uses' => 'MonitoringController@index', 'as' => 'monitoring-index']);
		Route::get('history', 		['uses' => 'MonitoringController@history', 'as' => 'monitoring-history']);

		Route::get('penugasan/{id}/{idlokasi}/{idbagian}', ['uses' => 'MonitoringController@penugasan', 'as' => 'monitoring-penugasan']);

		Route::get('entri/{id?}/{id4w}',	['uses' => 'MonitoringController@entri', 'as' => 'monitoring-entri']);
		Route::get('data/{idlok?}', 		['uses' => 'MonitoringController@monitoringData', 'as' => 'monitoring-data']);
		Route::get('data-history/{idlok?}', ['uses' => 'MonitoringController@history', 'as' => 'monitoring-history']);

		Route::post('simpan', 		['uses' => 'MonitoringController@simpan', 'as' => 'monitoring-simpan']);
		Route::post('update', 		['uses' => 'MonitoringController@update', 'as' => 'monitoring-update']);
		Route::post('penugasan', 	['uses' => 'MonitoringController@penugasanSimpan', 'as' => 'monitoring-penugasanSimpan']);
	});

	Route::group(['prefix' => 'perawatan', 'as' => 'perawatan::'], function(){
		Route::get('/', 					['uses' => 'TindakanController@perawatanLink', 'as' => 'perawatan-index']);
		Route::get('data', 					['uses' => 'TindakanController@perawatanData', 'as' => 'perawatan-data']);
		Route::post('penugasan',			['uses' => 'TindakanController@perawatanPenugasanSimpan', 'as' => 'perawatan-penugasan-simpan']);
		Route::post('close',				['uses' => 'TindakanController@perawatanClose', 'as' => 'perawatan-close']);
		Route::post('metode',				['uses' => 'TindakanController@metodePerawatanSimpan', 'as' => 'perawatan-metode-simpan']);
		Route::post('sukucadang',			['uses' => 'TindakanController@sukucadangSimpan', 'as' => 'sukucadang-simpan']);
		Route::post('ded',					['uses' => 'TindakanController@dedPerawatanSimpan', 'as' => 'perawatan-ded-simpan']);
		Route::post('takeover',			['uses' => 'TindakanController@takeoverPerawatanSimpan', 'as' => 'perawatan-takeover-simpan']);
		Route::get('dataDetail/{id}', 			['uses' => 'TindakanController@perawatanDataDetail', 'as' => 'perawatan-data-detail']);
		Route::get('takeover/{id}',				['uses' => 'TindakanController@takeoverPerawatan', 'as' => 'perawatan-takeover']);
		Route::get('metode/{id}',				['uses' => 'TindakanController@metodePerawatan', 'as' => 'perawatan-metode']);
		Route::get('view/{id}',					['uses' => 'TindakanController@viewPerawatan', 'as' => 'perawatan-view']);
		Route::get('analisa/{id}',				['uses' => 'TindakanController@analisaPerawatan', 'as' => 'perawatan-analisa']);
		Route::get('sukucadang/{id}/{aset?}',	['uses' => 'TindakanController@sukucadangPerawatan', 'as' => 'perawatan-sukucadang']);
		Route::get('penugasan/{id}/{idlokasi}/{idbagian?}',		['uses' => 'TindakanController@perawatanPenugasan', 'as' => 'perawatan-penugasan']);
	});

	Route::group(['prefix' => 'perrbaikan', 'as' => 'perbaikan::'], function(){
		Route::get('/', 					['uses' => 'TindakanController@perbaikanLink', 'as' => 'perbaikan-index']);
		Route::get('data', 					['uses' => 'TindakanController@perbaikanData', 'as' => 'perbaikan-data']);
		Route::get('dataaduan', 			['uses' => 'TindakanController@perbaikanDataAduan', 'as' => 'perbaikan-dataAduan']);
		Route::get('dataDetail/{id}', 			['uses' => 'TindakanController@perbaikanDataDetail', 'as' => 'perbaikan-data-detail']);
		Route::post('penugasan',			['uses' => 'TindakanController@perbaikanPenugasanSimpan', 'as' => 'perbaikan-penugasan-simpan']);
		Route::post('penugasanAduan',		['uses' => 'TindakanController@penugasanAduanSimpan', 'as' => 'penugasanAduan-simpan']);
		Route::post('penugasanAnalisa',		['uses' => 'TindakanController@penugasanAnalisaSimpan', 'as' => 'penugasanAnalisa-simpan']);
		Route::post('penugasanAduanClose',		['uses' => 'TindakanController@penugasanAduanClose', 'as' => 'penugasanAduanClose-simpan']);
		Route::post('metode',			['uses' => 'TindakanController@metodePerbaikanSimpan', 'as' => 'perbaikan-metode-simpan']);
		Route::post('close',			['uses' => 'TindakanController@perbaikanClose', 'as' => 'perbaikan-close']);
		Route::post('ded',				['uses' => 'TindakanController@dedPerbaikanSimpan', 'as' => 'perbaikan-ded-simpan']);
		Route::post('msppp',			['uses' => 'TindakanController@mspppSimpan', 'as' => 'perbaikan-msppp-simpan']);
		Route::post('takeover',			['uses' => 'TindakanController@takeoverPerbaikanSimpan', 'as' => 'perbaikan-takeover-simpan']);

		Route::get('lokasiSelect/{id}', 	['uses' => 'TindakanController@lokasiSelect', 'as' => 'perbaikan-lokasiselect']);
		Route::get('asetSelect/{id}', 		['uses' => 'TindakanController@asetSelect', 'as' => 'perbaikan-asetselect']);

		Route::get('takeover/{id}',			['uses' => 'TindakanController@takeoverPerbaikan', 'as' => 'perbaikan-takeover']);
		Route::get('analisa/{id}', 			['uses' => 'TindakanController@analisaPerbaikan', 'as' => 'perbaikan-analisa']);
		Route::get('metode/{id}',			['uses' => 'TindakanController@metodePerbaikan', 'as' => 'perbaikan-metode']);
		Route::get('view/{id}',				['uses' => 'TindakanController@viewPerbaikan', 'as' => 'perbaikan-view']);

		Route::get('penugasanAduan/{aduan_id?}/{barcode?}',		['uses' => 'TindakanController@penugasanAduan', 'as' => 'penugasan-aduan']);
		Route::get('penugasanAnalisa/{id}/{aduan_id}',		['uses' => 'TindakanController@penugasanAnalisa', 'as' => 'penugasan-analisa']);
		Route::get('penugasanAnalisaFinish/{id}/{aduan_id}',		['uses' => 'TindakanController@penugasanAnalisaFinish', 'as' => 'penugasan-analisa-finish']);
		Route::get('penugasan/{id}/{idlokasi}/{idbagian?}',		['uses' => 'TindakanController@perbaikanPenugasan', 'as' => 'perbaikan-penugasan']);
	});

	Route::group(['prefix' => 'proposal', 'as' => 'proposal::'], function(){
		Route::get('getjson/{wo}/{id}',			['uses' => 'ProposalController@getJson', 'as' => 'getjson']);
		Route::get('{wo}/{id}',					['uses' => 'ProposalController@pekerjaan', 'as' => 'pekerjaan']);
		Route::post('simpan',					['uses' => 'ProposalController@store', 'as' => 'simpan']);
	});

	Route::group(['prefix' => 'todolist', 'as' => 'todolist::'], function(){
		Route::get('/', 						['uses' => 'TodolistController@index', 'as' => 'todolist-index']);
		Route::get('data/{minggu?}', 			['uses' => 'TodolistController@todolistData', 'as' => 'todolist-data']);
		Route::delete('delete/{id}',			['uses' => 'TodolistController@delete', 'as' => 'todolist-delete']);
		// Route::get('entri/{id?}/{id4w}',	['uses' => 'MonitoringController@entri', 'as' => 'monitoring-entri']);
		// Route::get('data', 			['uses' => 'MonitoringController@monitoringData', 'as' => 'monitoring-data']);
		// Route::post('simpan', 		['uses' => 'MonitoringController@simpan', 'as' => 'monitoring-simpan']);
		// Route::post('update', 		['uses' => 'MonitoringController@update', 'as' => 'monitoring-update']);

		Route::get('prw-rutin',				['uses' => 'PrwRutinController@todolist', 'as' => 'todolist-prwrutin']);
		Route::get('prw-rutin/data',		['uses' => 'PrwRutinController@todolistData', 'as' => 'todolist-prwrutin-data']);
		Route::get('prw-rutin/sukucadang/{woid}',	['uses' => 'PrwRutinController@sukucadangShow', 'as' => 'prwrutin-sukucadang']);
		Route::delete('prw-rutin/delete/{id}',			['uses' => 'PrwRutinController@delete', 'as' => 'prwrutin-delete']);
	});

	Route::group(['prefix' => 'prwrutin', 'as' => 'prwrutin::'], function(){
		
	});

	Route::group(['prefix' => 'rpembobotan', 'as' => 'rpembobotan::'], function(){
		Route::get('/', 			['uses' => 'RPembobotanController@index', 'as' => 'rpembobotan-index']);
		Route::post('laporan',		['uses' => 'RPembobotanController@laporan', 'as' => 'rpembobotan-laporan']);
	});

	Route::group(['prefix' => 'jadwalkerja', 'as' => 'jadwalkerja::'], function(){
		Route::get('/', 			['uses' => 'JadwalLiburController@index', 'as' => 'jadwalkerja-index']);
		Route::get('data', 			['uses' => 'JadwalLiburController@jadwalData', 'as' => 'jadwalkerja-data']);
		Route::get('entri/{id?}', 	['uses' => 'JadwalLiburController@entri', 'as' => 'jadwalkerja-entri']);
		Route::post('simpan', 		['uses' => 'JadwalLiburController@simpan', 'as' => 'jadwalkerja-simpan']);
		Route::delete('delete/{id}',['uses' => 'JadwalLiburController@delete', 'as' => 'jadwalkerja-delete']);
	});

	Route::group(['prefix' => 'ManajemenUser', 'as' => 'mnjuser::'], function(){
		Route::get('/', 			['uses' => 'MUserController@index', 'as' => 'mnjuser-index']);
		Route::get('data', 			['uses' => 'MUserController@userData', 'as' => 'mnjuser-data']);
		Route::get('entri/{id?}', 	['uses' => 'MUserController@entri', 'as' => 'mnjuser-entri']);
		Route::post('simpan', 		['uses' => 'MUserController@simpan', 'as' => 'mnjuser-simpan']);
	});

	Route::group(['prefix' => 'NonOperasi', 'as' => 'non-operasi::'], function(){
		Route::get('aduan', 			['uses' => 'NonOperasiController@aduanIndex', 'as' => 'aduan-index']);
		Route::get('usulan', 			['uses' => 'NonOperasiController@usulanIndex', 'as' => 'usulan-index']);

		Route::get('aduan-data', 				['uses' => 'NonOperasiController@aduanData', 'as' => 'aduan-data']);
		Route::get('usulan-data', 				['uses' => 'NonOperasiController@usulanData', 'as' => 'usulan-data']);

		Route::get('aduan-entri/{id?}', 		['uses' => 'NonOperasiController@aduanEntri', 'as' => 'aduan-entri']);
		Route::get('aduan-view/{id}', 			['uses' => 'NonOperasiController@aduanView', 'as' => 'aduan-view']);
		Route::get('usulan-view/{id}', 			['uses' => 'NonOperasiController@usulanView', 'as' => 'usulan-view']);
		Route::get('aduan-disposisi/{id?}', 	['uses' => 'NonOperasiController@aduanDisposisi', 'as' => 'aduan-disposisi']);
		Route::get('aduan-investigasi/{id?}', 	['uses' => 'NonOperasiController@aduanInvestigasi', 'as' => 'aduan-investigasi']);
		Route::get('aduan-metode/{id?}', 		['uses' => 'NonOperasiController@aduanMetode', 'as' => 'aduan-metode']);
		Route::get('aduan-approval/{id?}', 		['uses' => 'NonOperasiController@aduanApproval', 'as' => 'aduan-approval']);
		Route::get('aduan-approval-dalops/{id?}',['uses' => 'NonOperasiController@aduanApprovalDalops', 'as' => 'aduan-approval-dalops']);

		Route::get('usulan-entri/{id?}', 		['uses' => 'NonOperasiController@usulanEntri', 'as' => 'usulan-entri']);
		Route::get('LokasiSelect/{id}', 		['uses' => 'NonOperasiController@lokasiSelect', 'as' => 'lokasi-select']);
		Route::get('AsetSelect/{id}', 		    ['uses' => 'NonOperasiController@asetSelect', 'as' => 'aset-select']);
		Route::get('takeover/{wo}/{id}',		['uses' => 'NonOperasiController@takeover', 'as' => 'takeover']);
		Route::get('jabselect/{id}/{wo}',		['uses' => 'NonOperasiController@selectJab', 'as' => 'jabatan-select']);

		
		Route::post('aduan-simpan', 			['uses' => 'NonOperasiController@aduanSimpan', 'as' => 'aduan-simpan']);
		Route::post('aduan-ded', 				['uses' => 'NonOperasiController@aduanDedSimpan', 'as' => 'aduan-ded-simpan']);

		Route::post('usulan-simpan', 			['uses' => 'NonOperasiController@usulanSimpan', 'as' => 'usulan-simpan']);
		Route::post('usulan-ded', 				['uses' => 'NonOperasiController@usulanDedSimpan', 'as' => 'usulan-ded-simpan']);

		Route::post('takeover',					['uses' => 'NonOperasiController@takeoverSimpan', 'as' => 'takeover-simpan']);
		Route::post('msppp',					['uses' => 'NonOperasiController@mspppSimpan', 'as' => 'msppp-simpan']);

		Route::post('aduan-metode', 			['uses' => 'NonOperasiController@aduanMetodeSimpan', 'as' => 'aduan-metode-simpan']);
		Route::post('usulan-metode',			['uses' => 'NonOperasiController@usulanMetodeSimpan', 'as' => 'usulan-metode-simpan']);
	});

	Route::group(['prefix' => 'ManajemenRole', 'as' => 'mnjrole::'], function(){
		Route::get('/', 			['uses' => 'MRoleController@index', 'as' => 'mnjrole-index']);
		Route::get('data', 			['uses' => 'MRoleController@roleData', 'as' => 'mnjrole-data']);
		Route::get('entri/{id?}', 	['uses' => 'MRoleController@entri', 'as' => 'mnjrole-entri']);
		Route::post('simpan', 		['uses' => 'MRoleController@simpan', 'as' => 'mnjrole-simpan']);
	});

	Route::group(['prefix' => 'ManajemenMenu', 'as' => 'mnjmenu::'], function(){
		Route::get('/', 			['uses' => 'MMenuController@index', 'as' => 'mnjmenu-index']);
		Route::get('data', 			['uses' => 'MMenuController@menuData', 'as' => 'mnjmenu-data']);
		Route::get('entri/{id?}', 	['uses' => 'MMenuController@entri', 'as' => 'mnjmenu-entri']);
		Route::post('simpan', 		['uses' => 'MMenuController@simpan', 'as' => 'mnjmenu-simpan']);
	});

	Route::group(['prefix' => 'temp', 'as' => 'temp::'], function(){
		Route::get('/', 			['uses'=> 'TempController@index', 'as' => 'temp-index']);
		Route::get('data', 			['uses' => 'TempController@formData', 'as' => 'temp-data']);
		Route::get('manual/{id}', 	['uses'=> 'TempController@manual', 'as' => 'temp-manual']);
		Route::post('simpan', 	['uses'=> 'TempController@simpan', 'as' => 'temp-simpan']);
	});

	Route::group(['prefix' => 'evaluasi', 'as' => 'evaluasi::'], function() {
		Route::get('get-available', 			['uses' => 'EvaluasiController@getAvailable', 'as'=>'get-available']);
		Route::post('laporan-available',		['uses' => 'EvaluasiController@laporanAvailable', 'as' => 'laporan-available']);

		Route::get('get-penjadwalan', 			['uses' => 'EvaluasiController@getPenjadwalan', 'as'=>'get-penjadwalan']);
		Route::post('laporan-penjadwalan',		['uses' => 'EvaluasiController@laporanPenjadwalan', 'as' => 'laporan-penjadwalan']);
		
		Route::get('get-mstrategi', 			['uses' => 'EvaluasiController@getMstrategi', 'as'=>'get-mstrategi']);
		Route::post('laporan-mstrategi',		['uses' => 'EvaluasiController@laporanMstrategi', 'as' => 'laporan-mstrategi']);

		Route::get('get-tindakan', 				['uses' => 'EvaluasiController@getTindakan', 'as'=>'get-tindakan']);
		Route::post('laporan-tindakan',			['uses' => 'EvaluasiController@laporanTindakan', 'as' => 'laporan-tindakan']);

		Route::get('get-efektifitas-jam', 			['uses' => 'EvaluasiController@getEfektifitasJam', 'as'=>'get-efektifitas-jam']);
		Route::post('laporan-efektifitas-jam',		['uses' => 'EvaluasiController@laporanEfektifitasJam', 'as' => 'laporan-efektifitas-jam']);

		Route::get('get-realisasi-pemeliharaan', 			['uses' => 'EvaluasiController@getRealisasiPemeliharaan', 'as'=>'get-realisasi-pemeliharaan']);
		Route::post('laporan-realisasi-pemeliharaan',		['uses' => 'EvaluasiController@laporanRealisasiPemeliharaan', 'as' => 'laporan-realisasi-pemeliharaan']);

		Route::get('get-investasi', 						['uses' => 'EvaluasiController@getInvestasi', 'as'=>'get-investasi']);
		Route::post('laporan-investasi',					['uses' => 'EvaluasiController@laporanInvestasi', 'as' => 'laporan-investasi']);

		Route::get('get-prioritas', 						['uses' => 'EvaluasiController@getPrioritas', 'as'=>'get-prioritas']);
		Route::post('laporan-prioritas',					['uses' => 'EvaluasiController@laporanPrioritas', 'as' => 'laporan-prioritas']);

		Route::get('entry-prioritas', 						['uses' => 'EvaluasiController@createPrioritas', 'as'=>'entry-prioritas']);
		Route::get('part-prioritas/{id}', 					['uses' => 'EvaluasiController@partPrioritas', 'as'=>'part-prioritas']);
		Route::post('entry-prioritas', 						['uses' => 'EvaluasiController@storePrioritas', 'as'=>'store-prioritas']);
	});

	Route::group(['prefix' => 'lampiranev', 'as' => 'lampiranev::'], function() {
		Route::get('get-penjadwalan', 		['uses' => 'LampiranEvController@getPenjadwalan', 'as' => 'get-penjadwalan']);
		Route::get('get-realisasi-pemeliharaan', 	['uses' => 'LampiranEvController@getRealisasiPemeliharaan', 'as'=>'get-realisasi-pemeliharaan']);
		Route::get('get-available', 			['uses' => 'LampiranEvController@getAvailable', 'as'=>'get-available']);	
		Route::get('get-kesesuaian', 			['uses' => 'LampiranEvController@getKesesuaian', 'as'=>'get-kesesuaian']);	

		Route::get('data-penjadwalan', 		['uses' => 'LampiranEvController@dataPenjadwalan', 'as' => 'data-penjadwalan']);
		Route::get('data-realisasi-perawatan', 	['uses' => 'LampiranEvController@dataRealisasiPerawatan', 'as'=>'data-realisasi-perawatan']);
		Route::get('data-realisasi-perbaikan', 	['uses' => 'LampiranEvController@dataRealisasiPerbaikan', 'as'=>'data-realisasi-perbaikan']);
		Route::get('data-realisasi-non-operasi', 	['uses' => 'LampiranEvController@dataRealisasiNonOperasi', 'as'=>'data-realisasi-non-operasi']);
		Route::get('data-realisasi-usulan', 	['uses' => 'LampiranEvController@dataRealisasiUsulan', 'as'=>'data-realisasi-usulan']);
		Route::get('data-available', 			['uses' => 'LampiranEvController@dataAvailable', 'as'=>'data-available']);	
		Route::get('data-kesesuaian', 			['uses' => 'LampiranEvController@dataKesesuaian', 'as'=>'data-kesesuaian']);	

		// Route::get('data', 					['uses' => 'LampiranEvController@menuData', 'as' => 'mnjmenu-data']);
	});

	Route::group(['prefix' => 'kpi', 'as' => 'kpi::'], function() {
		Route::get('index',			['uses' => 'KpiController@index', 'as' => 'kpi-index']);
		Route::get('index-setting',	['uses' => 'KpiController@listSetting', 'as' => 'kpi-index-setting']);
		Route::get('data-setting',	['uses' => 'KpiController@dataSetting', 'as' => 'kpi-data-setting']);
		Route::get('setting',		['uses' => 'KpiController@setting', 'as' => 'kpi-setting']);
		Route::get('closing/{id}',	['uses' => 'KpiController@closing', 'as' => 'kpi-closing']);

		Route::post('cetak',		['uses' => 'KpiController@cetak', 'as' => 'kpi-cetak']);
		Route::post('setting',		['uses' => 'KpiController@settingSimpan', 'as' => 'kpi-setting-simpan']);
	});

	Route::group(['prefix' => 'investasi', 'as' => 'investasi::'], function() {
		Route::get('npv',				['uses' => 'InvestasiController@npv', 'as' => 'npv']);
		Route::post('npv-store',				['uses' => 'InvestasiController@npvstore', 'as' => 'npv-store']);
	});
	
	Route::get('DataRoleMenus', ['uses' => 'UserController@datarolemenus', 'as' => 'datarolemenus-link']);
    Route::get('DataRoleMenusJson', ['uses' => 'UserController@datarolemenus_json', 'as' => 'datarolemenus-json']);
    Route::get('Add-DataRoleMenus', ['uses' => 'UserController@add_datarolemenus', 'as' => 'datarolemenus-add']);
    Route::post('Post-DataRoleMenus', ['uses' => 'UserController@post_datarolemenus', 'as' => 'datarolemenus-post']);
    Route::get('Del-DataRoleAllMenus/role={role}', ['uses' => 'UserController@del_dataroleallmenus', 'as' => 'dataroleallmenus-del']);
    Route::get('Del-DataRoleMenus/role={role}/menu={menu}', ['uses' => 'UserController@del_datarolemenus', 'as' => 'datarolemenus-del']);
    Route::get('Del-DataRoleHeadMenus/role={role}/menu={menu}', ['uses' => 'UserController@del_dataroleheadmenus', 'as' => 'dataroleheadmenus-del']);
    Route::get('Del-DataRoleSubHeadMenus/role={role}/menu={menu}', ['uses' => 'UserController@del_datarolesubheadmenus', 'as' => 'datarolesubheadmenus-del']);
    Route::get('View-DataRoleMenus/role={role}', ['uses' => 'UserController@view_datarolemenus', 'as' => 'datarolemenus-view']);
});

Route::get('pic-api/gambar/{recid}/{disk?}', function ($recid, $disk = '') {
    //menampilkan gambar
    if (empty($disk)) {
    	$disk = 'sftp';
    }
    $mime = "image/jpeg";

    $cek = Storage::disk($disk)->has(str_replace('&', '/', $recid));
    if($cek){
    	$img = Storage::disk($disk)->get(str_replace('&', '/', $recid));
    	$mime = Storage::disk($disk)->mimeType(str_replace('&', '/', $recid));
    }else{
    	$img = Storage::disk($disk)->get('imagenotfound.png');
    }

    return Response::make($img, 200, ['Content-Type' => $mime]);
});

Route::get('pic-tangki/gambar/{recid}', function ($recid) {
    //menampilkan gambar
    $disk = "sftp-test";
    $mime = "image/jpeg";

    $cek = Storage::disk($disk)->has(str_replace('&', '/', $recid));
    if($cek){
    	$img = Storage::disk($disk)->get(str_replace('&', '/', $recid));
    	$mime = Storage::disk($disk)->mimeType(str_replace('&', '/', $recid));
    }else{
    	$img = Storage::disk($disk)->get('imagenotfound.png');
    }

    return Response::make($img, 200, ['Content-Type' => $mime]);
});

Route::get('doc-api/dokumen/{recid}', function ($recid) {
    //menampilkan gambar
    $cek = Storage::disk('sftp-doc')->has(str_replace('&', '/', $recid));
    if($cek){
    	$doc = Storage::disk('sftp-doc')->get(str_replace('&', '/', $recid));
    }else{
    	$doc = Storage::disk('sftp')->get('imagenotfound.png');
    }
    return Response::make($doc, 200, ['Content-Type' => 'application/pdf']);
});

// API
Route::group(['prefix' => 'api', 'namespace' => 'Api', 'middleware' => 'nip'], function() {
	Route::post('login', 			['uses' => 'UserController@login']);
	Route::post('logout', 			['uses' => 'UserController@logout']);

	Route::group(['prefix' => 'monitoring'], function() {
		Route::get('/', 				['uses' => 'MonitoringController@index']);

		Route::get('wo-perbaikan', 		['uses' => 'WoController@perbaikan']);
		Route::get('wo-perawatan', 		['uses' => 'WoController@perawatan']);

		Route::post('wo-perbaikan-inv', 		['uses' => 'WoController@perbaikanInvSimpan']);
		Route::post('wo-perawatan-inv', 		['uses' => 'WoController@perawatanInvSimpan']);
		
		Route::post('wo-perbaikan', 		['uses' => 'WoController@perbaikanSimpan']);
		Route::post('wo-perawatan', 		['uses' => 'WoController@perawatanSimpan']);

		Route::get('wo-perbaikan-detail', 		['uses' => 'WoController@perbaikanDetail']);
		Route::get('wo-perawatan-detail', 		['uses' => 'WoController@perawatanDetail']);
	});

	Route::group(['prefix' => 'todolist'], function() {
		Route::get('/', 		['uses' => 'MonitoringController@todolist']);
		Route::get('scan', 		['uses' => 'MonitoringController@scan']);
		Route::post('simpan',	['uses' => 'MonitoringController@simpan']);
		// Development
		Route::post('simpandev',	['uses' => 'MonitoringController@simpandev']);
		Route::get('testjson/{kodefm}', 		['uses' => 'MonitoringController@testjson']);
	});

	Route::group(['prefix' => 'aduan'], function() {
		Route::get('/', 				['uses' => 'AduanController@index']);
		Route::post('tambah', 			['uses' => 'AduanController@tambah']);
		Route::post('investigasi', 		['uses' => 'AduanController@investigasi']);
		Route::post('analisa', 			['uses' => 'AduanController@analisa']);
	});

	Route::group(['prefix' => 'aduan-spv'], function() {
		Route::get('/', 				['uses' => 'AduanSpvController@index']);
		Route::get('listpetugas', 		['uses' => 'AduanSpvController@petugas']);
		Route::get('detail/{recidkeluhan}', 	['uses' => 'AduanSpvController@detail']);
		Route::post('disposisi', 		['uses' => 'AduanSpvController@disposisi']);
		Route::post('metode', 			['uses' => 'AduanSpvController@metode']);
		Route::post('close', 			['uses' => 'AduanSpvController@close']);
	});

	Route::group(['prefix' => 'monitoring-spv'], function() {
		Route::get('prw', 				['uses' => 'MonitoringSpvController@perawatan']);
		Route::post('prw-disposisi',	['uses' => 'MonitoringSpvController@prwDisposisi']);
		Route::post('prw-metode',		['uses' => 'MonitoringSpvController@prwMetode']);
		Route::post('prw-close',		['uses' => 'MonitoringSpvController@prwClose']);

		Route::get('prb', 				['uses' => 'MonitoringSpvController@perbaikan']);
		Route::post('prb-disposisi',	['uses' => 'MonitoringSpvController@prbDisposisi']);
		Route::post('prb-metode',		['uses' => 'MonitoringSpvController@prbMetode']);
		Route::post('prb-close',		['uses' => 'MonitoringSpvController@prbClose']);
	});

	Route::group(['prefix' => 'approval'], function() {
		// aduan
		Route::get('aduan', 				['uses' => 'ApprovalController@aduan']);
		Route::post('aduan', 				['uses' => 'ApprovalController@approveAduanManajer']);
		Route::post('aduan-dalops', 		['uses' => 'ApprovalController@approveAduanDalops']);

		// perbaikan monitoring
		Route::get('perbaikan', 			['uses' => 'ApprovalController@perbaikan']);
		Route::post('perbaikan', 			['uses' => 'ApprovalController@approvePerbaikanManajer']);
		Route::post('perbaikan-dalops', 	['uses' => 'ApprovalController@approvePerbaikanDalops']);

		// perawatan monitoring
		Route::get('perawatan', 			['uses' => 'ApprovalController@perawatan']);
		Route::post('perawatan', 			['uses' => 'ApprovalController@approvePerawatanManajer']);
		Route::post('perawatan-dalops', 	['uses' => 'ApprovalController@approvePerawatanDalops']);

		// Perawatan Rutin
		Route::get('perawatan-rutin',		['uses' => 'ApprovalController@perawatanRutin']);
		Route::post('perawatan-rutin',		['uses' => 'ApprovalController@approvePerawatanRutinManajer']);
		Route::post('perawatan-rutin-dalops',['uses' => 'ApprovalController@approvePerawatanRutinDalops']);

		// Approve MS PPP (all wo perbaikan perawatan)
		Route::post('ms-ppp', 				['uses' => 'ApprovalController@approveMsPpp']);
	});

	Route::group(['prefix' => 'aduan-non-operasi'], function() {
		Route::get('',						['uses' => 'NonOperasiController@aduan']);
		Route::get('selectJab',				['uses' => 'AduanNonOperasiController@selectJab']);
		Route::post('tambah',				['uses' => 'AduanNonOperasiController@store']);

		Route::group(['prefix' => 'petugas'], function() {
			Route::post('investigasi',		['uses' => 'AduanNonOperasiController@investigasi']);
			Route::post('penanganan',		['uses' => 'AduanNonOperasiController@penanganan']);
		});

		Route::group(['prefix' => 'spv'], function() {
			Route::post('disposisi',		['uses' => 'AduanNonOperasiSpvController@disposisi']);
			Route::post('metode',			['uses' => 'AduanNonOperasiSpvController@metode']);
			Route::post('close',			['uses' => 'AduanNonOperasiSpvController@aduanClose']);
		});

		Route::group(['prefix' => 'manajer'], function() {
			Route::post('approve',			['uses' => 'AduanNonOperasiApprovalController@approveManajer']);
			Route::post('approve-dalops',	['uses' => 'AduanNonOperasiApprovalController@approveDalops']);
		});
	});

	Route::group(['prefix' => 'usulan-non-operasi'], function() {
		Route::get('',						['uses' => 'NonOperasiController@usulan']);
		Route::post('tambah',				['uses' => 'UsulanNonOperasiController@store']);

		Route::group(['prefix' => 'petugas'], function() {
			Route::post('investigasi',		['uses' => 'UsulanNonOperasiController@investigasi']);
			Route::post('penanganan',		['uses' => 'UsulanNonOperasiController@penanganan']);
		});

		Route::group(['prefix' => 'spv'], function() {
			Route::post('disposisi',		['uses' => 'UsulanNonOperasiSpvController@disposisi']);
			Route::post('metode',			['uses' => 'UsulanNonOperasiSpvController@metode']);
			Route::post('close',			['uses' => 'UsulanNonOperasiSpvController@usulanClose']);
		});

		Route::group(['prefix' => 'manajer'], function() {
			Route::post('approve',			['uses' => 'UsulanNonOperasiApprovalController@approveManajer']);
			Route::post('approve-dalops',	['uses' => 'UsulanNonOperasiApprovalController@approveDalops']);
		});
	});

	Route::group(['prefix' => 'prw-rutin'], function() {
		Route::get('index',						['uses' => 'RutinController@index']);		

		Route::group(['prefix' => 'petugas'], function() {
			Route::post('penanganan',		['uses' => 'RutinController@penanganan']);
		});

		Route::group(['prefix' => 'spv'], function() {
			Route::post('disposisi',		['uses' => 'RutinSpvController@disposisi']);
		});
	});	

	Route::group(['prefix' => 'sukucadang'], function() {
		Route::get('permohonan',						['uses' => 'SukuCadangController@listPermohonan']);
		Route::get('permohonan-list',					['uses' => 'SukuCadangController@listPermohonanAll']);
		Route::get('stok',								['uses' => 'SukuCadangController@stok']);
		Route::get('delete',							['uses' => 'SukuCadangController@delete']);
		Route::post('permohonan',						['uses' => 'SukuCadangController@permohonan']);
		Route::post('waiting-list',						['uses' => 'SukuCadangController@waitingList']);
		Route::post('kirim-waitinglist',				['uses' => 'SukuCadangController@kirimWaitinglist']);
	});

	Route::group(['prefix' => 'master'], function() {
		Route::get('instalasi',				['uses' => 'MasterController@instalasi']);
		Route::get('lokasi',				['uses' => 'MasterController@lokasi']);
		Route::get('status',				['uses' => 'MasterController@status']);
		Route::get('barang',				['uses' => 'MasterController@barang']);
		Route::get('unitkerja',				['uses' => 'MasterController@unitKerja']);
		Route::get('gudang',				['uses' => 'MasterController@gudang']);

	});

	Route::group(['prefix' => 'proposal'], function() {
		Route::post('simpan',			['uses' => 'ProposalController@simpan']);
		Route::get('getjson/{wo}/{id}',	['uses' => 'ProposalController@getJson1']);
		Route::get('getjson1/{wo}/{id}',	['uses' => 'ProposalController@getJson1']);
		Route::get('loadpdf/{wo}/{id}',	['uses' => 'ProposalController@loadPdf']);
	});

	Route::group(['prefix' => 'test'], function() {
		Route::get('index', 				['uses' => 'DmgLabController@index']);
		Route::get('form', 				['uses' => 'DmgLabController@testForm']);
		Route::post('up', 				['uses' => 'DmgLabController@index']);
		Route::post('down', 				['uses' => 'DmgLabController@down']);
	});
});

// Non Auth access
Route::group(['prefix' => 'api-general', 'namespace' => 'Api'], function() {
	Route::get('index', 				['uses' => 'DmgLabController@index']);
	Route::post('postlab', 				['uses' => 'DmgLabController@postlab']);
	
	Route::group(['prefix' => 'aduan-non-operasi'], function() {
		Route::get('selectJab',				['uses' => 'AduanNonOperasiController@selectJab']);
		Route::post('tambah',				['uses' => 'AduanNonOperasiController@store']);
	});

	Route::group(['prefix' => 'usulan-non-operasi'], function() {
		Route::post('tambah',				['uses' => 'UsulanNonOperasiController@store']);
	});	

	Route::group(['prefix' => 'aduan'], function() {
		Route::get('/', 				['uses' => 'AduanController@index']);
		Route::post('tambah', 			['uses' => 'AduanController@tambah']);
	});

	Route::group(['prefix' => 'jadwal-pompa'], function() {
		Route::get('/', 				['uses' => 'JadwalPompaController@index']);
		Route::post('switch', 			['uses' => 'JadwalPompaController@switch']);
	});

	Route::group(['prefix' => 'biaya-opr'], function() {
		Route::get('/', 				['uses' => 'LccaController@index']);
		Route::post('tambah', 			['uses' => 'LccaController@oprStore']);
		Route::get('get-pemakaian',     ['uses' => 'LccaController@show']);
		Route::post('hapus',     		['uses' => 'LccaController@oprDelete']);
	});
		
	// Master
	Route::group(['prefix' => 'master'], function() {
		Route::get('instalasi',				['uses' => 'MasterController@instalasi']);
		Route::get('lokasi',				['uses' => 'MasterController@lokasi']);
		Route::get('status',				['uses' => 'MasterController@status']);
		Route::get('barang',				['uses' => 'MasterController@barang']);
		Route::get('unitkerja',				['uses' => 'MasterController@unitKerja']);
		Route::get('gudang',				['uses' => 'MasterController@gudang']);
		Route::get('aset', 					['uses' => 'MasterController@allAset']);
		Route::get('combo-aset',			['uses' => 'MasterController@asetSelect']);

		Route::get('combo-lokasi/{id}',			['uses' => 'MasterController@lokasiSelect']);
		Route::get('combo-ruang/{id}',			['uses' => 'MasterController@ruangSelect']);

		Route::get('combo-subkategori/{id}',	['uses' => 'MasterController@subkategoriSelect']);
		Route::get('combo-subsubkategori/{id}',	['uses' => 'MasterController@subsubkategoriSelect']);
	});
});
// ./API
