<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $this->call('HotelBaFangCitySeeder');      //  生成酒店
         $this->call('HotelTokenBaFangCitySeeder'); //  酒店 token
    }
}
