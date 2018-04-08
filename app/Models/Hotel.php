<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    public function toGet()
    {
        return Hotel::select('hid', 'time')
            ->get();
    }

    public function toFind($hid)
    {
        return Hotel::select('hid', 'name', 'address', 'phone', 'time', 'censor')
            ->where('hid', $hid)
            ->first();
    }

    static function toGetTime($hid)
    {
        $hotel = Hotel::select('time')
            ->where('hid', $hid)
            ->first();

        return $hotel['time'];
    }

    public function toUpdateDate($hid, $date)
    {
        return Hotel::where('hid', $hid)
            ->update(['time' => $date]);
    }
}