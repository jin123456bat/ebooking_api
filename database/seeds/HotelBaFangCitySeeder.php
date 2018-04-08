<?php

use Illuminate\Database\Seeder;

class HotelBaFangCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('hotels')->insert([
            'name' => '西溪八方城',
            'name_english' => 'XixiBaFangCity',
            'address' => '浙江 杭州 钱塘江',
            'time' => date('Ymd'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
