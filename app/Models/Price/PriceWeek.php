<?php

namespace App\Models\Price;

use Validator;
use Illuminate\Database\Eloquent\Model;

class PriceWeek extends Model
{
    protected $primaryKey = 'pwid';

    protected $table = 'price_week';

    protected $fillable = [
        'hid', 'channel', 'channel_name', 'vip', 'vip_name', 'tid', 'type', 'cid', 'price', 'week'
    ];

    protected $hidden = [];

    public function toCreate($input)
    {
        return PriceWeek::create([
            'hid' => $input['hid'],
            'channel' => $input['channel'],
            'channel_name' => $input['channel_name'],
            'vip' => $input['vip'],
            'vip_name' => $input['vip_name'],
            'tid' => $input['tid'],
            'type' => $input['type'],
            'cid' => $input['cid'],
            'price' => $input['price'],
            'week' => $input['week'],
        ]);
    }

    public function toDelete($pwid, $hid)
    {
        return PriceWeek::where('hid', $hid)
            ->where('pwid', $pwid)
            ->delete();
    }

    public function toUpdate($week, $input)
    {
        $week->price = $input['price'];

        $week->save();
    }

    public function toSimpleFind($pwid, $hid)
    {
        return PriceWeek::select('pwid', 'channel_name', 'vip_name', 'type', 'cid', 'price')
            ->where('pwid', $pwid)
            ->where('hid', $hid)
            ->first();
    }

    public function toFind($hid, $channel, $vip, $tid, $cid)
    {
        return PriceWeek::select('price', 'week')
            ->where('hid', $hid)
            ->where('channel', $channel)
            ->where('vip', $vip)
            ->where('tid', $tid)
            ->where('cid', $cid)
            ->get();
    }

    public function toGet($hid, $isAll, $num = 0, $page = 0)
    {
        if ($isAll) {
            return PriceWeek::select('pwid', 'channel as key', 'channel_name as channel', 'vip as lv', 'vip_name as vip', 'tid', 'type', 'companies.cid', 'companies.name as company', 'price')
                ->leftJoin('companies', 'price_week.cid', '=', 'companies.cid')
                ->where('price_week.hid', $hid)
                ->get();
        } else {
            return PriceWeek::select('pwid', 'channel_name as channel', 'vip_name as vip', 'type', 'companies.name as company', 'price')
                ->leftJoin('companies', 'price_week.cid', '=', 'companies.cid')
                ->where('price_week.hid', $hid)
                ->skip($num * $page)
                ->take($num)
                ->get();
        }
    }

    public function toCount($hid)
    {
        return PriceWeek::where('hid', $hid)
            ->count();
    }

    public function toValidator($input)
    {
        $validator = Validator::make($input, [
            'channel' => 'bail|required|integer',
            'vip' => 'bail|sometimes|integer|min:0',
            'cid' => 'bail|sometimes|integer|min:0',
            'tid' => 'bail|required|integer|min:1',
            'price' => 'bail|required|integer|min:1',
            'week' => 'bail|required|in: ' . implode(',',
                    [1, 2, 3, 4, 5, 6, 7]
                ),
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
            'price.required' => '价格不能为空',
            'price.integer' => '价格格式错误',
            'price.min' => '价格最低为 1',
            'week.required' => '星期不能为空',
            'week.in' => '星期格式错误',
        ]);

        if ($validator->fails()) {

            return [
                'msg' => $validator->errors()->first(),
                'err' => 422,
            ];
        }
    }

    public function toUniquePrice($input)
    {
        $num = PriceWeek::where('hid', $input['hid'])
            ->where('channel', $input['channel'])
            ->where('vip', $input['vip'])
            ->where('cid', $input['cid'])
            ->where('tid', $input['tid'])
            ->where('week', $input['week'])
            ->count();

        if (empty($num)) {
            return false;
        } else {
            return true;
        }
    }
}