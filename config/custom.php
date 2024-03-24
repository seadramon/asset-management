<?php

return [

// Exception role when data filtered by login \Auth::user()->userid
	'roleException' => [
		'SPV PENGOLAHAN',
		'SPV PERENCANAAN OPERASI',
		'Super Administrator'
	],

// Exception role when data filtered by login \Auth::user()->userid without admin
	'roleExceptionNoAdmin' => [
		'SPV PENGOLAHAN',
		'SPV PERENCANAAN OPERASI',
	],

	'rolePengolahan' => [
		'SPV PENGOLAHAN', 
		'ADMIN PENGOLAHAN',
		'MANAJER PRODUKSI'
	],


//	Kelayakan Operasional
	'kelayakanOperasional' => [
		''  => "- Pilih -",
		'1' => "Tidak ada dampak produksi",
		'2' => "Penurunan produksi kecil",
		'3' => "Penurunan produksi / Kualitas diambang batas",
		'4' => "Kehilangan produksi kecil",
		'5' => "Kehilangan produksi / Kualitas tidak diterima"
	], 

// Kelayakan Keuangan
	'kelayakanKeuangan' => [
		'',
		'NPV > 3' => 'NPV > 3',
		'NPV 2 - 3' => 'NPV 2 - 3',
		'NPV 1 - 2' => 'NPV 1 - 2',
		'NPV = 1' => 'NPV = 1',
		'NPV  < 1' => 'NPV  < 1'
	],

//Waktu Kebutuhan
	'waktuKebutuhan' => [
		''  => '- Pilih -',
		'1' => 'Nice to Have (>1 tahun)',
		'2' => '2',
		'3' => 'Important (1 tahun)',
		'4' => '4',
		'5' => 'Urgent (<1 tahun)'
	],

// Status PKO
	'statusPerencanaan' => [
		'2',
		'4.0',
		'4.1',
		'4.2',
		'4.3'
	],

	// 'manajerDalops' => '10601381',
	'manajerDalops' => '10601344',

	'dalops' => [
		'10601344',
        '10601381',
        '10901554'
	],

// recidjabatan
	'80' => [ //SPV Pemeliharaan Mekanikal & Pompa Ngagel
		'lokasi' => ['17', '18', '19'],
		'bagian' => ['1']
	],
	'81' => [ //SPV Pemeliharaan Mekanikal & Pompa Karang Pilang
		'lokasi' => ['21', '22', '23'],
		'bagian' => ['1']
	],
	'82' => [ //SPV Pemeliharaan Elektrikal Ngagel
		'lokasi' => ['17', '18', '19'],
		'bagian' => ['2']
	],
	'83' => [ //SPV Pemeliharaan Elektrikal Karang Pilang
		'lokasi' => ['21', '22', '23'],
		'bagian' => ['2']
	],
	'84' => [ //SPV Pemeliharaan Mekanikal & Elektrikal Distribusi
		'lokasi' => [],
		'bagian' => []
	],
	'85' => [ //SPV Pemeliharaan Sipil Ngagel
		'lokasi' => ['17', '18', '19'],
		'bagian' => ['4']
	],
	'86' => [ //SPV Pemeliharaan Sipil Karang Pilang
		'lokasi' => ['21', '22', '23'],
		'bagian' => ['4']
	],
	'87' => [ 
		'lokasi' => [],
		'bagian' => []
	],
	'218' => [//SPV Kontrol Digital dan Instrumentasi
		'lokasi' => ['17','18','19','21','22','23','36','16','24','25','26','27','28','29','30','31','32','33','34','35','39','61','62','63','64','67'],
		'bagian' => ['3']
	],
	'231' => [ //SPV Rumah Pompa
		'lokasi' => ['16','36','37','24','25','26','27','28','29','30','31','32','33','34','35','39','61','62','63','64','67'],
		'bagian' => ['1', '2', '4']
	],

	'spvPelaksana' => ['80', '82', '85', '81', '83', '86', '218', '223', '224', '64','65','66', '67','68','69'],


	'bagian' => [
		'1' => 'Mekanikal',
		'2' => 'Elektrikal',
		'3' => 'Instrumentasi',
		'4' => 'Sipil' 
	],


	'skipStatus' => [
		'10',
		'99',
		'98'
	],

	'hideStatus' => [
		'99',
		'98'
	],

	'kondisi' => [
        '' => '- Pilih Kondisi -',
        'beroperasi' => 'Dapat Beroperasi',
        'tidak beroperasi' => 'Tidak Dapat Beroperasi'
    ],

    'metode' => [
        '' => '- Pilih Pelaksana Pekerjaan -',
        'internal' => 'Internal',
        'eksternal pp' => 'Eksternal PP',
        'eksternal kontrak payung' => 'Eksternal Kontrak Payung',
        'masa garansi pemeliharaan' => 'Masa Garansi Pemeliharaan',
        'masa garansi investasi' => 'Masa Garansi Investasi'
    ],

    'tingkat' => [
        '' => '- Pilih Tingkat Perbaikan -',
        'ringan' => 'Ringan (Repair)',
        'berat' => 'Berat (Refurbish)',
        'replace' => 'Penggantian Aset (Replace)',
        'overhoul' => 'Overhoul',
    ],

    'sifat' => [
        '' => '- Pilih Sifat Pekerjaan -',
        'overhaul' => 'Overhaul',
        'emergency' => 'Emergency',
        'biasa' => 'Biasa',
    ], 

    'pko' => [
    	'MANAJER PERENCANAAN OPERASI',
    	'SPV PERENCANAAN OPERASI',
    	'SPV PERENCANAAN PROSES DAN PEMELIHARAAN',
    	'MANAJER PERENCANAAN PROSES DAN PEMELIHARAAN'
    ],

    'pko-statusdisplay' => ['4.0', '4.1', '4.2', '4.3'],

    'mainRole' => [
    	'PETUGAS MONITORING',
    	'SPV PEMELIHARAAN',
    	'MANAJER PEMELIHARAAN',
    	'SPV PENGENDALIAN ASET OPERASI',
    	'SPV DALPRO',
    	'MS Pengendalian Proses & Perencanaan (PPP)',
    	'MANAJER PERENCANAAN PROSES DAN PEMELIHARAAN',
    	'MANAJER PEMELIHARAAN/TRANDIST/TSI',
    	'SPV PENGOLAHAN',
    	'ADMIN PENGOLAHAN',
    	'MANAJER PENGENDALIAN PROSES',
    	'MS PEMELIHARAAN INSTALASI'
    ],

    'idMainRole' => [
    	'26', '2', '28', '11', '1'
    ],

    'filterStatus' => [
    	'semua' => 'Semua',
        'baru' => 'Baru',
        'investigasi' => 'Investigasi',
        'sudah diinvestigasi' => 'Sudah diinvestigasi',
        'menunggu approval manajer pemeliharaan' => 'Menunggu Approval Manajer Pemeliharaan',
        'revisi input metode dari manajer pemeliharaan' => 'Revisi Input Metode dari Manajer Pemeliharaan',
        'menunggu approval manajer dalops' => 'Menunggu Approval Manajer Dalops',
        'revisi input metode dari manajer dalops' => 'Revisi Input Metode dari Manajer Dalops',
        'menunggu approval ms ppp' => 'Menunggu Approval MS Pengendalian Proses & Perencanaan (PPP)',
        'revisi input metode dari ms ppp' => 'Revisi Input Metode dari MS PPP',
        'proses ded (baru)' => 'Proses DED (Baru)',
        'proses ded (proses)' => 'Proses DED (proses)',
        'proses ded (revisi)' => 'Proses DED (revisi)',
        'proses ded (selesai)' => 'Proses DED (Selesai)',
        'penanganan' => 'Penanganan',
        'selesai' => 'Selesai',
        'nonaktif' => 'Non Aktif / Ditolak',
        'digantikan' => 'Digantikan',
    ],

    'filterStatusPko' => [
    	'proses ded (baru)' => 'Proses DED (Baru)',
        'proses ded (proses)' => 'Proses DED (proses)',
        'proses ded (revisi)' => 'Proses DED (revisi)',
        'proses ded (selesai)' => 'Proses DED (Selesai)',
        'penanganan' => 'Penanganan'
    ],

    'id_ms_ppp' => 211, //Manajer PKO

	'kodejabatanManajer' => [
		'029', '209', '210', '022'
	],

	// Instalasi id yang masuk kategori lokasi IPAM
	'ipam' => [
		'17', '18', '19', '21', '22', '23'
	],

	// Instalasi id yang masuk kategori lokasi Distribusi (KECUALI)
	'distribusiExcept' => [
		'49', '16', '1','2','3','4','5','6','7','8','9','10','11','12','13','14'
	],

	// recidrole produksi
	'jabatanProduksiIpamNgagel' => [
		'64','65','66'
	],

	'jabatanProduksiIpamKp' => [
		'67','68','69'
	],

	'penggunaan_anggaran' => [
		'rkap' => 'RKAP',
		'tahun berjalan' => 'Tahun Berjalan'
	],

	'lingkup_kerja' => [
		'Perbaikan/Overhoul/ Penggantian Aset Operasi' => 'Perbaikan/Overhoul/ Penggantian Aset Operasi',
		'Perubahan/ Penambahan' => 'Perubahan/ Penambahan'
	]
];

