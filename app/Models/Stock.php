<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Validator;

class Stock extends Model
{
    protected $primaryKey = 'sid';

    public $timestamps = false;

    public function toCount($tid)
    {
        return Stock::where('tid', $tid)
            ->count();
    }

    public function hasEmpty($hid, $tid, $start, $end, $isOrdain = true)
    {
        $start = date('Ymd', strtotime($start));
        $end = date('Ymd', strtotime($end));

        $result = DB::select('SELECT (`stock` - `reserve` - live' . ($isOrdain ? ' - `booking`' : '') . ') AS `last` FROM `stocks` WHERE `tid` = ? AND `hid` = ? AND `date` >= ? AND `date` < ? ORDER BY `last` ASC LIMIT 0,1', [$tid, $hid, $start, $end]);

        $tmpArr = head($result);

        return isset($tmpArr->last) ? $tmpArr->last : 0;
    }

    public function toGetStock($hid, $tid, $start, $end)
    {
        $start = date('Ymd', strtotime($start));
        $end = date('Ymd', strtotime($end));

        return Stock::select('tid', 'stock', 'date')
            ->where('hid', $hid)
            ->where('tid', $tid)
            ->where('date', '>=', $start)
            ->where('date', '<', $end)
            ->get();
    }

    public function init($hid, $tid, $stock, $start, $cycle = 'year')
    {
        $start = strtotime($start);

        $year = date('Y', $start);
        $month = date('m', $start);
        $day = date('d', $start);

        $end = 0;

        if ($cycle == 'month') {

            if ($month < 12) {
                $month += 1;
            } else {
                $year += 1;
                $month = 1;
            }

            $endDate = $year . '-' . $month . '-' . $day . ' 00:00:00';
            $end = strtotime($endDate);

        } else if ($cycle == 'week') {

            $end = $start + 86400 * 7;
        } else {

            $endDate = ($year + 2) . '-' . $month . '-' . $day . ' 00:00:00';
            $end = strtotime($endDate);
        }

        $create = [];

        for ($i = $start; $i < $end; $i += 86400) {
            $arr['hid'] = $hid;
            $arr['tid'] = $tid;
            $arr['stock'] = $stock;
            $arr['date'] = date('Ymd', $i);

            $create[] = $arr;
        }

        return Stock::insert($create);
    }

    public function toFindLast($hid, $tid)
    {
        return Stock::select('date')
            ->where('hid', $hid)
            ->where('tid', $tid)
            ->orderBy('sid', 'DESC')
            ->first();
    }

    public function toIncrementStock($date, $tid)
    {
        return Stock::where('tid', $tid)
            ->where('date', '>=', $date)
            ->increment('stock', 1);
    }

    public function toDecrementStock($hid, $date, $tid, $num = 1)
    {
        return Stock::where('tid', $tid)
            ->where('date', '>=', $date)
            ->where('hid', $hid)
            ->decrement('stock', $num);
    }

    public function toUpdateBooking($hid, $tid, $start, $end, $num)
    {
        $start = date('Ymd', strtotime($start));
        $end = date('Ymd', strtotime($end));

        return Stock::where('tid', $tid)
            ->where('hid', $hid)
            ->where('date', '>=', $start)
            ->where('date', '<', $end)
            ->where(DB::raw('stock - reserve - booking - live - ' . $num), '>=', 0)
            ->increment('booking', $num);

    }

    public function toDecrementBooking($hid, $tid, $start, $end, $num)
    {
        $start = date('Ymd', strtotime($start));
        $end = date('Ymd', strtotime($end));

        return Stock::where('tid', $tid)
            ->where('hid', $hid)
            ->where('date', '>=', $start)
            ->where('date', '<', $end)
            ->where(DB::raw('booking - ' . $num), '>=', 0)
            ->decrement('booking', $num);

    }

    public function toIncrementLive($hid, $tid, $start, $end, $num)
    {
        $start = date('Ymd', strtotime($start));
        $end = date('Ymd', strtotime($end));

        return Stock::where('tid', $tid)
            ->where('hid', $hid)
            ->where('date', '>=', $start)
            ->where('date', '<', $end)
            ->where(DB::raw('stock - reserve - booking - live - ' . $num), '>=', 0)
            ->increment('live', $num);
    }

    public function toDecrementLive($hid, $date, $tid, $num)
    {
        $date = date('Ymd', strtotime($date));

        return Stock::where('tid', $tid)
            ->where('hid', $hid)
            ->where('date', $date)
            ->where(DB::raw('live - ' . $num), '>=', 0)
            ->decrement('live', $num);
    }

    public function toUpdateStock($hid, $tid)
    {
        return DB::update('UPDATE stocks SET stock = 0 WHERE hid = ? AND tid = ?', [$hid, $tid]);
    }

    public function toValidatorStockSearch($tid, $start, $end, $date)
    {

        $input['tid'] = $tid;
        $input['start'] = $start;
        $input['end'] = $end;

        $validator = Validator::make($input, [
            'tid' => 'bail|required|integer',
            'start' => 'bail|required|date|after_or_equal:' . $date,
            'end' => 'bail|required|date|after:start',
        ], [
            'tid.required' => '房型不能为空',
            'tid.integer' => '房型格式错误',
            'start.required' => '开始日期不能为空',
            'start.date' => '开始日期格式错误',
            'start.after' => '开始日期不能小于酒店日期',
            'end.required' => '结束日期格式错误',
            'end.date' => '结束日期格式错误',
            'end.after' => '结束日期不能小于开始日期',
        ]);

        if ($validator->fails()) {

            return [
                'msg' => $validator->errors()->first(),
                'err' => 422,
            ];
        }
    }
}