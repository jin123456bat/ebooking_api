<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Live extends Model
{
    protected $primaryKey = 'lid';

    public $timestamps = false;

    protected $fillable = [
        'hid', 'oid', 'tid', 'uid', 'rid', 'money', 'deposit', 'find_price', 'start', 'end', 'save', 'init', 'breakfast', 'status', 'type'
    ];

    protected $hidden = [];

    public function toCreate($input)
    {
        return Live::create([
            'hid' => $input['hid'],
            'oid' => $input['oid'],
            'tid' => $input['tid'],
            'uid' => $input['uid'],
            'rid' => $input['rid'],
            'money' => $input['money'],
            'deposit' => $input['deposit'],
            'find_price' => $input['find_price'],
            'start' => $input['start'],
            'end' => $input['end'],
            'save' => $input['save'],
            'init' => time(),
            'breakfast' => $input['breakfast'],
            'status' => $input['status'],
            'type' => $input['type']
        ]);
    }

    public function toUpdateChange($live, $input)
    {
        $live->tid = $input['tid'];
        $live->rid = $input['rid'];

        if (isset($input['find_price'])) {

            $live->find_price = $input['find_price'];
        }

        $live->save();
    }

    public function toUpdateRid($hid, $lid, $rid)
    {
        return DB::update('UPDATE `lives` SET `rid` = ? WHERE `lid` = ? AND `hid` = ?', [$rid, $lid, $hid]);
    }

    public function toUpdate($price, $input)
    {
        $price->find_price = $input['find_price'];
        $price->end = $input['end'];

        $price->save();
    }

    public function toUpdateStatus($lid, $status)
    {
        return Live::where('lid', $lid)
            ->update(['status' => $status]);
    }

    public function toUpdateOrdainLive($hid, $lid, $uid)
    {
        return DB::update('UPDATE `lives` SET `uid` = ?, `status` = 1 WHERE `hid` = ? AND `lid` = ?', [$uid, $hid, $lid]);
    }

    public function toFind($hid, $oid, $rid)
    {
        return Live::select('lid', 'uid', 'tid', 'find_price', 'end')
            ->where('hid', $hid)
            ->where('oid', $oid)
            ->where('rid', $rid)
            ->first();
    }

    public function toFindLiveWithRid($hid, $rid)
    {
        $result = DB::select('SELECT `lid`, `oid`, `lives`.`rid`, `room_no` AS `no`, lives.`tid` FROM `lives` LEFT JOIN rooms ON lives.rid = rooms.rid WHERE lives.hid = ? AND lives.rid = ? AND lives.`status` = 1 LIMIT 1', [$hid, $rid]);

        return head($result);
    }

    public function toFindLiveRoom($hid, $rid, $type = 0)
    {
        $result = DB::select('SELECT `lid`, `oid`, `start`, `end`, `init`, `breakfast` FROM `lives` WHERE hid = ? AND rid = ? AND type = ? AND `status` = 1 LIMIT 1', [$hid, $rid, $type]);

        return head($result);
    }

    public function toFindOrdainLive($hid, $lid)
    {
        $result = DB::select('SELECT `lid`, `oid`, `rid`, `tid`, `start`, `end` FROM `lives` WHERE hid = ? AND lid = ? AND `status` = 0 LIMIT 0,1', [$hid, $lid]);

        return head($result);
    }

    public function toFindChangeLive($hid, $rid, $oid)
    {
        $result = DB::select('SELECT `lid`, `oid`, `rid`, `tid`, `start`, `end` FROM `lives` WHERE hid = ? AND rid = ? AND oid = ? AND `status` = 1 LIMIT 0,1', [$hid, $rid, $oid]);

        return head($result);
    }

    public function toGetLiveRoom($hid, $tid, $start, $end)
    {
        return DB::select('SELECT rid FROM `lives` WHERE hid = ? AND tid = ? AND rid != \'\' AND type = 0 AND (`status` = 0 OR `status` = 1 OR `status` = 3) AND ( ( ? <= `start` AND ? >= `end` ) OR ( ? >= `start` AND ? <= `end` ) OR ( ? >= `start` AND ? < `end` AND ? >= `end` ) OR ( ? <= `start` AND ? >= `start` AND ? <= `end` ) ) GROUP BY rid', [$hid, $tid, $start, $end, $start, $end, $start, $start, $end, $start, $end, $end]);
    }

    public function toCountQuit($hid, $oid, $rid = 0)
    {
        return Live::where('hid', $hid)
            ->where('oid', $oid)
            ->where('rid', (empty($rid) ? '<>' : '='), $rid)
            ->count();
    }

    public function toQuit($hid, $oid, $rid = 0)
    {
        return Live::where('hid', $hid)
            ->where('oid', $oid)
            ->where('rid', (empty($rid) ? '<>' : '='), $rid)
            ->update([
                'leave' => time(),
                'status' => 2
            ]);
    }

    public function toCountLive($hid, $oid)
    {
        return Live::where('hid', $hid)
            ->where('oid', $oid)
            ->where('status', 0)
            ->count();
    }

    public function toGetRid($hid, $oid)
    {
        return Live::select('rid', 'tid', 'end')
            ->where('hid', $hid)
            ->where('oid', $oid)
            ->groupBy('rid')
            ->get();
    }

    public function toCountLeave($hid, $date)
    {
        return Live::where('hid', $hid)
            ->where('leave', 0)
            ->where('status', 1)
            ->where('end', '=', $date)
            ->count();
    }

    public function toGetLive($hid, $oid)
    {
        return DB::select('SELECT `lid`, `oid`, `tid`, `rid`, `start`, `end`, `save`, `init`, `breakfast`, `status` FROM `lives` WHERE hid = ? AND oid = ? AND type = 0', [$hid, $oid]);
    }

    public function toGetLeave($hid, $date)
    {
        return Live::select('oid', 'lives.rid', 'room_no', 'init', 'end')
            ->leftJoin('rooms', 'lives.rid', '=', 'rooms.rid')
            ->where('lives.hid', $hid)
            ->where('leave', 0)
            ->where('lives.status', 1)
            ->where('end', '=', $date)
            ->get();
    }

    public function toGetNotReach($hid, $date)
    {
        return DB::select('SELECT `lid`, `oid`, `tid`, `end` FROM `lives` WHERE hid = ? AND `start` <= ? AND `status` = 0', [$hid, $date]);
    }

    public function toGetOrderOtherBooking($hid, $oidArr, $date)
    {
        return DB::select('SELECT `oid` FROM `lives` WHERE `hid` = ? AND `status` = 0 AND `start` = ? AND `oid` IN (?) GROUP BY `oid`',
            [
                $hid, implode(',', $oidArr), $date
            ]);
    }

}