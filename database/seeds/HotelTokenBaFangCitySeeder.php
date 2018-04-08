<?php

use Illuminate\Database\Seeder;

class HotelTokenBaFangCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'hid' => 1000,
            'name' => '官网',
            'password' => \Illuminate\Support\Facades\Hash::make('PBZeNDpGuHNdJ124TOtSVVjJU03zCDwU'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
