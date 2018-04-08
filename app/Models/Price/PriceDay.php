<?php

namespace App\Models\Price;

use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Database\Eloquent\Model;

class PriceDay extends Model
{

    protected $primaryKey = 'pdid';

    protected $table = 'price_day';

    protected $fillable = [
        'hid', 'channel', 'channel_name', 'vip', 'vip_name', 'tid', 'type', 'cid', 'price', 'date'
    ];

    protected $hidden = [];

    public function toCreate($input)
    {
        return PriceDay::create([
            'hid' => $input['hid'],
            'channel' => $input['channel'],
            'channel_name' => $input['channel_name'],
            'vip' => $input['vip'],
            'vip_name' => $input['vip_name'],
            'tid' => $input['tid'],
            'type' => $input['type'],
            'cid' => $input['cid'],
            'price' => $input['price'],
            'date' => $input['date'],
        ]);
    }

    public function toDelete($pdid, $hid)
    {
        return PriceDay::where('hid', $hid)
            ->where('pdid', $pdid)
            ->delete();
    }

    public function toUpdate($day, $input)
    {
        $day->price = $input['price'];

        $day->save();
    }

    public function toSimpleFind($pdid, $hid)
    {
        return PriceDay::select('pdid', 'channel_name', 'vip_name', 'type', 'cid', 'price')
            ->where('pdid', $pdid)
            ->where('hid', $hid)
            ->first();
    }

    public function toFind($hid, $channel, $vip, $tid, $cid)
    {
        return PriceDay::select('price', 'date')
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
            return PriceDay::select('pdid', 'channel as key', 'channel_name as channel', 'vip as lv', 'vip_name as vip', 'tid', 'type', 'companies.cid', 'price')
                ->leftJoin('companies', 'price_day.cid', '=', 'companies.cid')
                ->where('price_day.hid', $hid)
                ->get();
        } else {
            return PriceDay::select('pdid', 'channel_name as channel', 'vip_name as vip', 'type', 'companies.name as company', 'price')
                ->leftJoin('companies', 'price_day.cid', '=', 'companies.cid')
                ->where('price_day.hid', $hid)
                ->skip($num * $page)
                ->take($num)
                ->get();
        }
    }

    public function toCount($hid)
    {
        return PriceDay::where('hid', $hid)
            ->count();
    }

    public function toFindPrice($hid, $condition, $date)
    {
        $result = DB::select('SELECT `pdid`, `price`, `date` FROM `price_day` WHERE `hid` = ? AND `channel` = ? AND `vip` = ? AND `tid` = ? AND `cid` = ? AND `date` = ?',
            [
                $hid,
                $condition['channel'],
                $condition['vip'],
                $condition['tid'],
                $condition['cid'],
                $date
            ]
        );
        return head($result);
    }

    public function toUpdatePrice($hid, $pdid, $price)
    {
        return DB::update('UPDATE price_day SET price = ? WHERE hid = ? AND pdid = ?', [$price, $hid, $pdid]);
    }

    public function toValidator($input, $date)
    {
        $validator = Validator::make($input, [
            'channel' => 'bail|required|integer',
            'vip' => 'bail|sometimes|integer|min:0',
            'cid' => 'bail|sometimes|integer|min:0',
            'tid' => 'bail|required|integer|min:1',
            'price' => 'bail|required|integer|min:1',
            'date' => 'bail|required|date|after_or_equal:' . $date,
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
            'date.required' => '日期不能为空',
            'date.date' => '日期格式错误',
            'date.after_or_equal' => '价格日期不能小于酒店当前日期',
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
        $num = PriceDay::where('hid', $input['hid'])
            ->where('channel', $input['channel'])
            ->where('vip', $input['vip'])
            ->where('cid', $input['cid'])
            ->where('tid', $input['tid'])
            ->where('date', $input['date'])
            ->count();

        if (empty($num)) {
            return false;
        } else {
            return true;
        }
    }
}