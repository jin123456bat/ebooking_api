<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Censor extends Model
{
    public function toCreate($hid, $date)
    {
        DB::insert('INSERT INTO `censors` (hid, time, date) VALUES (?, ?, ?)', [$hid, time(), $date]);
    }
}