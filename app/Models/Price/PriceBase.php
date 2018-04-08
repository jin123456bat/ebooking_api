<?php

namespace App\Models\Price;

use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Database\Eloquent\Model;

class PriceBase extends Model
{
    protected $primaryKey = 'pbid';

    protected $table = 'price_base';

    protected $fillable = [
        'hid', 'channel', 'channel_name', 'vip', 'vip_name', 'tid', 'type', 'cid', 'base', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'
    ];

    public function toCreate($input)
    {
        return PriceBase::create([
            'hid' => $input['hid'],
            'channel' => $input['channel'],
            'channel_name' => $input['channel_name'],
            'vip' => $input['vip'],
            'vip_name' => $input['vip_name'],
            'tid' => $input['tid'],
            'type' => $input['type'],
            'cid' => $input['cid'],
            'base' => $input['base'],
            'monday' => $input['monday'],
            'tuesday' => $input['tuesday'],
            'wednesday' => $input['wednesday'],
            'thursday' => $input['thursday'],
            'friday' => $input['friday'],
            'saturday' => $input['saturday'],
            'sunday' => $input['sunday'],
        ]);
    }

    public function toDelete($pbid, $hid)
    {
        return PriceBase::where('hid', $hid)
            ->where('pbid', $pbid)
            ->delete();
    }

    public function toUpdate($base, $input)
    {
        $base->base = $input['base'];
        $base->monday = $input['monday'];
        $base->tuesday = $input['tuesday'];
        $base->wednesday = $input['wednesday'];
        $base->thursday = $input['thursday'];
        $base->friday = $input['friday'];
        $base->saturday = $input['saturday'];
        $base->sunday = $input['sunday'];

        $base->save();
    }

    public function toFind($condition, $hid)
    {
        $result = DB::select('SELECT `pbid`, `base`, `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`, `sunday` FROM `price_base` WHERE `hid` = ? AND `channel` = ? AND `vip` = ? AND `tid` = ? AND `cid` = ?', [
            $hid, $condition['channel'], $condition['vip'], $condition['tid'], $condition['cid']
        ]);
//        return $result;
        return empty($result) ? null : head($result);
    }

    public function toSimpleFind($pbid, $hid)
    {
        return PriceBase::select('pbid', 'channel_name', 'vip_name', 'type', 'cid', 'base', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')
            ->where('pbid', $pbid)
            ->where('hid', $hid)
            ->first();
    }

    public function toGet($hid, $isAll, $num = 0, $page = 0)
    {
        if ($isAll) {
            return DB::select('SELECT `pbid`, `channel` AS `key`, `channel_name` AS `channel`, `vip` AS `lv`, `vip_name` AS `vip`, `tid`, `type`, `cid`, `base`, `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`, `sunday` FROM `price_base` WHERE `hid` = ?', [$hid]);
        } else {
            return PriceBase::select('pbid', 'channel_name as channel', 'vip_name as vip', 'type', 'companies.name as company', 'base')
                ->leftJoin('companies', 'price_base.cid', '=', 'companies.cid')
                ->where('price_base.hid', $hid)
                ->skip($num * $page)
                ->take($num)
                ->get();
        }
    }

    public function toCount($hid)
    {
        return PriceBase::where('hid', $hid)
            ->count();
    }

    public function toValidator($input)
    {
        $validator = Validator::make($input, [
            'channel' => 'bail|required|integer',
            'vip' => 'bail|sometimes|integer|min:0',
            'cid' => 'bail|sometimes|integer|min:0',
            'tid' => 'bail|required|integer|min:1',
            'base' => 'bail|required|integer|min:1',
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
            'base.required' => '价格不能为空',
            'base.integer' => '价格格式错误',
            'base.min' => '价格最低为 1',
        ]);

        if ($validator->fails()) {

            return [
                'msg' => $validator->errors()->first(),
                'err' => 422,
            ];
        }
    }

    public function toUniquePrice($input, $pbid = 0, $isCreate = true)
    {
        $num = PriceBase::where('hid')
            ->where('channel', $input['channel'])
            ->where('vip', $input['vip'])
            ->where('cid', $input['cid'])
            ->where('tid', $input['tid'])
            ->where('pbid', ($isCreate ? '<>' : '='), $pbid)
            ->count();

        if (empty($num)) {
            return false;
        } else {
            return true;
        }
    }

}