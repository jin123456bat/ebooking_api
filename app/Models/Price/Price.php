<?php

namespace App\Models\Price;

use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    public function toInsert($input)
    {
        return Price::insert($input);
    }

    public function toCount($hid)
    {
        return Price::where('hid', $hid)
            ->count();
    }

    public function toDelete($hid)
    {
        return Price::where('hid', $hid)
            ->delete();
    }

    public function toUpdateDay($hid, $channel, $vip, $tid, $cid, $price, $date)
    {
        return DB::update('UPDATE `prices` SET `price` = ? WHERE `hid` = ? AND `channel` = ? AND `vip` = ? AND `tid` = ? AND `cid` = ? AND `date` = ?', [
            $price, $hid, $channel, $vip, $tid, $cid, $date
        ]);
    }

    public function toGet($hid, $condition, $start, $end)
    {
        $start = date('Ymd', strtotime($start));
        $end = date('Ymd', strtotime($end));

        return Price::select('channel_name as channel', 'price', 'date')
            ->where('hid', $hid)
            ->where('channel', $condition['channel'])
            ->where('vip', $condition['vip'])
            ->where('tid', $condition['tid'])
            ->where('cid', $condition['cid'])
            ->where('date', '>=', $start)
            ->where('date', '<', $end)
            ->get();
    }

    public function toValidator($input, $date)
    {
        $validator = Validator::make($input, [
            'channel' => 'bail|required|integer',
            'vip' => 'bail|sometimes|integer|min:0',
            'cid' => 'bail|sometimes|integer|min:0',
            'tid' => 'bail|required|integer|min:1',
            'start' => 'bail|required|date|after_or_equal:' . $date,
            'end' => 'bail|required|date|after_or_equal:start',
        ], [
            'channel.required' => '渠道不能为空',
            'channel.integer' => '渠道格式错误',
            'vip.integer' => '会员格式错误',
            'vip.min' => '会员格式错误',
            'cid.integer' => '协议单位格式错误',
            'cid.min' => '协议单位格式错误',
            'tid.required' => '房型不能为空',
            'tid.integer' => '房型格式错误',
            'tid.min' => '房型格式错误',
            'start.required' => '开始日期不能为空',
            'start.date' => '开始日期格式错误',
            'start.after_or_equal' => '开始日期日期不能小于酒店当前日期',
            'end.required' => '结束日期不能为空',
            'end.date' => '结束日期格式错误',
            'end.after_or_equal' => '结束日期不能小于开始日期',
        ]);

        if ($validator->fails()) {

            return [
                'msg' => $validator->errors()->first(),
                'err' => 422,
            ];
        }
    }
}