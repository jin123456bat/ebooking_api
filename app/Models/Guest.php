<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Guest extends Model
{
    protected $primaryKey = 'gid';

    protected $fillable = [
        'hid', 'oid', 'uid', 'user', 'rid', 'start', 'end', 'remark', 'status'
    ];

    public function toInsert($input)
    {
        return Guest::insert($input);
    }

    public function toGet($hid, $rid, $oid)
    {
        return DB::select('SELECT `gid`, `uid`, `user`, `start`, `end`, `remark` FROM `guests` WHERE hid = ? AND rid = ? AND oid = ?', [$hid, $rid, $oid]);
    }

    public function toUpdateRid($hid, $oid, $before, $after)
    {
        return DB::update('UPDATE guests SET rid = ? WHERE hid = ? AND oid = ? AND rid = ?', [$after, $hid, $oid, $before]);
    }
}