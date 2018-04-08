<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Value extends Model
{
    protected $primaryKey = 'vid';

    protected $fillable = [
        'hid', 'oid', 'rid', 'price', 'date', 'type', 'status', 'description'
    ];

    public $timestamps = false;

    public function toInsert($input)
    {
        return Value::insert($input);
    }

    public function toDelete($tid, $hid, $rid, $oid, $date)
    {
        return Value::where('tid', $tid)
            ->where('hid', $hid)
            ->where('rid', $rid)
            ->where('oid', $oid)
            ->where('date', '>=', $date)
            ->where('type', 0)
            ->delete();
    }

    public function toUpdateStatus($hid, $vid, $date, $status = 1)
    {
        $date = date('Ymd', strtotime($date));

        return DB::update("UPDATE `values` SET `status` = ? WHERE `hid` = ? AND `vid` = ? AND `date` <= ? AND `status` = 0",
            [
                $status, $hid, $vid, $date
            ]);

    }

    public function toBetchUpdateStatus($hid, $rid, $date, $status = 1)
    {
        $date = date('Ymd', strtotime($date));

        return DB::update('UPDATE `values` SET `status` = ? WHERE `hid` = ? AND `rid` = ? AND `date` >= ? AND `status` = 0',
            [
                $status, $hid, $rid, $date
            ]);

    }

    public function toUpdate($tid, $hid, $before, $after, $oid, $date)
    {
        return Value::where('tid', $tid)
            ->where('hid', $hid)
            ->where('rid', $before)
            ->where('oid', $oid)
            ->where('date', '>=', $date)
            ->where('type', 0)
            ->update(['rid' => $after, 'description' => '换房后价格']);
    }

    public function toFindValue($hid, $vid)
    {
        $result = DB::select("SELECT `vid`, `date`, `description` FROM `values` WHERE `hid` = ? AND `vid` = ? AND `status` = 0 LIMIT 0,1", [$hid, $vid]);
        return head($result);
    }

    public function toUpdateValue($vid, $price, $description, $hid)
    {
        return DB::update('UPDATE `values` SET `price` = ?, `description` = ? WHERE `hid` = ? AND `vid` = ? AND `status` = 0', [$price, $description, $hid, $vid]);
    }

    public function toFind($tid, $hid)
    {
        return Value::select('rid', 'name', 'description', 'stock')
            ->where('hid', $hid)
            ->where('tid', $tid)
            ->first();
    }

    public function toGet($hid, $rid, $oid)
    {
        return DB::select('SELECT `vid`, price, date, `status` FROM `values` WHERE hid = ? AND rid = ? AND oid = ?', [$hid, $rid, $oid]);
    }

    public function toGetNowLeavePrice($hid, $oid, $date, $rid = 0)
    {
        $date = date('Ymd', strtotime($date));

        return Value::select('vid', 'values.rid', 'rooms.room_no', 'price', 'date')
            ->leftJoin('rooms', 'rooms.rid', '=', 'values.rid')
            ->where('values.hid', $hid)
            ->where('values.oid', $oid)
            ->where('values.rid', empty($rid) ? '<>' : '=', $rid)
            ->where('values.type', 1)
            ->where('values.date', '<=', $date)
            ->where('values.status', 0)
            ->get();
    }

    public function toGetFreeStock($hid, $oid, $date, $rid = 0)
    {
        return Value::select('tid', DB::raw('count(tid) as free_stock'), 'date')
            ->where('oid', $oid)
            ->where('hid', $hid)
            ->where('rid', (empty($rid) ? '<>' : '='), $rid)
            ->where('date', '>=', $date)
            ->where('type', '<>', 1)
            ->groupBy('tid')
            ->groupBy('date')
            ->get();
    }

    public function toUpdateRid($hid, $lid, $rid)
    {
        return DB::update('UPDATE `values` SET rid = ?, status = 0 WHERE lid = ? AND hid = ?', [$rid, $lid, $hid]);
    }

    public function toGroupGetRoom($hid, $tid, $start, $end)
    {
        $start = date('Ymd', strtotime($start));
        $end = date('Ymd', strtotime($end));

        return Value::select('rid')
            ->where('hid', $hid)
            ->where('tid', $tid)
            ->where('type', '<>', 1)
            ->where('status', '=', 0)
            ->where('date', '>=', $start)
            ->where('tid', '<', $end)
            ->groupBy('rid')
            ->get();
    }

    public function toGetDayReceipt($hid, $date)
    {
        $date = date('Ymd', strtotime($date));

        return DB::select('SELECT `values`.`vid`, `values`.`oid`, `values`.`rid`, `rooms`.`room_no`, `values`.`price`, `values`.`date` FROM `values` LEFT JOIN rooms ON rooms.rid = `values`.rid WHERE `values`.`hid` = ? AND `values`.`date` <= ? AND `values`.`status` = 0',
            [
                $hid, $date
            ]);
    }

}
