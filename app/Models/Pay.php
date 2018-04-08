<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pay extends Model
{
    static function toGetCash($hid)
    {
        $pay = Pay::select('pid')
            ->where('hid', $hid)
            ->where('type', 0)
            ->first();

        return $pay->pid;
    }

    public function toFind($hid, $pid)
    {
        return Pay::select('pid', 'name', 'type', 'remark')
            ->where('hid', $hid)
            ->where('pid', $pid)
            ->first();
    }

    public function toGet($hid)
    {
        return Pay::select('pid', 'name')
            ->where('hid', $hid)
            ->get();
    }

    public function toFindRMBPid($hid)
    {
        $result = DB::select('SELECT * FROM pays WHERE hid = ? AND type = 0 LIMIT 1', [$hid]);

        $tmp = head($result);

        return isset($tmp->pid) ? $tmp->pid : 0;
    }
}