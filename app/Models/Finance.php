<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Finance extends Model
{
    protected $primaryKey = 'fid';

    protected $fillable = [
        'hid', 'oid', 'rid', 'pay', 'price', 'remark', 'time', 'type'
    ];

    protected $guarded = [];

    public function toInsert($input)
    {
        return Finance::insert($input);
    }

    public function toCreate($input)
    {
        return Finance::create([
            'hid' => $input['hid'],
            'oid' => $input['oid'],
            'rid' => $input['rid'],
            'pay' => $input['pay'],
            'price' => $input['price'],
            'remark' => $input['remark'],
            'date' => $input['date'],
            'time' => $input['time'],
            'type' => $input['type']
        ]);
    }

    public function toGetRoom($hid, $oid, $rid)
    {
        return DB::select('SELECT fid, pays.`name`,price,finances.remark,time FROM `finances` LEFT JOIN pays ON finances.pay = pays.pid WHERE finances.hid = ? AND oid = ? AND finances.rid = ?', [$hid, $oid, $rid]);
    }

    public function toGetWithDay($date, $hid)
    {
        return DB::select('SELECT SUM(price) AS `price`, `finances`.`type`, `date` FROM `finances` WHERE `date` = ? AND `hid` = ? GROUP BY type ORDER BY `type` ASC', [$date, $hid]);
    }

    public function toConfirmRMB($hid)
    {
        return DB::update('UPDATE finances SET confirm = `time` WHERE hid = ? AND confirm = 0 AND pay = (SELECT pid FROM pays WHERE hid = ? AND type = 0 LIMIT 1)', [$hid, $hid]);
    }

    public function toConfirmWeChatOrAliPay($hid)
    {
        return DB::update('UPDATE finances SET confirm = `time` WHERE hid = ? AND confirm = 0 AND pay IN (SELECT pid FROM pays WHERE hid = ? AND type = 4)', [$hid, $hid]);
    }

    public function toGetConsumeWithDay($date, $hid)
    {
        return DB::select('SELECT finances.fid, finances.oid, pays.pid, pays.`name` AS `pay`, rooms.room_no, finances.time, finances.remark,price FROM finances LEFT JOIN rooms ON finances.rid = rooms.rid LEFT JOIN pays ON finances.pay = pays.pid WHERE finances.hid = ? AND finances.date = ?', [$hid, $date]);
    }

    public function toGetIncome($date, $hid)
    {
        return DB::select('SELECT finances.fid, finances.oid, pays.pid, pays.`name` AS `pay`, rooms.room_no AS `no`, finances.time, finances.remark, price FROM finances LEFT JOIN rooms ON finances.rid = rooms.rid LEFT JOIN pays ON finances.pay = pays.pid WHERE finances.hid = ? AND confirm > (SELECT censors.time FROM censors WHERE hid = ? AND date < ? ORDER BY cid DESC LIMIT 1) AND confirm <= (SELECT censors.time FROM censors WHERE hid = ? AND date = ? LIMIT 1)', [$hid, $hid, $date, $hid, $date]);
    }

    public function toGetAR($date, $hid)
    {
        return DB::select('SELECT finances.fid, finances.oid, pays.pid, pays.`name` AS `pay`, rooms.room_no AS `no`, finances.time, finances.remark, price FROM finances LEFT JOIN rooms ON finances.rid = rooms.rid LEFT JOIN pays ON finances.pay = pays.pid WHERE finances.hid = ? AND `date` = ? AND confirm = 0', [$hid, $date]);
    }

    public function toGetArRZ($hid, $date)
    {
        return DB::select('SELECT `finances`.`fid`, `finances`.`oid`, `pays`.`pid`, `pays`.`name` AS `pay`, `rooms`.room_no AS `no`, `finances`.confirm AS `time`, `finances`.`remark`, `price` FROM `finances` LEFT JOIN `rooms` ON `finances`.`rid` = `rooms`.`rid` LEFT JOIN `pays` ON `finances`.`pay` = `pays`.`pid` WHERE `finances`.`hid` = ? AND `confirm` > ( SELECT `censors`.`time` FROM `censors` WHERE `hid` = ? AND `date` < ? ORDER BY `cid` DESC LIMIT 1 ) AND `confirm` <= ( SELECT `censors`.`time` FROM `censors` WHERE `hid` = ? AND `date` = ? LIMIT 1 ) AND `pay` IN (SELECT `pid` FROM pays WHERE `hid` = ? AND `type` = 5)', [$hid, $hid, $date, $hid, $date, $hid]);
    }

    public function toGetArGZ($hid, $start, $end)
    {
        return DB::select('SELECT `finances`.`fid`, `finances`.`oid`, `pays`.`pid`, `pays`.`name` AS `pay`, `rooms`.room_no AS `no`, `finances`.confirm AS `time`, `finances`.`remark`, `price` FROM `finances` LEFT JOIN `rooms` ON `finances`.`rid` = `rooms`.`rid` LEFT JOIN `pays` ON `finances`.`pay` = `pays`.`pid` WHERE `finances`.`hid` = ? AND `time` > ? AND `time` < ? AND `pay` IN (SELECT `pid` FROM pays WHERE `hid` = ? AND `type` = 5)', [$hid, $start, $end, $hid]);
    }
}