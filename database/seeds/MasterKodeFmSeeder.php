<?php

use Illuminate\Database\Seeder;

class MasterKodeFmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('MASTER_KODEFM')->insert([
		    /*[
		    	'kode' => 'E1', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],*/
		   	/*[
		    	'kode' => 'E2', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'E3', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'E5', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'E5B', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'E5C', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'E6', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'E11', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'E11B', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'E11C', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'E13', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'E14', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'E15', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'E16', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'E17', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],*/
		   	/*[
		    	'kode' => 'M1', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'M1B', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'M1C', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'M2', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'M2B', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'M2C', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'M3', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'M4', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'M4B', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'M4C', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'M5', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'M6', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'M9', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'M10', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'M12', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'M13', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'M13B', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],[
		    	'kode' => 'M13C', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B1',
		    	'umur_ekonomis' => 8
		   	],*/
		   	/*[
		    	'kode' => 'E4', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B2',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'E4A', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B2',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'E4B', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B2',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'E4C', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B2',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'E4D', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B2',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'E5A', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B2',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'E11A', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B2',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'I1', 
		    	'bagian' => '3',
		    	'kode_bobot' => 'B2',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'I2', 
		    	'bagian' => '3',
		    	'kode_bobot' => 'B2',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'I3', 
		    	'bagian' => '3',
		    	'kode_bobot' => 'B2',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'I4', 
		    	'bagian' => '3',
		    	'kode_bobot' => 'B2',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'M1A', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B2',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'M2A', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B2',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'M4A', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B2',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'M11', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B2',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'M13A', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B2',
		    	'umur_ekonomis' => 4
		   	],*/
		   	/*[
		    	'kode' => 'E7', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B3',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'E8', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B3',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'E9', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B3',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'E10', 
		    	'bagian' => '2',
		    	'kode_bobot' => 'B3',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'M7', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B3',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'M8', 
		    	'bagian' => '1',
		    	'kode_bobot' => 'B3',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'S7', 
		    	'bagian' => '4',
		    	'kode_bobot' => 'B3',
		    	'umur_ekonomis' => 4
		   	],[
		    	'kode' => 'S8', 
		    	'bagian' => '4',
		    	'kode_bobot' => 'B3',
		    	'umur_ekonomis' => 4
		   	],*/
		   	[
		    	'kode' => 'S1', 
		    	'bagian' => '4',
		    	'kode_bobot' => 'B4',
		    	'umur_ekonomis' => 20
		   	],[
		    	'kode' => 'S2', 
		    	'bagian' => '4',
		    	'kode_bobot' => 'B4',
		    	'umur_ekonomis' => 20
		   	],[
		    	'kode' => 'S3', 
		    	'bagian' => '4',
		    	'kode_bobot' => 'B4',
		    	'umur_ekonomis' => 20
		   	],[
		    	'kode' => 'S4', 
		    	'bagian' => '4',
		    	'kode_bobot' => 'B4',
		    	'umur_ekonomis' => 20
		   	],[
		    	'kode' => 'S5', 
		    	'bagian' => '4',
		    	'kode_bobot' => 'B4',
		    	'umur_ekonomis' => 20
		   	],[
		    	'kode' => 'S6', 
		    	'bagian' => '4',
		    	'kode_bobot' => 'B4',
		    	'umur_ekonomis' => 20
		   	],
		]);
    }
}
